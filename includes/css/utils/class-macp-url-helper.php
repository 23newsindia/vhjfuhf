<?php
class MACP_CSS_URL_Helper {
    public static function make_absolute_url($url, $base) {
        if (strpos($url, 'http') !== 0) {
            if (strpos($url, '//') === 0) {
                return 'https:' . $url;
            }
            if (strpos($url, '/') === 0) {
                $parsed = parse_url($base);
                return $parsed['scheme'] . '://' . $parsed['host'] . $url;
            }
        }
        return $url;
    }
}