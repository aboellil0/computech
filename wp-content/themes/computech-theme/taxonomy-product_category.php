<?php
/**
 * Product category archive template.
 * Shows products directly assigned to this category + all descendant categories.
 */
get_header();

$term = get_queried_object();
if (!$term instanceof WP_Term || is_wp_error($term)) {
    get_footer();
    return;
}

if (function_exists('computech_arch_is_category_visible') && !computech_arch_is_category_visible((int) $term->term_id)) {
    status_header(404);
    ?>
    <section class="prod-hero"><div class="prod-container prod-hero-inner"><h1 class="prod-hero-title">القسم غير متاح</h1><p class="prod-hero-subtitle">هذا القسم مخفي من لوحة التحكم.</p></div></section>
    <?php
    get_footer();
    return;
}

$parents = function_exists('computech_arch_category_breadcrumb_root') ? computech_arch_category_breadcrumb_root() : array(array('label' => 'أقسام المتجر', 'url' => computech_page_url('categories')));
$ancestor_ids = array_reverse(get_ancestors((int) $term->term_id, 'product_category', 'taxonomy'));
foreach ($ancestor_ids as $ancestor_id) {
    $ancestor = get_term((int) $ancestor_id, 'product_category');
    if ($ancestor instanceof WP_Term && !is_wp_error($ancestor)) {
        $parents[] = array('label' => $ancestor->name, 'url' => get_term_link($ancestor));
    }
}
computech_breadcrumbs($term->name, $parents);

$description = term_description($term, 'product_category');
if (!$description && function_exists('computech_arch_term_meta')) {
    $description = computech_arch_term_meta((int) $term->term_id, '_computech_cat_full_description', '');
}

$child_terms = get_terms(array(
    'taxonomy' => 'product_category',
    'hide_empty' => false,
    'parent' => (int) $term->term_id,
));
if (!is_wp_error($child_terms)) {
    if (function_exists('computech_arch_is_category_visible')) {
        $child_terms = array_values(array_filter($child_terms, static fn($child): bool => $child instanceof WP_Term && computech_arch_is_category_visible((int) $child->term_id)));
    }
    if (function_exists('computech_arch_term_meta')) {
        usort($child_terms, static function(WP_Term $a, WP_Term $b): int {
            $ao = (int) computech_arch_term_meta((int) $a->term_id, '_computech_term_order', '0');
            $bo = (int) computech_arch_term_meta((int) $b->term_id, '_computech_term_order', '0');
            return $ao === $bo ? strnatcasecmp($a->name, $b->name) : $ao <=> $bo;
        });
    }
}

$meta_query = function_exists('computech_arch_visibility_meta_query') ? computech_arch_visibility_meta_query() : array();
$products_query = new WP_Query(array(
    'post_type' => 'products',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
    'tax_query' => array(array(
        'taxonomy' => 'product_category',
        'field' => 'term_id',
        'terms' => array((int) $term->term_id),
        'include_children' => true,
    )),
    'meta_query' => $meta_query,
));
?>

<section class="prod-hero">
    <div class="prod-container prod-hero-inner">
        <h1 class="prod-hero-title"><?php echo esc_html($term->name); ?></h1>
        <p class="prod-hero-subtitle"><?php echo esc_html(wp_strip_all_tags($description ?: sprintf('منتجات هذا القسم من %s، وتشمل المنتجات المرتبطة بالأقسام الفرعية تلقائيًا.', computech_site_name()))); ?></p>
    </div>
</section>

<?php if (!is_wp_error($child_terms) && !empty($child_terms) && function_exists('computech_arch_term_to_card_item')) : ?>
<section class="cat-all">
    <div class="cat-container">
        <div class="cat-section-header"><h2 class="cat-section-title">أقسام <span class="cat-section-highlight">فرعية</span></h2><p class="cat-section-subtitle">تقدر تدخل لأي فرع وتشوف منتجاته هو وما تحته.</p></div>
        <div class="cat-grid">
            <?php foreach ($child_terms as $child) { computech_arch_render_category_grid_card(computech_arch_term_to_card_item($child, 'all')); } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="prod-grid-section">
    <div class="prod-container">
        <div class="prod-section-head"><h2>منتجات القسم</h2><p>تظهر هنا المنتجات المرتبطة مباشرة بهذا القسم أو بأي قسم فرعي داخله.</p></div>
        <div id="prodGrid" class="prod-grid">
            <?php if ($products_query->have_posts()) : while ($products_query->have_posts()) : $products_query->the_post(); computech_product_card(); endwhile; wp_reset_postdata(); else : ?>
                <div class="wp-product-empty"><h2>لا توجد منتجات في هذا القسم حالياً</h2><p>أضف منتجات من لوحة التحكم ثم اختر هذا القسم أو أي قسم فرعي تحته.</p></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php get_footer(); ?>
