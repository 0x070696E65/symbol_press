<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\AccountKeyLinkTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedAccountKeyLinkTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\LinkAction;
use Exception;

class AccountKeyLinkTransaction extends BaseTransaction {
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
      $transaction = new AccountKeyLinkTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedAccountKeyLinkTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    $linked_public_key = sanitize_text_field($arrgs['linked_public_key']);
    $transaction->linkedPublicKey = new PublicKey($linked_public_key);
    $transaction->linkAction = new LinkAction(sanitize_text_field($arrgs['link_action']) == 'link' ? 1 : 0);

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