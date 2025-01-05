<?php
class MACP_CSS_Optimizer {
    public static function optimize($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove media queries (for mobile)
        $css = preg_replace('/@media\s+[^{]+\{([^{}]*\{[^{}]*\})*[^{}]*\}/i', '', $css);
        
        return trim($css);
    }
}