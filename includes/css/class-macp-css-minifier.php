<?php
class MACP_CSS_Minifier {
    private $cache_dir;
    private $minifier;

    public function __construct() {
        $this->cache_dir = WP_CONTENT_DIR . '/cache/min/';
        $this->ensure_cache_directory();
        $this->minifier = new MatthiasMullie\Minify\CSS();
    }

    private function ensure_cache_directory() {
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
            // Create .htaccess to ensure direct file access
            file_put_contents($this->cache_dir . '.htaccess', 
                "Options -Indexes\n" .
                "<IfModule mod_headers.c>\n" .
                "    Header set Cache-Control 'max-age=31536000, public'\n" .
                "</IfModule>"
            );
            file_put_contents($this->cache_dir . 'index.php', '<?php // Silence is golden');
        }
    }

    public function process_stylesheet($tag) {
        // Skip if already minified
        if (strpos($tag, 'data-minify="1"') !== false) {
            return $tag;
        }

        if (!preg_match('/\shref=[\'"]([^\'"]+)[\'"]/', $tag, $matches)) {
            return $tag;
        }

        $url = $matches[1];
        $path = $this->get_file_path($url);
        
        if (!$path || !file_exists($path)) {
            return $tag;
        }

        // Generate cache key based on file path and modification time
        $cache_key = md5($path . filemtime($path));
        $cached_file = $this->cache_dir . $cache_key . '.css';

        // Create minified version if it doesn't exist
        if (!file_exists($cached_file)) {
            try {
                $css_content = file_get_contents($path);
                if ($css_content === false) {
                    return $tag;
                }

                $this->minifier->add($css_content);
                $minified_content = $this->minifier->minify();
                
                if (file_put_contents($cached_file, $minified_content) === false) {
                    return $tag;
                }

                // Set proper permissions
                chmod($cached_file, 0644);
            } catch (Exception $e) {
                error_log('MACP CSS Minification Error: ' . $e->getMessage());
                return $tag;
            }
        }

        // Get the URL for the cached file
        $cached_url = content_url('cache/min/' . basename($cached_file));
        
        // Replace the original URL with the cached one
        $new_tag = str_replace($url, $cached_url, $tag);
        $new_tag = str_replace('<link', '<link data-minify="1"', $new_tag);

        return $new_tag;
    }

    private function get_file_path($url) {
        // Handle protocol-relative URLs
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        // Convert URL to local path
        $site_url = trailingslashit(site_url());
        $content_url = trailingslashit(content_url());
        
        if (strpos($url, $site_url) === 0) {
            return str_replace($site_url, ABSPATH, $url);
        } elseif (strpos($url, $content_url) === 0) {
            return str_replace($content_url, WP_CONTENT_DIR . '/', $url);
        }
        
        return false;
    }
}