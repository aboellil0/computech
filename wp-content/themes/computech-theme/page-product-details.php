<?php
/**
 * Template Name: صفحة تفاصيل المنتج - تحويل للمنتجات الحقيقية
 *
 * This legacy page does not contain hard-coded products anymore.
 */
get_header();
computech_breadcrumbs('تفاصيل المنتج', function_exists('computech_arch_category_breadcrumb_root') ? computech_arch_category_breadcrumb_root() : array(array('label' => 'أقسام المتجر', 'url' => computech_page_url('categories'))));

$product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
$product_slug = isset($_GET['slug']) ? sanitize_title(wp_unslash($_GET['slug'])) : '';
$product = null;

if ($product_id > 0 && get_post_type($product_id) === 'products') {
    $product = get_post($product_id);
} elseif ($product_slug !== '') {
    $product = get_page_by_path($product_slug, OBJECT, 'products');
}

if ($product instanceof WP_Post && $product->post_status === 'publish') {
    wp_safe_redirect(get_permalink($product));
    exit;
}
?>
<section class="pd-page">
    <div class="pd-container">
        <div class="wp-product-empty">
            <h1>تفاصيل المنتجات أصبحت من الداتابيز</h1>
            <p>افتح أي منتج من صفحة المنتجات أو من كارت المنتج، وسيتم عرض التفاصيل من بيانات المنتج الحقيقية في لوحة التحكم.</p>
            <a class="pd-btn pd-btn-primary" href="<?php echo esc_url(computech_page_url('products')); ?>">العودة إلى المنتجات</a>
        </div>
    </div>
</section>
<?php get_footer(); ?>
