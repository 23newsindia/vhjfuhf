<?php
class MACP_Installer {
    public static function install() {
        self::check_composer_autoload();
        self::create_cache_directory();
    }

    private static function check_composer_autoload() {
        if (!file_exists(MACP_PLUGIN_DIR . 'vendor/autoload.php')) {
            MACP_Debug::log("Composer dependencies not installed. Please run 'composer install' in the plugin directory.");
        }
    }

    private static function create_cache_directory() {
        $cache_dir = WP_CONTENT_DIR . '/cache/macp/';
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
    }
}