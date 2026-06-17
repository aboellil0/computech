<?php
/**
 * Template Name: صفحة المنتجات - كمبيوتك WooCommerce
 */
get_header();

computech_breadcrumbs('المنتجات');
?>

<section class="prod-hero">
    <div class="prod-hero-bg"><div class="prod-hero-circuit prod-hero-circuit-1"></div><div class="prod-hero-circuit prod-hero-circuit-2"></div><div class="prod-hero-circuit prod-hero-circuit-3"></div><div class="prod-hero-dot prod-hero-dot-1"></div><div class="prod-hero-dot prod-hero-dot-2"></div><div class="prod-hero-dot prod-hero-dot-3"></div><div class="prod-hero-dot prod-hero-dot-4"></div><div class="prod-hero-glow prod-hero-glow-1"></div><div class="prod-hero-glow prod-hero-glow-2"></div></div>
    <div class="prod-container prod-hero-inner">
        <div class="prod-hero-decorative-dots"><span class="h-dot blue"></span><span class="h-dot cyan"></span><span class="h-dot green"></span></div>
        <h1 class="prod-hero-title">قائمة المنتجات</h1>
        <p class="prod-hero-subtitle">كل المنتجات، البحث، الفلاتر، الأسعار، الصور، الأقسام، والمخزون من WooCommerce فقط.</p>
        <div class="prod-hero-pills"><span class="prod-hero-pill">WooCommerce Products</span><span class="prod-hero-pill">Product Categories</span><span class="prod-hero-pill">Attributes Filters</span></div>
    </div>
</section>

<?php
if (!function_exists('computech_wc_active') || !computech_wc_active()) : ?>
    <section class="prod-grid-section"><div class="prod-container"><div class="wp-product-empty"><h2>WooCommerce غير مفعل</h2><p>فعّل WooCommerce ثم أضف المنتجات من Products.</p></div></div></section>
<?php else :
    computech_wc_render_products_filters();
    $products_query = new WP_Query(computech_wc_product_query_args_from_request(12));
    ?>
    <section class="prod-grid-section"><div class="prod-container"><div id="prodGrid" class="prod-grid">
        <?php if ($products_query->have_posts()) : ?>
            <?php while ($products_query->have_posts()) : $products_query->the_post(); computech_wc_product_card(get_post()); endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="wp-product-empty"><h2>لا توجد منتجات</h2><p>أضف المنتجات من WooCommerce أو غيّر الفلاتر.</p></div>
        <?php endif; ?>
    </div>
    <?php
    $pagination = paginate_links(array(
        'total' => $products_query->max_num_pages,
        'current' => max(1, (int) get_query_var('paged'), (int) get_query_var('page')),
        'type' => 'list',
        'add_args' => array_map('sanitize_text_field', wp_unslash($_GET)),
    ));
    if ($pagination) {
        echo '<nav class="woocommerce-pagination">' . wp_kses_post($pagination) . '</nav>';
    }
    ?>
    </div></section>
<?php endif; ?>

<section class="prod-trust"><div class="prod-container"><div class="prod-trust-bar"><div class="prod-trust-item"><div class="prod-trust-text"><strong>WooCommerce</strong><span>مصدر المنتجات الوحيد</span></div></div><div class="prod-trust-sep"></div><div class="prod-trust-item"><div class="prod-trust-text"><strong>Attributes</strong><span>مصدر الفلاتر</span></div></div><div class="prod-trust-sep"></div><div class="prod-trust-item"><div class="prod-trust-text"><strong>Product Categories</strong><span>مصدر الأقسام والكروت</span></div></div></div></div></section>

<?php get_footer(); ?>
