<?php
class MACP_Script_Loader {
    private $plugin_url;
    
    public function __construct() {
        $this->plugin_url = plugins_url('', dirname(dirname(__FILE__)));
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }

    public function enqueue_frontend_scripts() {
        if (!$this->is_lazy_load_enabled()) {
            return;
        }

        // Register and enqueue main stylesheet
        wp_register_style(
            'macp-lazy-load',
            false
        );
        wp_enqueue_style('macp-lazy-load');

        // Add inline styles
        wp_add_inline_style('macp-lazy-load', "
            .macp-lazy {
                opacity: 0;
                transition: opacity 0.3s ease-in;
            }
            .macp-lazy.macp-lazy-loaded {
                opacity: 1;
            }
        ");

        // Enqueue vanilla-lazyload
        wp_enqueue_script(
            'vanilla-lazyload',
            $this->plugin_url . '/assets/js/vanilla-lazyload.min.js',
            [],
            '17.8.3',
            true
        );

        // Enqueue our lazy load implementation
        wp_enqueue_script(
            'macp-lazy-load',
            $this->plugin_url . '/assets/js/lazy-load.js',
            ['vanilla-lazyload'],
            '1.0.0',
            true
        );

        // Debug output
        error_log('MACP: Lazy load scripts enqueued');
        error_log('MACP: Plugin URL: ' . $this->plugin_url);
    }

    private function is_lazy_load_enabled() {
        return get_option('macp_enable_lazy_load', 1);
    }
}