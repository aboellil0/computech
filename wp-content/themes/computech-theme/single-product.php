<?php
/**
 * WooCommerce single product template.
 */
get_header();

if (!function_exists('wc_get_product')) {
    get_footer();
    return;
}

while (have_posts()) : the_post();
    $product = wc_get_product(get_the_ID());
    if (!$product instanceof WC_Product) {
        continue;
    }
    $product_id = $product->get_id();
    $title = $product->get_name();
    $primary_id = function_exists('computech_wc_product_primary_category_id') ? computech_wc_product_primary_category_id($product_id) : 0;
    $parents = array(array('label' => 'المنتجات', 'url' => function_exists('computech_wc_products_page_url') ? computech_wc_products_page_url() : computech_page_url('products')));
    if ($primary_id) {
        $ancestor_ids = array_reverse(get_ancestors($primary_id, 'product_cat', 'taxonomy'));
        foreach ($ancestor_ids as $ancestor_id) {
            $ancestor = get_term((int) $ancestor_id, 'product_cat');
            if ($ancestor instanceof WP_Term && !is_wp_error($ancestor)) {
                $parents[] = array('label' => $ancestor->name, 'url' => get_term_link($ancestor));
            }
        }
        $primary = get_term($primary_id, 'product_cat');
        if ($primary instanceof WP_Term && !is_wp_error($primary)) {
            $parents[] = array('label' => $primary->name, 'url' => get_term_link($primary));
        }
    }
    computech_breadcrumbs($title, $parents);

    $image_id = $product->get_image_id();
    $main_img = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src('large');
    $main_alt = $image_id ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : $title;
    $gallery_ids = $product->get_gallery_image_ids();
    $gallery = array();
    if ($main_img) { $gallery[] = array('url' => $main_img, 'alt' => $main_alt ?: $title); }
    foreach ($gallery_ids as $gallery_id) {
        $url = wp_get_attachment_image_url($gallery_id, 'large');
        if ($url) {
            $gallery[] = array('url' => $url, 'alt' => (string) get_post_meta($gallery_id, '_wp_attachment_image_alt', true) ?: $title);
        }
    }
    $whatsapp_url = function_exists('computech_wc_product_whatsapp_url') ? computech_wc_product_whatsapp_url($product) : '';
    $badge = function_exists('computech_wc_product_condition_label') ? computech_wc_product_condition_label($product) : '';
    $highlights = function_exists('computech_wc_product_highlights') ? computech_wc_product_highlights($product, 8) : array();
    ?>

    <section class="pd-page">
        <div class="pd-container" id="pd-app">
            <div class="pd-main">
                <div class="pd-info">
                    <?php if ($badge !== '') : ?><span class="pd-badge"><?php echo esc_html($badge); ?></span><?php endif; ?>
                    <?php if (!$product->is_in_stock()) : ?><span class="pd-badge outofstock">غير متوفر</span><?php endif; ?>
                    <h1 class="pd-title"><?php echo esc_html($title); ?></h1>
                    <p class="pd-desc"><?php echo esc_html(wp_strip_all_tags($product->get_short_description()) ?: wp_trim_words(wp_strip_all_tags($product->get_description()), 36, '...')); ?></p>
                    <div class="pd-meta-row">
                        <?php if ($product->get_sku()) : ?><span>SKU: <?php echo esc_html($product->get_sku()); ?></span><?php endif; ?>
                        <span><?php echo esc_html($product->is_in_stock() ? 'متوفر' : 'غير متوفر'); ?></span>
                    </div>
                    <?php if ($product->get_price_html() !== '') : ?><div class="pd-price-row"><span class="pd-price"><?php echo wp_kses_post($product->get_price_html()); ?></span></div><?php endif; ?>
                    <div class="pd-divider"></div>
                    <div class="pd-buttons">
                        <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-quantity="1" data-product_id="<?php echo esc_attr((string) $product_id); ?>" class="pd-btn pd-btn-primary add_to_cart_button ajax_add_to_cart"><?php echo esc_html($product->add_to_cart_text()); ?></a>
                        <?php endif; ?>
                        <?php if ($whatsapp_url !== '') : ?><a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="pd-btn pd-btn-whatsapp"><?php echo computech_whatsapp_icon(); ?>استفسر واتساب</a><?php endif; ?>
                        <a href="#product-specs" class="pd-btn pd-btn-share">المواصفات</a>
                    </div>
                </div>
                <div class="pd-gallery">
                    <?php if (!empty($gallery)) : ?><div class="pd-gallery-main"><img src="<?php echo esc_url($gallery[0]['url']); ?>" alt="<?php echo esc_attr($gallery[0]['alt']); ?>" id="pdMainImg"></div><?php endif; ?>
                    <?php if (count($gallery) > 1) : ?><div class="pd-gallery-thumbs"><?php foreach ($gallery as $img) : ?><button type="button" class="pd-thumb" onclick="document.getElementById('pdMainImg').src='<?php echo esc_js($img['url']); ?>'"><img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>"></button><?php endforeach; ?></div><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($highlights || $product->get_attributes()) : ?>
    <section class="pd-specs" id="product-specs"><div class="pd-container"><div class="pd-section-header"><div class="pd-section-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div><h2 class="pd-section-title">مواصفات <span class="pd-highlight">المنتج</span></h2></div><div class="pd-specs-card"><table class="pd-specs-table"><tbody>
        <?php foreach ($product->get_attributes() as $attribute) :
            if (!$attribute instanceof WC_Product_Attribute || !$attribute->get_visible()) { continue; }
            $name = wc_attribute_label($attribute->get_name());
            if ($attribute->is_taxonomy()) {
                $values = wc_get_product_terms($product_id, $attribute->get_name(), array('fields' => 'names'));
                $value = is_wp_error($values) ? '' : implode(', ', $values);
            } else {
                $value = implode(', ', $attribute->get_options());
            }
            if ($value === '') { continue; }
        ?>
            <tr><td><?php echo esc_html($name); ?></td><td><?php echo esc_html($value); ?></td></tr>
        <?php endforeach; ?>
        <?php foreach ($highlights as $line) : if (strpos($line, ':') === false) { continue; } $parts = array_map('trim', explode(':', $line, 2)); ?><tr><td><?php echo esc_html($parts[0]); ?></td><td><?php echo esc_html($parts[1] ?? ''); ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div></section>
    <?php endif; ?>

    <?php
    $related_ids = wc_get_related_products($product_id, 4);
    if ($related_ids) : ?>
    <section class="prod-grid-section"><div class="prod-container"><div class="prod-section-head"><h2>منتجات مشابهة</h2><p>من WooCommerce related products.</p></div><div class="prod-grid"><?php foreach ($related_ids as $related_id) { $related_product = wc_get_product($related_id); if ($related_product) { computech_wc_product_card($related_product); } } ?></div></div></section>
    <?php endif; ?>

<?php endwhile; get_footer(); ?>
