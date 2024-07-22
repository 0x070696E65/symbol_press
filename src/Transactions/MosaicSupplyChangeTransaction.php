<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\MosaicSupplyChangeTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMosaicSupplyChangeTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\Amount;
use SymbolSdk\Symbol\Models\UnresolvedMosaicId;
use SymbolSdk\Symbol\Models\MosaicSupplyChangeAction;
use Exception;

class MosaicSupplyChangeTransaction extends BaseTransaction {
  private const FIELDS = [
    'mosaic_id' => [
      'type' => 'text'
    ],
    'action' => [
      'type' => 'radio',
      'options' => ['increase', 'decrease']
    ],
    'delta' => [
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
      $transaction = new MosaicSupplyChangeTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMosaicSupplyChangeTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    
    $mosaicId = sanitize_text_field($arrgs['mosaic_id']);
    $delta = intval($arrgs['delta']);

    $transaction->mosaicId = new UnresolvedMosaicId('0x' . $mosaicId);
    $transaction->delta = new Amount($delta);
    $transaction->action = new MosaicSupplyChangeAction(sanitize_text_field($arrgs['action']) == 'increase' ? 1 : 0);

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