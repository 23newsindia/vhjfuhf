<?php
class MACP_Redis_Primer {
    private $redis;
    
    public function __construct(MACP_Redis $redis) {
        $this->redis = $redis;
    }

    public function prime_cache() {
        if (!$this->redis->is_available()) {
            return;
        }

        try {
            $cache_dir = WP_CONTENT_DIR . '/cache/macp/';
            if (!is_dir($cache_dir)) {
                return;
            }

            $files = glob($cache_dir . '*.html');
            if (!is_array($files)) {
                return;
            }

            foreach ($files as $file) {
                $key = basename($file, '.html');
                $content = @file_get_contents($file);
                if ($content !== false) {
                    $this->redis->set($key, $content);
                }
            }
        } catch (Exception $e) {
            error_log('Redis cache priming failed: ' . $e->getMessage());
        }
    }
}