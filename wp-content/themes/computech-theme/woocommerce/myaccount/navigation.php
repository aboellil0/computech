<?php
/**
 * Custom account navigation.
 *
 * @package Computech
 * @version 9.3.0
 */

defined('ABSPATH') || exit;

$items = wc_get_account_menu_items();
$arabic_labels = array(
    'dashboard'       => 'لوحة الحساب',
    'orders'          => 'الطلبات',
    'downloads'       => 'التحميلات',
    'edit-address'    => 'العناوين',
    'payment-methods' => 'طرق الدفع',
    'edit-account'    => 'بيانات الحساب',
    'customer-logout' => 'تسجيل الخروج',
);
?>
<nav class="woocommerce-MyAccount-navigation ct-account-nav" aria-label="قائمة الحساب">
    <ul>
        <?php foreach ($items as $endpoint => $label) : ?>
            <?php $label = $arabic_labels[$endpoint] ?? $label; ?>
            <li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>">
                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
                    <span class="ct-account-nav-dot" aria-hidden="true"></span>
                    <span class="ct-account-nav-label"><?php echo esc_html($label); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
