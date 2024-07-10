<?php
namespace SymbolPress;

use SymbolPress\Transactions;
use SymbolPress\Utils;

$transactionDefinitions = include(plugin_dir_path(__FILE__) . 'Transactions/transaction_definitions.php');

class ShortcodeHandler {
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

    global $transactionDefinitions;
    // ショートコードを切り替えるためのセレクトボックスを生成
    $output = '<form id="' . $form_id . '" action="" method="post">';
    $output .= '<select id="shortcode-select-' . $id_suffix . '" name="shortcode-select">';
    foreach($transactionDefinitions as $transactionName) {
      $output .= '<option value="' . Utils::pascalToSnake($transactionName) . '">' . $transactionName . '</option>';
    }
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

foreach ($transactionDefinitions as $transactionName) {
  add_shortcode(Utils::pascalToSnake($transactionName), array('SymbolPress\\Transactions\\' . $transactionName, 'drawForm'));
}

