<?php
class MACP_CSS_Minifier_Processor {
    private $cache_dir;
    private $filesystem;

    public function __construct() {
        $this->cache_dir = WP_CONTENT_DIR . '/cache/min/';
        $this->filesystem = new MACP_Filesystem();
        $this->ensure_cache_directory();
    }

    private function ensure_cache_directory() {
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
    }

    public function process($css_content) {
        // Remove comments
        $css_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_content);
        
        // Remove whitespace
        $css_content = preg_replace('/\s+/', ' ', $css_content);
        $css_content = preg_replace('/\s*([\{\};:,])\s*/', '$1', $css_content);
        
        return trim($css_content);
    }
}