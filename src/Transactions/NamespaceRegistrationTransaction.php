<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\IdGenerator;
use SymbolSdk\Symbol\Models\NamespaceRegistrationTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedNamespaceRegistrationTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\BlockDuration;
use SymbolSdk\Symbol\Models\NamespaceId;
use SymbolSdk\Symbol\Models\NamespaceRegistrationType;
use Exception;

class NamespaceRegistrationTransaction extends BaseTransaction {
  private const FIELDS = [
    'name' => [
      'type' => 'text'
    ],
    'duration' => [
      'type' => 'number'
    ],
    'parent_id' => [
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
      $transaction = new NamespaceRegistrationTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedNamespaceRegistrationTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $parent_id = sanitize_text_field($arrgs['parent_id']);
    $parent_id = $parent_id == 'blank' ? 0 : '0x' . $parent_id;
    $name = sanitize_text_field($arrgs['name']);
    $duration = intval($arrgs['duration']);
    $registrationType = $parent_id == 0 ? 0 : 1;
    $transaction->registrationType = new NamespaceRegistrationType($registrationType);

    $transaction->parentId = new NamespaceId($parent_id);
    $transaction->id = new NamespaceId(IdGenerator::generateNamespaceId($name, $transaction->parentId->value));
    $transaction->name = $name;
    $transaction->duration = new BlockDuration($duration);

    return $transaction;
  }

  public static function drawForm($atts){
    try {
      $tx = new self($atts);
      return $tx->_drawForm();
    } catch (Exception $e) {
      return self::showErrorMessage($e);
    }
  }
}