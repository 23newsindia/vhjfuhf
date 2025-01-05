<?php
/**
 * Handles HTML attribute optimization
 */
class MACP_Attribute_Processor {
    public function process($html) {
        // Remove unnecessary quotes from attributes
        $html = preg_replace('/(\s+[a-zA-Z-]+)=(["\'])([a-zA-Z0-9-_]+)\2/', '$1=$3', $html);
        
        // Remove type="text/javascript" from scripts
        $html = preg_replace('/<script(\s+[^>]*)?(\s+type=["\']text\/javascript["\'])([^>]*)>/', '<script$1$3>', $html);
        
        // Remove type="text/css" from styles
        $html = preg_replace('/<style(\s+[^>]*)?(\s+type=["\']text\/css["\'])([^>]*)>/', '<style$1$3>', $html);
        
        // Remove unnecessary attributes from links
        $html = preg_replace('/<link([^>]+)(?:\s+type=["\']text\/css["\'])([^>]*)>/', '<link$1$2>', $html);
        
        return $html;
    }
}