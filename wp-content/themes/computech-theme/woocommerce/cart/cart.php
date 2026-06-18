<?php
/**
 * Computech custom cart template.
 *
 * @package Computech
 * @version 7.9.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<div class="ct-cart-shell">
    <form class="woocommerce-cart-form ct-cart ct-cart-main" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <?php do_action('woocommerce_before_cart_table'); ?>
            <div class="ct-cart-panel ct-cart-items-panel">
                <div class="ct-cart-panel-head">
                    <div>
                        <span class="ct-cart-kicker">سلة المشتريات</span>
                        <h2>منتجاتك المختارة</h2>
                    </div>
                    <span class="ct-cart-count-pill">
                        <?php echo esc_html(sprintf(_n('%d منتج', '%d منتجات', WC()->cart->get_cart_contents_count(), 'computech'), WC()->cart->get_cart_contents_count())); ?>
                    </span>
                </div>

                <div class="ct-cart-list" role="table" aria-label="عناصر السلة">
                    <?php do_action('woocommerce_before_cart_contents'); ?>

                    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
                        <?php
                        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                        $product_name = apply_filters('woocommerce_cart_item_name', $_product ? $_product->get_name() : '', $cart_item, $cart_item_key);

                        if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0 || !apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                            continue;
                        }

                        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        ?>

                        <div class="ct-cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>" role="row">
                            <div class="ct-cart-item-media">
                                <?php
                                $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
                                if (!$product_permalink) {
                                    echo wp_kses_post($thumbnail);
                                } else {
                                    printf('<a href="%s">%s</a>', esc_url($product_permalink), wp_kses_post($thumbnail));
                                }
                                ?>
                            </div>

                            <div class="ct-cart-item-info">
                                <div class="ct-cart-item-title">
                                    <?php
                                    if (!$product_permalink) {
                                        echo wp_kses_post($product_name . '&nbsp;');
                                    } else {
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                                    }

                                    do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);
                                    echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                                    if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . 'متاح للحجز المسبق' . '</p>', $product_id));
                                    }
                                    ?>
                                </div>

                                <div class="ct-cart-item-meta">
                                    <span><strong>السعر:</strong> <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                    <span><strong>الإجمالي:</strong> <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                </div>
                            </div>

                            <div class="ct-cart-item-actions">
                                <div class="ct-cart-quantity-wrap">
                                    <span class="ct-cart-field-label">الكمية</span>
                                    <?php
                                    if ($_product->is_sold_individually()) {
                                        $min_quantity = 1;
                                        $max_quantity = 1;
                                    } else {
                                        $min_quantity = 0;
                                        $max_quantity = $_product->get_max_purchase_quantity();
                                    }

                                    $product_quantity = woocommerce_quantity_input(
                                        array(
                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                            'input_value'  => $cart_item['quantity'],
                                            'max_value'    => $max_quantity,
                                            'min_value'    => $min_quantity,
                                            'product_name' => $product_name,
                                        ),
                                        $_product,
                                        false
                                    );

                                    echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    ?>
                                </div>

                                <?php
                                echo apply_filters(
                                    'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a href="%s" class="ct-cart-remove remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
                                        esc_url(wc_get_cart_remove_url($cart_item_key)),
                                        esc_attr(sprintf('حذف %s من السلة', wp_strip_all_tags($product_name))),
                                        esc_attr($product_id),
                                        esc_attr($_product->get_sku()),
                                        '<span aria-hidden="true">×</span><span>حذف</span>'
                                    ),
                                    $cart_item_key
                                );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php do_action('woocommerce_cart_contents'); ?>
                </div>
            </div>

            <div class="ct-cart-panel ct-cart-actions-panel">
                <?php if (wc_coupons_enabled()) : ?>
                    <div class="coupon ct-cart-coupon">
                        <label for="coupon_code" class="screen-reader-text">كود الخصم:</label>
                        <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="كود الخصم" />
                        <button type="submit" class="button" name="apply_coupon" value="تطبيق الكوبون">تطبيق الكوبون</button>
                        <?php do_action('woocommerce_cart_coupon'); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="button ct-cart-update" name="update_cart" value="تحديث السلة">تحديث السلة</button>
                <?php do_action('woocommerce_cart_actions'); ?>
                <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
            </div>
        <?php do_action('woocommerce_after_cart_contents'); ?>
        <?php do_action('woocommerce_after_cart_table'); ?>
    </form>

    <aside class="ct-cart-summary">
        <?php do_action('woocommerce_before_cart_collaterals'); ?>
        <div class="cart-collaterals">
            <?php woocommerce_cart_totals(); ?>
        </div>
    </aside>
</div>

<?php do_action('woocommerce_after_cart'); ?>
