<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\NodeKeyLinkTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedNodeKeyLinkTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\LinkAction;

class NodeKeyLinkTransaction extends BaseTransaction {
  public function __construct($atts)
  {
    $atts = shortcode_atts( array(
      'linked_public_key' => '',
      'link_action' => '',
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => 'node_key_link',
      'label' => ''
    ), $atts);

    if ($atts['label'] == 'null') {
      $this->label = null;
    } elseif ($atts['label'] == '') {
      $this->label = 'NodeKeyLinkTransaction';
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
    ];
    $this->fields = array_merge($this->fields, $fields);
  }

  public static function createTransaction(SymbolService $symbolService, array $arrgs, bool $isEmbedded){
    if(!$isEmbedded){
      $transaction = new NodeKeyLinkTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedNodeKeyLinkTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    $linked_public_key = sanitize_text_field($arrgs['linked_public_key']);
    $transaction->linkedPublicKey = new PublicKey($linked_public_key);
    $transaction->linkAction = new LinkAction(sanitize_text_field($arrgs['action']) == 'link' ? 1 : 0);

    return $transaction;
  }
}