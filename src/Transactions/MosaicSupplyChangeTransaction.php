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


class MosaicSupplyChangeTransaction extends BaseTransaction {
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'mosaic_id' => '',
      'action' => '',
      'delta' => '',
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'mosaic_supply_change'
    ), $atts);
    $this->label = 'MosaicSupplyChangeTransaction';
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
        'type' => 'radio',
        'id' => 'action',
        'label' => 'Action',
        'value' => isset($atts['action']) ? $atts['action'] : '',
        'options' => [
          'increase' => 'Increase',
          'decrease' => 'Decrease',
        ],
      ],
      [
        'type' => 'number',
        'id' => 'delta',
        'label' => 'Delta',
        'value' => $atts['delta'],
      ]
    ];
    $this->fields = array_merge($this->fields, $fields);
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
}