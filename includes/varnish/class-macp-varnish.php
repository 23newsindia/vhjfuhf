<?php
class MACP_Varnish {
    private $varnish_servers = [];
    private $varnish_port = 6081;
    private $purge_method = 'PURGE';
    private $purge_headers = [];

    public function __construct() {
        $this->init_varnish_config();
        $this->init_hooks();
    }

    private function init_varnish_config() {
        // Get Varnish configuration from options
        $this->varnish_servers = get_option('macp_varnish_servers', ['127.0.0.1']);
        $this->varnish_port = get_option('macp_varnish_port', 6081);
        
        // Set custom headers for user segmentation
        $this->purge_headers = [
            'X-Purge-Method' => 'regex',
            'X-MACP-Host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
        ];
    }

    private function init_hooks() {
        // Add Vary header for user-specific caching
        add_action('template_redirect', [$this, 'set_cache_headers']);
        
        // Handle cache purging
        add_action('macp_clear_page_cache', [$this, 'purge_url']);
        add_action('macp_clear_all_cache', [$this, 'purge_all']);
    }

    public function set_cache_headers() {
        if (!is_user_logged_in()) {
            header('X-MACP-Cache: guest');
            header('Vary: Cookie, X-MACP-Cache');
        } else {
            $user_id = get_current_user_id();
            header('X-MACP-Cache: user-' . $user_id);
            header('Vary: Cookie, X-MACP-Cache');
            
            // Set shorter TTL for logged-in users
            header('Cache-Control: max-age=300');
        }
    }

    public function purge_url($url) {
        if (empty($url)) return false;

        foreach ($this->varnish_servers as $server) {
            $parsed_url = parse_url($url);
            $purge_url = $parsed_url['path'];
            
            if (isset($parsed_url['query'])) {
                $purge_url .= '?' . $parsed_url['query'];
            }

            $headers = $this->purge_headers;
            $headers['Host'] = $parsed_url['host'];

            // Purge both guest and user-specific caches
            $this->send_purge_request($server, $purge_url, $headers);
            
            // Also purge URL with wildcard for user-specific variations
            $this->send_purge_request($server, $purge_url . '.*', array_merge(
                $headers,
                ['X-Purge-Method' => 'regex']
            ));
        }

        return true;
    }

    public function purge_all() {
        foreach ($this->varnish_servers as $server) {
            $this->send_purge_request($server, '/.*', array_merge(
                $this->purge_headers,
                ['X-Purge-Method' => 'regex']
            ));
        }
    }

    private function send_purge_request($server, $url, $headers) {
        $parsed_url = parse_url($url);
        
        if (empty($parsed_url)) {
            return false;
        }

        $sock = fsockopen($server, $this->varnish_port, $errno, $errstr, 2);
        
        if (!$sock) {
            MACP_Debug::log("Failed to connect to Varnish: $errstr ($errno)");
            return false;
        }

        $request = "{$this->purge_method} $url HTTP/1.1\r\n";
        foreach ($headers as $key => $value) {
            $request .= "$key: $value\r\n";
        }
        $request .= "Connection: Close\r\n\r\n";

        fwrite($sock, $request);
        $response = fgets($sock);
        fclose($sock);

        return strpos($response, '200 OK') !== false;
    }
}