<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\Amount;
use SymbolSdk\Symbol\Models\TransferTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedTransferTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedMosaic;
use SymbolSdk\Symbol\Models\UnresolvedMosaicId;
use SymbolSdk\Symbol\Models\UnresolvedAddress;
use Exception;

class TransferTransaction extends BaseTransaction {
  private const FIELDS = [
    'recipient_address' => [
      'type' => 'text'
    ],
    'mosaic' => [
      'type' => [
        'mosaic_id' => [
          'type' => 'text'
        ],
        'mosaic_amount' => [
          'type' => 'number'
        ]
      ]
    ],
    'message' => [
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
      $transaction = new TransferTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedTransferTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    $recipientAddress = sanitize_text_field($arrgs['recipient_address']);
    $message = sanitize_text_field($arrgs['message']);

    $mosaics = [];
    if(isset($arrgs['mosaic'])){
      foreach($arrgs['mosaic'] as $mosaic) {
        array_push($mosaics, new UnresolvedMosaic(
          mosaicId: new UnresolvedMosaicId('0x' . sanitize_text_field($mosaic['mosaic_id'])),
          amount: new Amount(intval($mosaic['mosaic_amount']))
        ));
      }
    }

    $transaction->recipientAddress = new UnresolvedAddress($recipientAddress);
    $transaction->mosaics = $mosaics;

    if ($message != null)
      $transaction->message = "\x00" . $message;

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

