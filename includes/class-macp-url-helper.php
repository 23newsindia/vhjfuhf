<?php
class MACP_URL_Helper {
    public static function is_https() {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }

    public static function get_current_url() {
        $protocol = self::is_https() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public static function normalize_url($url) {
        if (strpos($url, '//') === 0) {
            return (self::is_https() ? 'https:' : 'http:') . $url;
        }
        return $url;
    }
}