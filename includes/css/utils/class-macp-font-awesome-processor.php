<?php
/**
 * Handles Font Awesome specific CSS optimization
 */
class MACP_Font_Awesome_Processor {
    public function process($css) {
        if (strpos($css, 'Font Awesome') === false) {
            return $css;
        }

        // Optimize Font Awesome specific rules
        $css = preg_replace('/font-family:\s*var\(--fa-style-family,([^)]+)\)/', 'font-family:$1', $css);
        $css = preg_replace('/font-weight:\s*var\(--fa-style,([^)]+)\)/', 'font-weight:$1', $css);
        $css = preg_replace('/display:\s*var\(--fa-display,([^)]+)\)/', 'display:$1', $css);
        
        return $css;
    }
}