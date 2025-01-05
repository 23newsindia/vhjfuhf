<?php
/**
 * Handles script delay functionality
 */
class MACP_Delay_Handler {
    /**
     * Check if script should be delayed based on handle/URL
     */
    public static function should_delay($src, $delay_scripts = []) {
        if (empty($delay_scripts)) {
            return false;
        }

        foreach ($delay_scripts as $pattern) {
            if (!empty($pattern) && strpos($src, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process script tag for delay
     */
    public static function process_tag($tag, $delay_scripts = []) {
        // Skip if already processed
        if (strpos($tag, 'rocketlazyloadscript') !== false) {
            return $tag;
        }

        $src = MACP_Script_Attributes::get_src($tag);
        if (!$src || !self::should_delay($src, $delay_scripts)) {
            return $tag;
        }

        $attributes = MACP_Script_Attributes::extract_attributes($tag);
        
        // Build delayed script tag
        $new_tag = '<script type="rocketlazyloadscript"';
        $new_tag .= ' data-rocket-src="' . $src . '"';

        // Add remaining attributes except type and src
        foreach ($attributes as $name => $value) {
            if (!in_array($name, ['type', 'src'])) {
                $new_tag .= ' ' . $name . '="' . $value . '"';
            }
        }

        $new_tag .= '></script>';
        return $new_tag;
    }
}