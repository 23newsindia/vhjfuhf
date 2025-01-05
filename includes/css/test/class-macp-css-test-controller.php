<?php
/**
 * Handles the unused CSS testing functionality
 */
class MACP_CSS_Test_Controller {
    private $optimizer;
    private $url_validator;

    public function __construct() {
        $this->optimizer = new MACP_CSS_Optimizer();
        $this->url_validator = new MACP_URL_Validator();
        
        add_action('wp_ajax_macp_test_unused_css', [$this, 'handle_test_request']);
    }

    public function handle_test_request() {
        try {
            // Verify nonce and capabilities
            check_ajax_referer('macp_admin_nonce', 'nonce');
            if (!current_user_can('manage_options')) {
                throw new Exception('Unauthorized access');
            }

            // Validate URL
            $url = isset($_POST['url']) ? $_POST['url'] : home_url('/');
            if (!$this->url_validator->is_valid_url($url)) {
                throw new Exception('Invalid URL provided');
            }

            // Run the test
            $results = $this->optimizer->test_unused_css($url);
            
            wp_send_json_success([
                'results' => $results,
                'url' => $url
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}