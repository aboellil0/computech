<?php
/**
 * Computech Arabic thank you page.
 *
 * @package Computech
 * @version 8.1.0
 */

defined('ABSPATH') || exit;
?>

<div class="ct-order-received" dir="rtl">
    <?php if ($order) : ?>
        <?php if ($order->has_status('failed')) : ?>
            <div class="ct-order-status-card is-failed">
                <span class="ct-order-kicker">لم يكتمل الطلب</span>
                <h2>للأسف لم تتم عملية الدفع</h2>
                <p>يمكنك المحاولة مرة أخرى أو التواصل معنا لمساعدتك في إتمام الطلب.</p>
                <div class="ct-order-actions">
                    <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay">إعادة المحاولة</a>
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay secondary">حسابي</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="ct-order-status-card is-success">
                <span class="ct-order-kicker">تم استلام الطلب</span>
                <h2>شكرًا لك، طلبك وصلنا بنجاح</h2>
                <p>سنراجع تفاصيل الطلب ونتواصل معك لتأكيد التجهيز أو التوصيل.</p>
            </div>

            <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details ct-order-overview">
                <li class="woocommerce-order-overview__order order">
                    <span>رقم الطلب</span>
                    <strong><?php echo esc_html($order->get_order_number()); ?></strong>
                </li>
                <li class="woocommerce-order-overview__date date">
                    <span>تاريخ الطلب</span>
                    <strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong>
                </li>
                <?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
                    <li class="woocommerce-order-overview__email email">
                        <span>البريد الإلكتروني</span>
                        <strong><?php echo esc_html($order->get_billing_email()); ?></strong>
                    </li>
                <?php endif; ?>
                <li class="woocommerce-order-overview__total total">
                    <span>الإجمالي</span>
                    <strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
                </li>
                <?php if ($order->get_payment_method_title()) : ?>
                    <li class="woocommerce-order-overview__payment-method method">
                        <span>طريقة الدفع</span>
                        <strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
        <?php do_action('woocommerce_thankyou', $order->get_id()); ?>
    <?php else : ?>
        <div class="ct-order-status-card is-success">
            <span class="ct-order-kicker">تم استلام الطلب</span>
            <h2>شكرًا لك</h2>
            <p>تم استلام طلبك بنجاح.</p>
        </div>
    <?php endif; ?>
</div>
