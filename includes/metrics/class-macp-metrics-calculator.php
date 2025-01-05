<?php
class MACP_Metrics_Calculator {
    private $metrics_key = 'macp_cache_metrics';

    public function get_hit_rate($cache_type) {
        $metrics = get_option($this->metrics_key, []);
        $hits = isset($metrics["{$cache_type}_hits"]) ? (int)$metrics["{$cache_type}_hits"] : 0;
        $misses = isset($metrics["{$cache_type}_misses"]) ? (int)$metrics["{$cache_type}_misses"] : 0;
        
        $total = $hits + $misses;
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    public function get_all_metrics() {
        $metrics = get_option($this->metrics_key, []);
        
        return [
            'html_cache' => [
                'hit_rate' => $this->get_hit_rate('html'),
                'hits' => (int)($metrics['html_hits'] ?? 0),
                'misses' => (int)($metrics['html_misses'] ?? 0)
            ],
            'redis_cache' => [
                'hit_rate' => $this->get_hit_rate('redis'),
                'hits' => (int)($metrics['redis_hits'] ?? 0),
                'misses' => (int)($metrics['redis_misses'] ?? 0)
            ],
            'total_requests' => (int)($metrics['total_requests'] ?? 0)
        ];
    }
}