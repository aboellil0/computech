<?php
/**
 * Custom Computech My Account layout.
 *
 * @package Computech
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wc_get_template('myaccount/form-login.php');
    return;
}
?>

<div class="ct-myaccount-shell">
    <aside class="ct-myaccount-sidebar">
        <div class="ct-account-user-card">
            <div class="ct-account-avatar"><?php echo get_avatar(get_current_user_id(), 72); ?></div>
            <div>
                <span>مرحبًا</span>
                <strong><?php echo esc_html(wp_get_current_user()->display_name ?: wp_get_current_user()->user_login); ?></strong>
            </div>
        </div>
        <?php do_action('woocommerce_account_navigation'); ?>
    </aside>
    <main class="ct-myaccount-content">
        <?php do_action('woocommerce_account_content'); ?>
    </main>
</div>
