<?php
namespace SymbolPress;

class ShortcodeHandler {
  public static function transferTransaction($atts){
    $tx = new Transactions\TransferTransaction($atts);
    return $tx->drawForm();
  }

  public static function mosaicDefinitionTransaction($atts){
    $tx = new Transactions\MosaicDefinitionTransaction($atts);
    return $tx->drawForm();
  }

  public static function mosaicSupplyChangeTransaction($atts){
    $tx = new Transactions\MosaicSupplyChangeTransaction($atts);
    return $tx->drawForm();
  }

  public static function accountKeyLinkTransaction($atts){
    $tx = new Transactions\AccountKeyLinkTransaction($atts);
    return $tx->drawForm();
  }

  public static function nodeKeyLinkTransaction($atts){
    $tx = new Transactions\NodeKeyLinkTransaction($atts);
    return $tx->drawForm();
  }

  public static function votingKeyLinkTransaction($atts){
    $tx = new Transactions\VotingKeyLinkTransaction($atts);
    return $tx->drawForm();
  }

  public static function vrfKeyLinkTransaction($atts){
    $tx = new Transactions\VrfKeyLinkTransaction($atts);
    return $tx->drawForm();
  }

  public static function hashLockTransaction($atts){
    $tx = new Transactions\HashLockTransaction($atts);
    return $tx->drawForm();
  }

  public static function accountMetadataTransaction($atts){
    $tx = new Transactions\AccountMetadataTransaction($atts);
    return $tx->drawForm();
  }

  public static function aggregateCompleteTransaction($atts, $innerTransactions = null){
    $atts = shortcode_atts(array(
      'has_add_button' => 'true'
    ), $atts);
    if ($innerTransactions) {
      $innerTransactions = preg_replace_callback('/\[(\w+_transaction)(.*?)\]/', function ($matches) {
        if (strpos($matches[2], 'hoge=') === false) {
          return '[' . $matches[1] . $matches[2] . ' is_inner="true"]';
        } else {
          return $matches[0];
        }
      }, $innerTransactions);
    }
    $innerTransactions = do_shortcode($innerTransactions);
    $tx = new Transactions\AggregateCompleteTransaction($atts);
    return $tx->drawForm($innerTransactions, $atts['has_add_button']);
  }

  public static function button_shortcode($atts) {
    $atts = shortcode_atts(array(
      'id' => '',
      'is_inner' => 'false'
    ), $atts);

    $id_suffix = bin2hex(random_bytes(2));
    $button_id = 'load-content-btn-' . $id_suffix;
    $form_id = 'load-content-form-' . $id_suffix;
    $atts['id'] = $atts['id'] == '' ? $id_suffix : $atts['id'];

    // ショートコードを切り替えるためのセレクトボックスを生成
    $output = '<form id="' . $form_id . '" action="" method="post">';
    $output .= '<select id="shortcode-select-' . $id_suffix . '" name="shortcode-select">';
    $output .= '<option value="transfer_transaction">TransferTransaction</option>';
    $output .= '<option value="mosaic_definition_transaction" selected>MosaicDefinitionTransaction</option>';
    $output .= '<option value="mosaic_supply_change_transaction">MosaicSupplyChangeTransaction</option>';
    $output .= '<option value="account_metadata_transaction">AccountMetadataTransaction</option>';
    $output .= '</select>';
    $output .= '<input type="hidden" id="inner-transaciton-' . $id_suffix . '" name="inner-transaciton-id" value="' . esc_attr($atts['id']) . '">';
    $output .= '<input type="hidden" id="is-inner-' . $id_suffix . '" name="is-inner" value="' . esc_attr($atts['is_inner']) . '">';
    $output .= '<button id="' . $button_id . '">コンテンツを読み込む</button>';
    if($atts['is_inner'] == 'false') $output .= '<div id="transactions-' . $id_suffix . '"></div>';
    $output .= '</form>' . "\n";

    return $output;
  }
}

add_shortcode('load_content_button', array('SymbolPress\ShortcodeHandler', 'button_shortcode'));

add_shortcode('transfer_transaction', array('SymbolPress\ShortcodeHandler', 'transferTransaction'));
add_shortcode('mosaic_definition_transaction', array('SymbolPress\ShortcodeHandler', 'mosaicDefinitionTransaction'));
add_shortcode('mosaic_supply_change_transaction', array('SymbolPress\ShortcodeHandler', 'mosaicSupplyChangeTransaction'));
add_shortcode('account_key_link_transaction', array('SymbolPress\ShortcodeHandler', 'accountKeyLinkTransaction'));
add_shortcode('node_key_link_transaction', array('SymbolPress\ShortcodeHandler', 'nodeKeyLinkTransaction'));
add_shortcode('voting_key_link_transaction', array('SymbolPress\ShortcodeHandler', 'votingKeyLinkTransaction'));
add_shortcode('vrf_key_link_transaction', array('SymbolPress\ShortcodeHandler', 'vrfKeyLinkTransaction'));
add_shortcode('hash_lock_transaction', array('SymbolPress\ShortcodeHandler', 'hashLockTransaction'));
add_shortcode('account_metadata_transaction', array('SymbolPress\ShortcodeHandler', 'accountMetadataTransaction'));

add_shortcode('aggregate_complete_transaction', array('SymbolPress\ShortcodeHandler', 'aggregateCompleteTransaction'));
