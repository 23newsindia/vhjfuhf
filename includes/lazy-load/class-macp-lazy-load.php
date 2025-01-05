<?php
class MACP_Lazy_Load {
    private $content_processor;
    private $plugin_url;

    public function __construct() {
        $this->content_processor = new MACP_Lazy_Load_Processor();
        $this->plugin_url = plugins_url('', dirname(dirname(__FILE__)));
        
        if ($this->is_lazy_load_enabled()) {
            $this->init_hooks();
        }
    }

    private function init_hooks() {
        // Remove WordPress default lazy loading
        add_filter('wp_lazy_loading_enabled', '__return_false');
        
        // Add our lazy loading to various content types
        add_filter('the_content', [$this->content_processor, 'process_content'], 99);
        add_filter('post_thumbnail_html', [$this->content_processor, 'process_content'], 99);
        add_filter('get_avatar', [$this->content_processor, 'process_content'], 99);
        add_filter('widget_text', [$this->content_processor, 'process_content'], 99);
        add_filter('render_block', [$this->content_processor, 'process_content'], 99);
        
        // Add filter for images added via wp_get_attachment_image
        add_filter('wp_get_attachment_image', [$this->content_processor, 'process_content'], 99);
        
        // Handle attachment images
        add_filter('wp_get_attachment_image_attributes', [$this, 'modify_attachment_image_attributes'], 99, 2);

        // Enqueue required scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        // Register and enqueue main stylesheet
        wp_register_style('macp-lazy-load', false);
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
    }

    public function modify_attachment_image_attributes($attributes, $attachment) {
        if (!isset($attributes['src'])) {
            return $attributes;
        }

        // Skip if already processed
        if (isset($attributes['data-src'])) {
            return $attributes;
        }

        $attributes['data-src'] = $attributes['src'];
        $attributes['src'] = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E";
        
        if (isset($attributes['srcset'])) {
            $attributes['data-srcset'] = $attributes['srcset'];
            unset($attributes['srcset']);
        }

        $attributes['class'] = isset($attributes['class']) 
            ? $attributes['class'] . ' macp-lazy'
            : 'macp-lazy';

        return $attributes;
    }

    private function is_lazy_load_enabled() {
        return get_option('macp_enable_lazy_load', 1);
    }
}