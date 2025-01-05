<?php
class MACP_CSS_Optimizer {
    private $minifier;
    private $unused_processor;
    private $filesystem;
    private $excluded_patterns;
    private $cache_dir;

    public function __construct() {
        $this->minifier = new MACP_CSS_Minifier_Processor();
        $this->unused_processor = new MACP_Unused_CSS_Processor();
        $this->filesystem = new MACP_Filesystem();
        $this->excluded_patterns = MACP_CSS_Config::get_excluded_patterns();
        $this->cache_dir = WP_CONTENT_DIR . '/cache/min/';
        
        add_filter('style_loader_tag', [$this, 'process_stylesheet'], 10, 4);
    }
  
  
  public function test_unused_css($url) {
    // Get the page HTML
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        throw new Exception('Failed to fetch URL: ' . $response->get_error_message());
    }

    $html = wp_remote_retrieve_body($response);
    if (empty($html)) {
        throw new Exception('Empty response from URL');
    }

    // Extract all CSS files
    $css_files = $this->extract_css_files($html);
    $results = [];

    foreach ($css_files as $css_file) {
        try {
            // Get original CSS content
            $original_css = $this->get_stylesheet_content($css_file);
            if (!$original_css) {
                continue;
            }

            $original_size = strlen($original_css);

            // Process CSS
            $optimized_css = $this->unused_processor->process($original_css, $html);
            $optimized_size = strlen($optimized_css);

            $results[] = [
                'file' => $css_file,
                'originalSize' => $original_size,
                'optimizedSize' => $optimized_size,
                'success' => true
            ];
        } catch (Exception $e) {
            $results[] = [
                'file' => $css_file,
                'originalSize' => 0,
                'optimizedSize' => 0,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    return $results;
}

private function extract_css_files($html) {
    $css_files = [];
    
    // Match all CSS file links
    if (preg_match_all('/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\']/', $html, $matches)) {
        $css_files = $matches[1];
    }

    // Convert relative URLs to absolute
    foreach ($css_files as &$file) {
        if (strpos($file, '//') === 0) {
            $file = 'https:' . $file;
        } elseif (strpos($file, '/') === 0) {
            $file = home_url($file);
        }
    }

    return array_unique($css_files);
}
  
  
  
  
  
    public function process_stylesheet($tag, $handle, $href, $media) {
        if (!$this->should_process($href)) {
            return $tag;
        }

        // Get the stylesheet content
        $css_content = $this->get_stylesheet_content($href);
        if (!$css_content) {
            return $tag;
        }

        // Generate cache key and paths
        $cache_key = md5($href . filemtime($this->get_local_path($href)));
        $cache_file = $this->cache_dir . $cache_key . '.css';
        $cache_url = content_url('cache/min/' . $cache_key . '.css');

        // Process CSS if not cached
        if (!file_exists($cache_file)) {
            // Step 1: Remove unused CSS if enabled
            if (get_option('macp_remove_unused_css', 0)) {
                $html = $this->get_page_html();
                $css_content = $this->unused_processor->process($css_content, $html);
            }

            // Step 2: Minify if enabled
            if (get_option('macp_minify_css', 0)) {
                $css_content = $this->minifier->process($css_content);
            }

            // Save processed CSS
            if (!$this->filesystem->write_file($cache_file, $css_content)) {
                return $tag;
            }
        }

        // Replace original URL with cached version
        return str_replace(
            $href, 
            $cache_url, 
            str_replace('<link', '<link data-minify="1"', $tag)
        );
    }

    private function should_process() {
        if (is_admin() || is_customize_preview()) {
            return false;
        }

        if (!get_option('macp_remove_unused_css', 0)) {
            return false;
        }

        return true;
    }

    private function get_stylesheet_content($url) {
        $local_path = $this->get_local_path($url);
        if ($local_path && file_exists($local_path)) {
            return file_get_contents($local_path);
        }

        $response = wp_remote_get($url);
        if (!is_wp_error($response)) {
            return wp_remote_retrieve_body($response);
        }

        return false;
    }

    private function get_local_path($url) {
        $site_url = trailingslashit(site_url());
        $content_url = trailingslashit(content_url());
        
        if (strpos($url, $site_url) === 0) {
            return str_replace($site_url, ABSPATH, $url);
        } elseif (strpos($url, $content_url) === 0) {
            return str_replace($content_url, WP_CONTENT_DIR . '/', $url);
        }
        
        return false;
    }
  

    private function get_page_html() {
        ob_start();
        if (is_singular()) {
            the_post();
        }
        get_header();
        get_template_part('template-parts/content', get_post_type());
        get_footer();
        $html = ob_get_clean();
        
        return $html;
    }
  
  
  private function should_process($href) {
        if (is_admin() || is_customize_preview()) {
            return false;
        }

        if (!get_option('macp_remove_unused_css', 0) && !get_option('macp_minify_css', 0)) {
            return false;
        }

        foreach ($this->excluded_patterns as $pattern) {
            if (strpos($href, $pattern) !== false) {
                return false;
            }
        }

        return true;
    }
  
  

    private function filter_css($css, $used_selectors) {
        $filtered = '';
        $safelist = MACP_CSS_Config::get_safelist();
        
        // Split CSS into rules
        preg_match_all('/([^{]+){[^}]*}/s', $css, $matches);
        
        foreach ($matches[0] as $rule) {
            $selectors = trim(preg_replace('/\s*{.*$/s', '', $rule));
            $selectors = explode(',', $selectors);
            
            foreach ($selectors as $selector) {
                $selector = trim($selector);
                
                // Keep if in safelist
                foreach ($safelist as $safe_pattern) {
                    if (fnmatch($safe_pattern, $selector)) {
                        $filtered .= $rule . "\n";
                        continue 2;
                    }
                }
                
                // Keep if used in HTML
                if ($this->is_selector_used($selector, $used_selectors)) {
                    $filtered .= $rule . "\n";
                    break;
                }
            }
        }
        
        return $filtered;
    }

    private function is_selector_used($selector, $used_selectors) {
        // Always keep essential selectors
        if (in_array($selector, ['html', 'body', '*'])) {
            return true;
        }

        // Keep @-rules
        if (strpos($selector, '@') === 0) {
            return true;
        }

        // Check if selector matches any used selectors
        foreach ($used_selectors as $used_selector) {
            if (strpos($used_selector, $selector) !== false) {
                return true;
            }
        }

        return false;
    }
}