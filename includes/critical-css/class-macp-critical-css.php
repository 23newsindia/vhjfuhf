<?php
class MACP_Critical_CSS {
    private $settings_manager;
    private $filesystem;
    private $critical_css_path;
    private $generator;

    public function __construct(MACP_Settings_Manager $settings_manager) {
        $this->settings_manager = $settings_manager;
        $this->filesystem = new MACP_Filesystem();
        $this->critical_css_path = WP_CONTENT_DIR . '/cache/macp/critical-css/';
        $this->generator = new MACP_Critical_CSS_Generation();
        
        $this->init();
    }

    private function init() {
        if (!file_exists($this->critical_css_path)) {
            wp_mkdir_p($this->critical_css_path);
        }

        add_action('wp_ajax_macp_generate_critical_css', [$this, 'ajax_generate_critical_css']);
        add_action('wp_ajax_macp_clear_critical_css', [$this, 'ajax_clear_critical_css']);
    }

    public function ajax_generate_critical_css() {
        check_ajax_referer('macp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        if (get_transient('macp_critical_css_generation_running')) {
            wp_send_json_error('Generation already in progress');
        }

        set_transient('macp_critical_css_generation_running', true, HOUR_IN_SECONDS);

        try {
            $this->generate_critical_css();
            wp_send_json_success('Critical CSS generation started');
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function generate_critical_css() {
        $home_url = home_url('/');
        return $this->generator->generate($home_url, 'front_page');
    }

    public function ajax_clear_critical_css() {
        check_ajax_referer('macp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        try {
            $this->clear_critical_css();
            wp_send_json_success('Critical CSS cache cleared');
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function clear_critical_css() {
        $files = glob($this->critical_css_path . '*.css');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}