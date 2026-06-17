<?php
/**
 * Computech Categories & Products Architecture Layer.
 *
 * Adds the taxonomy/product dashboard fields and front-end rules needed for
 * unlimited category depth, parent/child product inheritance, primary category
 * breadcrumbs, visibility rules, and home-section display controls.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------------------
 * Shared helpers
 * ------------------------------------------------------------ */

function computech_arch_bool_meta($value, string $default = '0'): string {
    if ($value === '' || $value === null) {
        return $default;
    }
    return (string) $value === '1' ? '1' : '0';
}

function computech_arch_term_meta(int $term_id, string $key, string $default = ''): string {
    $value = get_term_meta($term_id, $key, true);
    return $value === '' ? $default : (string) $value;
}

function computech_arch_post_meta(int $post_id, string $key, string $default = ''): string {
    $value = get_post_meta($post_id, $key, true);
    return $value === '' ? $default : (string) $value;
}

function computech_arch_visibility_meta_query(string $key = '_computech_product_visibility'): array {
    return array(
        'relation' => 'OR',
        array('key' => $key, 'compare' => 'NOT EXISTS'),
        array('key' => $key, 'value' => 'hidden', 'compare' => '!='),
    );
}

function computech_arch_is_product_visible(int $post_id): bool {
    return computech_arch_post_meta($post_id, '_computech_product_visibility', 'visible') !== 'hidden';
}

function computech_arch_is_category_visible(int $term_id): bool {
    return computech_arch_term_meta($term_id, '_computech_cat_visibility', 'visible') !== 'hidden';
}

