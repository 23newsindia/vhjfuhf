<?php
class MACP_Varnish_Settings {
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('macp_varnish_settings', 'macp_enable_varnish');
        register_setting('macp_varnish_settings', 'macp_varnish_servers');
        register_setting('macp_varnish_settings', 'macp_varnish_port');
    }

    public function render_settings() {
        ?>
        <div class="macp-card">
            <h2>Varnish Configuration</h2>
            
            <label class="macp-toggle">
                <input type="checkbox" name="macp_enable_varnish" value="1" 
                    <?php checked(get_option('macp_enable_varnish'), 1); ?>>
                <span class="macp-toggle-slider"></span>
                Enable Varnish Cache Integration
            </label>

            <div class="macp-option-group">
                <h3>Varnish Servers</h3>
                <textarea name="macp_varnish_servers" rows="3" class="large-text code"><?php 
                    echo esc_textarea(implode("\n", get_option('macp_varnish_servers', ['127.0.0.1']))); 
                ?></textarea>
                <p class="description">Enter one server IP per line</p>
            </div>

            <div class="macp-option-group">
                <label>
                    <span>Varnish Port</span>
                    <input type="number" name="macp_varnish_port" value="<?php 
                        echo esc_attr(get_option('macp_varnish_port', 80)); 
                    ?>" class="small-text">
                </label>
            </div>

            <div class="macp-option-group">
                <h3>VCL Configuration</h3>
                <p>Copy and paste this VCL configuration to your Varnish server:</p>
                <textarea readonly rows="10" class="large-text code"><?php 
                    echo esc_textarea(MACP_VCL_Generator::generate_vcl()); 
                ?></textarea>
            </div>
        </div>
        <?php
    }
}