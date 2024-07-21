<?php
/**
 * Plugin Name: Symbol Press
 * Description: A WordPress plugin for symbol transactions.
 * Version: 1.0
 * Author: toshi
 * License: MIT
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wpdb;

register_activation_hook(__FILE__, 'symbol_press_activate');

function symbol_press_activate() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'symbol_press_table';

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      node text NOT NULL,
      fee_multi_plier tinyint NOT NULL,
      deadline_seconds smallint NOT NULL,
      PRIMARY KEY  (id)
  ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

// Composerのオートローダーを読み込み
require_once __DIR__ . '/vendor/autoload.php';

// 管理画面用のコードを読み込み
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';

// ショートコードハンドラを読み込み
require_once plugin_dir_path(__FILE__) . 'src/ShortcodeHandler.php';

// Ajaxハンドラを読み込み
require_once plugin_dir_path(__FILE__) . 'src/AjaxHandler.php';

// JavaScriptファイルを登録
function symbol_press_enqueue_scripts() {
  // CSSファイルを登録
  wp_enqueue_style(
    'symbol-press-style',
    plugins_url('public/css/style.css', __FILE__),
    array(),
    filemtime(plugin_dir_path(__FILE__) . 'public/css/style.css')
  );

  // nonceをローカライズしてJavaScriptに渡す
  wp_enqueue_script('symbol-press-script', plugins_url('public/js/script.js', __FILE__), array('jquery'), null, true);
  wp_localize_script('symbol-press-script', 'symbol_press', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('symbol_press_nonce'),));

  wp_enqueue_script('form-draw-script', plugin_dir_url(__FILE__) . 'public/js/form-draw.js', array('jquery'), null, true);
  wp_localize_script('form-draw-script', 'form_draw', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('load_content_nonce')));

  wp_enqueue_script('generate-mosaic-id-script', plugin_dir_url(__FILE__) . 'public/js/generate-mosaic-id.js', array('jquery'), null, true);
  wp_localize_script('generate-mosaic-id-script', 'generate_mosaic_id', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('generate_mosaic_id_nonce')));

  wp_enqueue_script('add-array-form-script', plugin_dir_url(__FILE__) . 'public/js/add-array-form.js', array('jquery'), null, true);
  wp_localize_script('add-array-form-script', 'add_array_form', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('add_array_form_nonce')));

  wp_enqueue_script('jquery-qrcode', plugins_url('public/js/jquery.qrcode.js', __FILE__), array('jquery'), null, true);
}

add_action('wp_enqueue_scripts', 'symbol_press_enqueue_scripts');
add_action('admin_enqueue_scripts', 'symbol_press_enqueue_scripts');

add_action('wp_ajax_generate_mosaic_id', array('SymbolPress\AjaxHandler', 'generate_mosaic_id_ajax_handler'));
add_action('wp_ajax_nopriv_generate_mosaic_id', array('SymbolPress\AjaxHandler', 'generate_mosaic_id_ajax_handler'));

add_action('wp_ajax_load_content', array('SymbolPress\AjaxHandler', 'load_content_ajax_handler'));
add_action('wp_ajax_nopriv_load_content', array('SymbolPress\AjaxHandler', 'load_content_ajax_handler'));

add_action('wp_ajax_send_transaction', array('SymbolPress\AjaxHandler', 'send_tranasction_ajax_handler'));
add_action('wp_ajax_nopriv_send_transaction', array('SymbolPress\AjaxHandler', 'send_tranasction_ajax_handler'));

add_action('wp_ajax_add_array_form', array('SymbolPress\AjaxHandler', 'add_array_form_ajax_handler'));
add_action('wp_ajax_nopriv_add_array_form', array('SymbolPress\AjaxHandler', 'add_array_form_ajax_handler'));