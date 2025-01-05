<?php
class MACP_Cache_Metrics {
    private $metrics = [];
    
    public function record_hit($type) {
        $this->increment_metric("hits_$type");
    }
    
    public function record_miss() {
        $this->increment_metric('misses');
    }
    
    private function increment_metric($key) {
        $metrics = get_option('macp_cache_metrics', []);
        $metrics[$key] = ($metrics[$key] ?? 0) + 1;
        update_option('macp_cache_metrics', $metrics);
    }

    public function save_metrics() {
        if (!empty($this->metrics)) {
            update_option('macp_cache_metrics', $this->metrics);
        }
    }

    public function get_hit_rate() {
        $metrics = get_option('macp_cache_metrics', []);
        $hits = array_sum(array_filter($metrics, fn($key) => strpos($key, 'hits_') === 0, ARRAY_FILTER_USE_KEY));
        $total = $hits + ($metrics['misses'] ?? 0);
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }
}