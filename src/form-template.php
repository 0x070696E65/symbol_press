<?php
namespace SymbolPress;
use SymbolPress\Utils;
?>
<?php
$form_id_suffix = bin2hex(random_bytes(2));
$form_id = 'symbol-press-form-' . $form_id_suffix;
?>
<div id="symbol-transaction-<?php echo $form_id_suffix?>">
<?php if($label != null) :?>
  <div class='transaction-label'>
    <label><?php echo $label ?></label>
  </div>
<?php endif; ?>
<?php
if(esc_attr($fields[0]['id']) == 'transaction_type' && strpos(esc_attr($fields[0]['value']), 'aggregate') !== false && $hasAddButton == 'true'){
  echo do_shortcode('[load_content_button id="' . $form_id_suffix . '" is_inner="true"]');
};
?>
<form id="<?php echo esc_attr($form_id); ?>" method="post" action="">
  <div>
    <?php foreach ($fields as $field) : ?>
      <?php if (!empty($field['value']) && !is_array($field['type'])) : ?>
        <input type="hidden" id="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($field['value']); ?>">
      <?php elseif ($field['type'] === 'radio') : ?>
        <div class="custom-radio-wrapper">
          <label for="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>"><?php _e($field['label'], 'symbol-press'); ?></label>
          <?php 
            $option_keys = array_keys($field['options']); // オプションのキーを取得
            $first_option_value = reset($option_keys); // 最初のオプションの値を取得
          ?>
          <?php foreach ($field['options'] as $option_value => $option_label) : ?>
            <input type="radio" id="<?php echo esc_attr($field['id'] . '-' . $option_value . '-' . $form_id_suffix); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($option_value); ?>" <?php checked($option_value, $first_option_value, true); ?>>
            <label for="<?php echo esc_attr($field['id'] . '-' . $option_value . '-' . $form_id_suffix); ?>"><?php echo esc_html($option_label); ?></label>
          <?php endforeach; ?>
        </div>
      <?php elseif ($field['type'] === 'check') : ?>
        <p><?php echo esc_attr(Utils::snakeToPascal($field['id'])) ?></p>
        <?php foreach ($field['options'] as $option) : ?>
          <div class="custom-radio-wrapper">
            <input type="checkbox" id="<?php echo esc_attr($field['id'] . '-' . Utils::pascalToSnake($option) . '-' . $form_id_suffix) ?>" name="<?php echo esc_attr($field['id'] . '-' . Utils::pascalToSnake($option)) ?>" />
            <label for="scales"><?php echo $option ?></label>
          </div>
        <?php endforeach; ?>
      <?php elseif (is_array($field['type'])) : ?>
        <div class='array_field'>
          <?php foreach ($field['value'] as $x) {
            foreach($x as $y){
              foreach($y as $key => $value){
                $id = $field['id'] . '-' . $key;
                echo '<input type="hidden" name="' . $id .  '" id="' . $id . '" value="' . $value . '">';
              }
            }
          }
          ?>
        <?php $array_id_suffix = bin2hex(random_bytes(2)); ?>
          <?php $arrays = '' ?>
          <?php foreach($field['type'] as $array_key => $array_value): ?>
            <?php
              $arrays .= esc_attr($array_key) . '\\' . esc_attr($array_value['type'] . ',');
            ?>
          <?php endforeach; ?>
          <?php if(count($field['value']) == 0):?>
          <p><?php echo Utils::snakeToPascal(esc_attr($field['id'])) ?></p>
          <button id="add-field-<?php echo $field['id'] . '-' . substr($arrays, 0, -1) . '-' . $array_id_suffix; ?>">フォームを追加</button>
          <?php endif; ?>
          <div id="<?php echo $field['id'] . '-' . $array_id_suffix; ?>"></div>
        </div>
      <?php else : ?>
        <div class="custom-input-wrapper">
          <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>" name="<?php echo esc_attr($field['id']); ?>" placeholder=" " value="<?php echo isset($field['value']) ? esc_attr($field['value']) : ''; ?>">
          <label for="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>"><?php _e($field['label'], 'symbol-press'); ?></label>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if(esc_attr($fields[0]['id']) == 'transaction_type' && strpos(esc_attr($fields[0]['value']), 'aggregate') !== false) : ?>
      <?php
      if(isset($hasAddButton) && $hasAddButton === 'true'){
        $transactions_label = 'display:block';
      } else {
        $transactions_label = 'display:none';
      }
      ?>
      <label style="<?php echo $transactions_label ?>">transactions</label>
      <div id="<?php echo 'transactions-' . $form_id_suffix; ?>">
      <?php if($innerTransactions) :?>
        <?php echo $innerTransactions ?>
      <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if($isInner == 'false') : ?>
    <div id="symbol-press-result-wrapper-<?php echo esc_attr($form_id_suffix); ?>">
      <div id="symbol-press-result-<?php echo esc_attr($form_id_suffix); ?>"></div>
      <div id="qrcode-<?php echo esc_attr($form_id_suffix); ?>"></div>
      <div id="explorer-link-<?php echo esc_attr($form_id_suffix); ?>"></div>
    </div>
    <div class="wp-block-button" style="text-align: center;">
      <button type="submit" class="wp-block-button__link has-text-align-center wp-element-button" id="send-button-<?php echo esc_attr($form_id_suffix); ?>">Send</button>
    </div>
    <?php elseif($isShortCode == 'false'): ?>
    <button class="remove-transaction-<?php echo $form_id_suffix?>">削除</button>
    <?php endif; ?>
</form>
</div>
</div>
