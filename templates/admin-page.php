<?php defined('ABSPATH') || exit; ?>
<?php defined('ABSPATH') || exit; 

// Get Redis status
$redis_status = new MACP_Redis_Status();
$redis_info = $redis_status->get_status();
?>

<div class="wrap macp-wrap">
    <h1>
        <img src="<?php echo plugins_url('assets/images/logo.png', MACP_PLUGIN_FILE); ?>" alt="Cache Plugin Logo" class="macp-logo">
        Advanced Cache Settings
    </h1>
  
<!-- Add Test Unused CSS Button and Results UI -->
    <div class="macp-card" style="margin-bottom: 20px;">
        <h2>Test Unused CSS Removal</h2>
        <div class="macp-test-controls">
            <input type="url" id="test-url" class="regular-text" placeholder="Enter URL to test (leave empty for homepage)" style="margin-right: 10px;">
            <button type="button" id="test-unused-css" class="button button-primary">Test Unused CSS Removal</button>
        </div>
        
        <div id="test-results" style="display: none; margin-top: 15px;">
            <h3>Test Results</h3>
            <div class="test-status"></div>
            <div class="results-table-wrap" style="margin-top: 10px;">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>CSS File</th>
                            <th>Original Size</th>
                            <th>Optimized Size</th>
                            <th>Reduction</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="results-body"></tbody>
                </table>
            </div>
        </div>
  </div>
  

    <div class="macp-dashboard-wrap">
        <!-- Status Card -->
        <div class="macp-card macp-status-card">
            <h2>Cache Status</h2>
            
            <!-- Redis Status -->
            <div class="macp-status-indicator <?php echo $redis_info['available'] ? 'active' : 'inactive'; ?>">
                Redis: <?php echo $redis_info['available'] ? 'Connected' : 'Not Connected'; ?>
            </div>
            
            <!-- HTML Cache Status -->
            <div class="macp-status-indicator <?php echo $settings['enable_html_cache'] ? 'active' : 'inactive'; ?>">
                HTML Cache: <?php echo $settings['enable_html_cache'] ? 'Enabled' : 'Disabled'; ?>
            </div>
            
            <?php if ($redis_info['available']): ?>
            <div class="macp-status-details">
                <p>Redis Version: <?php echo esc_html($redis_info['version']); ?></p>
                <p>Memory Usage: <?php echo esc_html($redis_info['memory_usage']); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Cache Metrics Summary -->
            <?php if ($settings['enable_html_cache']): ?>
            <div class="macp-metrics-summary">
                <p>HTML Cache Hit Rate: <?php echo number_format($metrics['html_cache']['hit_rate'], 1); ?>%</p>
                <p>Redis Cache Hit Rate: <?php echo number_format($metrics['redis_cache']['hit_rate'], 1); ?>%</p>
                <a href="<?php echo admin_url('admin.php?page=macp-metrics'); ?>" class="button button-secondary">View Detailed Metrics</a>
            </div>
            <?php endif; ?>
            
            <button class="button button-primary macp-clear-cache">Clear Cache</button>
        </div>

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

            <!-- Optimization Options -->
            <div class="macp-card">
                <h2>Optimization Options</h2>
                
                <label class="macp-toggle">
                    <input type="checkbox" name="macp_minify_html" value="1" <?php checked($settings['minify_html'], 1); ?>>
                    <span class="macp-toggle-slider"></span>
                    Minify HTML
                </label>

                <label class="macp-toggle">
                    <input type="checkbox" name="macp_minify_css" value="1" <?php checked($settings['minify_css'], 1); ?>>
                    <span class="macp-toggle-slider"></span>
                    Minify CSS
                </label>

                <label class="macp-toggle">
                    <input type="checkbox" name="macp_minify_js" value="1" <?php checked($settings['minify_js'], 1); ?>>
                    <span class="macp-toggle-slider"></span>
                    Minify JavaScript
                </label>

                <label class="macp-toggle">
                    <input type="checkbox" name="macp_remove_unused_css" value="1" <?php checked($settings['remove_unused_css'], 1); ?>>
                    <span class="macp-toggle-slider"></span>
                    Remove Unused CSS
                </label>

                <label class="macp-toggle">
                    <input type="checkbox" name="macp_process_external_css" value="1" <?php checked($settings['process_external_css'], 1); ?>>
                    <span class="macp-toggle-slider"></span>
                    Process External CSS
                </label>
            </div>
          <!-- Include lazy load settings -->
            <?php include MACP_PLUGIN_DIR . 'templates/lazy-load-settings.php'; ?>


            <!-- Include other sections -->
            <?php include MACP_PLUGIN_DIR . 'templates/css-exclusions.php'; ?>
            <?php include MACP_PLUGIN_DIR . 'templates/js-optimization.php'; ?>
            <?php include MACP_PLUGIN_DIR . 'templates/critical-css-section.php'; ?>

            <!-- Varnish Settings -->
            <?php 
            if (class_exists('MACP_Varnish_Settings')) {
                $varnish_settings = new MACP_Varnish_Settings();
                $varnish_settings->render_settings(); 
            }
            ?>

            <?php submit_button('Save Changes', 'primary', 'macp_save_settings'); ?>
        </form>
    </div>
</div>