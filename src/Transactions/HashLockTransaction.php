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

class HashLockTransaction extends BaseTransaction {
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'mosaic_id' => '',
      'mosaic_amount' => '',
      'duration' => '',
      'hash' => '',
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'hash_lock',
      'label' => ''
    ), $atts);

    if ($atts['label'] == 'null') {
      $this->label = null;
    } elseif ($atts['label'] == '') {
      $this->label = 'HashLockTransaction';
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
        'id' => 'mosaic_id',
        'label' => 'MosaicId',
        'value' => $atts['mosaic_id'],
      ],
      [
        'type' => 'number',
        'id' => 'mosaic_amount',
        'label' => 'MosaicAmount',
        'value' => $atts['mosaic_amount'],
      ],
      [
        'type' => 'number',
        'id' => 'duration',
        'label' => 'Duration',
        'value' => $atts['duration'],
      ],
      [
        'type' => 'text',
        'id' => 'hash',
        'label' => 'Hash',
        'value' => $atts['hash'],
      ],
    ];
    $this->fields = array_merge($this->fields, $fields);
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
}