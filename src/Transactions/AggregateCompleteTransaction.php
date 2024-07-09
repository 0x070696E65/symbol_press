<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolPress\Transactions;
use SymbolSdk\Symbol\Models\AggregateCompleteTransactionV2;

class AggregateCompleteTransaction extends BaseTransaction {
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'aggregate_complete'
    ), $atts);
    $this->label = 'AggregateCompleteTransaction';
    parent::__construct($atts);
  }

  public static function createTransaction(SymbolService $symbolService, array $arrgs, bool $isEmbedded){
    $transactions = [];
    foreach($arrgs['transactions'] as $tx){
      switch($tx['transaction_type']){
        case 'transfer':
          array_push($transactions, Transactions\TransferTransaction::createTransaction($symbolService, $tx, true));
          break;
        case 'mosaic_definition':
          array_push($transactions, Transactions\MosaicDefinitionTransaction::createTransaction($symbolService, $tx, true));
          break;
        case 'mosaic_supply_change':
          array_push($transactions, Transactions\MosaicSupplyChangeTransaction::createTransaction($symbolService, $tx, true));
          break;
        case 'account_metadata':
          array_push($transactions, Transactions\AccountMetadataTransaction::createTransaction($symbolService, $tx, true));
          break;
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
}