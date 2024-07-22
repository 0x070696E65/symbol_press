<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\MosaicId;
use SymbolSdk\Symbol\Models\MosaicAliasTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMosaicAliasTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\AliasAction;
use SymbolSdk\Symbol\Models\NamespaceId;
use Exception;

class MosaicAliasTransaction extends BaseTransaction {
  private const FIELDS = [
    'namespace_id' => [
      'type' => 'text'
    ],
    'mosaic_id' => [
      'type' => 'text'
    ],
    'alias_action' => [
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
      $transaction = new MosaicAliasTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMosaicAliasTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    $namespace_id = sanitize_text_field($arrgs['namespace_id']);
    $mosaic_id = sanitize_text_field($arrgs['mosaic_id']);
    $transaction->namespaceId = new NamespaceId($namespace_id);
    $transaction->mosaicId = new MosaicId($mosaic_id);
    $transaction->aliasAction = new AliasAction(sanitize_text_field($arrgs['alias_action']) == 'link' ? 1 : 0);

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