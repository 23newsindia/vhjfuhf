<?php
class MACP_CSS_Processor {
    public function process($css) {
        if (empty($css)) {
            return '';
        }

        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove media queries (optional)
        $css = preg_replace('/@media[^{]+\{([^{}]|{[^{}]*})*\}/i', '', $css);
        
        // Extract only above-the-fold styles (customize based on your needs)
        $critical_selectors = [
            'body', 'header', '#main-header', '.site-header', '#post-custom-css-css', '#home-custom-css-css',
            '.main-navigation', '.hero', '#hero',
            '.banner', '#banner', '.main-content', '.home-custom-css-css',
            '.entry-header', '.post-thumbnail'
        ];
        
        $critical_css = '';
        foreach ($critical_selectors as $selector) {
            if (preg_match_all('/' . preg_quote($selector) . '[^{]*{[^}]*}/i', $css, $matches)) {
                $critical_css .= implode("\n", $matches[0]);
            }
        }

        return $critical_css;
    }
}