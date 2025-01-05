<?php
class MACP_Cache_Helper {
    public static function get_cache_key($url = null) {
        if ($url === null) {
            $url = MACP_URL_Helper::get_current_url();
        }

        // Add user-specific cache key components
        $user_key = '';
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $user_key = md5($user->ID . $user->user_login . $user->user_email);
        } else {
            $user_key = 'guest';
        }

        return md5($url . $user_key);
    }

    public static function get_cache_path($key, $is_gzip = false) {
        $cache_dir = WP_CONTENT_DIR . '/cache/macp/';
        
        // Create user-specific subdirectory
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $cache_dir .= 'users/' . $user_id . '/';
        } else {
            $cache_dir .= 'guest/';
        }

        // Ensure directory exists
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }

        return $cache_dir . $key . ($is_gzip ? '.gz' : '.html');
    }

    public static function is_cacheable_request() {
        // Don't cache POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return false;
        }

        // Don't cache query strings
        if (!empty($_GET)) {
            return false;
        }

        // Don't cache admin pages
        if (is_admin()) {
            return false;
        }

        return true;
    }

    public static function clear_page_cache($post_id) {
        $url = get_permalink($post_id);
        if (!$url) return;

        // Clear guest cache
        $guest_key = md5($url . 'guest');
        $guest_paths = [
            WP_CONTENT_DIR . '/cache/macp/guest/' . $guest_key . '.html',
            WP_CONTENT_DIR . '/cache/macp/guest/' . $guest_key . '.gz'
        ];

        foreach ($guest_paths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Clear user-specific caches for this page
        $users_cache_dir = WP_CONTENT_DIR . '/cache/macp/users/';
        if (file_exists($users_cache_dir)) {
            $user_dirs = glob($users_cache_dir . '*', GLOB_ONLYDIR);
            foreach ($user_dirs as $user_dir) {
                $user_key = md5($url . basename($user_dir));
                $user_paths = [
                    $user_dir . '/' . $user_key . '.html',
                    $user_dir . '/' . $user_key . '.gz'
                ];
                foreach ($user_paths as $path) {
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
        }
    }
}