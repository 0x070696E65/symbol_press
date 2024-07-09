<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\CryptoTypes\PrivateKey;

abstract class BaseTransaction {
  public array $fields;
  public string $isInner;
  public ?string $label = '';
  public function __construct($atts)
  {
    $this->isInner = $atts['is_inner'];
    $this->fields = self::generateFieldsHeader($atts);
  }

  private static function generateFieldsHeader($atts){
    $fields = [[
      'type' => 'hidden',
      'id' => 'transaction_type',
      'value' => $atts['transaction_type'],
    ]];
    if($atts['is_inner'] == "true") {
      $additionalFields = [[
        'type' => 'text',
        'id' => 'signer_public_key',
        'label' => 'SignerPublicKey',
        'value' => isset($atts['signer_public_key']) ? $atts['signer_public_key'] : '',
      ]];
    } else {
      $additionalFields = [[
        'type' => 'text',
        'id' => 'private_key',
        'label' => 'PrivateKey',
        'value' => $atts['private_key'],
      ]];
    }
    $fields = array_merge($fields, $additionalFields);

    return $fields;
  }

  public function drawForm($innerTransactions = null, $hasAddButtton = true){
    ob_start();
    extract([
      'fields' => $this->fields,
      'isInner' => $this->isInner,
      'innerTransactions' => $innerTransactions,
      'hasAddButtton' => $hasAddButtton,
      'label' => $this->label
    ]);

    include plugin_dir_path(__FILE__) . '../form-template.php';
    return ob_get_clean();
  }

  public static function excuteTransaction($node, array $arrgs, bool $isEmbedded = false, $cosignatureCount = 0){
    $symbolService = new SymbolService($node);
    $privateKey = new PrivateKey(sanitize_text_field($arrgs['private_key']));
    $account = $symbolService->facade->createAccount($privateKey);
    $arrgs['signer_public_key'] = $account->publicKey->binaryData;
    $transaction = static::createTransaction($symbolService, $arrgs, $isEmbedded);
    $symbolService->setFee($transaction, $cosignatureCount);
    $signedTransaction = $symbolService->signTransaction($transaction, $privateKey);
    return $symbolService->accounceTransaction($signedTransaction);
  }

  abstract public static function createTransaction(SymbolService $symbolService, array $arrgs, bool $isEmbedded);
}