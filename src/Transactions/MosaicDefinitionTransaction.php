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
use Exception;

class MosaicDefinitionTransaction extends BaseTransaction {
  private const FIELDS = [
    'address' => [
      'type' => 'text'
    ],
    'supply_mutable' => [
      'type' => 'radio',
      'options' => ['true', 'false']
    ],
    'transferable' => [
      'type' => 'radio',
      'options' => ['true', 'false']
    ],
    'restrictable' => [
      'type' => 'radio',
      'options' => ['true', 'false']
    ],
    'revokable' => [
      'type' => 'radio',
      'options' => ['true', 'false']
    ],
    'mosaic_id' => [
      'type' => 'text'
    ],
    'mosaic_nonce' => [
      'type' => 'hidden'
    ],
    'duration' => [
      'type' => 'number'
    ],
    'divisibility' => [
      'type' => 'number'
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

  public static function drawForm($atts){
    try {
      if(isset($atts['address']) && $atts['address'] != '') {
          $mosaicId = SymbolService::generateMosaicId($atts['address']);
          $atts['mosaic_id'] = $mosaicId['id'];
          $atts['mosaic_nonce'] = $mosaicId['nonce'];
      }
      $tx = new self($atts);
      return $tx->_drawForm();
    } catch (Exception $e) {
      return self::showErrorMessage($e);
    }
  }
}