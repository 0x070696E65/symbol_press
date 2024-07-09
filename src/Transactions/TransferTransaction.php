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

class TransferTransaction extends BaseTransaction {
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'recipient_address' => '',
      'mosaic_id' => '',
      'mosaic_amount' => '',
      'message' => '',
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'transfer',
      'label' => ''
    ), $atts);

    if ($atts['label'] == 'null') {
      $this->label = null;
    } elseif ($atts['label'] == '') {
      $this->label = 'TransferTransaction';
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
        'id' => 'recipient_address',
        'label' => 'RecipientAddress',
        'value' => $atts['recipient_address'],
      ],
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
        'type' => 'text',
        'id' => 'message',
        'label' => 'Message',
        'value' => $atts['message'],
      ],
    ];
    $this->fields = array_merge($this->fields, $fields);
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
    $mosaicId = sanitize_text_field($arrgs['mosaic_id']);
    $mosaicAmount = intval($arrgs['mosaic_amount']);
    $message = sanitize_text_field($arrgs['message']);

    $transaction->recipientAddress = new UnresolvedAddress($recipientAddress);

    if ($mosaicId != null) {
      $mosaic = new UnresolvedMosaic(
        mosaicId: new UnresolvedMosaicId('0x' . $mosaicId),
        amount: new Amount(intval($mosaicAmount))
      );
      array_push($transaction->mosaics, $mosaic);
    }
    if ($message != null) {
      $transaction->message = "\x00" . $message;
    }
    return $transaction;
  }
}