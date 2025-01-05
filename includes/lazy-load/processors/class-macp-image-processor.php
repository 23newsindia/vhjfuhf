<?php
class MACP_Image_Processor {
    private $excluded_classes = ['no-lazy', 'skip-lazy'];
    private $placeholder_svg = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
    private $custom_processor;

    public function __construct() {
        $this->custom_processor = new MACP_Custom_Attribute_Processor();
    }

    public function process($html) {
        if (empty($html)) {
            return $html;
        }

        // Process picture elements first
        $html = preg_replace_callback(
            '/<picture.*?>.*?<\/picture>/is',
            function($matches) {
                $picture = $matches[0];
                
                // Process source tags
                $picture = preg_replace_callback(
                    '/<source[^>]*>/i',
                    function($sourceMatches) {
                        $source = $sourceMatches[0];
                        if (strpos($source, 'srcset') !== false) {
                            $source = str_replace('srcset=', 'data-srcset=', $source);
                        }
                        return $source;
                    },
                    $picture
                );

                // Process img tag within picture
                $picture = preg_replace_callback(
                    '/<img[^>]*>/i',
                    [$this, 'process_image'],
                    $picture
                );

                return $picture;
            },
            $html
        );

        // Process remaining images
        return preg_replace_callback(
            '/<img[^>]*>/i',
            [$this, 'process_image'],
            $html
        );
    }

    private function process_image($matches) {
        $img = $matches[0];

        // Skip if already processed or excluded
        if ($this->should_skip($img)) {
            return $img;
        }

        // Process custom attributes first
        $img = $this->custom_processor->process($img);

        // Add lazy load class
        $img = $this->add_lazy_load_class($img);

        // Add placeholder src if no src exists
        if (!preg_match('/\ssrc=["\']/', $img)) {
            $img = str_replace('<img', '<img src="' . $this->placeholder_svg . '"', $img);
        }

        return $img;
    }

    private function should_skip($img) {
        // Skip if already has macp-lazy class
        if (strpos($img, 'macp-lazy') !== false) {
            return true;
        }

        // Check for excluded classes
        foreach ($this->excluded_classes as $class) {
            if (preg_match('/class=["\'][^"\']*\b' . $class . '\b[^"\']*["\']/', $img)) {
                return true;
            }
        }

        return false;
    }

    private function add_lazy_load_class($img) {
        if (preg_match('/class=(["\'])(.*?)\1/', $img, $matches)) {
            $classes = $matches[2];
            if (strpos($classes, 'macp-lazy') === false) {
                $new_classes = $classes . ' macp-lazy';
                $img = str_replace('class=' . $matches[1] . $classes . $matches[1], 
                                 'class=' . $matches[1] . $new_classes . $matches[1], 
                                 $img);
            }
        } else {
            $img = str_replace('<img', '<img class="macp-lazy"', $img);
        }
        return $img;
    }
}