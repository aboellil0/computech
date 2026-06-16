<?php
/**
 * Single product template.
 */
get_header();

while (have_posts()) : the_post();
$post_id = get_the_ID();
$title = get_the_title();

if (function_exists('computech_arch_is_product_visible') && !computech_arch_is_product_visible($post_id)) {
    status_header(404);
    computech_breadcrumbs('المنتج غير متاح', function_exists('computech_arch_category_breadcrumb_root') ? computech_arch_category_breadcrumb_root() : array(array('label' => 'أقسام المتجر', 'url' => computech_page_url('categories'))));
    echo '<section class="pd-page"><div class="pd-container"><div class="wp-product-empty"><h1>المنتج غير متاح</h1><p>هذا المنتج مخفي من لوحة التحكم.</p></div></div></section>';
    get_footer();
    return;
}

$regular_price = computech_get_meta($post_id, '_computech_regular_price', computech_get_meta($post_id, '_computech_old_price', ''));
$sale_price = computech_get_meta($post_id, '_computech_sale_price', computech_get_meta($post_id, '_computech_price', ''));
$price = $sale_price !== '' ? $sale_price : $regular_price;
$old_price = ($sale_price !== '' && $regular_price !== '' && $sale_price !== $regular_price) ? $regular_price : computech_get_meta($post_id, '_computech_old_price', '');
$show_price = computech_get_meta($post_id, '_computech_show_price', '1') !== '0';
$condition = computech_get_meta($post_id, '_computech_condition', computech_get_meta($post_id, '_computech_status', ''));
$availability = computech_get_meta($post_id, '_computech_availability', '');
$badge_text = computech_get_meta($post_id, '_computech_badge_text', '');
if ($badge_text === '' && $condition !== '') {
    $badge_text = $condition === 'imported' ? 'استيراد خارج' : ($condition === 'used' ? 'مستعمل' : ($condition === 'refurbished' ? 'مجدد' : ($condition === 'new' ? 'جديد' : $condition)));
}
$rating = computech_get_meta($post_id, '_computech_rating', '');
$main_thumb_id = get_post_thumbnail_id($post_id);
$main_img = $main_thumb_id ? wp_get_attachment_image_url($main_thumb_id, 'large') : '';
$main_alt = $main_thumb_id ? (string) get_post_meta($main_thumb_id, '_wp_attachment_image_alt', true) : '';
$gallery_ids = array_filter(array_map('absint', explode(',', computech_get_meta($post_id, '_computech_gallery_ids', ''))));
$gallery = $main_img !== '' ? array($main_img) : array();
foreach ($gallery_ids as $gallery_id) {
    $url = wp_get_attachment_image_url($gallery_id, 'large');
    if ($url) {
        $gallery[] = $url;
    }
}
$gallery = array_values(array_unique($gallery));
$whatsapp_url = function_exists('computech_arch_product_whatsapp_url') ? computech_arch_product_whatsapp_url($post_id, $title) : '';
$full_specs = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', computech_get_meta($post_id, '_computech_full_specs', ''))));
if (!$full_specs) {
    $full_specs = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', computech_get_meta($post_id, '_computech_specs', ''))));
}
$breadcrumb_parents = function_exists('computech_arch_product_breadcrumb_parents') ? computech_arch_product_breadcrumb_parents($post_id) : array(array('label' => 'أقسام المتجر', 'url' => computech_page_url('categories')));
computech_breadcrumbs($title, $breadcrumb_parents);
$show_details = computech_get_meta($post_id, '_computech_show_details_button', '1') !== '0';
$show_whatsapp = computech_get_meta($post_id, '_computech_show_whatsapp_button', '1') !== '0';
$show_cart = computech_get_meta($post_id, '_computech_show_add_to_cart', '1') !== '0';
$details_text = computech_get_meta($post_id, '_computech_details_button_text', '');
$warranty = computech_get_meta($post_id, '_computech_warranty', '');
$price_note = computech_get_meta($post_id, '_computech_price_note', '');
$brand = computech_get_meta($post_id, '_computech_brand', '');
$model = computech_get_meta($post_id, '_computech_model', '');
$sku = computech_get_meta($post_id, '_computech_sku', '');
$add_to_cart_text = computech_get_meta($post_id, '_computech_add_to_cart_text', '');
$whatsapp_button_text = computech_get_meta($post_id, '_computech_whatsapp_button_text', '');
?>
<section class="pd-page">
    <div class="pd-container" id="pd-app">
        <div class="pd-main">
            <div class="pd-info">
                <?php if ($badge_text !== '') : ?><span class="pd-badge <?php echo esc_attr($condition); ?>"><?php echo esc_html($badge_text); ?></span><?php endif; ?>
                <?php if ($availability !== '' && $availability !== 'in-stock') : ?><span class="pd-badge <?php echo esc_attr($availability); ?>"><?php echo esc_html($availability === 'coming-soon' ? 'قريبًا' : 'غير متوفر'); ?></span><?php endif; ?>
                <h1 class="pd-title"><?php echo esc_html($title); ?></h1>
                <p class="pd-desc"><?php echo esc_html(get_the_excerpt() ?: wp_trim_words(wp_strip_all_tags(get_the_content()), 36, '...')); ?></p>
                <?php if ($brand || $model || $sku) : ?><div class="pd-meta-row"><?php if ($brand) : ?><span>البراند: <?php echo esc_html($brand); ?></span><?php endif; ?><?php if ($model) : ?><span>الموديل: <?php echo esc_html($model); ?></span><?php endif; ?><?php if ($sku) : ?><span>SKU: <?php echo esc_html($sku); ?></span><?php endif; ?></div><?php endif; ?>
                <?php if ($rating !== '') : ?><div class="pd-rating"><div class="pd-stars">★★★★★</div><span class="pd-rating-text"><?php echo esc_html($rating); ?> تقييم</span></div><?php endif; ?>
                <?php if ($show_price && ($price !== '' || $old_price !== '')) : ?><div class="pd-price-row"><?php if ($price !== '') : ?><span class="pd-price"><?php echo esc_html($price); ?></span><?php endif; ?><?php if ($old_price !== '') : ?><span class="pd-old-price"><?php echo esc_html($old_price); ?></span><?php endif; ?></div><?php if ($price_note !== '') : ?><p class="pd-desc"><?php echo esc_html($price_note); ?></p><?php endif; ?><?php endif; ?>
                <div class="pd-divider"></div>
                <div class="pd-buttons">
                    <?php if ($show_cart && $add_to_cart_text !== '') : ?><button class="pd-btn pd-btn-primary" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><?php echo esc_html($add_to_cart_text); ?></button><?php endif; ?>
                    <?php if ($show_whatsapp && $whatsapp_url !== '' && $whatsapp_button_text !== '') : ?><a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="pd-btn pd-btn-whatsapp"><?php echo computech_whatsapp_icon(); ?><?php echo esc_html($whatsapp_button_text); ?></a><?php endif; ?>
                    <?php if ($show_details && $details_text !== '') : ?><a href="#product-specs" class="pd-btn pd-btn-share"><?php echo esc_html($details_text); ?></a><?php endif; ?>
                    <button class="pd-btn pd-btn-share" onclick="if(navigator.share){navigator.share({title:document.title,url:window.location.href})}else{navigator.clipboard&&navigator.clipboard.writeText(window.location.href);alert('تم نسخ الرابط')}">مشاركة</button>
                </div>
            </div>
            <div class="pd-gallery">
                <?php if (!empty($gallery)) : ?><div class="pd-gallery-main"><img src="<?php echo esc_url($gallery[0]); ?>" alt="<?php echo esc_attr($main_alt !== '' ? $main_alt : $title); ?>" id="pdMainImg"></div><?php endif; ?>
                <?php if (count($gallery) > 1) : ?><div class="pd-gallery-thumbs"><?php foreach ($gallery as $img) : ?><button type="button" class="pd-thumb" onclick="document.getElementById('pdMainImg').src='<?php echo esc_js($img); ?>'"><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>"></button><?php endforeach; ?></div><?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if ($full_specs) : ?>
