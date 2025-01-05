<?php
/**
 * Defines rules for script handling
 */
class MACP_Script_Rules {
    /**
     * Check if script should be excluded based on handle or URL
     */
    public static function is_excluded($handle, $src, $excluded_scripts) {
        // Never exclude scripts unless they're in the user-defined exclusion list
        if (empty($excluded_scripts)) {
            return false;
        }

        // Check URL-based exclusions
        foreach ($excluded_scripts as $excluded_script) {
            if (!empty($excluded_script) && strpos($src, $excluded_script) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if script can be safely deferred
     */
    public static function can_defer($tag, $handle, $src) {
        // Skip if already has defer
        if (MACP_Script_Attributes::has_attribute($tag, 'defer')) {
            return false;
        }

        // Skip if it's an inline config script
        if (MACP_Script_Attributes::is_inline_config($tag)) {
            return false;
        }

        return true;
    }
}