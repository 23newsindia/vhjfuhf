<?php
class MACP_Redis_Status {
    private $connection;

    public function __construct() {
        $this->connection = MACP_Redis_Connection::get_instance();
    }

    public function get_status() {
        return [
            'available' => $this->connection->is_connected(),
            'error' => $this->connection->get_last_error(),
            'version' => $this->get_redis_version(),
            'memory_usage' => $this->get_memory_usage()
        ];
    }

    private function get_redis_version() {
        if (!$this->connection->is_connected()) {
            return null;
        }

        try {
            $info = $this->connection->get_redis()->info();
            return $info['redis_version'] ?? 'Unknown';
        } catch (Exception $e) {
            return null;
        }
    }

    private function get_memory_usage() {
        if (!$this->connection->is_connected()) {
            return null;
        }

        try {
            $info = $this->connection->get_redis()->info('memory');
            return $info['used_memory_human'] ?? 'Unknown';
        } catch (Exception $e) {
            return null;
        }
    }
}