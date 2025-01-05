<?php
/**
 * Handles layered caching with Redis as primary and file as secondary
 */
class MACP_Layered_Cache {
    private $redis;
    private $file_cache;
    private $metrics_recorder;
    private $default_ttl = 3600;

    public function __construct() {
        $this->redis = new MACP_Redis();
        $this->file_cache = new MACP_File_Cache();
        $this->metrics_recorder = new MACP_Metrics_Recorder();
    }

    public function get($key) {
        // Try Redis first (fastest)
        $content = $this->get_from_redis($key);
        if ($content !== false) {
            return $content;
        }

        // Try file cache as fallback
        $content = $this->get_from_file($key);
        if ($content !== false) {
            // Repopulate Redis cache
            $this->set($key, $content);
            return $content;
        }

        return false;
    }

    public function set($key, $content, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        
        // Set in Redis
        $redis_success = $this->redis->set($key, $content, $ttl);
        
        // Set in file cache
        $file_success = $this->file_cache->set($key, $content);
        
        return $redis_success && $file_success;
    }

    private function get_from_redis($key) {
        $content = $this->redis->get($key);
        if ($content !== false) {
            $this->metrics_recorder->record_hit('redis');
            return $content;
        }
        $this->metrics_recorder->record_miss('redis');
        return false;
    }

    private function get_from_file($key) {
        $content = $this->file_cache->get($key);
        if ($content !== false) {
            $this->metrics_recorder->record_hit('html');
            return $content;
        }
        $this->metrics_recorder->record_miss('html');
        return false;
    }

    public function delete($key) {
        $this->redis->delete($key);
        $this->file_cache->delete($key);
    }

    public function flush() {
        $this->redis->flush();
        $this->file_cache->flush();
    }
}