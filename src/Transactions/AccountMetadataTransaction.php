<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Metadata;
use SymbolSdk\Symbol\Models\AccountMetadataTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedAccountMetadataTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedAddress;

class AccountMetadataTransaction extends BaseTransaction {
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'target_address' => '',
      'scoped_metadata_key' => '',
      'value' => '',
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'account_metadata',
      'label' => ''
    ), $atts);

    if ($atts['label'] == 'null') {
      $this->label = null;
    } elseif ($atts['label'] == '') {
      $this->label = 'AccountMetadataTransaction';
    } else {
      $this->label = $atts['label'];
    }
  
    parent::__construct($atts);
    $this->generateFields($atts);
  }

  private function generateFields($atts){
    $fields = [
      [
        'type' => 'text',
        'id' => 'target_address',
        'label' => 'TargetAddress',
        'value' => $atts['target_address'],
      ],
      [
        'type' => 'text',
        'id' => 'scoped_metadata_key',
        'label' => 'Key',
        'value' => $atts['scoped_metadata_key'],
      ],
      [
        'type' => 'text',
        'id' => 'value',
        'label' => 'Value',
        'value' => $atts['value'],
      ],
    ];
    $this->fields = array_merge($this->fields, $fields);
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
}