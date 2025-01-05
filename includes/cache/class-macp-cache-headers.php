<?php
class MACP_Cache_Headers {
    public function set_headers() {
        if (!$this->should_cache_page()) {
            header('X-MACP-Cache: BYPASS');
            return;
        }

        $cache_key = $this->get_cache_key();
        $cache_file = MACP_Cache_Helper::get_cache_path($cache_key);

        if (file_exists($cache_file)) {
            $this->set_hit_headers($cache_file);
        } else {
            $this->set_miss_headers();
        }
    }

    private function set_hit_headers($cache_file) {
        $cache_time = filemtime($cache_file);
        $max_age = get_option('macp_cache_ttl', 3600);

        header('X-MACP-Cache: HIT');
        header('Cache-Control: public, max-age=' . $max_age);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $cache_time) . ' GMT');
        header('X-MACP-Cached-On: ' . gmdate('D, d M Y H:i:s', $cache_time) . ' GMT');
        header('X-MACP-Cache-Expires: ' . gmdate('D, d M Y H:i:s', $cache_time + $max_age) . ' GMT');
    }

    private function set_miss_headers() {
        header('X-MACP-Cache: MISS');
        header('Cache-Control: no-cache');
    }
}