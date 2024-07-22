<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\Amount;
use SymbolSdk\Symbol\Models\HashLockTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedHashLockTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedMosaic;
use SymbolSdk\Symbol\Models\UnresolvedMosaicId;
use SymbolSdk\Symbol\Models\BlockDuration;
use SymbolSdk\Symbol\Models\Hash256;
use Exception;

class HashLockTransaction extends BaseTransaction {
  private const FIELDS = [
    'mosaic_id' => [
      'type' => 'text'
    ],
    'mosaic_amount' => [
      'type' => 'number'
    ],
    'duration' => [
      'type' => 'number'
    ],
    'hash' => [
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
      $transaction = new HashLockTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedHashLockTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $mosaicId = sanitize_text_field($arrgs['mosaic_id']);
    $mosaicAmount = intval($arrgs['mosaic_amount']);
    $duration = intval($arrgs['duration']);
    $hash = sanitize_text_field($arrgs['hash']);

    $mosaic = new UnresolvedMosaic(
      mosaicId: new UnresolvedMosaicId('0x' . $mosaicId),
      amount: new Amount(intval($mosaicAmount))
    );

    $transaction->mosaic = $mosaic;
    $transaction->duration = new BlockDuration($duration);
    $transaction->hash = new Hash256($hash);

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