<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\MosaicSupplyRevocationTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMosaicSupplyRevocationTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\Amount;
use SymbolSdk\Symbol\Models\UnresolvedAddress;
use SymbolSdk\Symbol\Models\UnresolvedMosaic;
use SymbolSdk\Symbol\Models\UnresolvedMosaicId;
use Exception;

class MosaicSupplyRevocationTransaction extends BaseTransaction {
  private const FIELDS = [
    'source_address' => [
      'type' => 'text'
    ],
    'mosaic_id' => [
      'type' => 'text'
    ],
    'amount' => [
      'type' => 'number'
    ],
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
      $transaction = new MosaicSupplyRevocationTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMosaicSupplyRevocationTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    
    $source_address = sanitize_text_field($arrgs['source_address']);
    $mosaicId = sanitize_text_field($arrgs['mosaic_id']);
    $amount = intval($arrgs['amount']);

    $mosaic = new UnresolvedMosaic(
      mosaicId: new UnresolvedMosaicId('0x' . $mosaicId),
      amount: new Amount($amount)
    );

    $transaction->mosaic = $mosaic;
    $transaction->sourceAddress = new UnresolvedAddress($source_address);

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