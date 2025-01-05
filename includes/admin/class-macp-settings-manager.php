<?php
/**
 * Handles all plugin settings management
 */
class MACP_Settings_Manager {
    private $default_settings = [
        'macp_enable_redis' => 1,
        'macp_enable_html_cache' => 1,
        'macp_enable_gzip' => 1,
        'macp_minify_html' => 0,
        'macp_minify_css' => 0,
        'macp_minify_js' => 0,
        'macp_remove_unused_css' => 0,
        'macp_process_external_css' => 0,
        'macp_enable_js_defer' => 0,
        'macp_enable_js_delay' => 0,
        'macp_enable_varnish' => 0,
        'macp_varnish_port' => 80
    ];

    /**
     * Get all plugin settings
     */
    public function get_all_settings() {
        $settings = [];
        foreach ($this->default_settings as $key => $default) {
            $clean_key = str_replace('macp_', '', $key);
            $settings[$clean_key] = (bool)get_option($key, $default);
        }

        // Add Varnish settings
        $settings['varnish_servers'] = get_option('macp_varnish_servers', ['127.0.0.1']);
        $settings['varnish_port'] = get_option('macp_varnish_port', 6081);

        return $settings;
    }

    /**
     * Update a single setting
     */
    public function update_setting($key, $value) {
        if (!array_key_exists($key, $this->default_settings)) {
            return false;
        }

        return update_option($key, $value);
    }

    /**
     * Get default settings
     */
    public function get_default_settings() {
        return $this->default_settings;
    }
}