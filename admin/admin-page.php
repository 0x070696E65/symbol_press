<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function symbol_press_add_admin_menu() {
    add_menu_page(
        'Symbol Press Settings', 
        'Symbol Press', 
        'manage_options', 
        'my-plugin', 
        'my_plugin_page',
    );
}
add_action('admin_menu', 'symbol_press_add_admin_menu');

function get_my_plugin_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'symbol_press_table';

    $data = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id LIMIT 1");
    return $data;
}

function my_plugin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'symbol_press_table';

    // フォームが送信された場合の処理
    if (isset($_POST['submit'])) {
        $node = sanitize_text_field($_POST['node']);
        $fee_multi_plier = intval($_POST['fee_multi_plier']);
        $deadline_seconds = intval($_POST['deadline_seconds']);

        // テーブルにデータがあるか確認
        $id = $wpdb->get_var("SELECT id FROM $table_name ORDER BY id LIMIT 1");

        if ($id) {
            // データが存在する場合は更新
            $wpdb->update(
                $table_name,
                array(
                    'node' => $node,
                    'fee_multi_plier' => $fee_multi_plier,
                    'deadline_seconds' => $deadline_seconds
                ),
                array( 'id' => $id )
            );
        } else {
            // データが存在しない場合は挿入
            $wpdb->insert(
                $table_name,
                array(
                    'node' => $node,
                    'fee_multi_plier' => $fee_multi_plier,
                    'deadline_seconds' => $deadline_seconds
                )
            );
        }
        
        echo "<div class='updated'><p>Data saved</p></div>";
    }

    // 保存されたデータを取得
    $data = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id LIMIT 1");

    $node = isset($data->node) ? $data->node : '';
    $fee_multi_plier = isset($data->fee_multi_plier) ? $data->fee_multi_plier : '100';
    $deadline_seconds = isset($data->deadline_seconds) ? $data->deadline_seconds : '3600';

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