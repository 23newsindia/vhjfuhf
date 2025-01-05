<?php
class MACP_Minify_JS {
    private static $instance = null;
    private $options = [];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->options = [
            'preserve_comments' => false,
            'preserve_important' => true
        ];
    }

    public function minify($js) {
        if (empty($js)) return $js;

        // Preserve important comments if needed
        if ($this->options['preserve_important']) {
            $js = preg_replace_callback('/\/\*![\s\S]*?\*\//', function($matches) {
                return '___PRESERVED_COMMENT___' . base64_encode($matches[0]) . '___PRESERVED_COMMENT___';
            }, $js);
        }

        // Remove comments
        if (!$this->options['preserve_comments']) {
            $js = preg_replace('/\/\*[\s\S]*?\*\/|([^\\:]|^)\/\/.*$/m', '$1', $js);
        }

        // Remove whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remove whitespace around operators
        $js = preg_replace('/\s*([\{\}:\[\]\(\),;=\+\-\*\/])\s*/', '$1', $js);
        
        // Remove trailing semicolons
        $js = preg_replace('/;}/', '}', $js);
        
        // Remove unnecessary semicolons
        $js = preg_replace('/;+/', ';', $js);

        // Restore preserved comments
        if ($this->options['preserve_important']) {
            $js = preg_replace_callback('/___PRESERVED_COMMENT___(.+?)___PRESERVED_COMMENT___/', function($matches) {
                return base64_decode($matches[1]);
            }, $js);
        }

        return trim($js);
    }
}