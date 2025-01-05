<?php
class MACP_CSS_Extractor {
    public static function extract_inline_styles($html) {
        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $matches);
        return !empty($matches[1]) ? $matches[1] : [];
    }

    public static function extract_external_stylesheets($html) {
        preg_match_all('/<link[^>]*rel=[\'"]stylesheet[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*>/i', $html, $matches);
        return !empty($matches[1]) ? $matches[1] : [];
    }

    public static function get_external_css($url) {
        $response = wp_remote_get($url);
        if (!is_wp_error($response)) {
            return wp_remote_retrieve_body($response);
        }
        return false;
    }
}