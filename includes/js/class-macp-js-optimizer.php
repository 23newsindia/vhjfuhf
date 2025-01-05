<?php
/**
 * Handles JavaScript optimization functionality
 */
class MACP_JS_Optimizer {
    private $excluded_scripts = [];
    private $buffer_handler;

    public function __construct() {
        // Only run on frontend
        if (!is_admin()) {
            add_action('init', [$this, 'initialize_settings']);
            add_action('template_redirect', [$this, 'setup_buffering'], -9999);
            add_action('shutdown', [$this, 'end_buffering'], 9999999);
            add_action('wp_footer', [$this, 'add_delay_script'], 99999);
        }
    }

    public function initialize_settings() {
        $this->excluded_scripts = get_option('macp_excluded_scripts', []);
        $this->buffer_handler = new MACP_JS_Buffer_Handler($this->excluded_scripts);
    }

    public function setup_buffering() {
        if (get_option('macp_enable_js_delay', 0)) {
            $this->buffer_handler->start_buffering();
        }
    }

    public function end_buffering() {
        if (get_option('macp_enable_js_delay', 0)) {
            $this->buffer_handler->end_buffering();
        }
    }

    public function add_delay_script() {
        if (!get_option('macp_enable_js_delay', 0)) {
            return;
        }
        
        $loader = new MACP_JS_Loader();
        echo $loader->get_loader_script();
    }
}