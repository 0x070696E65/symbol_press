<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Metadata;
use SymbolSdk\Symbol\Models\MosaicMetadataTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMosaicMetadataTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedAddress;
use SymbolSdk\Symbol\Models\UnresolvedMosaicId;

class MosaicMetadataTransaction extends BaseTransaction {
  private const FIELDS = [
    'target_address' => [
      'type' => 'text'
    ],
    'target_mosaic_id' => [
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
      $transaction = new MosaicMetadataTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMosaicMetadataTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $target_address = sanitize_text_field($arrgs['target_address']);
    $target_mosaic_id = sanitize_text_field($arrgs['target_mosaic_id']);
    $scoped_metadata_key = sanitize_text_field($arrgs['scoped_metadata_key']);
    $value = sanitize_text_field($arrgs['value']);

    $transaction->targetAddress = new UnresolvedAddress($target_address);
    $transaction->scopedMetadataKey = Metadata::metadataGenerateKey($scoped_metadata_key);
    $transaction->targetMosaicId = new UnresolvedMosaicId($target_mosaic_id);
    $transaction->valueSizeDelta = strlen($value);
    $transaction->value = $value;

    return $transaction;
  }

  public static function drawForm($atts){
    $tx = new self($atts);
    return $tx->_drawForm();
  }
}