function computech_arch_term_image_data(int $term_id, string $fallback = ''): array {
    $image_id = (int) computech_arch_term_meta($term_id, '_computech_cat_image_id', '0');
    if ($image_id > 0) {
        $url = wp_get_attachment_image_url($image_id, 'large');
        if ($url) {
            $alt = (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $title = get_the_title($image_id);
            return array(
                'url' => $url,
                'alt' => $alt !== '' ? $alt : ($title ?: get_term_field('name', $term_id, 'product_category')),
                'title' => $title ?: '',
            );
        }
    }

    if ($fallback === '') {
        $stored_fallback = (string) get_term_meta($term_id, '_computech_cat_image_fallback', true);
        $fallback = $stored_fallback !== '' ? $stored_fallback : '';
    }

    return array('url' => $fallback, 'alt' => (string) get_term_field('name', $term_id, 'product_category'), 'title' => '');
}

function computech_arch_get_product_term_slugs_with_ancestors(int $post_id): array {
    $terms = get_the_terms($post_id, 'product_category');
    if (is_wp_error($terms) || empty($terms)) {
        return array('general');
    }

    $slugs = array();
    foreach ($terms as $term) {
        if (!$term instanceof WP_Term) {
            continue;
        }
        $slugs[] = $term->slug;
        $ancestors = get_ancestors($term->term_id, 'product_category', 'taxonomy');
        foreach ($ancestors as $ancestor_id) {
            $ancestor = get_term((int) $ancestor_id, 'product_category');
            if ($ancestor instanceof WP_Term && !is_wp_error($ancestor)) {
                $slugs[] = $ancestor->slug;
            }
        }
    }

    $slugs = array_values(array_unique(array_filter(array_map('sanitize_html_class', $slugs))));
    return $slugs ?: array('general');
}

function computech_product_category_filter_slugs(int $post_id): string {
    return implode(' ', computech_arch_get_product_term_slugs_with_ancestors($post_id));
}

function computech_arch_product_primary_category(int $post_id): ?WP_Term {
    $primary_id = (int) computech_arch_post_meta($post_id, '_computech_primary_category', '0');
    if ($primary_id > 0) {
        $term = get_term($primary_id, 'product_category');
        if ($term instanceof WP_Term && !is_wp_error($term)) {
            return $term;
        }
    }

    $terms = get_the_terms($post_id, 'product_category');
    if (!is_wp_error($terms) && !empty($terms)) {
        return $terms[0];
    }

    return null;
}

function computech_arch_product_primary_path(int $post_id): array {
    $term = computech_arch_product_primary_category($post_id);
    if (!$term) {
        return array();
    }

    $ids = array_reverse(get_ancestors($term->term_id, 'product_category', 'taxonomy'));
    $ids[] = $term->term_id;

    $parents = array();
    foreach ($ids as $term_id) {
        $node = get_term((int) $term_id, 'product_category');
        if ($node instanceof WP_Term && !is_wp_error($node)) {
            $parents[] = array('label' => $node->name, 'url' => get_term_link($node));
        }
    }
    return $parents;
}

function computech_arch_category_breadcrumb_root(): array {
    return array(array('label' => 'أقسام المتجر', 'url' => computech_page_url('categories')));
}

function computech_arch_product_breadcrumb_parents(int $post_id): array {
    $parents = computech_arch_category_breadcrumb_root();
    $path = computech_arch_product_primary_path($post_id);
    if ($path) {
        $parents = array_merge($parents, $path);
    }
    return $parents;
}


function computech_arch_product_whatsapp_url(int $post_id, string $title = ''): string {
    $title = $title !== '' ? $title : get_the_title($post_id);
    $number = computech_clean_phone(computech_arch_post_meta($post_id, '_computech_whatsapp_override', ''));
    if ($number === '') {
        $number = computech_clean_phone(computech_arch_post_meta($post_id, '_computech_whatsapp', ''));
    }
    if ($number === '') {
        $number = computech_clean_phone(computech_header_setting('whatsapp_number', ''));
    }
    if ($number === '') {
        return '';
    }

    $message = computech_arch_post_meta($post_id, '_computech_whatsapp_message', '');
    $url = 'https://wa.me/' . $number;
    if ($message !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}

function computech_arch_category_has_parent_loop(int $term_id, int $parent_id): bool {
    if ($term_id <= 0 || $parent_id <= 0) {
        return false;
    }
    if ($term_id === $parent_id) {
        return true;
    }

    $seen = array($term_id => true);
    $current = $parent_id;
    while ($current > 0) {
        if (isset($seen[$current])) {
            return true;
        }
        $seen[$current] = true;
        $parent = (int) get_term_field('parent', $current, 'product_category');
        $current = $parent;
    }
    return false;
}

/* ------------------------------------------------------------
 * Admin: Product category fields
 * ------------------------------------------------------------ */

function computech_arch_category_fields_markup(?WP_Term $term = null): void {
    $term_id = $term instanceof WP_Term ? (int) $term->term_id : 0;
    $visibility = computech_arch_term_meta($term_id, '_computech_cat_visibility', 'visible');
    $image_id = computech_arch_term_meta($term_id, '_computech_cat_image_id', '');
    $icon = computech_arch_term_meta($term_id, '_computech_cat_icon', 'desktop');
    $term_order = computech_arch_term_meta($term_id, '_computech_term_order', '0');
    $show_shop = computech_arch_term_meta($term_id, '_computech_shop_show', '0');
    $shop_order = computech_arch_term_meta($term_id, '_computech_shop_order', '0');
    $shop_badge = computech_arch_term_meta($term_id, '_computech_shop_badge', '');
    $shop_button = computech_arch_term_meta($term_id, '_computech_shop_button', '');
    $show_featured = computech_arch_term_meta($term_id, '_computech_featured_cat_show', '0');
    $featured_order = computech_arch_term_meta($term_id, '_computech_featured_cat_order', '0');
    $featured_badge = computech_arch_term_meta($term_id, '_computech_featured_cat_badge', '');
    $featured_button = computech_arch_term_meta($term_id, '_computech_featured_cat_button', '');
    $full_description = computech_arch_term_meta($term_id, '_computech_cat_full_description', '');
    $is_edit = $term instanceof WP_Term;
    $wrap_start = $is_edit ? '<tr class="form-field term-computech-arch-wrap"><th scope="row">%s</th><td>' : '<div class="form-field term-computech-arch-wrap"><label>%s</label>';
    $wrap_end = $is_edit ? '</td></tr>' : '</div>';
    ?>
    <?php printf($wrap_start, 'Visibility / الظهور'); ?>
        <select name="computech_cat_visibility" class="widefat">
            <option value="visible" <?php selected($visibility, 'visible'); ?>>ظاهر</option>
            <option value="hidden" <?php selected($visibility, 'hidden'); ?>>مخفي</option>
        </select>
        <p class="description">لو القسم مخفي لن يظهر في الصفحة الرئيسية أو قوائم الأقسام.</p>
    <?php echo $wrap_end; ?>

    <?php printf($wrap_start, 'Full Description / الوصف الكامل'); ?>
        <textarea name="computech_cat_full_description" rows="4" class="widefat" style="direction:rtl"><?php echo esc_textarea($full_description); ?></textarea>
        <p class="description">يظهر في صفحة القسم عند الحاجة، أما الوصف القصير هو Description الافتراضي.</p>
    <?php echo $wrap_end; ?>

    <?php printf($wrap_start, 'Category Image / صورة القسم'); ?>
        <input type="hidden" name="computech_cat_image_id" id="computech_cat_image_id" value="<?php echo esc_attr($image_id); ?>">
        <button type="button" class="button computech-arch-media-button" data-target="#computech_cat_image_id" data-preview="#computech_cat_image_preview">اختيار صورة من Media Library</button>
        <button type="button" class="button computech-arch-media-clear" data-target="#computech_cat_image_id" data-preview="#computech_cat_image_preview">إزالة</button>
        <div id="computech_cat_image_preview" style="margin-top:10px"><?php if ((int) $image_id > 0) { echo wp_get_attachment_image((int) $image_id, 'thumbnail'); } ?></div>
        <p class="description">Alt Text وTitle يتسحبوا تلقائيًا من بيانات الصورة في Media Library.</p>
    <?php echo $wrap_end; ?>

    <?php printf($wrap_start, 'Category Icon / أيقونة القسم'); ?>
        <select name="computech_cat_icon" class="widefat">
            <?php foreach (array('desktop' => 'Desktop', 'globe' => 'Globe', 'tag' => 'Tag', 'offer' => 'Offer') as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($icon, $key); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    <?php echo $wrap_end; ?>

    <?php printf($wrap_start, 'Order inside Parent / الترتيب داخل نفس الأب'); ?>
        <input type="number" name="computech_term_order" value="<?php echo esc_attr($term_order); ?>" class="small-text" min="0" step="1">
        <p class="description">هذا الترتيب يخص مكان القسم داخل نفس الـ parent فقط.</p>
    <?php echo $wrap_end; ?>

    <?php printf($wrap_start, 'Home Display / الظهور في الصفحة الرئيسية'); ?>
        <fieldset style="border:1px solid #dcdcde;border-radius:10px;padding:12px;margin-bottom:12px">
            <legend style="font-weight:700">تسوق حسب القسم</legend>
            <label><input type="checkbox" name="computech_shop_show" value="1" <?php checked($show_shop, '1'); ?>> Show in Shop Section / يظهر في تسوق حسب القسم</label>
            <p><label>Shop Section Order<br><input type="number" name="computech_shop_order" value="<?php echo esc_attr($shop_order); ?>" class="small-text" min="0" step="1"></label></p>
            <p><label>Shop Badge Text<br><input type="text" name="computech_shop_badge" value="<?php echo esc_attr($shop_badge); ?>" class="widefat" placeholder="+320 منتج أو عروض حصرية"></label></p>
            <p><label>Shop Button Text<br><input type="text" name="computech_shop_button" value="<?php echo esc_attr($shop_button); ?>" class="widefat" placeholder="استكشف القسم"></label></p>
        </fieldset>
        <fieldset style="border:1px solid #dcdcde;border-radius:10px;padding:12px">
            <legend style="font-weight:700">الأقسام المميزة</legend>
            <label><input type="checkbox" name="computech_featured_cat_show" value="1" <?php checked($show_featured, '1'); ?>> Show in Featured Categories / يظهر في الأقسام المميزة</label>
            <p><label>Featured Categories Order<br><input type="number" name="computech_featured_cat_order" value="<?php echo esc_attr($featured_order); ?>" class="small-text" min="0" step="1"></label></p>
            <p><label>Featured Badge Text<br><input type="text" name="computech_featured_cat_badge" value="<?php echo esc_attr($featured_badge); ?>" class="widefat" placeholder="+250 منتج"></label></p>
            <p><label>Featured Button Text<br><input type="text" name="computech_featured_cat_button" value="<?php echo esc_attr($featured_button); ?>" class="widefat" placeholder="عرض المنتجات"></label></p>
        </fieldset>
        <p class="description">لا يوجد حقل عام اسمه Is Featured. كل سكشن له show/order خاص به.</p>
    <?php echo $wrap_end; ?>
    <?php
}

function computech_arch_add_category_fields(): void {
    computech_arch_category_fields_markup(null);
}
add_action('product_category_add_form_fields', 'computech_arch_add_category_fields');

function computech_arch_edit_category_fields(WP_Term $term): void {
    computech_arch_category_fields_markup($term);
}
add_action('product_category_edit_form_fields', 'computech_arch_edit_category_fields');

function computech_arch_save_category_fields(int $term_id): void {
    $fields = array(
        '_computech_cat_visibility' => sanitize_key(wp_unslash($_POST['computech_cat_visibility'] ?? 'visible')),
        '_computech_cat_full_description' => sanitize_textarea_field(wp_unslash($_POST['computech_cat_full_description'] ?? '')),
        '_computech_cat_image_id' => (string) absint($_POST['computech_cat_image_id'] ?? 0),
        '_computech_cat_icon' => sanitize_key(wp_unslash($_POST['computech_cat_icon'] ?? 'desktop')),
        '_computech_term_order' => (string) absint($_POST['computech_term_order'] ?? 0),
        '_computech_shop_show' => !empty($_POST['computech_shop_show']) ? '1' : '0',
        '_computech_shop_order' => (string) absint($_POST['computech_shop_order'] ?? 0),
        '_computech_shop_badge' => sanitize_text_field(wp_unslash($_POST['computech_shop_badge'] ?? '')),
        '_computech_shop_button' => sanitize_text_field(wp_unslash($_POST['computech_shop_button'] ?? '')),
        '_computech_featured_cat_show' => !empty($_POST['computech_featured_cat_show']) ? '1' : '0',
        '_computech_featured_cat_order' => (string) absint($_POST['computech_featured_cat_order'] ?? 0),
        '_computech_featured_cat_badge' => sanitize_text_field(wp_unslash($_POST['computech_featured_cat_badge'] ?? '')),
        '_computech_featured_cat_button' => sanitize_text_field(wp_unslash($_POST['computech_featured_cat_button'] ?? '')),
    );

    foreach ($fields as $key => $value) {
        update_term_meta($term_id, $key, $value);
    }
}
add_action('created_product_category', 'computech_arch_save_category_fields');
add_action('edited_product_category', 'computech_arch_save_category_fields');

function computech_arch_guard_category_parent_loop(int $term_id): void {
    static $running = false;
    if ($running) {
        return;
    }
    $term = get_term($term_id, 'product_category');
    if (!$term instanceof WP_Term || is_wp_error($term)) {
        return;
    }
    if (computech_arch_category_has_parent_loop((int) $term->term_id, (int) $term->parent)) {
        $running = true;
        wp_update_term($term->term_id, 'product_category', array('parent' => 0));
        $running = false;
    }
}
add_action('edited_product_category', 'computech_arch_guard_category_parent_loop', 20);

/* ------------------------------------------------------------
 * Admin: Product fields
 * ------------------------------------------------------------ */

function computech_arch_product_term_options(int $selected = 0): string {
    $terms = get_terms(array('taxonomy' => 'product_category', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC'));
    if (is_wp_error($terms) || empty($terms)) {
        return '<option value="0">لا توجد أقسام بعد</option>';
    }

    $by_parent = array();
    foreach ($terms as $term) {
        $by_parent[(int) $term->parent][] = $term;
    }

    $html = '<option value="0">اختر القسم الأساسي</option>';
    $walk = function(int $parent_id, int $depth) use (&$walk, &$by_parent, $selected, &$html): void {
        if (empty($by_parent[$parent_id])) {
            return;
        }
        foreach ($by_parent[$parent_id] as $term) {
            $prefix = str_repeat('— ', $depth);
            $html .= sprintf('<option value="%d" %s>%s%s</option>', (int) $term->term_id, selected($selected, (int) $term->term_id, false), esc_html($prefix), esc_html($term->name));
            $walk((int) $term->term_id, $depth + 1);
        }
    };
    $walk(0, 0);
    return $html;
}

function computech_arch_product_data_metabox(WP_Post $post): void {
    wp_nonce_field('computech_arch_save_product_data', 'computech_arch_product_nonce');
    $post_id = (int) $post->ID;
    $visibility = computech_arch_post_meta($post_id, '_computech_product_visibility', 'visible');
    $condition = computech_arch_post_meta($post_id, '_computech_condition', computech_arch_post_meta($post_id, '_computech_status', ''));
    $availability = computech_arch_post_meta($post_id, '_computech_availability', '');
    $show_featured = computech_arch_post_meta($post_id, '_computech_show_featured_products', computech_arch_post_meta($post_id, '_computech_featured_home', '0'));
    $primary_id = (int) computech_arch_post_meta($post_id, '_computech_primary_category', '0');
    $show_price = computech_arch_post_meta($post_id, '_computech_show_price', '1');
    $show_details = computech_arch_post_meta($post_id, '_computech_show_details_button', '1');
    $show_whatsapp = computech_arch_post_meta($post_id, '_computech_show_whatsapp_button', '1');
    $show_cart = computech_arch_post_meta($post_id, '_computech_show_add_to_cart', '1');
    ?>
    <div class="computech-product-admin computech-arch-admin" style="direction:rtl;display:grid;gap:18px">
        <style>
            .computech-arch-admin .arch-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:16px}.computech-arch-admin h3{margin:0 0 12px}.computech-arch-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.computech-arch-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.computech-arch-admin label{font-weight:700;display:block;margin-bottom:6px}.computech-arch-admin p{margin:0 0 12px}.computech-arch-admin .arch-check label{display:inline;font-weight:400}@media(max-width:900px){.computech-arch-grid,.computech-arch-grid-3{grid-template-columns:1fr}}
        </style>

        <section class="arch-card"><h3>1. Basic Info / البيانات الأساسية</h3><div class="computech-arch-grid-3">
            <p><label>Brand / البراند</label><input type="text" name="_computech_brand" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_brand', '')); ?>" class="widefat"></p>
            <p><label>Model / الموديل</label><input type="text" name="_computech_model" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_model', '')); ?>" class="widefat"></p>
            <p><label>SKU / كود المنتج</label><input type="text" name="_computech_sku" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_sku', '')); ?>" class="widefat"></p>
            <p><label>Visibility / الظهور</label><select name="_computech_product_visibility" class="widefat"><option value="visible" <?php selected($visibility, 'visible'); ?>>ظاهر</option><option value="hidden" <?php selected($visibility, 'hidden'); ?>>مخفي</option></select></p>
            <p><label>Product Order / ترتيب المنتج</label><input type="number" name="_computech_product_order" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_product_order', (string) $post->menu_order)); ?>" class="widefat" min="0" step="1"></p>
        </div></section>

        <section class="arch-card"><h3>2. Images / الصور</h3>
            <p>الصورة الأساسية من صندوق Featured Image. بيانات Alt وTitle من Media Library.</p>
            <p><label>Product Gallery / معرض صور المنتج</label><input type="text" name="_computech_gallery_ids" id="computech_gallery_ids" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_gallery_ids', '')); ?>" class="widefat" placeholder="IDs مثل: 12,15,20"></p>
            <button type="button" class="button computech-arch-gallery-button" data-target="#computech_gallery_ids">اختيار صور من Media Library</button>
        </section>

        <section class="arch-card"><h3>3. Categories / الأقسام</h3><div class="computech-arch-grid">
            <p><label>Primary Category / القسم الأساسي</label><select name="_computech_primary_category" class="widefat"><?php echo computech_arch_product_term_options($primary_id); ?></select><span class="description">لازم يكون من الأقسام المختارة في صندوق Product Categories.</span></p>
        </div></section>

        <section class="arch-card"><h3>4. Pricing / الأسعار</h3><div class="computech-arch-grid-3">
            <p><label>Regular Price / السعر الأساسي</label><input type="text" name="_computech_regular_price" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_regular_price', computech_arch_post_meta($post_id, '_computech_old_price', ''))); ?>" class="widefat"></p>
            <p><label>Sale Price / سعر الخصم</label><input type="text" name="_computech_sale_price" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_sale_price', computech_arch_post_meta($post_id, '_computech_price', ''))); ?>" class="widefat"></p>
            <p><label>Currency / العملة</label><input type="text" name="_computech_currency" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_currency', '')); ?>" class="widefat"></p>
            <p><label>Discount Label</label><input type="text" name="_computech_discount_label" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_discount_label', '')); ?>" class="widefat" placeholder="خصم 18%"></p>
            <p><label>Price Note / ملاحظة السعر</label><input type="text" name="_computech_price_note" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_price_note', '')); ?>" class="widefat" placeholder="السعر شامل الضمان"></p>
            <p class="arch-check"><label><input type="checkbox" name="_computech_show_price" value="1" <?php checked($show_price, '1'); ?>> إظهار السعر</label></p>
        </div></section>

        <section class="arch-card"><h3>5. Status / Condition</h3><div class="computech-arch-grid-3">
            <p><label>Condition / الحالة</label><select name="_computech_condition" class="widefat"><option value="" <?php selected($condition, ''); ?>>— بدون تحديد —</option><option value="new" <?php selected($condition, 'new'); ?>>جديد</option><option value="imported" <?php selected($condition, 'imported'); ?>>استيراد خارج</option><option value="used" <?php selected($condition, 'used'); ?>>مستعمل</option><option value="refurbished" <?php selected($condition, 'refurbished'); ?>>مجدد</option></select></p>
            <p><label>Availability / التوفر</label><select name="_computech_availability" class="widefat"><option value="" <?php selected($availability, ''); ?>>— بدون تحديد —</option><option value="in-stock" <?php selected($availability, 'in-stock'); ?>>متوفر</option><option value="out-of-stock" <?php selected($availability, 'out-of-stock'); ?>>غير متوفر</option><option value="coming-soon" <?php selected($availability, 'coming-soon'); ?>>قريبًا</option></select></p>
            <p><label>Badge Text</label><input type="text" name="_computech_badge_text" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_badge_text', '')); ?>" class="widefat" placeholder="جديد / استيراد خارج"></p>
            <p><label>Stock Quantity</label><input type="number" name="_computech_stock_quantity" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_stock_quantity', '')); ?>" class="widefat" min="0" step="1"></p>
        </div></section>

        <section class="arch-card"><h3>6. Card Display / شكل الكارت</h3><div class="computech-arch-grid">
            <p><label>Card Title Override</label><input type="text" name="_computech_card_title_override" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_card_title_override', '')); ?>" class="widefat"></p>
            <p><label>Card Subtitle</label><input type="text" name="_computech_card_subtitle" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_card_subtitle', '')); ?>" class="widefat"></p>
            <?php for ($i = 1; $i <= 4; $i++) : ?><p><label>Highlight <?php echo (int) $i; ?></label><input type="text" name="_computech_highlight_<?php echo (int) $i; ?>" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_highlight_' . $i, '')); ?>" class="widefat"></p><?php endfor; ?>
            <p style="grid-column:1/-1"><label>Card Note</label><input type="text" name="_computech_card_note" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_card_note', '')); ?>" class="widefat" placeholder="ضمان 12 شهر"></p>
        </div></section>

        <section class="arch-card"><h3>7. Featured Products Settings / منتجات مميزة</h3><div class="computech-arch-grid">
            <p class="arch-check"><label><input type="checkbox" name="_computech_show_featured_products" value="1" <?php checked($show_featured, '1'); ?>> Show in Featured Products / يظهر في سكشن منتجات مميزة</label></p>
            <p><label>Featured Products Order</label><input type="number" name="_computech_featured_order" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_featured_order', (string) $post->menu_order)); ?>" class="widefat" min="0" step="1"></p>
        </div></section>

        <section class="arch-card"><h3>8. Specifications / المواصفات</h3>
            <p><label>Flexible Specs - كل سطر: الاسم: القيمة</label><textarea name="_computech_full_specs" rows="7" class="widefat" style="direction:rtl"><?php echo esc_textarea(computech_arch_post_meta($post_id, '_computech_full_specs', '')); ?></textarea></p>
        </section>

        <section class="arch-card"><h3>9. Warranty & Support / الضمان والدعم</h3><div class="computech-arch-grid-3">
            <p><label>Warranty Type</label><select name="_computech_warranty_type" class="widefat"><option value="" <?php selected(computech_arch_post_meta($post_id, '_computech_warranty_type', ''), ''); ?>>— بدون تحديد —</option><option value="warranty" <?php selected(computech_arch_post_meta($post_id, '_computech_warranty_type', ''), 'warranty'); ?>>ضمان</option><option value="inspection" <?php selected(computech_arch_post_meta($post_id, '_computech_warranty_type', ''), 'inspection'); ?>>فحص</option><option value="none" <?php selected(computech_arch_post_meta($post_id, '_computech_warranty_type', ''), 'none'); ?>>بدون ضمان</option></select></p>
            <p><label>Warranty Duration</label><input type="text" name="_computech_warranty_duration" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_warranty_duration', '')); ?>" class="widefat" placeholder="12 شهر"></p>
            <p><label>Warranty Note</label><input type="text" name="_computech_warranty_note" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_warranty_note', computech_arch_post_meta($post_id, '_computech_warranty', ''))); ?>" class="widefat"></p>
            <p class="arch-check"><label><input type="checkbox" name="_computech_after_sale_support" value="1" <?php checked(computech_arch_post_meta($post_id, '_computech_after_sale_support', '1'), '1'); ?>> After Sale Support</label></p>
            <p class="arch-check"><label><input type="checkbox" name="_computech_maintenance_available" value="1" <?php checked(computech_arch_post_meta($post_id, '_computech_maintenance_available', '1'), '1'); ?>> Maintenance Available</label></p>
        </div></section>

        <section class="arch-card"><h3>10. Buttons / Actions</h3><div class="computech-arch-grid">
            <p class="arch-check"><label><input type="checkbox" name="_computech_show_details_button" value="1" <?php checked($show_details, '1'); ?>> إظهار زر التفاصيل</label></p>
            <p><label>Details Button Text</label><input type="text" name="_computech_details_button_text" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_details_button_text', '')); ?>" class="widefat"></p>
            <p class="arch-check"><label><input type="checkbox" name="_computech_show_whatsapp_button" value="1" <?php checked($show_whatsapp, '1'); ?>> إظهار زر واتساب</label></p>
            <p><label>WhatsApp Button Text</label><input type="text" name="_computech_whatsapp_button_text" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_whatsapp_button_text', '')); ?>" class="widefat"></p>
            <p><label>WhatsApp Number Override</label><input type="text" name="_computech_whatsapp_override" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_whatsapp_override', computech_arch_post_meta($post_id, '_computech_whatsapp', ''))); ?>" class="widefat"></p>
            <p style="grid-column:1/-1"><label>WhatsApp Message</label><input type="text" name="_computech_whatsapp_message" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_whatsapp_message', '')); ?>" class="widefat"></p>
            <p class="arch-check"><label><input type="checkbox" name="_computech_show_add_to_cart" value="1" <?php checked($show_cart, '1'); ?>> إظهار زر السلة</label></p>
            <p><label>Add to Cart Button Text</label><input type="text" name="_computech_add_to_cart_text" value="<?php echo esc_attr(computech_arch_post_meta($post_id, '_computech_add_to_cart_text', '')); ?>" class="widefat"></p>
        </div></section>
    </div>
    <?php
}

function computech_arch_replace_product_metabox(): void {
    remove_meta_box('computech_product_data', 'products', 'normal');
    add_meta_box('computech_product_architecture_data', 'بيانات المنتج — المعمارية الكاملة', 'computech_arch_product_data_metabox', 'products', 'normal', 'high');
}
add_action('add_meta_boxes', 'computech_arch_replace_product_metabox', 99);

function computech_arch_save_product_data(int $post_id): void {
    if (!isset($_POST['computech_arch_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_arch_product_nonce'])), 'computech_arch_save_product_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = array(
        '_computech_brand','_computech_model','_computech_sku','_computech_product_visibility','_computech_product_order','_computech_gallery_ids','_computech_regular_price','_computech_sale_price','_computech_currency','_computech_discount_label','_computech_price_note','_computech_condition','_computech_availability','_computech_badge_text','_computech_stock_quantity','_computech_card_title_override','_computech_card_subtitle','_computech_highlight_1','_computech_highlight_2','_computech_highlight_3','_computech_highlight_4','_computech_card_note','_computech_featured_order','_computech_full_specs','_computech_warranty_type','_computech_warranty_duration','_computech_warranty_note','_computech_details_button_text','_computech_whatsapp_button_text','_computech_add_to_cart_text','_computech_whatsapp_override','_computech_whatsapp_message'
    );
    foreach ($text_fields as $field) {
        $value = wp_unslash($_POST[$field] ?? '');
        if ($field === '_computech_full_specs') {
            update_post_meta($post_id, $field, sanitize_textarea_field($value));
        } elseif ($field === '_computech_gallery_ids') {
            $ids = array_filter(array_map('absint', explode(',', (string) $value)));
            update_post_meta($post_id, $field, implode(',', $ids));
        } elseif (in_array($field, array('_computech_product_order','_computech_featured_order','_computech_stock_quantity'), true)) {
            update_post_meta($post_id, $field, (string) absint($value));
        } else {
            update_post_meta($post_id, $field, sanitize_text_field($value));
        }
    }

    $bool_fields = array('_computech_show_price','_computech_show_featured_products','_computech_after_sale_support','_computech_maintenance_available','_computech_show_details_button','_computech_show_whatsapp_button','_computech_show_add_to_cart');
    foreach ($bool_fields as $field) {
        update_post_meta($post_id, $field, !empty($_POST[$field]) ? '1' : '0');
    }

    $primary = absint($_POST['_computech_primary_category'] ?? 0);
    update_post_meta($post_id, '_computech_primary_category', (string) $primary);

    $order = absint($_POST['_computech_product_order'] ?? 0);
    if ($order !== (int) get_post_field('menu_order', $post_id)) {
        remove_action('save_post_products', 'computech_arch_save_product_data', 25);
        wp_update_post(array('ID' => $post_id, 'menu_order' => $order));
        add_action('save_post_products', 'computech_arch_save_product_data', 25);
    }

    $highlights = array();
    for ($i = 1; $i <= 4; $i++) {
        $highlight = sanitize_text_field(wp_unslash($_POST['_computech_highlight_' . $i] ?? ''));
        if ($highlight !== '') {
            $highlights[] = $highlight;
        }
    }
    update_post_meta($post_id, '_computech_specs', implode("\n", $highlights));

    $sale = sanitize_text_field(wp_unslash($_POST['_computech_sale_price'] ?? ''));
    $regular = sanitize_text_field(wp_unslash($_POST['_computech_regular_price'] ?? ''));
    update_post_meta($post_id, '_computech_price', $sale !== '' ? $sale : $regular);
    update_post_meta($post_id, '_computech_old_price', ($sale !== '' && $regular !== '') ? $regular : '');
    update_post_meta($post_id, '_computech_status', sanitize_key(wp_unslash($_POST['_computech_condition'] ?? '')));

    $warranty_note = sanitize_text_field(wp_unslash($_POST['_computech_warranty_note'] ?? ''));
    $warranty_duration = sanitize_text_field(wp_unslash($_POST['_computech_warranty_duration'] ?? ''));
    update_post_meta($post_id, '_computech_warranty', trim($warranty_note . ($warranty_note && $warranty_duration ? ' - ' : '') . $warranty_duration));
    update_post_meta($post_id, '_computech_whatsapp', sanitize_text_field(wp_unslash($_POST['_computech_whatsapp_override'] ?? '')));
    update_post_meta($post_id, '_computech_featured_home', !empty($_POST['_computech_show_featured_products']) ? '1' : '0');
}
add_action('save_post_products', 'computech_arch_save_product_data', 25);

function computech_arch_validate_primary_category(int $post_id): void {
    if (wp_is_post_revision($post_id) || get_post_type($post_id) !== 'products') {
        return;
    }
    $terms = get_the_terms($post_id, 'product_category');
    if (is_wp_error($terms) || empty($terms)) {
        update_post_meta($post_id, '_computech_primary_category', '0');
        return;
    }
    $term_ids = array_map(static fn($term) => (int) $term->term_id, $terms);
    $primary = (int) get_post_meta($post_id, '_computech_primary_category', true);
    if (!in_array($primary, $term_ids, true)) {
        update_post_meta($post_id, '_computech_primary_category', (string) $term_ids[0]);
    }
}
add_action('save_post_products', 'computech_arch_validate_primary_category', 99);

/* ------------------------------------------------------------
 * Admin media buttons
 * ------------------------------------------------------------ */

function computech_arch_admin_assets(string $hook): void {
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }
    if (!in_array($screen->id, array('edit-product_category', 'term', 'products'), true) && $screen->post_type !== 'products') {
        return;
    }
    wp_enqueue_media();
    $script = <<<'JS'
jQuery(function($){
    $(document).on('click','.computech-arch-media-button',function(e){
        e.preventDefault();
        var button=$(this),target=$(button.data('target')),preview=$(button.data('preview'));
        var frame=wp.media({title:'اختيار صورة القسم',button:{text:'استخدام الصورة'},multiple:false});
        frame.on('select',function(){
            var att=frame.state().get('selection').first().toJSON();
            target.val(att.id);
            if(preview.length){
                var img=(att.sizes&&att.sizes.thumbnail)?att.sizes.thumbnail.url:att.url;
                preview.html('<img src="'+img+'" style="max-width:120px;height:auto;border-radius:8px">');
            }
        });
        frame.open();
    });
    $(document).on('click','.computech-arch-media-clear',function(e){
        e.preventDefault();
        $($(this).data('target')).val('');
        $($(this).data('preview')).empty();
    });
    $(document).on('click','.computech-arch-gallery-button',function(e){
        e.preventDefault();
        var target=$($(this).data('target'));
        var frame=wp.media({title:'اختيار صور المنتج',button:{text:'استخدام الصور'},multiple:true});
        frame.on('select',function(){
            var ids=[];
            frame.state().get('selection').each(function(att){ids.push(att.toJSON().id);});
            target.val(ids.join(','));
        });
        frame.open();
    });
});
JS;
    wp_add_inline_script('jquery-core', $script);
}
add_action('admin_enqueue_scripts', 'computech_arch_admin_assets');


function computech_arch_repair_primary_category_after_delete(int $term_id): void {
    $q = new WP_Query(array(
        'post_type' => 'products',
        'post_status' => array('publish', 'draft', 'pending', 'private'),
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => array(array('key' => '_computech_primary_category', 'value' => (string) $term_id, 'compare' => '=')),
        'no_found_rows' => true,
    ));
    foreach ($q->posts as $post_id) {
        $terms = get_the_terms((int) $post_id, 'product_category');
        if (!is_wp_error($terms) && !empty($terms)) {
            update_post_meta((int) $post_id, '_computech_primary_category', (string) $terms[0]->term_id);
        } else {
            update_post_meta((int) $post_id, '_computech_primary_category', '0');
        }
    }
    wp_reset_postdata();
}
add_action('delete_product_category', 'computech_arch_repair_primary_category_after_delete');


/* ------------------------------------------------------------
 * Frontend: category items and cards
 * ------------------------------------------------------------ */

function computech_arch_get_terms_for_home(string $show_key, string $order_key, int $limit = 0): array {
    $terms = get_terms(array(
        'taxonomy' => 'product_category',
        'hide_empty' => false,
    ));
    if (is_wp_error($terms) || empty($terms)) {
        return array();
    }
    $terms = array_values(array_filter($terms, static function(WP_Term $term) use ($show_key): bool {
        return computech_arch_is_category_visible((int) $term->term_id)
            && computech_arch_term_meta((int) $term->term_id, $show_key, '0') === '1';
    }));
    usort($terms, function(WP_Term $a, WP_Term $b) use ($order_key): int {
        $ao = (int) computech_arch_term_meta($a->term_id, $order_key, '0');
        $bo = (int) computech_arch_term_meta($b->term_id, $order_key, '0');
        if ($ao === $bo) {
            return strnatcasecmp($a->name, $b->name);
        }
        return $ao <=> $bo;
    });
    if ($limit > 0) {
        $terms = array_slice($terms, 0, $limit);
    }
    return $terms;
}

function computech_arch_product_count_for_term_tree(int $term_id): int {
    $q = new WP_Query(array(
        'post_type' => 'products',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'tax_query' => array(array('taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => array($term_id), 'include_children' => true)),
        'meta_query' => computech_arch_visibility_meta_query(),
    ));
    $count = (int) $q->found_posts;
    wp_reset_postdata();
    return $count;
}

function computech_arch_term_to_card_item(WP_Term $term, string $mode = 'shop'): array {
    $is_featured = $mode === 'featured';
    $badge_key = $is_featured ? '_computech_featured_cat_badge' : '_computech_shop_badge';
    $button_key = $is_featured ? '_computech_featured_cat_button' : '_computech_shop_button';
    $badge = computech_arch_term_meta($term->term_id, $badge_key, '');
    if ($badge === '') {
        $count = computech_arch_product_count_for_term_tree((int) $term->term_id);
        $badge = $count > 0 ? '+' . $count . ' منتج' : '';
    }
    $image = computech_arch_term_image_data((int) $term->term_id);
    $description = trim((string) $term->description);
    if ($description === '') {
        $description = wp_trim_words(computech_arch_term_meta($term->term_id, '_computech_cat_full_description', ''), 20, '...');
    }
    return array(
        'title' => $term->name,
        'text' => $description,
        'pill' => $badge,
        'link_text' => computech_arch_term_meta($term->term_id, $button_key, ''),
        'url' => get_term_link($term),
        'target' => '',
        'image' => $image['url'],
        'alt' => $image['alt'] ?: $term->name,
        'icon' => computech_arch_term_meta($term->term_id, '_computech_cat_icon', 'desktop'),
        'term' => $term,
    );
}

function computech_get_shop_section_category_items(): array {
    $terms = computech_arch_get_terms_for_home('_computech_shop_show', '_computech_shop_order');
    return array_map(static fn(WP_Term $term): array => computech_arch_term_to_card_item($term, 'shop'), $terms);
}

function computech_get_featured_category_items(int $limit = 3): array {
    $terms = computech_arch_get_terms_for_home('_computech_featured_cat_show', '_computech_featured_cat_order', $limit);
    return array_map(static fn(WP_Term $term): array => computech_arch_term_to_card_item($term, 'featured'), $terms);
}

function computech_arch_get_all_visible_category_items(): array {
    $terms = get_terms(array('taxonomy' => 'product_category', 'hide_empty' => false));
    if (is_wp_error($terms) || empty($terms)) {
        return array();
    }
    $terms = array_values(array_filter($terms, static fn(WP_Term $term): bool => computech_arch_is_category_visible((int) $term->term_id)));
    usort($terms, function(WP_Term $a, WP_Term $b): int {
        if ((int) $a->parent === (int) $b->parent) {
            $ao = (int) computech_arch_term_meta($a->term_id, '_computech_term_order', isset($a->term_order) ? (string) $a->term_order : '0');
            $bo = (int) computech_arch_term_meta($b->term_id, '_computech_term_order', isset($b->term_order) ? (string) $b->term_order : '0');
            return $ao === $bo ? strnatcasecmp($a->name, $b->name) : $ao <=> $bo;
        }
        return (int) $a->parent <=> (int) $b->parent;
    });
    return array_map(static fn(WP_Term $term): array => computech_arch_term_to_card_item($term, 'all'), $terms);
}

function computech_arch_render_category_icon(string $icon): void {
    if (function_exists('computech_section_icon_svg')) {
        echo computech_section_icon_svg($icon);
        return;
    }
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8"/></svg>';
}

function computech_arch_render_featured_category_card(array $item): void {
    ?>
    <div class="cat-feat-card">
        <?php if ($item['image'] !== '') : ?><div class="cat-feat-card-image"><div class="cat-feat-card-glow"></div><img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['alt']); ?>"></div><?php endif; ?>
        <div class="cat-feat-card-content"><div class="cat-feat-card-icon-wrap"><?php computech_arch_render_category_icon((string) $item['icon']); ?></div><h3 class="cat-feat-card-title"><?php echo esc_html($item['title']); ?></h3><?php if ($item['text'] !== '') : ?><p class="cat-feat-card-desc"><?php echo esc_html($item['text']); ?></p><?php endif; ?><?php if ($item['pill'] !== '') : ?><span class="cat-feat-card-count"><?php echo esc_html($item['pill']); ?></span><?php endif; ?><?php if ($item['link_text'] !== '') : ?><a href="<?php echo esc_url($item['url']); ?>" class="cat-feat-card-btn"><?php echo esc_html($item['link_text']); ?><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 3 11 8 6 13"/></svg></a><?php endif; ?></div>
    </div>
    <?php
}

function computech_arch_render_category_grid_card(array $item): void {
    ?>
    <div class="cat-card">
        <?php if ($item['image'] !== '') : ?><div class="cat-card-image"><img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['alt']); ?>"></div><?php endif; ?>
        <div class="cat-card-body"><div class="cat-card-icon-wrap"><?php computech_arch_render_category_icon((string) $item['icon']); ?></div><h3 class="cat-card-title"><?php echo esc_html($item['title']); ?></h3><?php if ($item['text'] !== '') : ?><p class="cat-card-desc"><?php echo esc_html($item['text']); ?></p><?php endif; ?><div class="cat-card-footer"><?php if ($item['pill'] !== '') : ?><span class="cat-card-count"><?php echo esc_html($item['pill']); ?></span><?php endif; ?><?php if ($item['link_text'] !== '') : ?><a href="<?php echo esc_url($item['url']); ?>" class="cat-card-btn"><?php echo esc_html($item['link_text']); ?><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 3 11 8 6 13"/></svg></a><?php endif; ?></div></div>
    </div>
    <?php
}

function computech_arch_render_categories_page(): void {
    $featured = computech_get_featured_category_items(3);
    $all = computech_arch_get_all_visible_category_items();
    ?>
    <section class="cat-hero"><div class="cat-hero-bg"><div class="cat-hero-circuit cat-hero-circuit-1"></div><div class="cat-hero-circuit cat-hero-circuit-2"></div><div class="cat-hero-circuit cat-hero-circuit-3"></div><div class="cat-hero-dot cat-hero-dot-1"></div><div class="cat-hero-dot cat-hero-dot-2"></div><div class="cat-hero-dot cat-hero-dot-3"></div><div class="cat-hero-dot cat-hero-dot-4"></div><div class="cat-hero-glow cat-hero-glow-1"></div><div class="cat-hero-glow cat-hero-glow-2"></div></div><div class="cat-container cat-hero-inner"><div class="cat-hero-decorative-dots"><span class="h-dot blue"></span><span class="h-dot cyan"></span><span class="h-dot green"></span></div><h1 class="cat-hero-title">أقسام المتجر</h1><p class="cat-hero-subtitle"><?php echo esc_html(sprintf('استكشف شجرة أقسام %s بأي عدد من المستويات، والمنتجات تظهر تلقائيًا داخل القسم وكل الأقسام الأب.', computech_site_name())); ?></p><div class="cat-hero-pills"><span class="cat-hero-pill">أقسام غير محدودة</span><span class="cat-hero-pill">ربط ذكي بالمنتجات</span><span class="cat-hero-pill">تحكم كامل من الداشبورد</span></div></div></section>

    <?php if (!empty($featured)) : ?>
    <section class="cat-featured"><div class="cat-featured-bg"><div class="cat-feat-glow cat-feat-glow-tr"></div><div class="cat-feat-glow cat-feat-glow-bl"></div><div class="cat-feat-dots cat-feat-dots-tr"></div><div class="cat-feat-dots cat-feat-dots-bl"></div></div><div class="cat-container"><div class="cat-section-header"><div class="cat-section-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div><h2 class="cat-section-title">الأقسام <span class="cat-section-highlight">المميزة</span></h2><p class="cat-section-subtitle">الأقسام التي تم تفعيل Show in Featured Categories لها من الداشبورد</p></div><div class="cat-featured-grid"><?php foreach ($featured as $item) { computech_arch_render_featured_category_card($item); } ?></div></div></section>
    <?php endif; ?>

    <section class="cat-all"><div class="cat-all-bg"><div class="cat-all-circuit cat-all-circuit-tr"></div><div class="cat-all-circuit cat-all-circuit-bl"></div><div class="cat-all-dots cat-all-dots-tr"></div><div class="cat-all-dots cat-all-dots-bl"></div><div class="cat-all-glow cat-all-glow-tr"></div><div class="cat-all-glow cat-all-glow-bl"></div></div><div class="cat-container"><div class="cat-section-header"><div class="cat-section-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div><h2 class="cat-section-title">جميع <span class="cat-section-highlight">الأقسام</span></h2><p class="cat-section-subtitle">كل قسم ظاهر في taxonomy أقسام المنتجات</p></div><div class="cat-grid"><?php if ($all) { foreach ($all as $item) { computech_arch_render_category_grid_card($item); } } else { echo '<div class="wp-product-empty"><h2>لا توجد أقسام بعد</h2><p>أضف أقسام المنتجات من لوحة التحكم.</p></div>'; } ?></div></div></section>
    <?php
}

/* ------------------------------------------------------------
 * Frontend: query rules and related products
 * ------------------------------------------------------------ */

function computech_arch_filter_product_queries(WP_Query $query): void {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->is_tax('product_category')) {
        $term = $query->get_queried_object();
        if ($term instanceof WP_Term) {
            $query->set('post_type', 'products');
            $query->set('tax_query', array(array('taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => array((int) $term->term_id), 'include_children' => true)));
            $query->set('meta_query', computech_arch_visibility_meta_query());
        }
    }
    if ($query->is_search()) {
        $meta = $query->get('meta_query');
        $meta = is_array($meta) ? $meta : array();
        $meta[] = computech_arch_visibility_meta_query();
        $query->set('meta_query', $meta);
    }
}
add_action('pre_get_posts', 'computech_arch_filter_product_queries');

function computech_arch_404_hidden_items(): void {
    if (is_singular('products') && !computech_arch_is_product_visible(get_queried_object_id())) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
    if (is_tax('product_category')) {
        $term = get_queried_object();
        if ($term instanceof WP_Term && !computech_arch_is_category_visible((int) $term->term_id)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }
}
add_action('template_redirect', 'computech_arch_404_hidden_items');

function computech_arch_related_products(int $post_id, int $limit = 4): array {
    $exclude = array($post_id);
    $items = array();
    $primary = computech_arch_product_primary_category($post_id);
    $term_ids = array();
    if ($primary) {
        $term_ids[] = (int) $primary->term_id;
        $term_ids = array_merge($term_ids, array_map('intval', get_ancestors($primary->term_id, 'product_category', 'taxonomy')));
    }
    $terms = get_the_terms($post_id, 'product_category');
    if (!is_wp_error($terms) && $terms) {
        foreach ($terms as $term) {
            $term_ids[] = (int) $term->term_id;
        }
    }
    $term_ids = array_values(array_unique(array_filter($term_ids)));
    if (!$term_ids) {
        return array();
    }

    $q = new WP_Query(array(
        'post_type' => 'products',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'post__not_in' => $exclude,
        'tax_query' => array(array('taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => $term_ids, 'include_children' => true)),
        'meta_query' => computech_arch_visibility_meta_query(),
        'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
        'no_found_rows' => true,
    ));
    $items = $q->posts;
    wp_reset_postdata();
    return is_array($items) ? $items : array();
}

/* ------------------------------------------------------------
 * Compatibility: default terms seed
 * ------------------------------------------------------------ */

function computech_arch_seed_default_category_meta(): void {
    // Disabled intentionally: product categories and their home display rules must come from the dashboard only.
}


