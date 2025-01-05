<?php
/**
 * Defines regex patterns for HTML minification
 */
class MACP_HTML_Patterns {
    public static function get_patterns() {
        return [
            'whitespace' => [
                '/>\s+</s'           => '><',    // Between tags
                '/\s+/'              => ' ',     // Multiple spaces
                '/\s+([^\s])/'       => '$1',    // Leading spaces
                '/([^\s])\s+$/'      => '$1',    // Trailing spaces
                '/\n\s+/'            => "\n",    // After line breaks
                '/\s+\n/'            => "\n",    // Before line breaks
                '/\n+/'              => "\n",    // Multiple line breaks
            ],
            'comments' => [
                '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s',  // Regular comments
                '/<!--[\s\S]*?-->/'                                      // Multiline comments
            ],
            'preserve' => [
                'conditional' => '/<!--\[if[^\]]*\]>.*?<!\[endif\]-->/is',
                'pre'        => '/<(pre|textarea|script|style)[^>]*>.*?<\/\\1>/is',
                'attributes' => '/\s+((?:data-|on[a-z]+)\s*=\s*["\'][^"\']*["\'])/i'
            ]
        ];
    }
}