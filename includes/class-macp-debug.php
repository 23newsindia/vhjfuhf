<?php
class MACP_Debug {
    public static function log($message, $data = null) {
        if (WP_DEBUG) {
            error_log('[MACP] ' . $message . ($data ? ' Data: ' . print_r($data, true) : ''));
        }
    }
}