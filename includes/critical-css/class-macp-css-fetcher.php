<?php
class MACP_CSS_Fetcher {
    public function fetch_page_css($url) {
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }

        $html = wp_remote_retrieve_body($response);
        if (empty($html)) {
            return false;
        }

        return $this->extract_css($html, $url);
    }

    private function extract_css($html, $base_url) {
        $css = '';
        
        // Extract inline styles
        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $matches);
        if (!empty($matches[1])) {
            $css .= implode("\n", $matches[1]);
        }

        // Extract linked stylesheets
        preg_match_all('/<link[^>]*rel=[\'"]stylesheet[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*>/i', $html, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $stylesheet) {
                $style_url = $this->make_absolute_url($stylesheet, $base_url);
                $style_content = $this->fetch_stylesheet($style_url);
                if ($style_content) {
                    $css .= "\n" . $style_content;
                }
            }
        }

        return $css;
    }

    private function make_absolute_url($url, $base_url) {
        if (strpos($url, 'http') !== 0) {
            if (strpos($url, '//') === 0) {
                return 'https:' . $url;
            }
            if (strpos($url, '/') === 0) {
                $parsed = parse_url($base_url);
                return $parsed['scheme'] . '://' . $parsed['host'] . $url;
            }
            return rtrim($base_url, '/') . '/' . ltrim($url, '/');
        }
        return $url;
    }

    private function fetch_stylesheet($url) {
        $response = wp_remote_get($url);
        if (!is_wp_error($response)) {
            return wp_remote_retrieve_body($response);
        }
        return false;
    }
}