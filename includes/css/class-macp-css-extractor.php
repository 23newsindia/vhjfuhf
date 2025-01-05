<?php
class MACP_CSS_Extractor {
    private $selector_parser;

    public function __construct() {
        $this->selector_parser = new MACP_CSS_Selector_Parser();
    }

    public function extract_used_selectors($html) {
        // Parse HTML
        $this->selector_parser->parse_html($html);

        // Get all CSS
        $css = $this->get_all_css($html);

        // Find used selectors
        return $this->selector_parser->find_used_selectors($css);
    }

    private function get_all_css($html) {
        $css = '';

        // Get inline styles
        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $matches);
        if (!empty($matches[1])) {
            $css .= implode("\n", $matches[1]);
        }

        // Get external stylesheets
        preg_match_all('/<link[^>]*rel=[\'"]stylesheet[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*>/i', $html, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $url) {
                $content = $this->get_external_css($url);
                if ($content) {
                    $css .= "\n" . $content;
                }
            }
        }

        return $css;
    }

    public static function get_external_css($url) {
        $response = wp_remote_get($url);
        if (!is_wp_error($response)) {
            return wp_remote_retrieve_body($response);
        }
        return false;
    }
}