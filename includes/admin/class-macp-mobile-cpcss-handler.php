<?php
/**
 * Handles AJAX requests for mobile critical CSS
 */
class MACP_Mobile_CPCSS_Handler {
    private $settings_manager;
    private $css_generator;

    public function __construct(MACP_Settings_Manager $settings_manager) {
        $this->settings_manager = $settings_manager;
        $this->css_generator = new MACP_Critical_CSS_Generator();
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('wp_ajax_macp_generate_mobile_cpcss', [$this, 'generate_mobile_cpcss']);
        add_action('wp_ajax_macp_toggle_mobile_cpcss', [$this, 'toggle_mobile_cpcss']);
    }

    public function generate_mobile_cpcss() {
        if (!check_ajax_referer('macp_admin_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        // Start generation
        if ($this->css_generator->generate_mobile_css()) {
            update_option('macp_mobile_cpcss_generating', false);
            wp_send_json_success(['message' => 'Mobile Critical CSS generated successfully']);
        } else {
            wp_send_json_error('Failed to generate Critical CSS');
        }
    }

    public function toggle_mobile_cpcss() {
        if (!check_ajax_referer('macp_admin_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $value = isset($_POST['value']) ? (int)$_POST['value'] : 0;
        
        if ($this->settings_manager->update_setting('async_css_mobile', $value)) {
            wp_send_json_success(['message' => 'Setting updated successfully']);
        } else {
            wp_send_json_error('Failed to update setting');
        }
    }
}