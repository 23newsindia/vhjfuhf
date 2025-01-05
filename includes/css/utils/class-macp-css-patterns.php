<?php
/**
 * Defines regex patterns for CSS minification
 */
class MACP_CSS_Patterns {
    public static function get_patterns() {
        return [
            'comments' => [
                '/\/\*[^*]*\*+([^\/][^*]*\*+)*\//',  // Standard comments
                '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/s', // Special comments
                '/\/\*![\s\S]*?\*\//' // Important comments
            ],
            'whitespace' => [
                '/\s+/' => ' ',                    // Multiple spaces to single
                '/\s*([\{\};:,])\s*/' => '$1',    // Around operators
                '/\s*([>+~])\s*/' => '$1',        // Around combinators
                '/\s*{\s*/' => '{',               // Before/after brackets
                '/\s*}\s*/' => '}',               // Before/after closing
                '/}\s+/' => '}',                  // Between rules
                '/\s+{/' => '{',                  // Before opening bracket
                '/;\s*;/' => ';'                  // Multiple semicolons
            ],
            'numbers' => [
                '/(?<=[\s:,\-])0(?:px|em|rem|pt|cm|mm|in|pc|ex|vh|vw|vmin|vmax)/' => '0', // Zero units
                '/(?<![\d.])\b0+(\.\d+)/' => '$1' // Leading zeros
            ],
            'colors' => [
                '/\#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3/i' => '#$1$2$3' // Hex colors
            ]
        ];
    }
}