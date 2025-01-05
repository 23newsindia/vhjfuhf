<?php
/**
 * Handles the HTML processing pipeline
 */
class MACP_HTML_Processor {
    private $minifier;
    private $options;

    public function __construct() {
        $this->minifier = new MACP_HTML_Minifier();
        $this->options = [
            'minify_html' => get_option('macp_minify_html', 0),
            'minify_css' => get_option('macp_minify_css', 0),
            'minify_js' => get_option('macp_minify_js', 0)
        ];
    }

    public function process($html) {
        if (empty($html)) {
            return $html;
        }

        if ($this->options['minify_html']) {
            $html = $this->minifier->minify($html);
        }

        return $html;
    }
}