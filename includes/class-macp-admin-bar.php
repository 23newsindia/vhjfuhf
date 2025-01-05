<?php
class MACP_Admin_Bar {
    public function __construct() {
        add_action('admin_bar_menu', [$this, 'add_cache_clear_button'], 100);
        add_action('wp_ajax_macp_clear_current_page_cache', [$this, 'clear_current_page_cache']);
    }

    public function add_cache_clear_button($admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $post;
        if ($post && get_permalink($post)) {
            $admin_bar->add_node([
                'id'    => 'macp-clear-page-cache',
                'title' => 'Clear Page Cache',
                'href'  => '#',
                'meta'  => [
                    'onclick' => 'macpClearPageCache(event)',
                    'class'   => 'macp-clear-page-cache'
                ]
            ]);

            // Add required JavaScript
            add_action('wp_footer', [$this, 'add_clear_cache_script']);
            add_action('admin_footer', [$this, 'add_clear_cache_script']);
        }
    }

    public function add_clear_cache_script() {
        ?>
        <script>
        function macpClearPageCache(e) {
            e.preventDefault();
            
            const button = document.querySelector('#wp-admin-bar-macp-clear-page-cache');
            if (button) {
                button.classList.add('clearing');
            }

            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'macp_clear_current_page_cache',
                    nonce: '<?php echo wp_create_nonce('macp_clear_page_cache'); ?>',
                    post_id: '<?php echo get_the_ID(); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (button) {
                        button.querySelector('.ab-item').textContent = 'Cache Cleared!';
                        setTimeout(() => {
                            button.querySelector('.ab-item').textContent = 'Clear Page Cache';
                            button.classList.remove('clearing');
                        }, 2000);
                    }
                }
            });
        }
        </script>
        <style>
        #wp-admin-bar-macp-clear-page-cache.clearing .ab-item {
            opacity: 0.5;
            pointer-events: none;
        }
        </style>
        <?php
    }

    public function clear_current_page_cache() {
        check_ajax_referer('macp_clear_page_cache', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $post_id = intval($_POST['post_id']);
        MACP_Cache_Helper::clear_page_cache($post_id);
        
        wp_send_json_success(['message' => 'Page cache cleared successfully']);
    }
}