<?php
class MACP_Redis {
    private $connection;
    private $metrics_recorder;
    private $compression_threshold = 1024;
    private $batch_queue = [];
    private $batch_size = 50;

    public function __construct() {
        $this->connection = MACP_Redis_Connection::get_instance();
        $this->metrics_recorder = new MACP_Metrics_Recorder();
    }

    public function get($key) {
        if (!$this->is_available()) {
            $this->metrics_recorder->record_miss('redis');
            return false;
        }

        try {
            $value = $this->connection->get_redis()->get("macp:$key");
            if ($value !== false) {
                $this->metrics_recorder->record_hit('redis');
                return $this->decompress($value);
            }
        } catch (Exception $e) {
            error_log('Redis get error: ' . $e->getMessage());
        }

        $this->metrics_recorder->record_miss('redis');
        return false;
    }

    public function keys($pattern) {
        if (!$this->is_available()) {
            $this->metrics_recorder->record_miss('redis');
            return $this->connection->get_redis()->keys("macp:$pattern");
        }

        try {
            $keys = $this->connection->get_redis()->keys("macp:$pattern");
            $this->metrics_recorder->record_hit('redis');
            return $keys;
        } catch (Exception $e) {
            error_log('Redis keys error: ' . $e->getMessage());
            $this->metrics_recorder->record_miss('redis');
            return [];
        }
    }

    public function queue_set($key, $value, $ttl = 3600) {
        $this->batch_queue[] = [
            'key' => $key,
            'value' => $this->compress($value),
            'ttl' => $ttl
        ];

        if (count($this->batch_queue) >= $this->batch_size) {
            return $this->flush_queue();
        }

        return true;
    }

    public function flush_queue() {
        if (!$this->is_available() || empty($this->batch_queue)) {
            return false;
        }

        try {
            $pipeline = $this->connection->get_redis()->multi(Redis::PIPELINE);
            
            foreach ($this->batch_queue as $item) {
                $pipeline->setex(
                    "macp:{$item['key']}", 
                    $item['ttl'], 
                    $item['value']
                );
            }

            $pipeline->exec();
            $this->batch_queue = [];
            return true;
        } catch (Exception $e) {
            error_log('Redis pipeline error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_pattern($pattern) {
        if (!$this->is_available()) {
            return false;
        }

        try {
            $keys = $this->keys($pattern);
            if (!empty($keys)) {
                return $this->connection->get_redis()->del($keys);
            }
            return true;
        } catch (Exception $e) {
            error_log('Redis delete pattern error: ' . $e->getMessage());
            return false;
        }
    }

    private function compress($data) {
        if (strlen($data) < $this->compression_threshold) {
            return $data;
        }
        return gzcompress($data, 9);
    }

    private function decompress($data) {
        if ($this->is_compressed($data)) {
            return gzuncompress($data);
        }
        return $data;
    }

    private function is_compressed($data) {
        return substr($data, 0, 2) === "\x78\x9c";
    }

    public function is_available() {
        return $this->connection->is_connected();
    }
}