<?php
/**
 * Custom Computech account dashboard.
 *
 * @package Computech
 * @version 4.4.0
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$account_url = function_exists('computech_account_url') ? computech_account_url() : wc_get_page_permalink('myaccount');
$orders_url = wc_get_account_endpoint_url('orders');
$edit_url = wc_get_account_endpoint_url('edit-account');
$products_url = function_exists('computech_wc_products_page_url') ? computech_wc_products_page_url() : (function_exists('computech_page_url') ? computech_page_url('products') : home_url('/products/'));
?>
<div class="ct-account-dashboard">
    <div class="ct-account-dashboard-head">
        <span class="ct-account-kicker">Account Overview</span>
        <h2>أهلًا <?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></h2>
        <p>من هنا تقدر تتابع طلباتك، تعدل بيانات حسابك، وتكمل التسوق بسهولة.</p>
    </div>

    <div class="ct-account-quick-grid">
        <a class="ct-account-quick-card" href="<?php echo esc_url($orders_url); ?>">
            <span>01</span>
            <strong>طلباتي</strong>
            <small>تابع حالة الطلبات السابقة والجديدة</small>
        </a>
        <a class="ct-account-quick-card" href="<?php echo esc_url($edit_url); ?>">
            <span>02</span>
            <strong>بيانات الحساب</strong>
            <small>حدّث الاسم، البريد، وكلمة المرور</small>
        </a>
        <a class="ct-account-quick-card" href="<?php echo esc_url($products_url); ?>">
            <span>03</span>
            <strong>تصفح المنتجات</strong>
            <small>ارجع لقائمة منتجات كمبيوتك</small>
        </a>
    </div>

    <div class="ct-account-logout-box">
        <div>
            <strong>هل تريد الخروج من الحساب؟</strong>
            <p>يمكنك تسجيل الخروج والرجوع لصفحة التسجيل في أي وقت.</p>
        </div>
        <a class="ct-account-logout-btn" href="<?php echo esc_url(wc_logout_url($account_url)); ?>">تسجيل الخروج</a>
    </div>
</div>
