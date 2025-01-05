<?php defined('ABSPATH') || exit; ?>

<!-- Settings Form -->
<form method="post" action="" class="macp-settings-form">
    <?php wp_nonce_field('macp_save_settings_nonce'); ?>
    
    <!-- Cache Options -->
    <div class="macp-card">
        <h2>Cache Options</h2>
        
        <label class="macp-toggle">
            <input type="checkbox" name="macp_enable_redis" value="1" <?php checked($settings['enable_redis'], 1); ?>>
            <span class="macp-toggle-slider"></span>
            Enable Redis Object Cache
        </label>

        <label class="macp-toggle">
            <input type="checkbox" name="macp_enable_html_cache" value="1" <?php checked($settings['enable_html_cache'], 1); ?>>
            <span class="macp-toggle-slider"></span>
            Enable HTML Cache
        </label>

        <label class="macp-toggle">
            <input type="checkbox" name="macp_enable_gzip" value="1" <?php checked($settings['enable_gzip'], 1); ?>>
            <span class="macp-toggle-slider"></span>
            Enable GZIP Compression
        </label>
    </div>

    <!-- Rest of your settings form content -->
</form>