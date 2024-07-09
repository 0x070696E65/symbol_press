<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\MosaicDefinitionTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMosaicDefinitionTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\MosaicId;
use SymbolSdk\Symbol\Models\MosaicFlags;
use SymbolSdk\Symbol\Models\BlockDuration;
use SymbolSdk\Symbol\Models\MosaicNonce;

class MosaicDefinitionTransaction extends BaseTransaction {
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'supply_mutable' => '',
      'transferable' => '',
      'restrictable' => '',
      'revokable' => '',
      'mosaic_id' => '',
      'nonce' => '',
      'duration' => '',
      'divisibility' => '',
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'mosaic_definition'
    ), $atts);
    $this->label = 'MosaicDefinitionTransaction';
    parent::__construct($atts);
    $this->generateFields($atts);
  }

  private function generateFields($atts){
    $fields = [
      [
        'type' => 'radio',
        'id' => 'supply_mutable',
        'label' => 'SupplyMutable',
        'value' => isset($atts['supply_mutable']) ? $atts['supply_mutable'] : '',
        'options' => [
          'true' => 'True',
          'false' => 'False',
        ],
      ],
      [
        'type' => 'radio',
        'id' => 'transferable',
        'label' => 'Transferable',
        'value' => isset($atts['transferable']) ? $atts['transferable'] : '',
        'options' => [
          'true' => 'True',
          'false' => 'False',
        ],
      ],
      [
        'type' => 'radio',
        'id' => 'restrictable',
        'label' => 'Restrictable',
        'value' => isset($atts['restrictable']) ? $atts['restrictable'] : '',
        'options' => [
          'true' => 'True',
          'false' => 'False',
        ],
      ],
      [
        'type' => 'radio',
        'id' => 'revokable',
        'label' => 'Revokable',
        'value' => isset($atts['revokable']) ? $atts['revokable'] : '',
        'options' => [
          'true' => 'True',
          'false' => 'False',
        ],
      ],
      [
        'type' => 'text',
        'id' => 'mosaic_id',
        'label' => 'MosaicId',
        'value' => $atts['mosaic_id'],
      ],
      [
        'type' => 'hidden',
        'id' => 'mosaic_nonce',
        'value' => isset($atts['mosaic_nonce']) ? $atts['mosaic_nonce'] : '',
      ],
      [
        'type' => 'number',
        'id' => 'duration',
        'label' => 'Duration',
        'value' => $atts['duration'],
      ],
      [
        'type' => 'text',
        'id' => 'divisibility',
        'label' => 'Divisibility',
        'value' => $atts['divisibility'],
      ],
    ];
    $this->fields = array_merge($this->fields, $fields);
  }

  public static function createTransaction(SymbolService $symbolService, array $arrgs, bool $isEmbedded){
    if(!$isEmbedded){
      $transaction = new MosaicDefinitionTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMosaicDefinitionTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $flags = 0;
    if(sanitize_text_field($arrgs['supply_mutable']) == 'true') $flags += 1;
    if(sanitize_text_field($arrgs['transferable']) == 'true') $flags += 2;
    if(sanitize_text_field($arrgs['restrictable']) == 'true') $flags += 4;
    if(sanitize_text_field($arrgs['revokable']) == 'true') $flags += 8;
    
    $mosaicId = sanitize_text_field($arrgs['mosaic_id']);
    $duration = intval($arrgs['duration']);
    $nonce = intval($arrgs['mosaic_nonce']);
    $divisibility = intval($arrgs['divisibility']);

    $transaction->id = new MosaicId('0x' . $mosaicId);
    $transaction->duration = new BlockDuration($duration);
    $transaction->nonce = new MosaicNonce($nonce);
    $transaction->divisibility = $divisibility;
    $transaction->flags = new MosaicFlags($flags);

    return $transaction;
  }
}