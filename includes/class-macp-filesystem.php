<?php
class MACP_Filesystem {
    public static function ensure_directory($dir) {
        if (!file_exists($dir)) {
            if (!wp_mkdir_p($dir)) {
                MACP_Debug::log("Failed to create directory: {$dir}");
                return false;
            }
            MACP_Debug::log("Created directory: {$dir}");
        }
        
        // Check directory permissions
        if (!is_writable($dir)) {
            chmod($dir, 0755);
            MACP_Debug::log("Updated permissions for: {$dir}");
        }
        
        return true;
    }

    public static function write_file($file, $content) {
        $result = file_put_contents($file, $content);
        if ($result === false) {
            MACP_Debug::log("Failed to write file: {$file}");
            return false;
        }
        MACP_Debug::log("Successfully wrote file: {$file}");
        return true;
    }
}