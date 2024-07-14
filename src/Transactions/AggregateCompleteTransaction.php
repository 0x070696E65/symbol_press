<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolPress\Transactions;
use SymbolSdk\Symbol\Models\AggregateCompleteTransactionV2;
use SymbolPress\Utils;

class AggregateCompleteTransaction extends BaseTransaction {
  private const FIELDS = [
/*     'cosignature' => [
      'type' => [
        'private_key' => [
          'type' => 'text'
        ],
      ]
    ], */
  ];

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
    $symbolService->createTransactionHeader($transaction, $arrgs);
    return $transaction;
  }

  public static function drawForm($atts, $innerTransactions = null){
    $atts = shortcode_atts(array(
      'signer_public_key' => '',
      'has_add_button' => 'true'
    ), $atts);
    if ($innerTransactions) {
      $innerTransactions = preg_replace_callback('/\[(\w+_transaction)(.*?)\]/', function ($matches) {
        return '[' . $matches[1] . $matches[2] . ' is_inner="true" is_short_code="true"]';
      }, $innerTransactions);
    }
    $innerTransactions = do_shortcode($innerTransactions);
    $tx = new Transactions\AggregateCompleteTransaction($atts);
    return $tx->_drawForm($innerTransactions, $atts['has_add_button']);
  }
}