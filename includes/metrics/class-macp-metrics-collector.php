<?php
/**
 * Handles collection of cache metrics
 */
class MACP_Metrics_Collector {
    private $redis;
    private $metrics_key = 'macp_cache_metrics';
    
    public function __construct(MACP_Redis $redis) {
        $this->redis = $redis;
    }

    public function record_hit($cache_type) {
        $this->increment_metric($cache_type . '_hits');
    }

    public function record_miss($cache_type) {
        $this->increment_metric($cache_type . '_misses');
    }

    private function increment_metric($metric) {
        if (!$this->redis) return;
        
        $this->redis->hincrby($this->metrics_key, $metric, 1);
        $this->redis->hincrby($this->metrics_key, 'total_requests', 1);
    }
}