<section class="pd-specs" id="product-specs"><div class="pd-container"><div class="pd-section-header"><div class="pd-section-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div><h2 class="pd-section-title">مواصفات <span class="pd-highlight">المنتج</span></h2></div><div class="pd-specs-card"><table class="pd-specs-table"><tbody><?php foreach ($full_specs as $line) : $parts = array_map('trim', explode(':', $line, 2)); if ($parts[0] === '') { continue; } ?><tr><td><?php echo esc_html($parts[0]); ?></td><td><?php echo esc_html($parts[1] ?? ''); ?></td></tr><?php endforeach; ?></tbody></table></div></div></section>
<?php endif; ?>

<?php if ($warranty !== '' || $price_note !== '') : ?><section class="pd-warranty-payment"><div class="pd-container"><?php if ($warranty !== '') : ?><div class="pd-warranty-card"><div class="pd-card-icon blue"></div><h3 class="pd-card-title">الضمان</h3><p class="pd-card-desc"><?php echo esc_html($warranty); ?></p></div><?php endif; ?><?php if ($price_note !== '') : ?><div class="pd-payment-card"><div class="pd-card-icon green"></div><h3 class="pd-card-title">ملاحظة السعر</h3><p class="pd-card-desc"><?php echo esc_html($price_note); ?></p></div><?php endif; ?></div></section><?php endif; ?>

<?php
$related = function_exists('computech_arch_related_products') ? computech_arch_related_products($post_id, 4) : array();
if ($related) : ?>
<section class="prod-grid-section"><div class="prod-container"><div class="prod-section-head"><h2>منتجات مشابهة</h2><p>مختارة من نفس القسم الأساسي أو الأقسام القريبة.</p></div><div class="prod-grid"><?php foreach ($related as $product) { computech_product_card($product); } ?></div></div></section>
<?php endif; ?>

<?php endwhile; get_footer(); ?>
