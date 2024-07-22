<?php

namespace SymbolPress\Transactions;

use Error;
use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\MosaicGlobalRestrictionTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMosaicGlobalRestrictionTransactionV1;
use SymbolSdk\Symbol\Models\UnresolvedMosaicId;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\MosaicRestrictionType;
use SymbolSdk\Symbol\Metadata;
use Exception;

class MosaicGlobalRestrictionTransaction extends BaseTransaction {
  private const FIELDS = [
    'mosaic_id' => [
      'type' => 'text'
    ],
    'reference_mosaic_id' => [
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
    'previous_restriction_type' => [
      'type' => 'radio',
      'options' => ['NONE', 'EQ', 'NE', 'LT', 'LE', 'GT', 'GE']
    ],
    'new_restriction_type' => [
      'type' => 'radio',
      'options' => ['NONE', 'EQ', 'NE', 'LT', 'LE', 'GT', 'GE']
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
      $transaction = new MosaicGlobalRestrictionTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMosaicGlobalRestrictionTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $mosaic_id = sanitize_text_field($arrgs['mosaic_id']);
    $reference_mosaic_id = sanitize_text_field($arrgs['reference_mosaic_id']);

    $previous_restriction_value = intval($arrgs['mosaic_amount']);
    $new_restriction_value = intval($arrgs['new_restriction_value']);
    $transaction->mosaicId = new UnresolvedMosaicId($mosaic_id);
    $transaction->referenceMosaicId = new UnresolvedMosaicId($reference_mosaic_id);
    $transaction->previousRestrictionValue = $previous_restriction_value;
    $transaction->newRestrictionValue = $new_restriction_value;
    $transaction->restrictionKey = Metadata::metadataGenerateKey($arrgs['restriction_key']);
    $transaction->previousRestrictionType = new MosaicRestrictionType(self::restrictionType(sanitize_text_field($arrgs['previous_restriction_type'])));
    $transaction->newRestrictionType = new MosaicRestrictionType(self::restrictionType(sanitize_text_field($arrgs['new_restriction_type'])));
    return $transaction;
  }

  private static function restrictionType($str){
    switch($str) {
      case 'NONE':
        return 0;
        break;
      case 'EQ':
        return 1;
        break;
      case 'NE':
        return 2;
        break;
      case 'LT':
        return 3;
        break;
      case 'LE':
        return 4;
        break;
      case 'GT':
        return 5;
        break;
      case 'GE':
        return 6;
        break;
      default:
      throw new Error('invalid string');
    }
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