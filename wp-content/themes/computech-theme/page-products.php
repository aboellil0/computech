<?php
/**
 * Template Name: صفحة المنتجات - كمبيوتيك
 */
get_header();

$products_query = new WP_Query(array(
    'post_type' => 'products',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
    'meta_query' => function_exists('computech_arch_visibility_meta_query') ? computech_arch_visibility_meta_query() : array(),
));

$terms = get_terms(array('taxonomy' => 'product_category', 'hide_empty' => false));
$visible_terms = array();
if (!is_wp_error($terms) && is_array($terms)) {
    foreach ($terms as $term) {
        if (!$term instanceof WP_Term) {
            continue;
        }
        if (function_exists('computech_arch_is_category_visible') && !computech_arch_is_category_visible((int) $term->term_id)) {
            continue;
        }
        $visible_terms[] = $term;
    }
}
?>

<?php computech_breadcrumbs('المنتجات'); ?>

<section class="prod-hero">
<div class="prod-hero-bg"><div class="prod-hero-circuit prod-hero-circuit-1"></div><div class="prod-hero-circuit prod-hero-circuit-2"></div><div class="prod-hero-circuit prod-hero-circuit-3"></div><div class="prod-hero-dot prod-hero-dot-1"></div><div class="prod-hero-dot prod-hero-dot-2"></div><div class="prod-hero-dot prod-hero-dot-3"></div><div class="prod-hero-dot prod-hero-dot-4"></div><div class="prod-hero-glow prod-hero-glow-1"></div><div class="prod-hero-glow prod-hero-glow-2"></div></div>
<div class="prod-container prod-hero-inner"><div class="prod-hero-decorative-dots"><span class="h-dot blue"></span><span class="h-dot cyan"></span><span class="h-dot green"></span></div><h1 class="prod-hero-title">قائمة المنتجات</h1><p class="prod-hero-subtitle">كل منتج ظاهر هنا يتم سحبه من لوحة تحكم ووردبريس حسب بيانات المنتج والأقسام المرتبطة به.</p><div class="prod-hero-pills"><span class="prod-hero-pill">من الداشبورد</span><span class="prod-hero-pill">مرتبط بالأقسام</span><span class="prod-hero-pill">بدون بيانات ثابتة</span></div></div>
</section>

<section class="prod-filters"><div class="prod-container"><div class="prod-filters-bar">
<div class="prod-filter-group prod-filter-search"><div class="prod-filter-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div><input type="text" id="prodSearchInput" placeholder="ابحث عن منتج..." class="prod-filter-input"></div>
<div class="prod-filter-group"><div class="prod-filter-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div><select id="prodCategoryFilter" class="prod-filter-select"><option value="all">كل الأقسام</option><?php foreach ($visible_terms as $term) : ?><option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option><?php endforeach; ?></select></div>
<div class="prod-filter-group"><div class="prod-filter-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></div><select id="prodStatusFilter" class="prod-filter-select"><option value="all">كل الحالات</option><option value="new">جديد</option><option value="imported">استيراد خارج</option><option value="used">مستعمل</option><option value="refurbished">مجدد</option><option value="in-stock">متوفر</option><option value="out-of-stock">غير متوفر</option><option value="coming-soon">قريبًا</option></select></div>
<div class="prod-filter-group"><div class="prod-filter-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg></div><select id="prodSortFilter" class="prod-filter-select"><option value="newest">الأحدث</option><option value="price-asc">السعر من الأقل للأعلى</option><option value="price-desc">السعر من الأعلى للأقل</option></select></div>
</div></div></section>

<section class="prod-grid-section"><div class="prod-container"><div id="prodGrid" class="prod-grid">
<?php if ($products_query->have_posts()) : ?>
    <?php while ($products_query->have_posts()) : $products_query->the_post(); computech_product_card(); endwhile; wp_reset_postdata(); ?>
<?php else : ?>
    <div class="wp-product-empty"><h2>لا توجد منتجات بعد</h2><p>أضف المنتجات من لوحة التحكم واربطها بأقسام المتجر، ثم ستظهر هنا تلقائيًا.</p></div>
<?php endif; ?>
</div></div></section>

<section class="prod-trust"><div class="prod-container"><div class="prod-trust-bar"><div class="prod-trust-item"><div class="prod-trust-text"><strong>ضمان حسب المنتج</strong><span>يتسحب من بيانات المنتج</span></div></div><div class="prod-trust-sep"></div><div class="prod-trust-item"><div class="prod-trust-text"><strong>أقسام مترابطة</strong><span>المنتج يظهر في القسم وكل آبائه</span></div></div><div class="prod-trust-sep"></div><div class="prod-trust-item"><div class="prod-trust-text"><strong>تحكم كامل</strong><span>من الداشبورد فقط</span></div></div></div></div></section>

<?php get_footer(); ?>
