<?php
/**
 * Handles script defer functionality
 */
class MACP_Defer_Handler {
    /**
     * Check if script should be deferred based on handle/URL
     */
    public static function should_defer($src, $deferred_scripts = []) {
        if (empty($deferred_scripts)) {
            return false;
        }

        foreach ($deferred_scripts as $pattern) {
            if (!empty($pattern) && strpos($src, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process script tag for defer
     */
    public static function process_tag($tag, $deferred_scripts = []) {
        $src = MACP_Script_Attributes::get_src($tag);
        
        // Skip if no src or already has defer
        if (!$src || MACP_Script_Attributes::has_attribute($tag, 'defer')) {
            return $tag;
        }

        // Add defer if script matches patterns
        if (self::should_defer($src, $deferred_scripts)) {
            return str_replace(' src=', ' defer="defer" src=', $tag);
        }

        return $tag;
    }
}