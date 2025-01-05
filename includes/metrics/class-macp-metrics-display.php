<?php
/**
 * Handles display of cache metrics in admin
 */
class MACP_Metrics_Display {
    private $calculator;

    public function __construct(MACP_Metrics_Calculator $calculator) {
        $this->calculator = $calculator;
    }

    public function add_metrics_page() {
        add_submenu_page(
            'macp-settings', // Parent slug
            'Cache Metrics', // Page title
            'Cache Metrics', // Menu title
            'manage_options', // Capability
            'macp-metrics', // Menu slug
            [$this, 'render_metrics_page'] // Callback function
        );
    }

    public function render_metrics_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $metrics = $this->calculator->get_all_metrics();
        ?>
        <div class="wrap macp-wrap">
            <h1>Cache Metrics</h1>

            <div class="macp-dashboard-wrap">
                <!-- Overall Stats -->
                <div class="macp-card">
                    <h2>Overall Statistics</h2>
                    <p>Total Requests: <?php echo number_format($metrics['total_requests']); ?></p>
                </div>

                <!-- HTML Cache Stats -->
                <div class="macp-card">
                    <h2>HTML Cache Performance</h2>
                    <div class="macp-stats">
                        <p>Hit Rate: <?php echo number_format($metrics['html_cache']['hit_rate'], 2); ?>%</p>
                        <p>Cache Hits: <?php echo number_format($metrics['html_cache']['hits']); ?></p>
                        <p>Cache Misses: <?php echo number_format($metrics['html_cache']['misses']); ?></p>
                    </div>
                </div>

                <!-- Redis Cache Stats -->
                <div class="macp-card">
                    <h2>Redis Cache Performance</h2>
                    <div class="macp-stats">
                        <p>Hit Rate: <?php echo number_format($metrics['redis_cache']['hit_rate'], 2); ?>%</p>
                        <p>Cache Hits: <?php echo number_format($metrics['redis_cache']['hits']); ?></p>
                        <p>Cache Misses: <?php echo number_format($metrics['redis_cache']['misses']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}