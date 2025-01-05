<?php
class MACP_Used_CSS_Table {
    private $table_name;
    private $db_version = '1.0';
    private $db_version_key = 'macp_used_css_version';

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'macp_used_css';
    }

    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            url varchar(2000) NOT NULL default '',
            css longtext default NULL,
            hash varchar(32) default '',
            error_code varchar(32) NULL default NULL,
            error_message longtext NULL default NULL,
            retries tinyint(1) NOT NULL default 1,
            is_mobile tinyint(1) NOT NULL default 0,
            status varchar(255) NOT NULL default '',
            modified timestamp NOT NULL default CURRENT_TIMESTAMP,
            last_accessed timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY url (url(150), is_mobile),
            KEY modified (modified),
            KEY last_accessed (last_accessed),
            KEY hash (hash)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option($this->db_version_key, $this->db_version);
    }

    public function drop_table() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->table_name}");
        delete_option($this->db_version_key);
    }
}