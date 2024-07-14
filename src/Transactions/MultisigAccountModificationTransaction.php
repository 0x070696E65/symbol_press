<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\MultisigAccountModificationTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedMultisigAccountModificationTransactionV1;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedAddress;

class MultisigAccountModificationTransaction extends BaseTransaction {
  private const FIELDS = [
    'min_removal_delta' => [
      'type' => 'number'
    ],
    'min_approval_delta' => [
      'type' => 'number'
    ],
    'address_additions' => [
      'type' => [
        'address' => [
          'type' => 'text'
        ]
      ]
    ],
    'address_deletions' => [
      'type' => [
        'address' => [
          'type' => 'text'
        ]
      ]
    ],
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
      $transaction = new MultisigAccountModificationTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedMultisigAccountModificationTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }
    $min_removal_delta = intval($arrgs['min_removal_delta']);
    $min_approval_delta = intval($arrgs['min_approval_delta']);
    $address_addtions = [];
    if(isset($arrgs['address_additions'])){
      foreach($arrgs['address_additions'] as $address_addtion) {
        array_push($address_addtions, new UnresolvedAddress(sanitize_text_field($address_addtion['address'])));
      }
    }
    $address_deletions = [];
    if(isset($arrgs['address_deletions'])){
      foreach($arrgs['address_deletions'] as $address_deletion) {
        array_push($address_deletions, new UnresolvedAddress(sanitize_text_field($address_deletion['address'])));
      }
    }

    $transaction->minRemovalDelta = $min_removal_delta;
    $transaction->minApprovalDelta = $min_approval_delta;
    $transaction->addressAdditions = $address_addtions;
    $transaction->addressDeletions = $address_deletions;

    return $transaction;
  }

  public static function drawForm($atts){
    $tx = new self($atts);
    return $tx->_drawForm();
  }
}

