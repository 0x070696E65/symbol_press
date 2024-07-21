<?php

namespace SymbolPress\Transactions;

use Error;
use SymbolPress\SymbolService;
use SymbolPress\Utils;

abstract class BaseTransaction {
  public array $fields;
  public string $isInner;
  public ?string $label;
  public string $is_short_code;
  public string $button_text;
  public string $button_color;
  public function __construct(&$atts, array $fields)
  {
    $base_atts = array(
      'sign_mode' => '',
      'signer_public_key' => '',
      'is_inner' => 'false',
      'is_short_code' => 'false',
      'button_text' => 'Send',
      'button_color' => '',
      'transaction_type' => Utils::pascalToSnake($this->getName()),
      'label' => ''
    );

    foreach($fields as $key => $value) {
      $base_atts += [$key => ''];
    }
    $atts2 = shortcode_atts($base_atts, $atts);
    $this->is_short_code = $atts2['is_short_code'];
    $this->button_text = $atts2['button_text'];
    $this->button_color = $atts2['button_color'];

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
    if($atts['is_inner'] != "true") {
      array_push($form_fields, [
        'id' => 'sign_mode',
        'label' => 'SignMode',
        'type' => 'radio',
        'options' => [
          'SSS' => 'SSS', 
          'aLice' => 'aLice'
        ],
        'value' => isset($atts['sign_mode']) ? $atts['sign_mode'] : '',
      ]);
    } else {
      array_push($form_fields, [
        'type' => 'text',
        'id' => 'signer_public_key',
        'label' => 'SignerPublicKey',
        'value' => isset($atts['signer_public_key']) ? $atts['signer_public_key'] : '',
      ]);
    }

    foreach($fields as $field => $details) {
      $fieldData = [
        'type' => $details['type'],
        'id' => $field,
        'label' => Utils::snakeToPascal($field),
        'value' => $atts[$field],
      ];

      if ($details['type'] == 'radio' || $details['type'] == 'check') {
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

  protected function _drawForm($innerTransactions = null, $hasAddButton = true){
    ob_start();
    extract([
      'fields' => $this->fields,
      'isInner' => $this->isInner,
      'innerTransactions' => $innerTransactions,
      'hasAddButton' => $hasAddButton,
      'isShortCode' => $this->is_short_code,
      'label' => $this->label,
      'buttonText' => $this->button_text,
      'buttonColor' => $this->button_color
    ]);

    include plugin_dir_path(__FILE__) . '../form-template.php';
    return ob_get_clean();
  }

  public static function excuteTransaction(array $arrgs, bool $isEmbedded = false, $cosignatureCount = 0){
    $symbolService = new SymbolService();
    $transaction = static::createTransaction($symbolService, $arrgs, $isEmbedded);
    $symbolService->setFee($transaction, $cosignatureCount);
    return [
      'payload' => strtoupper(bin2hex($transaction->serialize())),
      'node' => $symbolService->node
    ];
  }

  abstract public function getName();
  abstract public static function createTransaction(SymbolService $symbolService, array $arrgs, bool $isEmbedded);
  abstract public static function drawForm($atts);
}
