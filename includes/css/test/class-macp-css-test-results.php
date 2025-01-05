<?php
/**
 * Handles CSS test results processing
 */
class MACP_CSS_Test_Results {
    public function format_results($css_files) {
        $results = [];
        
        foreach ($css_files as $file) {
            $result = [
                'file' => $this->get_relative_path($file['file']),
                'originalSize' => $file['originalSize'],
                'optimizedSize' => $file['optimizedSize'],
                'success' => $file['success']
            ];

            if (isset($file['error'])) {
                $result['error'] = $file['error'];
            }

            $results[] = $result;
        }

        return $results;
    }

    private function get_relative_path($url) {
        $site_url = site_url();
        return str_replace($site_url, '', $url);
    }
}