<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\Address;
use SymbolSdk\Symbol\Models\AddressAliasTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedAddressAliasTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\AliasAction;
use SymbolSdk\Symbol\Models\NamespaceId;
use Exception;

class AddressAliasTransaction extends BaseTransaction {
  private const FIELDS = [
    'namespace_id' => [
      'type' => 'text'
    ],
    'address' => [
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
      $transaction = new AddressAliasTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedAddressAliasTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    $namespace_id = sanitize_text_field($arrgs['namespace_id']);
    $address = sanitize_text_field($arrgs['address']);
    $transaction->namespaceId = new NamespaceId($namespace_id);
    $transaction->address = new Address($address);
    $transaction->aliasAction = new AliasAction(sanitize_text_field($arrgs['alias_action']) == 'link' ? 1 : 0);

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