<?php
/**
 * Handles script and style tag processing
 */
class MACP_Script_Style_Processor {
    private $minify_js;
    private $minify_css;

    public function __construct() {
        $this->minify_js = MACP_Minify_JS::get_instance();
        $this->minify_css = MACP_Minify_CSS::get_instance();
    }

    public function process($html) {
        // Process inline scripts
        if (get_option('macp_minify_js', 0)) {
            $html = preg_replace_callback('/<script[^>]*>(.*?)<\/script>/is', 
                [$this, 'minify_script'], $html);
        }

        // Process inline styles
        if (get_option('macp_minify_css', 0)) {
            $html = preg_replace_callback('/<style[^>]*>(.*?)<\/style>/is', 
                [$this, 'minify_style'], $html);
        }

        return $html;
    }

    private function minify_script($matches) {
        if (empty($matches[1])) {
            return $matches[0];
        }

        $script = $matches[1];
        if (strpos($matches[0], 'type="text/template"') !== false) {
            return $matches[0];
        }

        $minified = $this->minify_js->minify($script);
        return str_replace($script, $minified, $matches[0]);
    }

    private function minify_style($matches) {
        if (empty($matches[1])) {
            return $matches[0];
        }

        $style = $matches[1];
        $minified = $this->minify_css->minify($style);
        return str_replace($style, $minified, $matches[0]);
    }
}