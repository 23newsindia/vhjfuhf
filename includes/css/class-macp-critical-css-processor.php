<?php
/**
 * Handles the actual critical CSS generation process
 */
class MACP_Critical_CSS_Processor {
    private $filesystem;
    private $critical_css_path;

    public function __construct($filesystem) {
        $this->filesystem = $filesystem;
        $this->critical_css_path = WP_CONTENT_DIR . '/cache/macp/critical-css/';
    }

    public function generate($url, $path, $params = []) {
        if (!$this->ensure_directory()) {
            return new WP_Error(
                'cpcss_generation_failed',
                __('Critical CSS directory is not writable', 'my-advanced-cache-plugin')
            );
        }

        $css = $this->get_critical_css($url, $params);
        
        if (is_wp_error($css)) {
            return $css;
        }

        $file_path = $this->critical_css_path . $path;
        $result = $this->filesystem->put_contents($file_path, $css);

        if (!$result) {
            return new WP_Error(
                'cpcss_generation_failed',
                sprintf(
                    /* translators: %s is the file path */
                    __('Could not write critical CSS to file: %s', 'my-advanced-cache-plugin'),
                    $file_path
                )
            );
        }

        return [
            'code' => 'generation_successful',
            'message' => sprintf(
                /* translators: %s is the path type */
                __('Successfully generated critical CSS for %s', 'my-advanced-cache-plugin'),
                $params['item_type']
            )
        ];
    }

    private function get_critical_css($url, $params) {
        // Get all CSS files from the URL
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'cpcss_generation_failed',
                sprintf(
                    /* translators: %s is the URL */
                    __('Could not fetch URL: %s', 'my-advanced-cache-plugin'),
                    $url
                )
            );
        }

        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            return new WP_Error(
                'cpcss_generation_failed',
                __('Empty response from URL', 'my-advanced-cache-plugin')
            );
        }

        // Extract and process CSS
        $critical_css = $this->extract_critical_css($html, $params);
        
        if (is_wp_error($critical_css)) {
            return $critical_css;
        }

        return $critical_css;
    }

    private function extract_critical_css($html, $params) {
        // Create DOM document
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        // Get all CSS links and inline styles
        $links = $dom->getElementsByTagName('link');
        $styles = $dom->getElementsByTagName('style');
        
        $critical_css = '';

        // Process external stylesheets
        foreach ($links as $link) {
            if ($link->getAttribute('rel') !== 'stylesheet') {
                continue;
            }

            $href = $link->getAttribute('href');
            if (empty($href)) {
                continue;
            }

            $css_content = $this->get_external_css($href);
            if (!is_wp_error($css_content)) {
                $critical_css .= $this->process_css($css_content, $params);
            }
        }

        // Process inline styles
        foreach ($styles as $style) {
            $critical_css .= $this->process_css($style->nodeValue, $params);
        }

        if (empty($critical_css)) {
            return new WP_Error(
                'cpcss_generation_failed',
                __('No CSS content found', 'my-advanced-cache-plugin')
            );
        }

        return $critical_css;
    }

    private function get_external_css($url) {
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        } elseif (strpos($url, '/') === 0) {
            $url = home_url($url);
        }

        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return $response;
        }

        return wp_remote_retrieve_body($response);
    }

    private function process_css($css, $params) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove media queries if mobile
        if (!empty($params['is_mobile'])) {
            $css = preg_replace('/@media\s+[^{]+\{([^{}]*\{[^{}]*\})*[^{}]*\}/i', '', $css);
        }

        return $css;
    }

    private function ensure_directory() {
        if (!$this->filesystem->is_dir($this->critical_css_path)) {
            if (!$this->filesystem->mkdir($this->critical_css_path)) {
                return false;
            }
        }

        return $this->filesystem->is_writable($this->critical_css_path);
    }
}