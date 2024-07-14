<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\NamespaceRegistrationTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedNamespaceRegistrationTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\BlockDuration;
use SymbolSdk\Symbol\Models\NamespaceId;
use SymbolSdk\Symbol\Models\NamespaceRegistrationType;

class NamespaceRegistrationTransaction extends BaseTransaction {
  private const FIELDS = [
    'duration' => [
      'type' => 'number'
    ],
    'parent_id' => [
      'type' => 'text'
    ],
    'id' => [
      'type' => 'text'
    ],
    'registration_type' => [
      'type' => 'radio',
      'options' => ['root', 'child']
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
      $transaction = new NamespaceRegistrationTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedNamespaceRegistrationTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $parent_id = sanitize_text_field($arrgs['parent_id']);
    $id = sanitize_text_field($arrgs['id']);
    $duration = intval($arrgs['duration']);
    $transaction->registrationType = new NamespaceRegistrationType(sanitize_text_field($arrgs['registration_type']) == 'root' ? 0 : 1);

    $transaction->parentId = new NamespaceId($parent_id);
    $transaction->id = new NamespaceId($id);
    $transaction->duration = new BlockDuration($duration);

    return $transaction;
  }

  public static function drawForm($atts){
    $tx = new self($atts);
    return $tx->_drawForm();
  }
}