<?php
require_once MACP_PLUGIN_DIR . 'includes/css/utils/class-macp-css-patterns.php';
require_once MACP_PLUGIN_DIR . 'includes/css/utils/class-macp-media-query-processor.php';
require_once MACP_PLUGIN_DIR . 'includes/css/utils/class-macp-font-awesome-processor.php';

class MACP_Minify_CSS {
    private static $instance = null;
    private $media_processor;
    private $fa_processor;
    
    public function __construct() {
        $this->media_processor = new MACP_Media_Query_Processor();
        $this->fa_processor = new MACP_Font_Awesome_Processor();
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function minify($css) {
        if (empty($css)) return $css;

        // Process Font Awesome specific rules
        $css = $this->fa_processor->process($css);

        // Process media queries
        $css = $this->media_processor->process($css);

        // Apply all minification patterns
        $patterns = MACP_CSS_Patterns::get_patterns();

        // Remove comments
        foreach ($patterns['comments'] as $pattern) {
            $css = preg_replace($pattern, '', $css);
        }

        // Process whitespace
        foreach ($patterns['whitespace'] as $pattern => $replacement) {
            $css = preg_replace($pattern, $replacement, $css);
        }

        // Process numbers
        foreach ($patterns['numbers'] as $pattern => $replacement) {
            $css = preg_replace($pattern, $replacement, $css);
        }

        // Process colors
        foreach ($patterns['colors'] as $pattern => $replacement) {
            $css = preg_replace($pattern, $replacement, $css);
        }

        // Final cleanup
        $css = preg_replace('/[^}]+{\s*}/', '', $css); // Remove empty rules
        $css = preg_replace('/;}/', '}', $css); // Remove last semicolon
        
        return trim($css);
    }
}