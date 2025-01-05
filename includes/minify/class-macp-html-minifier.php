<?php
class MACP_HTML_Minifier {
    private $options = [
        'remove_comments' => true,
        'remove_whitespace' => true,
        'remove_blank_lines' => true,
        'compress_js' => false, // Disabled to prevent JS issues
        'compress_css' => false, // Disabled to prevent CSS issues
        'preserve_conditional_comments' => true,
        'preserve_data_attributes' => true // Added to preserve data attributes
    ];

    private $preserved_tags = [
        'pre',
        'textarea',
        'script',
        'style',
        'code',
        'noscript'
    ];

    private $preserved_attributes = [
        'data-',
        'on',
        'id',
        'class',
        'style'
    ];

    public function __construct($options = []) {
        $this->options = array_merge($this->options, $options);
    }

    public function minify($html) {
        if (empty($html)) {
            return $html;
        }

        // Store preserved content
        $preservedTokens = [];
        
        // Preserve conditional comments
        if ($this->options['preserve_conditional_comments']) {
            $html = preg_replace_callback('/<!--\[if[^\]]*\]>.*?<!\[endif\]-->/is', function($matches) use (&$preservedTokens) {
                $token = '<!--PRESERVED' . count($preservedTokens) . '-->';
                $preservedTokens[$token] = $matches[0];
                return $token;
            }, $html);
        }

        // Preserve script tags with their content
        $html = preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/is', function($matches) use (&$preservedTokens) {
            $token = '<!--PRESERVED' . count($preservedTokens) . '-->';
            $preservedTokens[$token] = $matches[0];
            return $token;
        }, $html);

        // Preserve content in special tags
        foreach ($this->preserved_tags as $tag) {
            $html = preg_replace_callback('/<' . $tag . '([^>]*?)>(.*?)<\/' . $tag . '>/is', function($matches) use (&$preservedTokens) {
                $token = '<!--PRESERVED' . count($preservedTokens) . '-->';
                $preservedTokens[$token] = $matches[0];
                return $token;
            }, $html);
        }

        // Preserve data attributes and event handlers
        $html = preg_replace_callback('/\s+((?:data-|on)[a-zA-Z-]+)="[^"]*"/', function($matches) use (&$preservedTokens) {
            $token = '<!--PRESERVED' . count($preservedTokens) . '-->';
            $preservedTokens[$token] = $matches[0];
            return $token;
        }, $html);

        // Remove HTML comments (not containing IE conditional comments)
        if ($this->options['remove_comments']) {
            $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        }

        // Remove whitespace more carefully
        if ($this->options['remove_whitespace']) {
            // Preserve whitespace in specific elements
            $html = preg_replace('/>\s+<(?!\/(?:textarea|pre|script|style|code))/i', '><', $html);
            $html = preg_replace('/\s{2,}/', ' ', $html);
        }

        // Restore preserved content
        foreach ($preservedTokens as $token => $content) {
            $html = str_replace($token, $content, $html);
        }

        return trim($html);
    }
}