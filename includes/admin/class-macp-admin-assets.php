<?php
/**
 * Handles admin assets (CSS/JS) enqueuing
 */
class MACP_Admin_Assets {
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'macp-') === false) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'macp-admin',
            plugins_url('assets/css/admin.css', MACP_PLUGIN_FILE)
        );

        // Enqueue JS
        wp_enqueue_script(
            'macp-admin',
            plugins_url('assets/js/admin.js', MACP_PLUGIN_FILE),
            ['jquery'],
            '1.0',
            true
        );

        // Add localized script data
        wp_localize_script('macp-admin', 'macp_admin', [
            'nonce' => wp_create_nonce('macp_admin_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }
}