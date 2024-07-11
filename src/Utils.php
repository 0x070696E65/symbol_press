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

  public static function recursive_array_key_exists($key, $array) {
    if (array_key_exists($key, $array)) {
        return true;
    }
    foreach ($array as $value) {
        if (is_array($value) && self::recursive_array_key_exists($key, $value)) {
            return true;
        }
    }
    return false;
}
}