<?php if($label != null) :?>
  <div class='transaction-label'>
    <label><?php echo $label ?></label>
  </div>
<?php endif; ?>
<?php
$form_id_suffix = bin2hex(random_bytes(2));
$form_id = 'symbol-press-form-' . $form_id_suffix;
?>
<?php
if(esc_attr($fields[0]['id']) == 'transaction_type' && strpos(esc_attr($fields[0]['value']), 'aggregate') !== false && $hasAddButtton == 'true'){
  echo do_shortcode('[load_content_button id="' . $form_id_suffix . '" is_inner="true"]');
};
?>
<form id="<?php echo esc_attr($form_id); ?>" method="post" action="">
  <div>
    <?php foreach ($fields as $field) : ?>
      <?php if (!empty($field['value'])) : ?>
        <input type="hidden" id="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($field['value']); ?>">
      <?php elseif ($field['type'] === 'radio') : ?>
        <div class="custom-radio-wrapper">
          <label for="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>"><?php _e($field['label'], 'symbol-press'); ?></label>
          <?php foreach ($field['options'] as $option_value => $option_label) : ?>
            <input type="radio" id="<?php echo esc_attr($field['id'] . '-' . $option_value . '-' . $form_id_suffix); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($option_value); ?>" <?php checked($field['value'], $option_value); ?>>
            <label for="<?php echo esc_attr($field['id'] . '-' . $option_value . '-' . $form_id_suffix); ?>"><?php echo esc_html($option_label); ?></label>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <div class="custom-input-wrapper">
          <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>" name="<?php echo esc_attr($field['id']); ?>" placeholder=" " value="<?php echo isset($field['value']) ? esc_attr($field['value']) : ''; ?>">
          <label for="<?php echo esc_attr($field['id'] . '-' . $form_id_suffix); ?>"><?php _e($field['label'], 'symbol-press'); ?></label>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if(esc_attr($fields[0]['id']) == 'transaction_type' && strpos(esc_attr($fields[0]['value']), 'aggregate') !== false) : ?>
      <label>transactions</label>
      <div id="<?php echo 'transactions-' . $form_id_suffix; ?>">
      <?php if($innerTransactions) :?>
        <?php echo $innerTransactions ?>
      <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if($isInner == 'false') : ?>
    <div id="symbol-press-result-wrapper-<?php echo esc_attr($form_id_suffix); ?>">
      <div id="symbol-press-result-<?php echo esc_attr($form_id_suffix); ?>"></div>
      <div id="explorer-link-<?php echo esc_attr($form_id_suffix); ?>"></div>
    </div>
    <div class="wp-block-button" style="text-align: center;">
      <button type="submit" class="wp-block-button__link has-text-align-center wp-element-button" id="send-button-<?php echo esc_attr($form_id_suffix); ?>">Send</button>
    </div>
    <?php endif; ?>
  </div>
</form>
