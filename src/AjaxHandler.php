<?php

namespace SymbolPress;
use SymbolPress\Utils;
use Exception;

class AjaxHandler{
  public static function generate_mosaic_id_ajax_handler() {
    // セキュリティチェック
    check_ajax_referer('generate_mosaic_id_nonce', 'nonce');

    $address = sanitize_text_field($_POST['address']);
    $mosaicId = SymbolService::generateMosaicId($address);

    wp_send_json_success(array(
      'mosaic_id' => $mosaicId['id'],
      'nonce' => $mosaicId['nonce']
    ));

    wp_die(); // 必ず終了する
  }

  public static function send_tranasction_ajax_handler() {
    $transactionDefinitions = include(plugin_dir_path(__FILE__) . 'Transactions/transaction_definitions.php');
    check_ajax_referer('symbol_press_nonce', 'nonce');
    $transactionType = sanitize_text_field($_POST['transaction_type']);

    foreach($transactionDefinitions as $transactionName) {
      if($transactionType == Utils::pascalToSnake($transactionName)) {
        try {
          $transactionClass = 'SymbolPress\\Transactions\\' . $transactionName;
          $tx = $transactionClass::excuteTransaction($_POST);
          wp_send_json_success(array(
            'payload' => $tx['payload'],
            'node' => $tx['node'],
            'sign_mode' => $_POST['sign_mode']
            ));
        } catch (Exception $e) {
          wp_send_json_error($e->getMessage());
        }
      }
    }
  }

  public static function load_content_ajax_handler() {
    check_ajax_referer('load_content_nonce', 'nonce');

    // POST データから inner-transaciton-id を取得
    $transaction_id = isset($_POST['inner-transaciton-id']) ? sanitize_text_field($_POST['inner-transaciton-id']) : '';

    $is_inner = isset($_POST['is-inner']) ? sanitize_text_field($_POST['is-inner']) : '';
    $shortcode = isset($_POST['shortcode']) ? sanitize_text_field($_POST['shortcode']) : 'transfer_transaction';

    // ショートコードを実行してコンテンツを取得
    $content = do_shortcode('[' . $shortcode . ' is_inner="' . $is_inner . '"]');

    // 既存のコンテンツを取得
    $existing_content = '';

    if (isset($_SESSION['loaded_content'])) {
      $existing_content = $_SESSION['loaded_content'];
    }

    // 新しいコンテンツを追加
    $new_content = $existing_content . $content;

    // セッションに追加したコンテンツを保存
    $_SESSION['loaded_content'] = $new_content;

    // JSON レスポンスを返す
    wp_send_json_success(array(
      'content' => $new_content,
      'id' => $transaction_id, // 取得したトランザクションIDを返す
    ));
    wp_die();
  }

  public static function add_array_form_ajax_handler(){
    check_ajax_referer('add_array_form_nonce', 'nonce');
    $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
    $arrays = isset($_POST['arrays']) ? sanitize_text_field($_POST['arrays']) : '';
    $arrays = explode(',', $arrays);

    $child_suffix = bin2hex(random_bytes(2));
    $output = '<div id="field-' . $child_suffix . '">';
    foreach($arrays as $array) {
      $keyValue = explode('\\\\', $array);
      $output .= '<div class="custom-input-wrapper">';
      $output .= '<input type="' . $keyValue[1] . '" id="' . $id . '-' . $keyValue[0] . '-' . $child_suffix . '" name="' . $id . '-' . $keyValue[0] . '-' . $child_suffix . '">';
      $output .= '<label for="' . $id . '-' . $keyValue[0] . '-' . $child_suffix . '">' . Utils::snakeToPascal($keyValue[0]) . '</label>';
      $output .= '</div>';
    }
    $output .= '<p><button class="remove-field-' . $child_suffix . ' wp-block-button__link has-text-align-center wp-element-button">Delete</button></p>';
    $output .= '</div>';

    wp_send_json_success(array(
      'id' => $id,
      'content' => $output,
      'child_suffix' => $child_suffix
    ));
    wp_die();
  }
}