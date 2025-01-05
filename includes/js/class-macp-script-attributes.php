<?php
/**
 * Handles script attribute parsing and validation
 */
class MACP_Script_Attributes {
    /**
     * Check if script tag has specific attribute
     */
    public static function has_attribute($tag, $attribute) {
        return strpos($tag, $attribute) !== false;
    }

    /**
     * Check if script is an inline configuration script
     */
    public static function is_inline_config($tag) {
        return strpos($tag, '-js-extra') !== false || 
               strpos($tag, 'CDATA') !== false ||
               !preg_match('/\ssrc=["\']([^"\']+)["\']/', $tag);
    }

    /**
     * Extract all attributes from a script tag
     */
    public static function extract_attributes($tag) {
        $attributes = [];
        if (preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $tag, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[2];
            }
        }
        return $attributes;
    }

    /**
     * Get src attribute from script tag
     */
    public static function get_src($tag) {
        if (preg_match('/src=["\']([^"\']+)["\']/', $tag, $match)) {
            return $match[1];
        }
        return null;
    }

    /**
     * Get inline content from script tag
     */
    public static function get_inline_content($tag) {
        if (preg_match('/<script[^>]*>(.*?)<\/script>/s', $tag, $match)) {
            return $match[1];
        }
        return '';
    }

    /**
     * Check if script should be excluded from processing
     */
    public static function is_excluded($tag, $excluded_scripts = []) {
        if (empty($excluded_scripts)) {
            return false;
        }

        $src = self::get_src($tag);
        if (!$src) {
            return self::is_inline_config($tag);
        }

        foreach ($excluded_scripts as $excluded_script) {
            if (!empty($excluded_script) && strpos($src, $excluded_script) !== false) {
                return true;
            }
        }

        return false;
    }
}