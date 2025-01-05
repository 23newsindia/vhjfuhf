<?php
require_once MACP_PLUGIN_DIR . 'includes/js/class-macp-script-exclusions.php';

class MACP_Script_Processor {
    /**
     * Process a script tag
     */
    public static function process_tag($tag) {
        // Skip if already processed
        if (strpos($tag, 'rocketlazyloadscript') !== false) {
            return $tag;
        }

        $src = self::get_script_src($tag);
        if (!$src) {
            return $tag;
        }

        // Check exclusions
        if (MACP_Script_Exclusions::should_exclude($src, 'defer')) {
            return $tag;
        }

        // Handle delay
        if (get_option('macp_enable_js_delay', 0) && !MACP_Script_Exclusions::should_exclude($src, 'delay')) {
            return self::process_delay($tag, $src);
        }

        // Handle defer
        if (get_option('macp_enable_js_defer', 0)) {
            return self::process_defer($tag);
        }

        return $tag;
    }

    private static function process_delay($tag, $src) {
        $attributes = self::get_script_attributes($tag);
        
        $new_tag = '<script type="rocketlazyloadscript"';
        $new_tag .= ' data-rocket-src="' . esc_attr($src) . '"';

        foreach ($attributes as $name => $value) {
            if (!in_array($name, ['type', 'src'])) {
                $new_tag .= ' ' . $name . '="' . esc_attr($value) . '"';
            }
        }

        $new_tag .= '></script>';
        return $new_tag;
    }

    private static function process_defer($tag) {
        if (strpos($tag, 'defer') === false) {
            return str_replace(' src=', ' defer="defer" src=', $tag);
        }
        return $tag;
    }

    private static function get_script_src($tag) {
        if (preg_match('/src=["\']([^"\']+)["\']/', $tag, $match)) {
            return $match[1];
        }
        return null;
    }

    private static function get_script_attributes($tag) {
        $attributes = [];
        if (preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $tag, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[2];
            }
        }
        return $attributes;
    }
}