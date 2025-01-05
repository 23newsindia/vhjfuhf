<?php defined('ABSPATH') || exit; ?>

<div class="wrap macp-wrap">
    <h1>Cache Debug Information</h1>
    
    <div class="macp-card">
        <h2>System Status</h2>
        <table class="widefat">
            <tbody>
                <?php foreach ($status as $key => $value): ?>
                <tr>
                    <td><?php echo ucwords(str_replace('_', ' ', $key)); ?></td>
                    <td>
                        <span class="dashicons <?php echo $value ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"
                              style="color: <?php echo $value ? '#46b450' : '#dc3232'; ?>;">
                        </span>
                        <?php echo $value ? 'Yes' : 'No'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="macp-card">
        <h2>Cache Directory</h2>
        <p>Path: <?php echo WP_CONTENT_DIR . '/cache/macp'; ?></p>
        <?php if ($status['cache_dir_exists']): ?>
            <p>Files in cache:</p>
            <pre><?php echo shell_exec('ls -la ' . WP_CONTENT_DIR . '/cache/macp'); ?></pre>
        <?php else: ?>
            <p style="color: #dc3232;">Cache directory does not exist!</p>
        <?php endif; ?>
    </div>
</div>