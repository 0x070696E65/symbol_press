<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolPress\Transactions;
use SymbolSdk\Symbol\Models\AggregateCompleteTransactionV2;
use SymbolPress\Utils;

class AggregateCompleteTransaction extends BaseTransaction {
  private const FIELDS = [];

  public function __construct($atts)
  {
    parent::__construct($atts, self::FIELDS);
  }

  public function getName()
  {
    return substr(self::class, strrpos(self::class, '\\') + 1);
  }

  public static function createTransaction(SymbolService $symbolService, array $arrgs, bool $isEmbedded){
    $transactionDefinitions = include(plugin_dir_path(__FILE__) . 'transaction_definitions.php');
    $transactions = [];
    foreach($arrgs['transactions'] as $tx){
      foreach ($transactionDefinitions as $transactionName) {
        if ($tx['transaction_type'] === Utils::pascalToSnake($transactionName)) {
          $transactionClass = 'SymbolPress\\Transactions\\' . $transactionName;
          array_push($transactions, $transactionClass::createTransaction($symbolService, $tx, true));
          break;
        }
      }
    }
    $merkleHash = $symbolService->facade->hashEmbeddedTransactions($transactions);
    $transaction = new AggregateCompleteTransactionV2(
      transactions: $transactions,
      transactionsHash: $merkleHash
    );
    $symbolService->createTransactionHeader($transaction, $arrgs, true);
    return $transaction;
  }

  public static function drawForm($atts, $innerTransactions = null){
    $atts = shortcode_atts(array(
      'has_add_button' => 'true'
    ), $atts);
    if ($innerTransactions) {
      $innerTransactions = self::add_attributes_to_transaction_shortcode($innerTransactions);
    }

    $innerTransactions = do_shortcode($innerTransactions);
    $tx = new Transactions\AggregateCompleteTransaction($atts);
    return $tx->_drawForm($innerTransactions, $atts['has_add_button']);
  }

  private static function add_attributes_to_transaction_shortcode($shortcode) {
    if (preg_match_all('/\[(\w+_transaction)([^\]]*)\]/', $shortcode, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {           
            $shortcode_name = $match[1];
            $existing_attributes = $match[2];
            $new_attributes = ' is_inner="true" is_short_code="true"';
            $new_shortcode = "[$shortcode_name$existing_attributes$new_attributes]";
            $shortcode = str_replace($match[0], $new_shortcode, $shortcode);
        }
    }
    return $shortcode;
  }
}