<?php
require_once MACP_PLUGIN_DIR . 'includes/js/class-macp-js-buffer-handler.php';

class MACP_JS_Optimizer {
    private $excluded_scripts = [];
    private $deferred_scripts = [];
    private $buffer_handler;
    private $admin_paths = ['wp-admin', 'wp-login.php', 'admin-ajax.php'];

    public function __construct() {
        add_action('init', [$this, 'initialize_settings']);
        add_action('template_redirect', [$this, 'setup_buffering'], -9999);
        add_action('shutdown', [$this, 'end_buffering'], 9999999);
        add_action('wp_footer', [$this, 'add_delay_script'], 99999);
    }

    public function initialize_settings() {
        $this->excluded_scripts = get_option('macp_excluded_scripts', []);
        $this->deferred_scripts = get_option('macp_deferred_scripts', ['jquery-core', 'jquery-migrate']);
        $this->buffer_handler = new MACP_JS_Buffer_Handler($this->excluded_scripts);
    }

    public function setup_buffering() {
        if (!$this->is_admin_page() && get_option('macp_enable_js_delay', 0)) {
            $this->buffer_handler->start_buffering();
        }
    }

    public function end_buffering() {
        if (!$this->is_admin_page() && get_option('macp_enable_js_delay', 0)) {
            $this->buffer_handler->end_buffering();
        }
    }

    private function is_admin_page() {
        foreach ($this->admin_paths as $path) {
            if (strpos($_SERVER['REQUEST_URI'], $path) !== false) {
                return true;
            }
        }
        return false;
    }

    public function add_delay_script() {
        if (!get_option('macp_enable_js_delay', 0)) {
            return;
        }
        ?>
<script type="text/javascript">
class RocketLazyLoadScripts {
    constructor() {
        this.triggerEvents = ["keydown", "mousedown", "mousemove", "touchmove", "touchstart", "touchend", "wheel"];
        this.userEventHandler = this._triggerListener.bind(this);
        this._addEventListener(this);
    }

    _addEventListener(t) {
        this.triggerEvents.forEach(e => window.addEventListener(e, t.userEventHandler, {passive: !0}));
    }

    _triggerListener() {
        this._removeEventListener(this);
        this._loadEverythingNow();
    }

    _removeEventListener(t) {
        this.triggerEvents.forEach(e => window.removeEventListener(e, t.userEventHandler, {passive: !0}));
    }

    _loadEverythingNow() {
        document.querySelectorAll("script[type=rocketlazyloadscript]").forEach(t => {
            t.setAttribute("type", "text/javascript");
            let src = t.getAttribute("data-rocket-src");
            if (src) {
                t.removeAttribute("data-rocket-src");
                t.setAttribute("src", src);
            }
        });
    }
}

window.addEventListener('DOMContentLoaded', function() {
    new RocketLazyLoadScripts();
});
</script>
        <?php
    }
}