<?php

namespace SymbolPress;
use SymbolPress\Utils;

class AjaxHandler{
  public static function generate_mosaic_id_ajax_handler() {
    // セキュリティチェック
    check_ajax_referer('generate_mosaic_id_nonce', 'nonce');

    $node = 'http://sym-test-01.opening-line.jp:3000';
    $signerPublicKey = sanitize_text_field($_POST['signer_public_key']);
    $symbolService = new SymbolService($node);
    $mosaicId = $symbolService->generateMosaicId($signerPublicKey);

    wp_send_json_success(array(
      'mosaic_id' => $mosaicId['id'],
      'nonce' => $mosaicId['nonce']
    ));

    wp_die(); // 必ず終了する
  }

  private static function showResult($result){
    if($result['isSuccess'] == true) {      
      wp_send_json_success(array(
        'message' => 'Transaction completed successfully.',
        'explorer_link' => $result['message']
      ));
    } else {
      wp_send_json_error($result['message']);
    }
  }

  public static function send_tranasction_ajax_handler() {
    $transactionDefinitions = include(plugin_dir_path(__FILE__) . 'Transactions/transaction_definitions.php');
    $node = 'http://sym-test-01.opening-line.jp:3000';
    check_ajax_referer('symbol_press_nonce', 'nonce');
    $transactionType = sanitize_text_field($_POST['transaction_type']);

    foreach($transactionDefinitions as $transactionName) {
      if($transactionType == Utils::pascalToSnake($transactionName)) {
        $transactionClass = 'SymbolPress\\Transactions\\' . $transactionName;
        $result = $transactionClass::excuteTransaction($node, $_POST);
        self::showResult($result);
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
}