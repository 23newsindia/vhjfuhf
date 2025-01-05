<?php
/**
 * Handles WordPress script processing and modifications
 */
class MACP_Script_Handler {
    private $excluded_scripts = [];

    public function __construct() {
        $this->excluded_scripts = get_option('macp_excluded_scripts', []);
        $this->init_hooks();
    }

    private function init_hooks() {
        // Only modify scripts on frontend
        if (!is_admin()) {
            add_filter('script_loader_tag', [$this, 'process_script_tag'], 10, 3);
        }
    }

    public function process_script_tag($tag, $handle, $src) {
        // Skip processing if defer is not enabled
        if (!get_option('macp_enable_js_defer', 0)) {
            return $tag;
        }

        // Skip if script is excluded
        if (MACP_Script_Rules::is_excluded($handle, $src, $this->excluded_scripts)) {
            return $tag;
        }

        // Check if script can be deferred
        if (!MACP_Script_Rules::can_defer($tag, $handle, $src)) {
            return $tag;
        }

        // Add defer attribute if not already present
        if (strpos($tag, 'defer="defer"') === false) {
            $tag = str_replace(' src=', ' defer="defer" src=', $tag);
        }

        return $tag;
    }
}