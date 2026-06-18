<?php
/**
 * Computech custom checkout layout.
 *
 * @package Computech
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout ct-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="إتمام الطلب">
    <div class="ct-checkout-shell">
        <?php if ($checkout->get_checkout_fields()) : ?>
            <section class="ct-checkout-main" aria-label="بيانات العميل والشحن">
                <div class="ct-checkout-panel-head">
                    <span class="ct-checkout-kicker">إتمام الطلب</span>
                    <h2>بيانات العميل</h2>
                    <p>أكمل بيانات التواصل والتوصيل لتجهيز طلبك بسرعة.</p>
                </div>

                <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                <div class="col2-set ct-checkout-fields" id="customer_details">
                    <div class="col-1 ct-checkout-field-group">
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>

                    <div class="col-2 ct-checkout-field-group">
                        <?php do_action('woocommerce_checkout_shipping'); ?>
                    </div>
                </div>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            </section>
        <?php endif; ?>

        <aside class="ct-checkout-summary" aria-label="ملخص الطلب">
            <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

            <div class="ct-checkout-summary-head">
                <span class="ct-checkout-kicker">مراجعة الطلب</span>
                <h3 id="order_review_heading">ملخص الطلب</h3>
            </div>

            <?php do_action('woocommerce_checkout_before_order_review'); ?>

            <div id="order_review" class="woocommerce-checkout-review-order ct-checkout-review">
                <?php do_action('woocommerce_checkout_order_review'); ?>
            </div>

            <?php do_action('woocommerce_checkout_after_order_review'); ?>
        </aside>
    </div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
