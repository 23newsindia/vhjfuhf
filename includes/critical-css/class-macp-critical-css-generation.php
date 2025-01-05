<?php
class MACP_Critical_CSS_Generation {
    private $css_fetcher;
    private $css_processor;
    private $filesystem;
    private $cache_dir;

    public function __construct() {
        $this->css_fetcher = new MACP_CSS_Fetcher();
        $this->css_processor = new MACP_CSS_Processor();
        $this->filesystem = new MACP_Filesystem();
        $this->cache_dir = WP_CONTENT_DIR . '/cache/macp/critical-css/';
        
        $this->init();
    }

    private function init() {
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
    }

    public function generate($url, $type = 'default') {
        // Generate cache key from URL
        $key = md5($url);
        $file_path = $this->cache_dir . $key . '.css';

        // Fetch CSS from the page
        $css = $this->css_fetcher->fetch_page_css($url);
        if (!$css) {
            return false;
        }

        // Process and optimize the CSS
        $critical_css = $this->css_processor->process($css);
        if (empty($critical_css)) {
            return false;
        }

        // Save to file
        if ($this->filesystem->write_file($file_path, $critical_css)) {
            // Store mapping for future reference
            $this->store_mapping($key, [
                'url' => $url,
                'type' => $type,
                'generated' => current_time('mysql')
            ]);
            return true;
        }

        return false;
    }

    private function store_mapping($key, $data) {
        $mappings = get_option('macp_critical_css_mappings', []);
        $mappings[$key] = $data;
        update_option('macp_critical_css_mappings', $mappings);
    }
}