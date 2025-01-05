<?php
class MACP_Redis_Connection {
    private static $instance = null;
    private $redis = null;
    private $is_connected = false;
    private $last_error = '';

    private function __construct() {
        $this->connect();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        if (!class_exists('Redis')) {
            $this->last_error = 'Redis extension is not installed';
            return false;
        }

        try {
            $this->redis = new Redis();
            $this->is_connected = $this->redis->connect('127.0.0.1', 6379);
            if ($this->is_connected) {
                $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            }
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            $this->is_connected = false;
        }

        return $this->is_connected;
    }

    public function get_redis() {
        return $this->redis;
    }

    public function is_connected() {
        return $this->is_connected;
    }

    public function get_last_error() {
        return $this->last_error;
    }
}