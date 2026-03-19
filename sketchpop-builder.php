<?php
/**
 * Plugin Name: SketchPop: Quick App & Promo Builder
 * Description: A distinctive, lightweight popup builder for App installs and Image banners. Optimized for speed and security.
 * Version: 5.1.1
 * Author: Sanjit Chaurasiya
 * Author URI: https://sketchleader.com
 * License: GPL2
 * Text Domain: sketchpop-builder
 */

// Block direct access for security
if (!defined('ABSPATH')) exit;

/**
 * 1. CONSTANTS
 */
define('SKPOP_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * 2. ADMIN MENU
 */
add_action('admin_menu', 'skpop_add_plugin_menu');
function skpop_add_plugin_menu() {
    add_menu_page(
        'SketchPop', 
        'SketchPop', 
        'manage_options', 
        'sketchpop-settings', 
        'skpop_render_settings_page', 
        'dashicons-megaphone', 
        110
    );
}

/**
 * 3. ENQUEUE ASSETS & INLINE SCRIPTS
 * Properly enqueued to satisfy WordPress.org requirements.
 */
add_action('admin_enqueue_scripts', 'skpop_load_admin_assets');
function skpop_load_admin_assets($hook) {
    if ($hook !== 'toplevel_page_sketchpop-settings') {
        return;
    }

    wp_enqueue_media();

    // Enqueue a script handle to attach our inline logic
    wp_enqueue_script('skpop-admin-logic', SKPOP_PLUGIN_URL . 'script.js', array('jquery'), '5.1.1', true);

    // Media Uploader logic moved from inline HTML to wp_add_inline_script
    $skpop_inline_js = "
        jQuery(document).ready(function($){
            $('#skpop_media_btn').click(function(e) {
                e.preventDefault();
                var skpop_frame = wp.media({
                    title: 'Select Popup Image',
                    multiple: false
                }).on('select', function() {
                    var selection = skpop_frame.state().get('selection').first().toJSON();
                    $('#skpop_img_path').val(selection.url);
                }).open();
            });
        });
    ";
    wp_add_inline_script('skpop-admin-logic', $skpop_inline_js);
}

add_action('wp_enqueue_scripts', 'skpop_load_frontend_assets');
function skpop_load_frontend_assets() {
    wp_enqueue_style('skpop-core-style', SKPOP_PLUGIN_URL . 'style.css', array(), '5.1.1');
    wp_enqueue_script('skpop-core-js', SKPOP_PLUGIN_URL . 'script.js', array('jquery'), '5.1.1', true);
}

/**
 * 4. SETTINGS & SANITIZATION
 */
add_action('admin_init', 'skpop_register_plugin_settings');
function skpop_register_plugin_settings() {
    register_setting('skpop_settings_group', 'skpop_options_data', array(
        'sanitize_callback' => 'skpop_sanitize_all_inputs'
    ));
}

function skpop_sanitize_all_inputs($input) {
    $clean = array();
    $clean['enabled']    = isset($input['enabled']) ? 1 : 0;
    $clean['hide_wm']    = isset($input['hide_wm']) ? 1 : 0; 
    $clean['hide_close'] = isset($input['hide_close']) ? 1 : 0;
    
    // Ensure we never pass null to sanitization functions
    $clean['title']       = sanitize_text_field(isset($input['title']) ? $input['title'] : '');
    $clean['btn_text']    = sanitize_text_field(isset($input['btn_text']) ? $input['btn_text'] : '');
    $clean['description'] = sanitize_textarea_field(isset($input['description']) ? $input['description'] : '');
    $clean['link_url']    = esc_url_raw(isset($input['link_url']) ? $input['link_url'] : '');
    $clean['image_url']   = esc_url_raw(isset($input['image_url']) ? $input['image_url'] : '');
    
    return $clean;
}

/**
 * 5. ADMIN DASHBOARD UI
 * Includes Null-checks to prevent PHP 8.1+ Deprecated warnings.
 */
function skpop_render_settings_page() {
    $options = get_option('skpop_options_data');
    
    // Fallback to empty strings to prevent 'null to parameter' errors
    $val_title = isset($options['title']) ? $options['title'] : '';
    $val_desc  = isset($options['description']) ? $options['description'] : '';
    $val_btn   = isset($options['btn_text']) ? $options['btn_text'] : '';
    $val_link  = isset($options['link_url']) ? $options['link_url'] : '';
    $val_img   = isset($options['image_url']) ? $options['image_url'] : '';
    ?>
    <div class="wrap">
        <h1 style="margin-bottom: 20px;">SketchPop Builder <span style="font-weight: 300; font-size: 14px;">v5.1.1</span></h1>
        
        <div style="background: #fff; border: 1px solid #ccd0d4; padding: 25px; border-radius: 10px; max-width: 650px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <form method="post" action="options.php">
                <?php settings_fields('skpop_settings_group'); ?>
                
                <div style="display:flex; gap:20px; border-bottom:1px solid #eee; padding-bottom:20px; margin-bottom:20px;">
                    <label><input type="checkbox" name="skpop_options_data[enabled]" value="1" <?php checked(1, @$options['enabled']); ?> /> <strong>Enable Popup</strong></label>
                    <label><input type="checkbox" name="skpop_options_data[hide_wm]" value="1" <?php checked(1, @$options['hide_wm']); ?> /> Hide Credit</label>
                    <label><input type="checkbox" name="skpop_options_data[hide_close]" value="1" <?php checked(1, @$options['hide_close']); ?> /> Hide Close (X)</label>
                </div>

                <p><strong>Popup Title</strong><br>
                <input type="text" name="skpop_options_data[title]" value="<?php echo esc_attr($val_title); ?>" class="regular-text" /></p>

                <p><strong>Description</strong><br>
                <textarea name="skpop_options_data[description]" class="regular-text" style="height:80px;"><?php echo esc_textarea($val_desc); ?></textarea></p>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <p><strong>Button Text</strong><br>
                    <input type="text" name="skpop_options_data[btn_text]" value="<?php echo esc_attr($val_btn); ?>" style="width:100%;" /></p>
                    
                    <p><strong>Button URL</strong><br>
                    <input type="text" name="skpop_options_data[link_url]" value="<?php echo esc_attr($val_link); ?>" style="width:100%;" /></p>
                </div>

                <p><strong>Banner Image (Optional)</strong><br>
                    <input type="text" id="skpop_img_path" name="skpop_options_data[image_url]" value="<?php echo esc_attr($val_img); ?>" style="width:75%;" /> 
                    <button type="button" id="skpop_media_btn" class="button">Upload Image</button>
                </p>

                <div style="margin-top: 25px;">
                    <?php submit_button('Update SketchPop Settings'); ?>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * 6. FRONTEND DISPLAY LOGIC
 */
add_action('wp_footer', 'skpop_render_frontend_html');
function skpop_render_frontend_html() {
    $opt = get_option('skpop_options_data');
    
    // Safety check: ensure title is a string and not empty
    $display_title = isset($opt['title']) ? $opt['title'] : '';
    if (empty($opt['enabled']) || empty($display_title)) return;

    $display_desc = isset($opt['description']) ? $opt['description'] : '';
    $display_link = isset($opt['link_url']) ? $opt['link_url'] : '';
    $display_btn  = isset($opt['btn_text']) ? $opt['btn_text'] : 'Check it Out';
    $display_img  = isset($opt['image_url']) ? $opt['image_url'] : '';

    ?>
    <div id="skpop-wrapper">
        <div class="skpop-overlay"></div>
        <div class="skpop-card skpop-entrance">
            <?php if (empty($opt['hide_close'])): ?>
                <button class="skpop-close-btn">&times;</button>
            <?php endif; ?>

            <?php if (!empty($display_img)): ?>
                <div class="skpop-image">
                    <img src="<?php echo esc_url($display_img); ?>" alt="Promotion">
                </div>
            <?php endif; ?>

            <div class="skpop-content">
                <h2><?php echo esc_html($display_title); ?></h2>
                <p><?php echo esc_html($display_desc); ?></p>
                
                <a href="<?php echo esc_url($display_link); ?>" class="skpop-cta">
                    <?php echo esc_html($display_btn); ?>
                </a>

                <?php if (empty($opt['hide_wm'])): ?>
                    <div class="skpop-brand">
                        Developed By <a href="https://sketchleader.com" target="_blank">Sketchleader</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
