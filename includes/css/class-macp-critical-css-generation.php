<?php
require_once dirname(__FILE__) . '/utils/class-macp-css-extractor.php';
require_once dirname(__FILE__) . '/utils/class-macp-url-helper.php';
require_once dirname(__FILE__) . '/utils/class-macp-css-optimizer.php';
require_once dirname(__FILE__) . '/generators/class-macp-template-css-generator.php';

class MACP_Critical_CSS_Generator {
    private $filesystem;
    private $base_dir;
    
    public function __construct() {
        $this->filesystem = new MACP_Filesystem();
        $this->base_dir = WP_CONTENT_DIR . '/cache/macp/critical-css/';
        $this->init();
    }

    private function init() {
        if (!file_exists($this->base_dir)) {
            wp_mkdir_p($this->base_dir);
        }
    }

    public function generate_mobile_css() {
        if (!file_exists($this->base_dir) && !wp_mkdir_p($this->base_dir)) {
            return false;
        }

        $templates = MACP_Template_CSS_Generator::get_templates_list();
        
        foreach ($templates as $key => $url) {
            if ($url) {
                $this->generate_template_css($key, $url);
            }
        }

        return true;
    }

    private function generate_template_css($key, $url) {
        $filename = $key . '-mobile.css';
        $filepath = $this->base_dir . $filename;

        $css = $this->extract_critical_css($url);
        
        if ($css) {
            return file_put_contents($filepath, $css);
        }
        
        return false;
    }

    private function extract_critical_css($url) {
        $response = wp_remote_get($url, [
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $html = wp_remote_retrieve_body($response);
        $css = '';

        // Get inline styles
        $inline_styles = MACP_CSS_Extractor::extract_inline_styles($html);
        $css .= implode("\n", $inline_styles);

        // Get external stylesheets
        $stylesheets = MACP_CSS_Extractor::extract_external_stylesheets($html);
        foreach ($stylesheets as $stylesheet) {
            $style_url = MACP_CSS_URL_Helper::make_absolute_url($stylesheet, $url);
            $style_content = MACP_CSS_Extractor::get_external_css($style_url);
            if ($style_content) {
                $css .= "\n" . $style_content;
            }
        }

        return MACP_CSS_Optimizer::optimize($css);
    }
}