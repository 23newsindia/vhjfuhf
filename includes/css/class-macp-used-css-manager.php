<?php
class MACP_Used_CSS_Manager {
    private $table;
    private $filesystem;
    private $cache_path;

    public function __construct() {
        $this->table = new MACP_Used_CSS_Table();
        $this->filesystem = new MACP_Filesystem();
        $this->cache_path = WP_CONTENT_DIR . '/cache/macp/used-css/';
        
        $this->init();
    }

    private function init() {
        if (!file_exists($this->cache_path)) {
            wp_mkdir_p($this->cache_path);
        }
    }

    public function save_used_css($url, $css, $is_mobile = false) {
        global $wpdb;
        
        $hash = md5($css);
        $file_path = $this->get_file_path($hash);
        
        // Save CSS to file
        if (!$this->filesystem->write_file($file_path, $css)) {
            return false;
        }

        // Save to database
        return $wpdb->insert(
            $this->table->get_table_name(),
            [
                'url' => $url,
                'hash' => $hash,
                'is_mobile' => $is_mobile ? 1 : 0,
                'status' => 'completed'
            ],
            ['%s', '%s', '%d', '%s']
        );
    }

    public function get_used_css($url, $is_mobile = false) {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table->get_table_name()} 
                WHERE url = %s AND is_mobile = %d AND status = 'completed'",
                $url,
                $is_mobile ? 1 : 0
            )
        );

        if (!$row || empty($row->hash)) {
            return false;
        }

        $file_path = $this->get_file_path($row->hash);
        return $this->filesystem->get_contents($file_path);
    }

    private function get_file_path($hash) {
        return $this->cache_path . $hash . '.css';
    }

    public function clear_used_css($url = null) {
        global $wpdb;
        
        if ($url) {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT hash FROM {$this->table->get_table_name()} WHERE url = %s",
                    $url
                )
            );

            foreach ($rows as $row) {
                $file_path = $this->get_file_path($row->hash);
                $this->filesystem->delete($file_path);
            }

            $wpdb->delete(
                $this->table->get_table_name(),
                ['url' => $url],
                ['%s']
            );
        } else {
            // Clear all files
            array_map('unlink', glob($this->cache_path . '*.css'));
            
            // Clear database
            $wpdb->query("TRUNCATE TABLE {$this->table->get_table_name()}");
        }
    }
}