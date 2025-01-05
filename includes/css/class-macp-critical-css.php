<?php
/**
 * Handles critical CSS generation process
 */
class MACP_Critical_CSS {
    private $process;
    private $settings_manager;
    private $filesystem;
    private $critical_css_path;
    private $items = [];

    public function __construct(MACP_Critical_CSS_Generation $process, MACP_Settings_Manager $settings_manager, $filesystem) {
        $this->process = $process;
        $this->settings_manager = $settings_manager;
        $this->filesystem = $filesystem;
        $this->critical_css_path = WP_CONTENT_DIR . '/cache/macp/critical-css/';
        
        // Initialize default items
        $this->items['front_page'] = [
            'type' => 'front_page',
            'url' => home_url('/'),
            'path' => 'front_page.css',
            'check' => 0
        ];
    }

    public function get_critical_css_path() {
        return $this->critical_css_path;
    }

    public function process_handler($version = 'default', $clean_version = '') {
        if (get_transient('macp_critical_css_generation_running')) {
            return;
        }

        if (empty($clean_version)) {
            $clean_version = $version;
        }

        $this->clean_critical_css($clean_version);
        $this->stop_generation();
        $this->set_items($version);

        array_map([$this->process, 'push_to_queue'], $this->items);
        $this->update_process_running_transient();
        $this->process->save()->dispatch();
    }

    public function stop_generation() {
        if (method_exists($this->process, 'cancel_process')) {
            $this->process->cancel_process();
        }
    }

    public function clean_critical_css($version = 'default') {
        $files = glob($this->critical_css_path . '*.css');
        
        if (!is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            if (!$this->filesystem->is_file($file)) {
                continue;
            }

            if ('mobile' === $version && false === strpos($file, '-mobile')) {
                continue;
            } elseif ('default' === $version && false !== strpos($file, '-mobile')) {
                continue;
            }

            $this->filesystem->delete($file);
        }
    }

    private function set_items($version = 'default') {
        // Add blog page if using static front page
        if ('page' === get_option('show_on_front') && !empty(get_option('page_for_posts'))) {
            $this->items['home'] = [
                'type' => 'home',
                'url' => get_permalink(get_option('page_for_posts')),
                'path' => 'home.css',
                'check' => 0
            ];
        }

        // Add post types
        $post_types = get_post_types(['public' => true], 'objects');
        foreach ($post_types as $post_type) {
            $posts = get_posts([
                'post_type' => $post_type->name,
                'posts_per_page' => 1,
                'post_status' => 'publish'
            ]);

            if (!empty($posts)) {
                $this->items[$post_type->name] = [
                    'type' => $post_type->name,
                    'url' => get_permalink($posts[0]->ID),
                    'path' => "{$post_type->name}.css",
                    'check' => 0
                ];
            }
        }

        // Add taxonomies
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy->name,
                'number' => 1,
                'hide_empty' => true
            ]);

            if (!empty($terms) && !is_wp_error($terms)) {
                $this->items[$taxonomy->name] = [
                    'type' => $taxonomy->name,
                    'url' => get_term_link($terms[0]),
                    'path' => "{$taxonomy->name}.css",
                    'check' => 0
                ];
            }
        }

        // Handle mobile versions
        if (in_array($version, ['all', 'mobile'], true)) {
            $mobile_items = [];

            foreach ($this->items as $key => $value) {
                $value['mobile'] = 1;
                $value['path'] = str_replace('.css', '-mobile.css', $value['path']);
                $mobile_items["{$key}-mobile"] = $value;
            }

            if ('mobile' === $version) {
                $this->items = $mobile_items;
            } elseif ('all' === $version) {
                $this->items = array_merge($this->items, $mobile_items);
            }
        }
    }

    private function update_process_running_transient() {
        $total = 0;
        foreach ($this->items as $item) {
            if (!isset($item['mobile'])) {
                $total++;
                continue;
            }
            if (1 !== $item['mobile']) {
                $total++;
            }
        }

        $transient = [
            'total' => $total,
            'items' => []
        ];

        set_transient('macp_critical_css_generation_running', $transient, HOUR_IN_SECONDS);
    }

    public function get_critical_css_content() {
        $filename = $this->get_current_page_critical_css();
        
        if (empty($filename)) {
            return $this->settings_manager->get_setting('critical_css', '');
        }

        return $this->filesystem->get_contents($filename);
    }

    public function get_current_page_critical_css() {
        $files = $this->get_critical_css_filenames();

        if ($this->is_mobile_cpcss_active() && wp_is_mobile() && 
            $this->filesystem->is_readable($this->critical_css_path . $files['mobile'])) {
            return $this->critical_css_path . $files['mobile'];
        }

        if ($this->filesystem->is_readable($this->critical_css_path . $files['default'])) {
            return $this->critical_css_path . $files['default'];
        }

        return '';
    }

    private function get_critical_css_filenames() {
        $default = [
            'default' => 'front_page.css',
            'mobile' => 'front_page-mobile.css'
        ];

        if (is_home() && 'page' === get_option('show_on_front')) {
            return [
                'default' => 'home.css',
                'mobile' => 'home-mobile.css'
            ];
        }

        if (is_front_page()) {
            return $default;
        }

        if (is_singular()) {
            $post_type = get_post_type();
            return [
                'default' => "{$post_type}.css",
                'mobile' => "{$post_type}-mobile.css"
            ];
        }

        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            return [
                'default' => "{$term->taxonomy}.css",
                'mobile' => "{$term->taxonomy}-mobile.css"
            ];
        }

        return $default;
    }

    private function is_mobile_cpcss_active() {
        return $this->settings_manager->get_setting('do_caching_mobile_files', 0) && 
               $this->settings_manager->get_setting('async_css_mobile', 0);
    }
}