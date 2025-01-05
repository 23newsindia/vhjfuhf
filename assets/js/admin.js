// Convert imports to IIFE pattern for WordPress compatibility
(function($) {
    function initTextareaHandler() {
        let textareaTimeout;
        $('.macp-exclusion-section textarea').on('input', function() {
            const $textarea = $(this);
            clearTimeout(textareaTimeout);
            textareaTimeout = setTimeout(function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'macp_save_textarea',
                        option: $textarea.attr('name'),
                        value: $textarea.val(),
                        nonce: macpAdmin.nonce
                    }
                });
            }, 1000);
        });
    }

    function initToggleHandler() {
        $('.macp-toggle input[type="checkbox"]').on('change', function() {
            const $checkbox = $(this);
            const option = $checkbox.attr('name');
            const value = $checkbox.prop('checked') ? 1 : 0;

            $checkbox.prop('disabled', true);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'macp_toggle_setting',
                    option: option,
                    value: value,
                    nonce: macpAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (option === 'macp_enable_html_cache') {
                            updateStatusIndicator(value);
                        }
                    } else {
                        $checkbox.prop('checked', !value);
                    }
                },
                error: function() {
                    $checkbox.prop('checked', !value);
                },
                complete: function() {
                    $checkbox.prop('disabled', false);
                }
            });
        });
    }

    function initCSSTest() {
        $('#test-unused-css').on('click', function() {
            const $button = $(this);
            const $results = $('#test-results');
            const $status = $('.test-status');
            const $resultsBody = $('.results-body');
            const testUrl = $('#test-url').val() || window.location.origin;

            $button.prop('disabled', true).text('Testing...');
            $status.removeClass('success error').empty();
            $resultsBody.empty();
            $results.show();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'macp_test_unused_css',
                    url: testUrl,
                    nonce: macpAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayResults(response.data, testUrl, $status, $resultsBody);
                    } else {
                        displayError(response.data, $status);
                    }
                },
                error: function() {
                    displayError('Failed to test unused CSS removal', $status);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Test Unused CSS Removal');
                }
            });
        });
    }

    function initCacheHandler() {
        $('.macp-clear-cache').on('click', function(e) {
            e.preventDefault();
            const $button = $(this);

            $button.prop('disabled', true).text('Clearing...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'macp_clear_cache',
                    nonce: macpAdmin.nonce
                },
                success: function() {
                    $button.text('Cache Cleared!');
                    setTimeout(function() {
                        $button.text('Clear Cache').prop('disabled', false);
                    }, 2000);
                },
                error: function() {
                    $button.text('Error!');
                    setTimeout(function() {
                        $button.text('Clear Cache').prop('disabled', false);
                    }, 2000);
                }
            });
        });
    }

    function displayResults(data, testUrl, $status, $resultsBody) {
        $status
            .addClass('success')
            .html(`Successfully analyzed CSS for <strong>${testUrl}</strong>`);

        data.forEach(result => {
            const reduction = ((result.originalSize - result.optimizedSize) / result.originalSize * 100).toFixed(1);
            const row = `
                <tr>
                    <td>${result.file}</td>
                    <td>${formatBytes(result.originalSize)}</td>
                    <td>${formatBytes(result.optimizedSize)}</td>
                    <td>${reduction}%</td>
                    <td class="file-status ${result.success ? 'success' : 'error'}">
                        ${result.success ? '✓ Optimized' : '✗ ' + (result.error || 'Failed')}
                    </td>
                </tr>
            `;
            $resultsBody.append(row);
        });
    }

    function displayError(message, $status) {
        $status
            .addClass('error')
            .html(`Error: ${message}`);
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateStatusIndicator(value) {
        $('.macp-status-indicator')
            .toggleClass('active inactive')
            .text(value ? 'Cache Enabled' : 'Cache Disabled');
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initTextareaHandler();
        initToggleHandler();
        initCSSTest();
        initCacheHandler();
    });
})(jQuery);