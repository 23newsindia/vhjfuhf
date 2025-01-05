<?php
use Symfony\Component\CssSelector\CssSelectorConverter;

class MACP_CSS_Selector_Parser {
    private $converter;
    private $dom;
    private $xpath;

    public function __construct() {
        $this->converter = new CssSelectorConverter();
    }

    public function parse_html($html) {
        $this->dom = new \DOMDocument();
        @$this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $this->xpath = new \DOMXPath($this->dom);
    }

    public function find_used_selectors($css) {
        if (!$this->dom || !$this->xpath) {
            return [];
        }

        $used_selectors = [];
        $rules = $this->extract_css_rules($css);

        foreach ($rules as $rule) {
            $selectors = explode(',', $rule['selectors']);
            foreach ($selectors as $selector) {
                $selector = trim($selector);
                
                // Skip invalid selectors
                if ($this->is_invalid_selector($selector)) {
                    continue;
                }

                try {
                    // Convert CSS selector to XPath
                    $xpath = $this->converter->toXPath($selector);
                    
                    // Try to find elements matching this selector
                    $elements = @$this->xpath->query($xpath);
                    
                    // If elements found, selector is used
                    if ($elements && $elements->length > 0) {
                        $used_selectors[] = $selector;
                    }
                } catch (\Exception $e) {
                    // Log invalid selectors for debugging
                    error_log("MACP: Invalid selector: " . $selector . " - " . $e->getMessage());
                    continue;
                }
            }
        }

        return array_unique($used_selectors);
    }

    private function extract_css_rules($css) {
        $rules = [];
        // Remove comments to avoid parsing issues
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Match selectors and their rules
        preg_match_all('/([^{]+){([^}]*)}/', $css, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $rules[] = [
                'selectors' => $match[1],
                'styles' => $match[2]
            ];
        }

        return $rules;
    }

    private function is_invalid_selector($selector) {
        // Skip empty selectors
        if (empty($selector)) {
            return true;
        }

        // Skip @-rules
        if (strpos($selector, '@') === 0) {
            return true;
        }

        // Skip keyframe selectors (percentage-based)
        if (preg_match('/^\d+%$/', trim($selector))) {
            return true;
        }

        return false;
    }
}