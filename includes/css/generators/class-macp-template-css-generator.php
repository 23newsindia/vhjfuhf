<?php
class MACP_Template_CSS_Generator {
    public static function get_templates_list() {
        return [
            'front_page' => home_url('/'),
            'blog' => get_permalink(get_option('page_for_posts')),
            'post' => self::get_latest_post_url(),
            'page' => self::get_sample_page_url()
        ];
    }

    private static function get_latest_post_url() {
        $posts = get_posts(['numberposts' => 1]);
        return !empty($posts) ? get_permalink($posts[0]) : false;
    }

    private static function get_sample_page_url() {
        $pages = get_pages(['number' => 1]);
        return !empty($pages) ? get_permalink($pages[0]) : false;
    }
}