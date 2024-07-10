<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function my_plugin_add_admin_menu() {
    add_menu_page(
        'Symbol Transactions Settings', 
        'Symbol Transactions', 
        'manage_options', 
        'my-plugin', 
        'my_plugin_options_page'
    );
}
add_action('admin_menu', 'my_plugin_add_admin_menu');

function my_plugin_options_page() {
    echo do_shortcode( '[transfer_transaction]' );
}

function my_plugin_settings_init() {
    register_setting('my_plugin_options', 'my_plugin_options');
    
    add_settings_section(
        'my_plugin_section', 
        __('Symbol Transactions Section', 'my-plugin'), 
        'my_plugin_section_callback', 
        'my_plugin'
    );
    
    add_settings_field(
        'my_plugin_field', 
        __('Enter Something', 'my-plugin'), 
        'my_plugin_field_render', 
        'my_plugin', 
        'my_plugin_section'
    );
}
add_action('admin_init', 'my_plugin_settings_init');

function my_plugin_section_callback() {
    echo __('Enter your settings below:', 'my-plugin');
}

function my_plugin_field_render() {
    $options = get_option('my_plugin_options');
    $value = isset($options['my_plugin_field']) ? $options['my_plugin_field'] : '';

    ?>
    <input type='text' name='my_plugin_options[my_plugin_field]' value='<?php echo esc_attr($value); ?>'>
    <?php
}

function my_plugin_display_message() {
    if (isset($_POST['my_plugin_field'])) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . esc_html($_POST['my_plugin_field']) . '</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'my_plugin_display_message');
