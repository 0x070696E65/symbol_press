<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function symbol_press_add_admin_menu() {
    add_menu_page(
        'Symbol Press Settings', 
        'Symbol Press', 
        'manage_options', 
        'symbol-press', 
        'symbol_press_page',
    );
}
add_action('admin_menu', 'symbol_press_add_admin_menu');

function symbol_press_page() {
    // フォームが送信された場合の処理
    if (isset($_POST['submit'])) {
        update_option('node', sanitize_text_field($_POST['node']), 'no' );
        update_option('fee_multi_plier', sanitize_text_field($_POST['fee_multi_plier']), 'no' );
        update_option('deadline_seconds', sanitize_text_field($_POST['deadline_seconds']), 'no' );
        echo "<div class='updated'><p>Data saved</p></div>";
    }

    // 保存されたデータを取得
    $node = get_option('node', '' );
    $fee_multi_plier = get_option('fee_multi_plier', '100' );
    $deadline_seconds = get_option('deadline_seconds', '3600' );

    ?>
    <div class="wrap">
        <h2>Settings</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Node</th>
                    <td><input type="text" name="node" value="<?php echo esc_attr($node); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Fee Multiplier</th>
                    <td><input type="number" name="fee_multi_plier" value="<?php echo esc_attr($fee_multi_plier); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Deadline Seconds</th>
                    <td><input type="number" name="deadline_seconds" value="<?php echo esc_attr($deadline_seconds); ?>" required /></td>
                </tr>
            </table>
            <?php submit_button('Save'); ?>
        </form>
    </div>
    <?php
}
?>