<?php
/**
 * Template Name: صفحة تفاصيل المنتج - WooCommerce Redirect
 */
$product_id = absint($_GET['id'] ?? 0);
$product_slug = sanitize_title((string) ($_GET['product'] ?? ''));

if ($product_id && get_post_type($product_id) === 'product') {
    wp_safe_redirect(get_permalink($product_id));
    exit;
}

if ($product_slug !== '') {
    $product_post = get_page_by_path($product_slug, OBJECT, 'product');
    if ($product_post instanceof WP_Post) {
        wp_safe_redirect(get_permalink($product_post));
        exit;
    }
}

get_header();
computech_breadcrumbs('تفاصيل المنتج');
?>
<section class="pd-page"><div class="pd-container"><div class="wp-product-empty"><h1>تفاصيل المنتج من WooCommerce</h1><p>افتح أي منتج من صفحة المنتجات. كل صفحات التفاصيل الآن تستخدم WooCommerce single product.</p><a class="pd-btn pd-btn-primary" href="<?php echo esc_url(function_exists('computech_wc_products_page_url') ? computech_wc_products_page_url() : computech_page_url('products')); ?>">العودة إلى المنتجات</a></div></div></section>
<?php get_footer(); ?>
