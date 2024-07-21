<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\Symbol\Models\AccountOperationRestrictionTransactionV1;
use SymbolSdk\Symbol\Models\EmbeddedAccountOperationRestrictionTransactionV1;
use SymbolSdk\Symbol\Models\AccountRestrictionFlags;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey;
use SymbolSdk\Symbol\Models\UnresolvedAddress;

class AccountOperationRestrictionTransaction extends BaseTransaction {
  private const FIELDS = [
    'account_restriction_flags' => [
      'type' => 'check',
      'options' => ['address', 'mosaic_id', 'transaction_type', 'out_going', 'block']
    ],
    'restriction_additions' => [
      'type' => [
        'address' => [
          'type' => 'text'
        ]
      ]
    ],
    'restriction_deletions' => [
      'type' => [
        'address' => [
          'type' => 'text'
        ]
      ]
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
      $transaction = new AccountOperationRestrictionTransactionV1();
      $symbolService->createTransactionHeader($transaction, $arrgs);
    } else {
      $transaction = new EmbeddedAccountOperationRestrictionTransactionV1(
        signerPublicKey: new PublicKey($arrgs['signer_public_key']),
        network: new NetworkType($symbolService->facade->network->identifier)
      );
    }

    $flags = 0;
    if(isset($arrgs['account_restriction_flags-address'])) $flags += 1;
    if(isset($arrgs['account_restriction_flags-mosaic_id'])) $flags += 2;
    if(isset($arrgs['account_restriction_flags-transaction_type'])) $flags += 4;
    if(isset($arrgs['account_restriction_flags-out_going'])) $flags += 16384;
    if(isset($arrgs['account_restriction_flags-block'])) $flags += 32768;

    $restriction_additions = [];
    if(isset($arrgs['restriction_additions'])){
      foreach($arrgs['restriction_additions'] as $restriction_addition) {
        array_push($restriction_additions, new UnresolvedAddress(sanitize_text_field($restriction_addition['address'])));
      }
    }
    $restriction_deletions = [];
    if(isset($arrgs['restriction_deletions'])){
      foreach($arrgs['restriction_deletions'] as $restriction_deletion) {
        array_push($restriction_deletions, new UnresolvedAddress(sanitize_text_field($restriction_deletion['address'])));
      }
    }

    $transaction->restrictionFlags = new AccountRestrictionFlags($flags);
    $transaction->restrictionAdditions = $restriction_additions;
    $transaction->restrictionDeletions = $restriction_deletions;
    
    return $transaction;
  }

  public static function drawForm($atts){
    $tx = new self($atts);
    return $tx->_drawForm();
  }
}