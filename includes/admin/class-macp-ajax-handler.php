<?php
/**
 * Handles AJAX requests for mobile critical CSS
 */
class MACP_Mobile_CPCSS_Ajax_Handler {
    private $settings_manager;
    private $filesystem;

    public function __construct(MACP_Settings_Manager $settings_manager, $filesystem) {
        $this->settings_manager = $settings_manager;
        $this->filesystem = $filesystem;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('wp_ajax_macp_generate_mobile_cpcss', [$this, 'generate_mobile_cpcss']);
        add_action('wp_ajax_macp_toggle_mobile_cpcss', [$this, 'toggle_mobile_cpcss']);
    }

    public function generate_mobile_cpcss() {
        check_ajax_referer('macp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        update_option('macp_mobile_cpcss_generating', true);
        
        wp_send_json_success(['message' => 'Mobile Critical CSS generation started']);
    }

    public function toggle_mobile_cpcss() {
        check_ajax_referer('macp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $value = isset($_POST['value']) ? (int)$_POST['value'] : 0;
        
        if ($this->settings_manager->update_setting('async_css_mobile', $value)) {
            wp_send_json_success(['message' => 'Setting updated successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to update setting']);
        }
    }
}