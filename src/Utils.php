<?php
namespace SymbolPress;

class Utils {
  public static function pascalToSnake($input) {
    $pattern = '/(?<!^)[A-Z]/';
    $replacement = '_$0';
    $snakeCase = strtolower(preg_replace($pattern, $replacement, $input));
    return $snakeCase;
  }

  public static function snakeToPascal($input) {
    $words = explode('_', $input);
    $pascalCase = '';
    foreach ($words as $word) {
        $pascalCase .= ucfirst($word);
    }
    return $pascalCase;
  }
}