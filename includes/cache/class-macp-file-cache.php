<?php
class MACP_File_Cache {
    private $cache_dir;
    
    public function __construct() {
        $this->cache_dir = WP_CONTENT_DIR . '/cache/macp/';
        $this->ensure_cache_directory();
    }

    private function ensure_cache_directory() {
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
            file_put_contents($this->cache_dir . 'index.php', '<?php // Silence is golden');
        }
    }

    public function get($key) {
        $file = $this->get_cache_file($key);
        if (!file_exists($file)) return false;
        
        // Check if cache is still valid
        if ($this->is_cache_valid($file)) {
            return file_get_contents($file);
        }
        
        unlink($file);
        return false;
    }

    public function set($key, $content) {
        $file = $this->get_cache_file($key);
        $temp_file = $file . '.tmp';
        
        if (file_put_contents($temp_file, $content) && rename($temp_file, $file)) {
            chmod($file, 0644);
            return true;
        }
        return false;
    }

    private function get_cache_file($key) {
        return $this->cache_dir . md5($key) . '.html';
    }

    private function is_cache_valid($file) {
        $max_age = get_option('macp_cache_ttl', 3600);
        return (time() - filemtime($file)) < $max_age;
    }
}