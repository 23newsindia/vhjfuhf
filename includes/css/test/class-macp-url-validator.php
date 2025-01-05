<?php
/**
 * Validates URLs for testing
 */
class MACP_URL_Validator {
    public function is_valid_url($url) {
        // Allow home URL
        if ($url === home_url('/')) {
            return true;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check if URL is from same domain
        $site_domain = parse_url(home_url(), PHP_URL_HOST);
        $test_domain = parse_url($url, PHP_URL_HOST);
        
        return $site_domain === $test_domain;
    }
}