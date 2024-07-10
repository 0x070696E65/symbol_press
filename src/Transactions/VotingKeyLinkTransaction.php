<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\VotingKeyLinkTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedVotingKeyLinkTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\VotingPublicKey;
use SymbolSdk\Symbol\Models\LinkAction;
use SymbolSdk\Symbol\Models\FinalizationEpoch;

class VotingKeyLinkTransaction extends BaseTransaction {
  private const FIELDS = [
    'linked_public_key' => [
      'type' => 'text'
    ],
    'link_action' => [
      'type' => 'radio',
      'options' => ['link', 'unlink']
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
      $transaction = new VotingKeyLinkTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedVotingKeyLinkTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    $linked_public_key = sanitize_text_field($arrgs['linked_public_key']);
    $start_epoch = intval($arrgs['start_epoch']);
    $end_epoch = intval($arrgs['end_epoch']);
    $transaction->linkedPublicKey = new VotingPublicKey($linked_public_key);
    $transaction->linkAction = new LinkAction(sanitize_text_field($arrgs['action']) == 'link' ? 1 : 0);
    $transaction->startEpoch = new FinalizationEpoch($start_epoch);
    $transaction->endEpoch = new FinalizationEpoch($end_epoch);

    return $transaction;
  }

  public static function drawForm($atts){
    $tx = new self($atts);
    return $tx->_drawForm();
  }
}