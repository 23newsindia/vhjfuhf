<?php
class MACP_Picture_Processor {
    public function process($html) {
        return preg_replace_callback(
            '/<picture.*?>.*?<\/picture>/is',
            [$this, 'process_picture_tag'],
            $html
        );
    }

    private function process_picture_tag($matches) {
        $picture = $matches[0];
        
        // Process source tags
        $picture = preg_replace_callback(
            '/<source[^>]*>/i',
            [$this, 'process_source'],
            $picture
        );

        // Process img tag
        $picture = preg_replace_callback(
            '/<img[^>]*>/i',
            [$this, 'process_img'],
            $picture
        );

        return $picture;
    }

    private function process_source($matches) {
        $source = $matches[0];
        if (strpos($source, 'srcset') !== false) {
            $source = preg_replace('/srcset=(["\'])(.*?)\1/i', 'data-srcset=$1$2$1', $source);
        }
        return $source;
    }

    private function process_img($matches) {
        $img = $matches[0];

        // Skip if already processed
        if (strpos($img, 'data-src') !== false || strpos($img, 'macp-lazy') !== false) {
            return $img;
        }

        // Replace src with data-src
        $img = preg_replace(
            '/\ssrc=(["\'])(.*?)\1/i',
            ' src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E" data-src=$1$2$1',
            $img
        );
        
        // Add lazy loading class
        if (strpos($img, 'class=') !== false) {
            $img = preg_replace('/class=(["\'])(.*?)\1/i', 'class="$2 macp-lazy"', $img);
        } else {
            $img = str_replace('<img', '<img class="macp-lazy"', $img);
        }

        return $img;
    }
}