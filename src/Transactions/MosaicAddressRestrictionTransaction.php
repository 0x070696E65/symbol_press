<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\MosaicAddressRestrictionTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMosaicAddressRestrictionTransactionV1;
use SymbolSdk\Symbol\Models\UnresolvedMosaicId;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedAddress;
use SymbolSdk\Symbol\Metadata;
use Exception;

class MosaicAddressRestrictionTransaction extends BaseTransaction {
  private const FIELDS = [
    'mosaic_id' => [
      'type' => 'text'
    ],
    'restriction_key' => [
      'type' => 'text'
    ],
    'previous_restriction_value' => [
      'type' => 'number'
    ],
    'new_restriction_value' => [
      'type' => 'number'
    ],
    'target_address' => [
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
      $transaction = new MosaicAddressRestrictionTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMosaicAddressRestrictionTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $mosaic_id = sanitize_text_field($arrgs['mosaic_id']);
    $target_address = sanitize_text_field($arrgs['target_address']);
    $previous_restriction_value = intval($arrgs['mosaic_amount']);
    $new_restriction_value = intval($arrgs['new_restriction_value']);
    $transaction->mosaicId = new UnresolvedMosaicId($mosaic_id);
    $transaction->targetAddress = new UnresolvedAddress($target_address);
    $transaction->previousRestrictionValue = $previous_restriction_value;
    $transaction->newRestrictionValue = $new_restriction_value;
    $transaction->restrictionKey = Metadata::metadataGenerateKey($arrgs['restriction_key']);
    
    return $transaction;
  }

  public static function drawForm($atts){
    try {
      $tx = new self($atts);
      return $tx->_drawForm();
    } catch (Exception $e) {
      return self::showErrorMessage($e);
    }
  }
}