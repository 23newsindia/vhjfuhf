<?php
/**
 * Handles media query processing and optimization
 */
class MACP_Media_Query_Processor {
    public function process($css) {
        // Extract and process media queries
        preg_match_all('/@media[^{]+\{([^{}]|{[^{}]*})*\}/i', $css, $matches);
        
        if (empty($matches[0])) {
            return $css;
        }

        foreach ($matches[0] as $mediaQuery) {
            $processed = $this->process_single_query($mediaQuery);
            $css = str_replace($mediaQuery, $processed, $css);
        }

        return $css;
    }

    private function process_single_query($query) {
        // Clean media query syntax
        $query = preg_replace('/\s*{\s*/', '{', $query);
        $query = preg_replace('/\s*}\s*/', '}', $query);
        
        // Clean spaces in media query conditions
        $query = preg_replace('/@media\s+/', '@media ', $query);
        $query = preg_replace('/\s*,\s*/', ',', $query);
        $query = preg_replace('/\s*\(\s*/', '(', $query);
        $query = preg_replace('/\s*\)\s*/', ')', $query);
        
        return $query;
    }
}