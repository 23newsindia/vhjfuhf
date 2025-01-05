// Initialize vanilla-lazyload
window.addEventListener('load', function() {
    window.lazyLoadInstance = new LazyLoad({
        elements_selector: ".macp-lazy",
        use_native: true,
        threshold: 300,
        callback_enter: function(element) {
            // Handle picture element sources
            if (element.parentNode.tagName === 'PICTURE') {
                element.parentNode.querySelectorAll('source').forEach(function(source) {
                    if (source.dataset.srcset) {
                        source.srcset = source.dataset.srcset;
                    }
                });
            }
        },
        callback_loaded: function(element) {
            element.classList.add('macp-lazy-loaded');
        },
        callback_error: function(element) {
            if (element.dataset.src) {
                element.src = element.dataset.src;
            }
        }
    });
});

// Update lazy loading on dynamic content
document.addEventListener('macp_content_updated', function() {
    if (window.lazyLoadInstance) {
        window.lazyLoadInstance.update();
    }
});