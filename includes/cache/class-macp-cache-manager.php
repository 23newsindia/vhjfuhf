<?php
class MACP_Cache_Manager {
    private $redis_cache;
    private $file_cache;
    private $cache_headers;
    private $cache_metrics;
    private $metrics_key = 'macp_cache_metrics';

    public function __construct() {
        $this->redis_cache = new MACP_Redis_Cache();
        $this->file_cache = new MACP_File_Cache();
        $this->cache_headers = new MACP_Cache_Headers();
        $this->cache_metrics = new MACP_Cache_Metrics();
        
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('template_redirect', [$this->cache_headers, 'set_headers']);
        add_action('shutdown', [$this->cache_metrics, 'save_metrics']);
    }

    public function get_cached_content($key) {
        // Try Redis first (fastest)
        $content = $this->redis_cache->get($key);
        if ($content) {
            $this->record_hit('redis');
            return $content;
        }

        // Try file cache
        $content = $this->file_cache->get($key);
        if ($content) {
            $this->record_hit('html');
            // Repopulate Redis
            $this->redis_cache->set($key, $content);
            return $content;
        }

        $this->record_miss();
        return false;
    }

    public function set_cached_content($key, $content) {
        // Save to both caches
        $this->redis_cache->set($key, $content);
        $this->file_cache->set($key, $content);
    }

    private function record_hit($type) {
        $metrics = get_option($this->metrics_key, []);
        $metrics["{$type}_hits"] = ($metrics["{$type}_hits"] ?? 0) + 1;
        $metrics['total_requests'] = ($metrics['total_requests'] ?? 0) + 1;
        update_option($this->metrics_key, $metrics);
    }

    private function record_miss() {
        $metrics = get_option($this->metrics_key, []);
        $metrics['html_misses'] = ($metrics['html_misses'] ?? 0) + 1;
        $metrics['redis_misses'] = ($metrics['redis_misses'] ?? 0) + 1;
        $metrics['total_requests'] = ($metrics['total_requests'] ?? 0) + 1;
        update_option($this->metrics_key, $metrics);
    }
}