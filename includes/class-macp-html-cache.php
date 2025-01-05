<?php
require_once MACP_PLUGIN_DIR . 'includes/class-macp-debug.php';
require_once MACP_PLUGIN_DIR . 'includes/class-macp-filesystem.php';
require_once MACP_PLUGIN_DIR . 'includes/class-macp-url-helper.php';
require_once MACP_PLUGIN_DIR . 'includes/class-macp-cache-helper.php';
require_once MACP_PLUGIN_DIR . 'includes/metrics/class-macp-metrics-recorder.php';

class MACP_HTML_Cache {
    private $cache_dir;
    private $excluded_urls;
    private $css_optimizer;
    private $redis;
    private $metrics_recorder;
    private $html_processor;

    public function __construct() {
        $this->cache_dir = WP_CONTENT_DIR . '/cache/macp/';
        $this->excluded_urls = $this->get_excluded_urls();
        $this->redis = new MACP_Redis();
        $this->metrics_recorder = new MACP_Metrics_Recorder();
        $this->html_processor = new MACP_HTML_Processor();
        
        if (get_option('macp_remove_unused_css', 0)) {
            $this->css_optimizer = new MACP_CSS_Optimizer();
        }
        
        $this->ensure_cache_directory();
    }

    public function should_cache_page() {
        // Never cache admin pages or logged-in users
        if (is_admin() || is_user_logged_in()) {
            return false;
        }

        if (!MACP_Cache_Helper::is_cacheable_request()) {
            return false;
        }

        // Check excluded URLs
        $current_url = $_SERVER['REQUEST_URI'];
        foreach ($this->excluded_urls as $excluded_url) {
            if (strpos($current_url, $excluded_url) !== false) {
                MACP_Debug::log("Not caching: Excluded URL pattern found - {$excluded_url}");
                return false;
            }
        }

        return true;
    }

    public function start_buffer() {
        if ($this->should_cache_page()) {
            ob_start([$this, 'cache_output']);
        }
    }

    public function cache_output($buffer) {
        if (strlen($buffer) < 255) {
            $this->metrics_recorder->record_miss('html');
            return $buffer;
        }
      
      // Process HTML before caching
        $buffer = $this->html_processor->process($buffer);


        // Get cache paths
        $cache_key = MACP_Cache_Helper::get_cache_key();
        $cache_paths = [
            'html' => MACP_Cache_Helper::get_cache_path($cache_key),
            'gzip' => MACP_Cache_Helper::get_cache_path($cache_key, true)
        ];
      

        // Save uncompressed version
        if (!MACP_Filesystem::write_file($cache_paths['html'], $buffer)) {
            $this->metrics_recorder->record_miss('html');
            return $buffer;
        }

        $this->metrics_recorder->record_hit('html');
        
        // Save gzipped version if enabled
        if (get_option('macp_enable_gzip', 1)) {
            $gzipped = gzencode($buffer, 9);
            if ($gzipped) {
                MACP_Filesystem::write_file($cache_paths['gzip'], $gzipped);
            }
        }

        // Store in Redis for faster access
        if ($this->redis) {
            $this->redis->queue_set('html_' . $cache_key, $buffer, 3600);
            $this->redis->flush_queue();
        }

        return $buffer;
    }

    public function get_cached_content($key) {
        $cache_file = MACP_Cache_Helper::get_cache_path($key);
        
        if (file_exists($cache_file)) {
            $this->metrics_recorder->record_hit('html');
            return file_get_contents($cache_file);
        }

        $this->metrics_recorder->record_miss('html');
        return false;
    }

    private function get_excluded_urls() {
        return [
            'wp-login.php',
            'wp-admin',
            'wp-cron.php',
            'wp-content',
            'wp-includes',
            'xmlrpc.php',
            'wp-api',
            '/cart/',
            '/checkout/',
            '/my-account/',
            'add-to-cart',
            'logout',
            'lost-password',
            'register'
        ];
    }

    private function ensure_cache_directory() {
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
            file_put_contents($this->cache_dir . 'index.php', '<?php // Silence is golden');
        }
    }

    public function clear_cache($post_id = null) {
        if ($post_id) {
            MACP_Cache_Helper::clear_page_cache($post_id);
        } else {
            array_map('unlink', glob($this->cache_dir . '*.{html,gz}', GLOB_BRACE));
            
            // Clear all Redis HTML cache
            if ($this->redis) {
                $keys = $this->redis->keys('html_*');
                if (!empty($keys)) {
                    $this->redis->delete_pattern('html_*');
                }
            }
        }
    }
}