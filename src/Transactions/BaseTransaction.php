<?php

namespace SymbolPress\Transactions;

use SymbolPress\SymbolService;
use SymbolSdk\CryptoTypes\PrivateKey;
use SymbolPress\Utils;

abstract class BaseTransaction {
  public array $fields;
  public string $isInner;
  public ?string $label = '';
  public function __construct(&$atts, array $fields)
  {
    $base_atts = array(
      'private_key' => '',
      'is_inner' => 'false',
      'transaction_type' => Utils::pascalToSnake($this->getName()),
      'label' => ''
    );

    foreach($fields as $key => $value) {
      $base_atts += [$key => ''];
    }
    $atts2 = shortcode_atts($base_atts, $atts);

    foreach($fields as $key => $value) {
      if(is_array($value['type'])) {
        $atts2[$key] = [];
        foreach($value['type'] as $valueTypeKey => $valueTypeValue){
          $inner = [];
          foreach($atts as $attsKey => $attsValue) {
            $attsKeyArray = explode('-', $attsKey);
            if($attsKeyArray[0] == $valueTypeKey) {
              $arr = [
                $attsKey => $attsValue
              ];
              array_push($inner, $arr);
            }
          }
          if(count($inner) != 0) array_push($atts2[$key], $inner);
        }
      }
    }

    if ($atts2['label'] == 'null') {
      $this->label = null;
    } elseif ($atts2['label'] == '') {
      $this->label = $this->getName();
    } else {
      $this->label = $atts2['label'];
    }
    $this->isInner = $atts2['is_inner'];

    $this->fields = self::generateFields($atts2, $fields);
  }

  private static function generateFields($atts, $fields){
    $form_fields = [[
      'type' => 'hidden',
      'id' => 'transaction_type',
      'value' => $atts['transaction_type'],
    ]];
    if($atts['is_inner'] == "true") {
      array_push($form_fields, [
        'type' => 'text',
        'id' => 'signer_public_key',
        'label' => 'SignerPublicKey',
        'value' => isset($atts['signer_public_key']) ? $atts['signer_public_key'] : '',
      ]);
    } else {
      array_push($form_fields, [
        'type' => 'text',
        'id' => 'private_key',
        'label' => 'PrivateKey',
        'value' => $atts['private_key'],
      ]);
    }

    foreach($fields as $field => $details) {
      $fieldData = [
        'type' => $details['type'],
        'id' => $field,
        'label' => Utils::snakeToPascal($field),
        'value' => $atts[$field],
      ];

      if ($details['type'] == 'radio') {
        if (isset($details['options']) && is_array($details['options']))
          $options = $details['options'];

        $fieldData['options'] = [];

        // options の値を設定
        foreach ($options as $option) {
          $fieldData['options'][$option] = Utils::snakeToPascal($option);
        }
      }
      array_push($form_fields, $fieldData);
    }
    return $form_fields;
  }

  protected function _drawForm($innerTransactions = null, $hasAddButtton = true){
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

  abstract public function getName();
  abstract public static function createTransaction(SymbolService $symbolService, array $arrgs, bool $isEmbedded);
  abstract public static function drawForm($atts);
}
