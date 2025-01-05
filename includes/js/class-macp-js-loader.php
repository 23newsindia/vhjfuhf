<?php
/**
 * Provides JavaScript loading functionality
 */
class MACP_JS_Loader {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_loader_script() {
        return <<<'EOT'
<script>
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
EOT;
    }
}