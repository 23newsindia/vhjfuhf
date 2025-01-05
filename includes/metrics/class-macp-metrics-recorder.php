<?php
/**
 * Handles recording of cache metrics
 */
class MACP_Metrics_Recorder {
    private $metrics_key = 'macp_cache_metrics';

    public function record_hit($type) {
        $this->increment_metric($type . '_hits');
    }

    public function record_miss($type) {
        $this->increment_metric($type . '_misses');
    }

    private function increment_metric($key) {
        $metrics = get_option($this->metrics_key, []);
        $metrics[$key] = ($metrics[$key] ?? 0) + 1;
        $metrics['total_requests'] = ($metrics['total_requests'] ?? 0) + 1;
        update_option($this->metrics_key, $metrics, false); // Don't autoload these metrics
    }

    public function reset_metrics() {
        delete_option($this->metrics_key);
    }
}