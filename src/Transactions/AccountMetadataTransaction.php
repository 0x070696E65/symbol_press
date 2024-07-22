<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Metadata;
use SymbolSdk\Symbol\Models\AccountMetadataTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedAccountMetadataTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedAddress;
use Exception;

class AccountMetadataTransaction extends BaseTransaction {
  private const FIELDS = [
    'target_address' => [
      'type' => 'text'
    ],
    'scoped_metadata_key' => [
      'type' => 'text'
    ],
    'value' => [
      'type' => 'text'
    ]
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
    if(!$isEmbedded){
      $transaction = new AccountMetadataTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedAccountMetadataTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $target_address = sanitize_text_field($arrgs['target_address']);
    $scoped_metadata_key = sanitize_text_field($arrgs['scoped_metadata_key']);
    $value = sanitize_text_field($arrgs['value']);

    $transaction->targetAddress = new UnresolvedAddress($target_address);
    $transaction->scopedMetadataKey = Metadata::metadataGenerateKey($scoped_metadata_key);
    $transaction->valueSizeDelta = strlen($value);
    $transaction->value = $value;

    return $transaction;
  }

  public static function drawForm($atts){
    try {
      $tx = new self($atts);
      return $tx->_drawForm();
    } catch (Exception $e) {
      return '<div class="error-message">エラーが発生しました: ' . esc_html($e->getMessage()) . '</div>';
    }
  }
}