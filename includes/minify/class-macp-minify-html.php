<?php
require_once MACP_PLUGIN_DIR . 'includes/html/utils/class-macp-html-patterns.php';
require_once MACP_PLUGIN_DIR . 'includes/html/processors/class-macp-script-style-processor.php';
require_once MACP_PLUGIN_DIR . 'includes/html/processors/class-macp-attribute-processor.php';

class MACP_Minify_HTML {
    private static $instance = null;
    private $script_style_processor;
    private $attribute_processor;
    private $patterns;

    public function __construct() {
        $this->script_style_processor = new MACP_Script_Style_Processor();
        $this->attribute_processor = new MACP_Attribute_Processor();
        $this->patterns = MACP_HTML_Patterns::get_patterns();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function minify($html) {
        if (empty($html)) {
            return $html;
        }

        // Save preserved content
        $preserved = $this->preserve_content($html);

        // Process scripts and styles
        $html = $this->script_style_processor->process($html);

        // Process attributes
        $html = $this->attribute_processor->process($html);

        // Remove comments (except IE conditionals)
        foreach ($this->patterns['comments'] as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }

        // Process whitespace
        foreach ($this->patterns['whitespace'] as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }

        // Restore preserved content
        $html = $this->restore_content($html, $preserved);

        return trim($html);
    }

    private function preserve_content($html) {
        $preserved = [];

        // Preserve conditional comments
        if (preg_match_all($this->patterns['preserve']['conditional'], $html, $matches)) {
            foreach ($matches[0] as $i => $match) {
                $preserved['%%CONDITIONAL' . $i . '%%'] = $match;
                $html = str_replace($match, '%%CONDITIONAL' . $i . '%%', $html);
            }
        }

        // Preserve pre, textarea, scripts, and styles
        if (preg_match_all($this->patterns['preserve']['pre'], $html, $matches)) {
            foreach ($matches[0] as $i => $match) {
                $preserved['%%PRESERVED' . $i . '%%'] = $match;
                $html = str_replace($match, '%%PRESERVED' . $i . '%%', $html);
            }
        }

        // Preserve data and event attributes
        if (preg_match_all($this->patterns['preserve']['attributes'], $html, $matches)) {
            foreach ($matches[1] as $i => $match) {
                $preserved['%%ATTRIBUTE' . $i . '%%'] = $match;
                $html = str_replace($match, '%%ATTRIBUTE' . $i . '%%', $html);
            }
        }

        return $preserved;
    }

    private function restore_content($html, $preserved) {
        return strtr($html, $preserved);
    }
}