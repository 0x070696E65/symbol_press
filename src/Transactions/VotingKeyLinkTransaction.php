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
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'linked_public_key' => '',
      'link_action' => '',
      'start_epoch' => '',
      'end_epoch' => '',
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'voting_key_link',
      'label' => ''
    ), $atts);

    if ($atts['label'] == 'null') {
      $this->label = null;
    } elseif ($atts['label'] == '') {
      $this->label = 'VotingKeyLinkTransaction';
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
        'id' => 'linked_public_key',
        'label' => 'LinkedPublicKey',
        'value' => $atts['linked_public_key'],
      ],
      [
        'type' => 'radio',
        'id' => 'link_action',
        'label' => 'LinkAction',
        'value' => isset($atts['link_action']) ? $atts['link_action'] : '',
        'options' => [
          'link' => 'Link',
          'unlink' => 'Unlink',
        ],
      ],
      [
        'type' => 'number',
        'id' => 'start_epoch',
        'label' => 'StartEpoch',
        'value' => $atts['start_epoch'],
      ],
      [
        'type' => 'number',
        'id' => 'end_epoch',
        'label' => 'EndEpoch',
        'value' => $atts['end_epoch'],
      ]
    ];
    $this->fields = array_merge($this->fields, $fields);
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
}