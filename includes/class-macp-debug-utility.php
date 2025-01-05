<?php
class MACP_Debug_Utility {
    public static function check_plugin_status() {
        $status = [
            'cache_dir_exists' => is_dir(WP_CONTENT_DIR . '/cache/macp'),
            'cache_dir_writable' => is_writable(WP_CONTENT_DIR . '/cache/macp'),
            'composer_loaded' => file_exists(MACP_PLUGIN_DIR . 'vendor/autoload.php'),
            'minification_enabled' => get_option('macp_minify_html', 0),
            'gzip_enabled' => get_option('macp_enable_gzip', 1),
            'html_cache_enabled' => get_option('macp_enable_html_cache', 1)
        ];

        foreach ($status as $key => $value) {
            MACP_Debug::log("Status check - {$key}: " . ($value ? 'YES' : 'NO'));
        }

        return $status;
    }
}