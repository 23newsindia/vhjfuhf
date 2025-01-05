<?php
class MACP_Custom_Attribute_Processor {
    private $custom_src_attributes = [
        'data-king-img-src'
    ];

    public function process($img) {
        foreach ($this->custom_src_attributes as $attr) {
            if (preg_match('/' . preg_quote($attr) . '=(["\'])(.*?)\1/', $img, $matches)) {
                $original_src = $matches[2];
                // Convert custom attribute to data-src
                $img = str_replace(
                    $attr . '=' . $matches[1] . $original_src . $matches[1],
                    'data-src=' . $matches[1] . $original_src . $matches[1],
                    $img
                );
            }
        }
        return $img;
    }
}