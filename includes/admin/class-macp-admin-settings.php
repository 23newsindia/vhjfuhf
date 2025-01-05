<?php
/**
 * Handles admin settings operations
 */
class MACP_Admin_Settings {
    private $settings_manager;

     public function __construct() {
        $this->settings_manager = new MACP_Settings_Manager();
        add_action('wp_ajax_macp_toggle_setting', [$this, 'ajax_toggle_setting']);
        add_action('wp_ajax_macp_save_textarea', [$this, 'ajax_save_textarea']);
        add_action('wp_ajax_macp_clear_cache', [$this, 'ajax_clear_cache']);
        add_action('admin_init', [$this, 'register_settings']);
    }
  
   public function register_settings() {
        register_setting('macp_settings', 'macp_enable_lazy_load');
        register_setting('macp_settings', 'macp_lazy_load_excluded');
    }


    public function get_all_settings() {
        return $this->settings_manager->get_all_settings();
    }

    public function ajax_toggle_setting() {
        check_ajax_referer('macp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $option = sanitize_key($_POST['option']);
        $value = (int)$_POST['value'];

        if (update_option($option, $value)) {
            do_action('macp_settings_updated', $option, $value);
            wp_send_json_success(['message' => 'Setting updated successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to update setting']);
        }
    }


    public function ajax_save_textarea() {
        check_ajax_referer('macp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $option = sanitize_key($_POST['option']);
        $value = sanitize_textarea_field($_POST['value']);

        switch ($option) {
            case 'macp_defer_excluded_scripts':
            case 'macp_delay_excluded_scripts':
                $type = strpos($option, 'defer') !== false ? 'defer' : 'delay';
                MACP_Script_Exclusions::save_exclusions($value, $type);
                break;
            case 'macp_varnish_servers':
                $servers = array_filter(array_map('trim', explode("\n", $value)));
                update_option($option, $servers);
                break;
            case 'macp_css_safelist':
                MACP_CSS_Config::save_safelist(array_filter(array_map('trim', explode("\n", $value))));
                break;
            case 'macp_css_excluded_patterns':
                MACP_CSS_Config::save_excluded_patterns(array_filter(array_map('trim', explode("\n", $value))));
                break;
        }

        do_action('macp_settings_updated', $option, $value);
        wp_send_json_success(['message' => 'Settings saved']);
    }

    public function ajax_clear_cache() {
        check_ajax_referer('macp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        do_action('macp_clear_cache');
        
        if (get_option('macp_enable_varnish', 0)) {
            do_action('macp_clear_all_cache');
        }
        
        wp_send_json_success(['message' => 'Cache cleared successfully']);
    }
}