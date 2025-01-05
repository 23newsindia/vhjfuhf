<?php
class MACP_CSS_Rule_Processor {
    private $safeSelectors;
    
    public function __construct() {
        $this->safeSelectors = array_merge(
            [':root', 'body', 'html'], // Always keep these
            MACP_CSS_Config::get_safelist()
        );
    }

    public function processRule($rule) {
        switch ($rule->type) {
            case CSSRule.STYLE_RULE:
                return $this->processStyleRule($rule);
            case CSSRule.MEDIA_RULE:
                return $rule->cssText; // Preserve all media queries
            case CSSRule.KEYFRAMES_RULE:
                return $rule->cssText; // Preserve all animations
            case CSSRule.FONT_FACE_RULE:
                return $rule->cssText; // Preserve all font faces
            default:
                return $rule->cssText; // Preserve other rules by default
        }
    }

    private function processStyleRule($rule) {
        // Split multiple selectors
        $selectors = explode(',', $rule->selectorText);
        $keepRule = false;

        foreach ($selectors as $selector) {
            $selector = trim($selector);
            
            // Keep if it's in safelist
            if ($this->isSafeSelector($selector)) {
                $keepRule = true;
                break;
            }

            // Keep if it uses pseudo-elements/classes
            if ($this->hasPseudoElement($selector)) {
                $keepRule = true;
                break;
            }

            // Keep if selector is actually used
            try {
                if (document.querySelector($selector)) {
                    $keepRule = true;
                    break;
                }
            } catch (Exception $e) {
                // If selector is invalid, keep it to be safe
                $keepRule = true;
                break;
            }
        }

        return $keepRule ? $rule->cssText : '';
    }

    private function isSafeSelector($selector) {
        foreach ($this->safeSelectors as $safe) {
            if (strpos($selector, $safe) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasPseudoElement($selector) {
        $pseudos = ['::', ':before', ':after', ':hover', ':focus', ':active'];
        foreach ($pseudos as $pseudo) {
            if (strpos($selector, $pseudo) !== false) {
                return true;
            }
        }
        return false;
    }
}