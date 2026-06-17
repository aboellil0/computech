<?php
/**
 * Computech WooCommerce integration.
 * Products, categories, search, filters, cards read from WooCommerce only.
 */

if (!defined('ABSPATH')) {
    exit;
}

function computech_wc_active(): bool {
    return class_exists('WooCommerce') && function_exists('wc_get_product');
}

function computech_wc_products_page_url(): string {
    $page_url = computech_page_url('products');
    if ($page_url !== '') {
        return $page_url;
    }
    if (computech_wc_active() && function_exists('wc_get_page_permalink')) {
        $shop = wc_get_page_permalink('shop');
        if ($shop) {
            return $shop;
        }
    }
    return home_url('/products/');
}

function computech_wc_category_url(WP_Term $term): string {
    $url = get_term_link($term, 'product_cat');
    return is_wp_error($url) ? computech_wc_products_page_url() : (string) $url;
}

function computech_wc_product_search_query_var(): string {
    return 'product_search';
}

function computech_wc_get_request(string $key, string $default = ''): string {
    if (!isset($_GET[$key])) {
        return $default;
    }
    return sanitize_text_field(wp_unslash($_GET[$key]));
}

function computech_wc_clean_price_request(string $key): string {
    if (!isset($_GET[$key])) {
        return '';
    }
    return preg_replace('/[^0-9.]/', '', (string) wp_unslash($_GET[$key]));
}

function computech_wc_term_meta(int $term_id, string $key, string $default = ''): string {
    $value = get_term_meta($term_id, $key, true);
    return $value === '' ? $default : (string) $value;
}

function computech_wc_bool_term_meta(int $term_id, string $key, bool $default = false): bool {
    $value = get_term_meta($term_id, $key, true);
    if ($value === '') {
        return $default;
    }
    return (string) $value === '1';
}

function computech_wc_term_image(int $term_id, string $size = 'large'): array {
    $thumb_id = absint(get_term_meta($term_id, 'thumbnail_id', true));
    if (!$thumb_id) {
        return array('url' => '', 'alt' => '');
    }
    $url = wp_get_attachment_image_url($thumb_id, $size);
    $alt = (string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
    return array('url' => $url ?: '', 'alt' => $alt);
}

function computech_wc_term_icon(int $term_id, string $size = 'thumbnail'): array {
    // Category has one real image only: native WooCommerce thumbnail.
    // Icons are rendered as theme SVG, not uploaded category images.
    return array('url' => '', 'alt' => '');
}

function computech_wc_category_product_count(int $term_id): int {
    $term_ids = array((int) $term_id);
    $children = get_term_children($term_id, 'product_cat');
    if (!is_wp_error($children) && is_array($children)) {
        $term_ids = array_merge($term_ids, array_map('intval', $children));
    }

    $q = new WP_Query(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => 1,
        'no_found_rows' => false,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => $term_ids,
            'include_children' => false,
        )),
    ));
    $count = (int) $q->found_posts;
    wp_reset_postdata();
    return $count;
}

function computech_wc_category_count_badge(int $count): string {
    if ($count > 250) {
        return '+250';
    }
    if ($count > 50) {
        return '+50';
    }
    return $count > 0 ? '+' . $count : '';
}

function computech_wc_term_card_item(WP_Term $term, string $context = 'shop'): array {
    $image = computech_wc_term_image((int) $term->term_id, 'large');
    $icon = computech_wc_term_icon((int) $term->term_id, 'thumbnail');
    $count = computech_wc_category_product_count((int) $term->term_id);
    $badge = computech_wc_category_count_badge($count);
    $button = 'استكشف القسم ←';
    return array(
        'term_id' => (int) $term->term_id,
        'title' => $term->name,
        'text' => wp_strip_all_tags(term_description($term, 'product_cat')),
        'url' => computech_wc_category_url($term),
        'image' => $image['url'],
        'alt' => $image['alt'] !== '' ? $image['alt'] : $term->name,
        'icon_url' => $icon['url'],
        'icon_alt' => $icon['alt'] !== '' ? $icon['alt'] : $term->name,
        'pill' => $badge,
        'link_text' => $button,
    );
}

function computech_wc_term_has_image(WP_Term $term): bool {
    $image = computech_wc_term_image((int) $term->term_id, 'thumbnail');
    return $image['url'] !== '';
}

function computech_wc_term_is_featured(WP_Term $term): bool {
    $term_id = (int) $term->term_id;
    $new_value = get_term_meta($term_id, '_computech_wc_is_featured', true);
    if ($new_value !== '') {
        return (string) $new_value === '1';
    }
    return computech_wc_bool_term_meta($term_id, '_computech_wc_show_featured_categories', false);
}

function computech_wc_get_category_items(string $section = 'shop', int $limit = 0): array {
    if (!taxonomy_exists('product_cat')) {
        return array();
    }

    $terms = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
    ));
    if (is_wp_error($terms) || !is_array($terms)) {
        return array();
    }

    $items = array();
    foreach ($terms as $term) {
        if (!$term instanceof WP_Term) {
            continue;
        }
        $visibility = computech_wc_term_meta((int) $term->term_id, '_computech_wc_category_visibility', 'visible');
        if ($visibility === 'hidden') {
            continue;
        }
        if (!computech_wc_term_has_image($term)) {
            continue;
        }

        if ($section === 'featured') {
            if (!computech_wc_term_is_featured($term)) {
                continue;
            }
            $order = (int) computech_wc_term_meta((int) $term->term_id, '_computech_wc_featured_order', (string) $term->term_order);
            $items[] = array(
                'order' => $order,
                'name' => $term->name,
                'item' => computech_wc_term_card_item($term, $section),
            );
            continue;
        }

        // Shop section is random, not manually selected.
        $items[] = array(
            'order' => 0,
            'name' => $term->name,
            'item' => computech_wc_term_card_item($term, $section),
        );
    }

    if ($section === 'featured') {
        usort($items, static function(array $a, array $b): int {
            return $a['order'] === $b['order'] ? strnatcasecmp($a['name'], $b['name']) : $a['order'] <=> $b['order'];
        });
    } else {
        shuffle($items);
    }

    $items = array_map(static fn(array $row): array => $row['item'], $items);
    return $limit > 0 ? array_slice($items, 0, $limit) : $items;
}

function computech_wc_render_category_icon(array $item, string $class = 'cat-card-icon-img'): void {
    if (!empty($item['icon_url'])) {
        echo '<img class="' . esc_attr($class) . '" src="' . esc_url($item['icon_url']) . '" alt="' . esc_attr((string) ($item['icon_alt'] ?? '')) . '" loading="lazy">';
        return;
    }
    if (function_exists('computech_section_icon_svg')) {
        echo computech_section_icon_svg('desktop');
        return;
    }
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8"/></svg>';
}

function computech_wc_render_shop_category_card(array $item, string $card_size_class): void {
    ?>
    <a href="<?php echo esc_url((string) $item['url']); ?>" class="shop-card <?php echo esc_attr($card_size_class); ?>">
        <div class="shop-card-content">
            <div class="shop-card-icon"><?php computech_wc_render_category_icon($item, 'shop-card-icon-img'); ?></div>
            <?php if (!empty($item['pill'])) : ?><span class="shop-card-badge"><?php echo esc_html((string) $item['pill']); ?></span><?php endif; ?>
            <h3 class="shop-card-title"><?php echo esc_html((string) $item['title']); ?></h3>
            <?php if (!empty($item['text'])) : ?><p class="shop-card-desc"><?php echo esc_html(wp_trim_words((string) $item['text'], 14, '...')); ?></p><?php endif; ?>
            <?php if (!empty($item['link_text'])) : ?><span class="shop-card-link"><?php echo esc_html((string) $item['link_text']); ?></span><?php endif; ?>
        </div>
        <?php if (!empty($item['image'])) : ?><div class="shop-card-image"><img src="<?php echo esc_url((string) $item['image']); ?>" alt="<?php echo esc_attr((string) $item['alt']); ?>" loading="lazy"></div><?php endif; ?>
    </a>
    <?php
}

function computech_wc_render_shop_categories_section(): void {
    if (!computech_wc_active()) {
        return;
    }
    if (function_exists('computech_home_section_option') && computech_home_section_option('shop_show', '0') !== '1') {
        return;
    }
    $items = computech_wc_get_category_items('shop', 5);
    if (!$items) {
        return;
    }
    $title = function_exists('computech_home_section_option') ? computech_home_section_option('shop_title', '') : '';
    $subtitle = function_exists('computech_home_section_option') ? computech_home_section_option('shop_subtitle', '') : '';
    $top_items = array_slice($items, 0, 3);
    $bottom_items = array_slice($items, 3, 2);
    ?>
    <section class="shop-section computech-wc-shop-categories">
        <div class="shop-bg-pattern">
            <div class="shop-circuit-pattern shop-circuit-top-right"></div>
            <div class="shop-circuit-pattern shop-circuit-bottom-left"></div>
            <div class="shop-dot-cluster shop-dots-tr"></div>
            <div class="shop-dot-cluster shop-dots-bl"></div>
            <div class="shop-glow-orb shop-glow-tr"></div>
            <div class="shop-glow-orb shop-glow-bl"></div>
            <div class="shop-glow-orb shop-glow-center"></div>
        </div>
        <div class="shop-container">
            <div class="shop-header">
                <div class="shop-decorative-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div>
                <?php if ($title !== '') : ?><h2 class="shop-title"><?php echo esc_html($title); ?></h2><?php endif; ?>
                <?php if ($subtitle !== '') : ?><p class="shop-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
            </div>
            <div class="shop-cards-layout">
                <?php if ($top_items) : ?>
                    <div class="shop-grid shop-grid-top">
                        <?php foreach ($top_items as $item) { computech_wc_render_shop_category_card($item, 'shop-card-lg'); } ?>
                    </div>
                <?php endif; ?>
                <?php if ($bottom_items) : ?>
                    <div class="shop-grid shop-grid-bottom">
                        <?php foreach ($bottom_items as $item) { computech_wc_render_shop_category_card($item, 'shop-card-xl'); } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

function computech_wc_product_primary_category_id(int $product_id): int {
    $primary = absint(get_post_meta($product_id, '_computech_wc_primary_category', true));
    if ($primary && has_term($primary, 'product_cat', $product_id)) {
        return $primary;
    }
    $terms = get_the_terms($product_id, 'product_cat');
    if (!is_wp_error($terms) && !empty($terms)) {
        usort($terms, static fn(WP_Term $a, WP_Term $b): int => (int) $b->parent <=> (int) $a->parent);
        return (int) $terms[0]->term_id;
    }
    return 0;
}

function computech_wc_product_whatsapp_url(WC_Product $product): string {
    $number = computech_clean_phone((string) get_post_meta($product->get_id(), '_computech_wc_whatsapp_number', true));
    if ($number === '') {
        $number = computech_business_whatsapp_number();
    }
    if ($number === '') {
        return '';
    }
    $message = trim((string) get_post_meta($product->get_id(), '_computech_wc_whatsapp_message', true));
    if ($message === '') {
        $message = sprintf('أريد الاستفسار عن المنتج: %s', $product->get_name());
    }
    return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
}

function computech_wc_product_condition_value(WC_Product $product): string {
    $value = sanitize_key((string) get_post_meta($product->get_id(), '_computech_wc_condition', true));
    if (in_array($value, array('new', 'imported'), true)) {
        return $value;
    }
    $attribute = trim((string) $product->get_attribute('pa_condition'));
    if ($attribute === '') {
        $attribute = trim((string) $product->get_attribute('condition'));
    }
    if (stripos($attribute, 'import') !== false || strpos($attribute, 'استيراد') !== false) {
        return 'imported';
    }
    return 'new';
}

function computech_wc_product_condition_label(WC_Product $product): string {
    if (!$product->is_in_stock()) {
        return 'غير متوفر';
    }
    $condition = computech_wc_product_condition_value($product);
    return $condition === 'imported' ? 'استيراد' : 'جديد';
}

function computech_wc_product_filter_category_slugs(int $product_id): string {
    $terms = get_the_terms($product_id, 'product_cat');
    if (is_wp_error($terms) || !$terms) {
        return '';
    }
    $slugs = array();
    foreach ($terms as $term) {
        $slugs[] = $term->slug;
        $ancestors = get_ancestors((int) $term->term_id, 'product_cat', 'taxonomy');
        foreach ($ancestors as $ancestor_id) {
            $ancestor = get_term((int) $ancestor_id, 'product_cat');
            if ($ancestor instanceof WP_Term && !is_wp_error($ancestor)) {
                $slugs[] = $ancestor->slug;
            }
        }
    }
    return implode(' ', array_unique(array_map('sanitize_html_class', $slugs)));
}

function computech_wc_product_price_number(WC_Product $product): int {
    $price = $product->get_price();
    return (int) preg_replace('/[^0-9]/', '', (string) $price);
}

function computech_wc_product_specs(WC_Product $product, int $limit = 0): array {
    $product_id = $product->get_id();
    $specs = array();

    $stored_specs = get_post_meta($product_id, '_computech_wc_specs', true);
    if (is_array($stored_specs)) {
        foreach ($stored_specs as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($label !== '' && $value !== '') {
                $specs[] = array('label' => $label, 'value' => $value);
            }
            if ($limit > 0 && count($specs) >= $limit) {
                return $specs;
            }
        }
    }

    if (empty($specs)) {
        for ($i = 1; $i <= 200; $i++) {
            $label = trim((string) get_post_meta($product_id, '_computech_wc_spec_label_' . $i, true));
            $value = trim((string) get_post_meta($product_id, '_computech_wc_spec_value_' . $i, true));
            if ($label !== '' && $value !== '') {
                $specs[] = array('label' => $label, 'value' => $value);
            }
            if ($limit > 0 && count($specs) >= $limit) {
                return $specs;
            }
        }
    }

    foreach ($product->get_attributes() as $attribute) {
        if (!$attribute instanceof WC_Product_Attribute || !$attribute->get_visible()) {
            continue;
        }
        $name = wc_attribute_label($attribute->get_name());
        $values = array();
        if ($attribute->is_taxonomy()) {
            $terms = wc_get_product_terms($product_id, $attribute->get_name(), array('fields' => 'names'));
            if (!is_wp_error($terms)) {
                $values = $terms;
            }
        } else {
            $values = $attribute->get_options();
        }
        $value = trim(implode(', ', array_map('wp_strip_all_tags', $values)));
        if ($name !== '' && $value !== '') {
            $specs[] = array('label' => $name, 'value' => $value);
        }
        if ($limit > 0 && count($specs) >= $limit) {
            break;
        }
    }
    return $specs;
}

function computech_wc_product_highlights(WC_Product $product, int $limit = 4): array {
    $specs = computech_wc_product_specs($product, $limit);
    $out = array();
    foreach ($specs as $spec) {
        $value = trim((string) ($spec['value'] ?? ''));
        if ($value !== '') {
            $out[] = $value;
        }
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}


function computech_wc_card_price_html(WC_Product $product): string {
    if (!$product instanceof WC_Product || $product->get_price() === '') {
        return '';
    }

    if ($product->is_type('simple') || $product->is_type('external')) {
        $current_price = $product->is_on_sale() && $product->get_sale_price() !== '' ? $product->get_sale_price() : $product->get_price();
        $regular_price = $product->get_regular_price();
        $current = wc_price(wc_get_price_to_display($product, array('price' => (float) $current_price)));
        $old = '';
        if ($product->is_on_sale() && $regular_price !== '' && (float) $regular_price > (float) $current_price) {
            $old = wc_price(wc_get_price_to_display($product, array('price' => (float) $regular_price)));
        }
        $html = '<span class="prod-price-current">' . $current . '</span>';
        if ($old !== '') {
            $html .= '<span class="prod-price-old">' . $old . '</span>';
        }
        return $html;
    }

    $price_html = $product->get_price_html();
    return $price_html !== '' ? '<span class="prod-price-current prod-price-range">' . $price_html . '</span>' : '';
}

function computech_wc_product_card($product_or_post = null): void {
    if (!computech_wc_active()) {
        return;
    }

    if ($product_or_post instanceof WC_Product) {
        $product = $product_or_post;
    } elseif ($product_or_post instanceof WP_Post) {
        $product = wc_get_product($product_or_post->ID);
    } else {
        $product = wc_get_product(get_the_ID());
    }
    if (!$product instanceof WC_Product || $product->get_status() !== 'publish') {
        return;
    }

    $product_id = $product->get_id();
    $title = $product->get_name();
    $subtitle = wp_strip_all_tags($product->get_short_description());
    if ($subtitle === '') {
        $subtitle = wp_trim_words(wp_strip_all_tags($product->get_description()), 10, '...');
    }
    $image_id = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src('large');
    $image_alt = $image_id ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
    $badge = computech_wc_product_condition_label($product);
    $condition = function_exists('computech_wc_product_condition_value') ? computech_wc_product_condition_value($product) : 'new';
    $highlights = computech_wc_product_highlights($product, 3);
    $permalink = get_permalink($product_id);
    ?>
    <div class="prod-card prod-card-modern" data-category="<?php echo esc_attr(computech_wc_product_filter_category_slugs($product_id)); ?>" data-status="<?php echo esc_attr(trim($condition . ' ' . $product->get_stock_status())); ?>" data-price="<?php echo esc_attr((string) computech_wc_product_price_number($product)); ?>" data-name="<?php echo esc_attr($title); ?>">
        <div class="prod-card-topline">
            <span class="prod-fav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6c-1.7-1.7-4.4-1.7-6.1 0L12 7.3 9.3 4.6c-1.7-1.7-4.4-1.7-6.1 0s-1.7 4.4 0 6.1L12 19.5l8.8-8.8c1.7-1.7 1.7-4.4 0-6.1z"/></svg></span>
            <?php if ($badge !== '') : ?><span class="prod-badge <?php echo esc_attr($condition === 'imported' ? 'prod-badge-imported' : 'prod-badge-new'); ?>"><?php echo esc_html($badge); ?></span><?php endif; ?>
        </div>
        <a href="<?php echo esc_url($permalink); ?>" class="prod-card-image" aria-label="<?php echo esc_attr($title); ?>"><img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt !== '' ? $image_alt : $title); ?>" loading="lazy"></a>
        <div class="prod-card-body">
            <h3 class="prod-card-title"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3>
            <?php if ($subtitle !== '') : ?><p class="prod-card-desc"><?php echo esc_html(wp_trim_words($subtitle, 12, '...')); ?></p><?php endif; ?>
            <?php if ($highlights) : ?><div class="prod-card-specs"><?php foreach ($highlights as $highlight) : ?><span class="prod-spec"><?php echo esc_html($highlight); ?></span><?php endforeach; ?></div><?php endif; ?>
            <?php $card_price_html = computech_wc_card_price_html($product); ?><?php if ($card_price_html !== '') : ?><div class="prod-card-price"><?php echo wp_kses_post($card_price_html); ?></div><?php endif; ?>
            <div class="prod-card-actions">
                <a href="<?php echo esc_url($permalink); ?>" class="prod-card-btn prod-btn-details">عرض التفاصيل <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></a>
                <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?><a href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-quantity="1" data-product_id="<?php echo esc_attr((string) $product_id); ?>" class="prod-card-btn prod-btn-cart add_to_cart_button ajax_add_to_cart" aria-label="إضافة للسلة"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L23 6H6"/></svg><span>إضافة للسلة</span></a><?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

function computech_wc_get_featured_products(int $limit = 12): array {
    if (!computech_wc_active()) {
        return array();
    }
    $products = wc_get_products(array(
        'status' => 'publish',
        'limit' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'objects',
        'meta_key' => '_computech_wc_is_featured',
        'meta_value' => '1',
    ));
    usort($products, static function(WC_Product $a, WC_Product $b): int {
        $ao = (int) get_post_meta($a->get_id(), '_computech_wc_featured_order', true);
        $bo = (int) get_post_meta($b->get_id(), '_computech_wc_featured_order', true);
        if ($ao === $bo) {
            return $b->get_date_created() <=> $a->get_date_created();
        }
        return $ao <=> $bo;
    });
    return $products;
}

function computech_wc_render_featured_products_section(): void {
    if (!computech_wc_active()) {
        return;
    }
    if (function_exists('computech_home_section_option') && computech_home_section_option('featured_show', '0') !== '1') {
        return;
    }
    $products = computech_wc_get_featured_products(12);
    if (!$products) {
        return;
    }
    $title = function_exists('computech_home_section_option') ? computech_home_section_option('featured_title', '') : '';
    $subtitle = function_exists('computech_home_section_option') ? computech_home_section_option('featured_subtitle', '') : '';
    $view_all_label = function_exists('computech_home_section_option') ? computech_home_section_option('featured_view_all_label', '') : '';
    ?>
    <section class="featured-section computech-wc-featured-products">
        <div class="featured-bg-pattern"><div class="feat-glow feat-glow-tr"></div><div class="feat-glow feat-glow-bl"></div><div class="feat-dots feat-dots-tr"></div><div class="feat-dots feat-dots-bl"></div></div>
        <div class="featured-container">
            <div class="featured-header">
                <div class="featured-header-right">
                    <?php if ($title !== '') : ?><h2 class="featured-title"><svg class="featured-title-arrow" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 2 8 6 4 10"/></svg><?php echo esc_html($title); ?></h2><?php endif; ?>
                    <?php if ($subtitle !== '') : ?><p class="featured-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
                </div>
                <?php if ($view_all_label !== '') : ?><a href="<?php echo esc_url(computech_wc_products_page_url()); ?>" class="featured-view-all"><?php echo esc_html($view_all_label); ?><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="10 3 5 8 10 13"/></svg></a><?php endif; ?>
            </div>
            <div class="featured-grid">
                <?php foreach ($products as $product) : ?>
                    <div class="feat-card-wrap"><?php computech_wc_product_card($product); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function computech_wc_render_featured_category_card(array $item): void {
    ?>
    <div class="cat-feat-card">
        <?php if (!empty($item['image'])) : ?><div class="cat-feat-card-image"><div class="cat-feat-card-glow"></div><img src="<?php echo esc_url((string) $item['image']); ?>" alt="<?php echo esc_attr((string) $item['alt']); ?>" loading="lazy"></div><?php endif; ?>
        <div class="cat-feat-card-content"><div class="cat-feat-card-icon-wrap"><?php computech_wc_render_category_icon($item, 'cat-card-icon-img'); ?></div><h3 class="cat-feat-card-title"><?php echo esc_html((string) $item['title']); ?></h3><?php if (!empty($item['text'])) : ?><p class="cat-feat-card-desc"><?php echo esc_html(wp_trim_words((string) $item['text'], 20, '...')); ?></p><?php endif; ?><?php if (!empty($item['pill'])) : ?><span class="cat-feat-card-count"><?php echo esc_html((string) $item['pill']); ?></span><?php endif; ?><?php if (!empty($item['link_text'])) : ?><a href="<?php echo esc_url((string) $item['url']); ?>" class="cat-feat-card-btn"><?php echo esc_html((string) $item['link_text']); ?><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 3 11 8 6 13"/></svg></a><?php endif; ?></div>
    </div>
    <?php
}

function computech_wc_render_category_grid_card(array $item): void {
    ?>
    <div class="cat-card">
        <?php if (!empty($item['image'])) : ?><div class="cat-card-image"><img src="<?php echo esc_url((string) $item['image']); ?>" alt="<?php echo esc_attr((string) $item['alt']); ?>" loading="lazy"></div><?php endif; ?>
        <div class="cat-card-body"><div class="cat-card-icon-wrap"><?php computech_wc_render_category_icon($item, 'cat-card-icon-img'); ?></div><h3 class="cat-card-title"><?php echo esc_html((string) $item['title']); ?></h3><?php if (!empty($item['text'])) : ?><p class="cat-card-desc"><?php echo esc_html(wp_trim_words((string) $item['text'], 20, '...')); ?></p><?php endif; ?><div class="cat-card-footer"><?php if (!empty($item['pill'])) : ?><span class="cat-card-count"><?php echo esc_html((string) $item['pill']); ?></span><?php endif; ?><?php if (!empty($item['link_text'])) : ?><a href="<?php echo esc_url((string) $item['url']); ?>" class="cat-card-btn"><?php echo esc_html((string) $item['link_text']); ?><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 3 11 8 6 13"/></svg></a><?php endif; ?></div></div>
    </div>
    <?php
}

function computech_wc_render_categories_page(): void {
    $featured = computech_wc_get_category_items('featured', 3);
    $terms = taxonomy_exists('product_cat') ? get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false)) : array();
    $all = array();
    if (!is_wp_error($terms) && is_array($terms)) {
        foreach ($terms as $term) {
            if (!$term instanceof WP_Term) { continue; }
            if (computech_wc_term_meta((int) $term->term_id, '_computech_wc_category_visibility', 'visible') === 'hidden') { continue; }
            if (!computech_wc_term_has_image($term)) { continue; }
            $all[] = computech_wc_term_card_item($term, 'all');
        }
    }
    ?>
    <section class="cat-hero"><div class="cat-hero-bg"><div class="cat-hero-circuit cat-hero-circuit-1"></div><div class="cat-hero-circuit cat-hero-circuit-2"></div><div class="cat-hero-circuit cat-hero-circuit-3"></div><div class="cat-hero-dot cat-hero-dot-1"></div><div class="cat-hero-dot cat-hero-dot-2"></div><div class="cat-hero-dot cat-hero-dot-3"></div><div class="cat-hero-dot cat-hero-dot-4"></div><div class="cat-hero-glow cat-hero-glow-1"></div><div class="cat-hero-glow cat-hero-glow-2"></div></div><div class="cat-container cat-hero-inner"><div class="cat-hero-decorative-dots"><span class="h-dot blue"></span><span class="h-dot cyan"></span><span class="h-dot green"></span></div><h1 class="cat-hero-title">أقسام المتجر</h1><p class="cat-hero-subtitle">كل الأقسام هنا من WooCommerce Product Categories.</p><div class="cat-hero-pills"><span class="cat-hero-pill">WooCommerce</span><span class="cat-hero-pill">أقسام غير محدودة</span><span class="cat-hero-pill">صورة واحدة لكل قسم</span></div></div></section>
    <?php if ($featured) : ?><section class="cat-featured"><div class="cat-featured-bg"><div class="cat-feat-glow cat-feat-glow-tr"></div><div class="cat-feat-glow cat-feat-glow-bl"></div><div class="cat-feat-dots cat-feat-dots-tr"></div><div class="cat-feat-dots cat-feat-dots-bl"></div></div><div class="cat-container"><div class="cat-section-header"><div class="cat-section-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div><h2 class="cat-section-title">الأقسام <span class="cat-section-highlight">المميزة</span></h2><p class="cat-section-subtitle">من WooCommerce Categories: Is Featured</p></div><div class="cat-featured-grid"><?php foreach ($featured as $item) { computech_wc_render_featured_category_card($item); } ?></div></div></section><?php endif; ?>
    <section class="cat-all"><div class="cat-all-bg"><div class="cat-all-circuit cat-all-circuit-tr"></div><div class="cat-all-circuit cat-all-circuit-bl"></div><div class="cat-all-dots cat-all-dots-tr"></div><div class="cat-all-dots cat-all-dots-bl"></div><div class="cat-all-glow cat-all-glow-tr"></div><div class="cat-all-glow cat-all-glow-bl"></div></div><div class="cat-container"><div class="cat-section-header"><div class="cat-section-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div><h2 class="cat-section-title">جميع <span class="cat-section-highlight">الأقسام</span></h2><p class="cat-section-subtitle">من WooCommerce Product Categories فقط</p></div><div class="cat-grid"><?php if ($all) { foreach ($all as $item) { computech_wc_render_category_grid_card($item); } } else { echo '<div class="wp-product-empty"><h2>لا توجد أقسام بعد</h2><p>أضف الأقسام من Products > Categories.</p></div>'; } ?></div></div></section>
    <?php
}

function computech_wc_product_query_args_from_request(int $per_page = 12): array {
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $search = computech_wc_get_request(computech_wc_product_search_query_var(), computech_wc_get_request('s', ''));
    $category = computech_wc_get_request('product_cat', computech_wc_get_request('category', ''));
    $stock = computech_wc_get_request('stock_status', '');
    $sort = computech_wc_get_request('sort', 'newest');
    $min_price = computech_wc_clean_price_request('min_price');
    $max_price = computech_wc_clean_price_request('max_price');

    $tax_query = array('relation' => 'AND');
    if ($category !== '' && $category !== 'all') {
        $tax_query[] = array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array($category), 'include_children' => true);
    }
    foreach (wc_get_attribute_taxonomies() ?: array() as $attribute) {
        $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
        $key = 'filter_' . $taxonomy;
        $value = computech_wc_get_request($key, '');
        if ($value !== '' && $value !== 'all' && taxonomy_exists($taxonomy)) {
            $tax_query[] = array('taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => array($value));
        }
    }

    $meta_query = array('relation' => 'AND');
    if ($stock !== '' && $stock !== 'all') {
        $meta_query[] = array('key' => '_stock_status', 'value' => $stock, 'compare' => '=');
    }
    if ($min_price !== '' || $max_price !== '') {
        $range = array('key' => '_price', 'type' => 'NUMERIC');
        if ($min_price !== '' && $max_price !== '') {
            $range['value'] = array((float) $min_price, (float) $max_price);
            $range['compare'] = 'BETWEEN';
        } elseif ($min_price !== '') {
            $range['value'] = (float) $min_price;
            $range['compare'] = '>=';
        } else {
            $range['value'] = (float) $max_price;
            $range['compare'] = '<=';
        }
        $meta_query[] = $range;
    }

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        's' => $search,
    );
    if (count($tax_query) > 1) { $args['tax_query'] = $tax_query; }
    if (count($meta_query) > 1) { $args['meta_query'] = $meta_query; }

    if ($sort === 'price-asc') {
        $args['meta_key'] = '_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'ASC';
    } elseif ($sort === 'price-desc') {
        $args['meta_key'] = '_price';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
    } elseif ($sort === 'popular') {
        $args['meta_key'] = 'total_sales';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
    } else {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }
    return $args;
}

function computech_wc_render_products_filters(): void {
    $search_key = computech_wc_product_search_query_var();
    $selected_category = computech_wc_get_request('product_cat', computech_wc_get_request('category', 'all'));
    $selected_stock = computech_wc_get_request('stock_status', 'all');
    $selected_sort = computech_wc_get_request('sort', 'newest');
    $terms = taxonomy_exists('product_cat') ? get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false)) : array();
    ?>
    <section class="prod-filters"><div class="prod-container"><form class="prod-filters-bar computech-wc-filters" method="get" action="<?php echo esc_url(computech_wc_products_page_url()); ?>">
        <div class="prod-filter-group prod-filter-search"><div class="prod-filter-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div><input type="text" name="<?php echo esc_attr($search_key); ?>" placeholder="ابحث عن منتج..." class="prod-filter-input" value="<?php echo esc_attr(computech_wc_get_request($search_key, computech_wc_get_request('s', ''))); ?>"></div>
        <div class="prod-filter-group"><select name="product_cat" class="prod-filter-select"><option value="all">كل الأقسام</option><?php if (!is_wp_error($terms)) { foreach ($terms as $term) { if ($term instanceof WP_Term) { echo '<option value="' . esc_attr($term->slug) . '" ' . selected($selected_category, $term->slug, false) . '>' . esc_html($term->name) . '</option>'; } } } ?></select></div>
        <div class="prod-filter-group"><select name="stock_status" class="prod-filter-select"><option value="all">كل التوفر</option><option value="instock" <?php selected($selected_stock, 'instock'); ?>>متوفر</option><option value="outofstock" <?php selected($selected_stock, 'outofstock'); ?>>غير متوفر</option><option value="onbackorder" <?php selected($selected_stock, 'onbackorder'); ?>>طلب مسبق</option></select></div>
        <div class="prod-filter-group"><input type="number" name="min_price" class="prod-filter-input" placeholder="أقل سعر" value="<?php echo esc_attr(computech_wc_clean_price_request('min_price')); ?>"></div>
        <div class="prod-filter-group"><input type="number" name="max_price" class="prod-filter-input" placeholder="أعلى سعر" value="<?php echo esc_attr(computech_wc_clean_price_request('max_price')); ?>"></div>
        <?php foreach (wc_get_attribute_taxonomies() ?: array() as $attribute) : $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name); if (!taxonomy_exists($taxonomy)) { continue; } $attr_terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false)); if (is_wp_error($attr_terms) || !$attr_terms) { continue; } $key = 'filter_' . $taxonomy; $selected = computech_wc_get_request($key, 'all'); ?>
            <div class="prod-filter-group"><select name="<?php echo esc_attr($key); ?>" class="prod-filter-select"><option value="all"><?php echo esc_html(wc_attribute_label($taxonomy)); ?></option><?php foreach ($attr_terms as $term) { if ($term instanceof WP_Term) { echo '<option value="' . esc_attr($term->slug) . '" ' . selected($selected, $term->slug, false) . '>' . esc_html($term->name) . '</option>'; } } ?></select></div>
        <?php endforeach; ?>
        <div class="prod-filter-group"><select name="sort" class="prod-filter-select"><option value="newest" <?php selected($selected_sort, 'newest'); ?>>الأحدث</option><option value="price-asc" <?php selected($selected_sort, 'price-asc'); ?>>السعر الأقل</option><option value="price-desc" <?php selected($selected_sort, 'price-desc'); ?>>السعر الأعلى</option><option value="popular" <?php selected($selected_sort, 'popular'); ?>>الأكثر مبيعًا</option></select></div>
        <button type="submit" class="prod-card-btn prod-btn-details">تصفية</button>
        <a href="<?php echo esc_url(computech_wc_products_page_url()); ?>" class="prod-card-btn prod-btn-clear">مسح</a>
    </form></div></section>
    <?php
}

function computech_wc_render_category_archive(WP_Term $term): void {
    $visibility = computech_wc_term_meta((int) $term->term_id, '_computech_wc_category_visibility', 'visible');
    if ($visibility === 'hidden') {
        status_header(404);
        echo '<section class="prod-hero"><div class="prod-container prod-hero-inner"><h1 class="prod-hero-title">القسم غير متاح</h1><p class="prod-hero-subtitle">هذا القسم مخفي من WooCommerce Categories.</p></div></section>';
        return;
    }
    $parents = array(array('label' => 'أقسام المتجر', 'url' => computech_page_url('categories')));
    $ancestor_ids = array_reverse(get_ancestors((int) $term->term_id, 'product_cat', 'taxonomy'));
    foreach ($ancestor_ids as $ancestor_id) {
        $ancestor = get_term((int) $ancestor_id, 'product_cat');
        if ($ancestor instanceof WP_Term && !is_wp_error($ancestor)) {
            $parents[] = array('label' => $ancestor->name, 'url' => computech_wc_category_url($ancestor));
        }
    }
    computech_breadcrumbs($term->name, $parents);
    $description = wp_strip_all_tags(term_description($term, 'product_cat'));
    ?>
    <section class="prod-hero"><div class="prod-container prod-hero-inner"><h1 class="prod-hero-title"><?php echo esc_html($term->name); ?></h1><p class="prod-hero-subtitle"><?php echo esc_html($description ?: 'منتجات WooCommerce داخل هذا القسم وكل الأقسام الفرعية.'); ?></p></div></section>
    <?php
    $children = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => (int) $term->term_id));
    if (!is_wp_error($children) && $children) {
        echo '<section class="cat-all"><div class="cat-container"><div class="cat-grid">';
        foreach ($children as $child) {
            if ($child instanceof WP_Term && computech_wc_term_meta((int) $child->term_id, '_computech_wc_category_visibility', 'visible') !== 'hidden' && computech_wc_term_has_image($child)) {
                computech_wc_render_category_grid_card(computech_wc_term_card_item($child, 'all'));
            }
        }
        echo '</div></div></section>';
    }

    $q = new WP_Query(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'paged' => max(1, (int) get_query_var('paged')),
        'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => array((int) $term->term_id), 'include_children' => true)),
    ));
    echo '<section class="prod-grid-section"><div class="prod-container"><div class="prod-grid">';
    if ($q->have_posts()) {
        while ($q->have_posts()) { $q->the_post(); computech_wc_product_card(get_post()); }
        wp_reset_postdata();
    } else {
        echo '<div class="wp-product-empty"><h2>لا توجد منتجات</h2><p>أضف منتجات WooCommerce واربطها بهذا القسم.</p></div>';
    }
    echo '</div>';
    $links = paginate_links(array('total' => $q->max_num_pages, 'current' => max(1, (int) get_query_var('paged')), 'type' => 'list'));
    if ($links) { echo '<nav class="woocommerce-pagination">' . wp_kses_post($links) . '</nav>'; }
    echo '</div></section>';
}

/* WooCommerce category extra fields */
function computech_wc_category_fields_markup(?WP_Term $term = null): void {
    $term_id = $term ? (int) $term->term_id : 0;
    $visibility = $term_id ? computech_wc_term_meta($term_id, '_computech_wc_category_visibility', 'visible') : 'visible';
    $is_featured = false;
    if ($term) {
        $new_featured = get_term_meta($term_id, '_computech_wc_is_featured', true);
        $is_featured = $new_featured !== '' ? ((string) $new_featured === '1') : computech_wc_bool_term_meta($term_id, '_computech_wc_show_featured_categories', false);
    }
    $featured_order = $term_id ? computech_wc_term_meta($term_id, '_computech_wc_featured_order', '0') : '0';

    if ($term) {
        ?>
        <tr class="form-field"><th scope="row">Computech Visibility</th><td><select name="_computech_wc_category_visibility"><option value="visible" <?php selected($visibility, 'visible'); ?>>Visible</option><option value="hidden" <?php selected($visibility, 'hidden'); ?>>Hidden</option></select></td></tr>
        <tr class="form-field"><th scope="row">Is Featured</th><td><label><input type="checkbox" name="_computech_wc_is_featured" value="1" <?php checked($is_featured); ?>> Yes</label><p class="description">Shows this category inside الأقسام المميزة.</p></td></tr>
        <tr class="form-field"><th scope="row">Featured Order</th><td><input type="number" name="_computech_wc_featured_order" value="<?php echo esc_attr($featured_order); ?>" min="0" step="1"><p class="description">Used only in الأقسام المميزة.</p></td></tr>
        <tr class="form-field"><th scope="row">Category Image</th><td><p class="description">Use the native WooCommerce thumbnail image below. Category cards need one image only.</p></td></tr>
        <?php
    } else {
        ?>
        <div class="form-field"><label>Computech Visibility</label><select name="_computech_wc_category_visibility"><option value="visible">Visible</option><option value="hidden">Hidden</option></select></div>
        <div class="form-field"><label><input type="checkbox" name="_computech_wc_is_featured" value="1"> Is Featured</label><p>Shows this category inside الأقسام المميزة.</p></div>
        <div class="form-field"><label>Featured Order</label><input type="number" name="_computech_wc_featured_order" value="0" min="0" step="1"></div>
        <div class="form-field"><p class="description">Set one native WooCommerce thumbnail image for this category.</p></div>
        <?php
    }
}
add_action('product_cat_add_form_fields', static function(): void { computech_wc_category_fields_markup(null); });
add_action('product_cat_edit_form_fields', static function(WP_Term $term): void { computech_wc_category_fields_markup($term); });

function computech_wc_save_category_fields(int $term_id): void {
    update_term_meta($term_id, '_computech_wc_category_visibility', isset($_POST['_computech_wc_category_visibility']) ? sanitize_text_field(wp_unslash($_POST['_computech_wc_category_visibility'])) : 'visible');
    update_term_meta($term_id, '_computech_wc_is_featured', !empty($_POST['_computech_wc_is_featured']) ? '1' : '0');
    update_term_meta($term_id, '_computech_wc_featured_order', (string) absint($_POST['_computech_wc_featured_order'] ?? 0));
    update_term_meta($term_id, 'display_type', '');

    foreach (array(
        '_computech_wc_show_shop_section',
        '_computech_wc_shop_order',
        '_computech_wc_show_featured_categories',
        '_computech_wc_shop_badge_text',
        '_computech_wc_shop_button_text',
        '_computech_wc_featured_badge_text',
        '_computech_wc_featured_button_text',
        '_computech_wc_category_icon_id'
    ) as $removed_key) {
        delete_term_meta($term_id, $removed_key);
    }
}
add_action('created_product_cat', 'computech_wc_save_category_fields');
add_action('edited_product_cat', 'computech_wc_save_category_fields');

function computech_wc_admin_media_script(string $hook): void {
    if (!is_admin()) { return; }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->taxonomy !== 'product_cat') { return; }

    $script = <<<'JS'
jQuery(function($){
    $('#display_type').val('');
    $('#display_type').closest('tr,.form-field').hide();
});
JS;
    wp_add_inline_script('jquery-core', $script);
}
add_action('admin_enqueue_scripts', 'computech_wc_admin_media_script');

function computech_wc_hide_default_category_admin_fields(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->taxonomy !== 'product_cat') { return; }
    echo '<style>.term-display-type-wrap{display:none!important}</style>';
}
add_action('admin_head', 'computech_wc_hide_default_category_admin_fields');

/* WooCommerce product extra fields */
function computech_wc_product_metabox(): void {
    add_meta_box('computech_wc_product_card', 'Computech Product Settings', 'computech_wc_product_metabox_html', 'product', 'normal', 'default');
}
add_action('add_meta_boxes_product', 'computech_wc_product_metabox');

function computech_wc_product_metabox_html(WP_Post $post): void {
    wp_nonce_field('computech_wc_save_product_fields', 'computech_wc_product_nonce');
    $is_featured = get_post_meta($post->ID, '_computech_wc_is_featured', true) === '1';
    $featured_order = (string) get_post_meta($post->ID, '_computech_wc_featured_order', true);
    $condition = sanitize_key((string) get_post_meta($post->ID, '_computech_wc_condition', true));
    if (!in_array($condition, array('new', 'imported'), true)) { $condition = 'new'; }
    ?>
    <div class="computech-product-admin" style="direction:rtl;display:grid;gap:18px">
        <p><strong>WooCommerce controls:</strong> price, stock, product image, gallery, categories, and core data.</p>
        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px">
            <p><label style="display:block;font-weight:700;margin-bottom:6px"><input type="checkbox" name="_computech_wc_is_featured" value="1" <?php checked($is_featured); ?>> Is Featured</label><span class="description">Shows this product in homepage featured products.</span></p>
            <p><label style="display:block;font-weight:700;margin-bottom:6px">Featured Order</label><input type="number" name="_computech_wc_featured_order" value="<?php echo esc_attr($featured_order); ?>" class="widefat" min="0" step="1"></p>
            <p><label style="display:block;font-weight:700;margin-bottom:6px">Status</label><select name="_computech_wc_condition" class="widefat"><option value="new" <?php selected($condition, 'new'); ?>>جديد</option><option value="imported" <?php selected($condition, 'imported'); ?>>استيراد</option></select></p>
        </div>
        <div class="computech-specs-editor">
            <h3 style="margin:0 0 8px">مواصفات المنتج</h3>
            <p class="description">أضف أي عدد من المواصفات. مثال: المعالج / Intel Core i9-14900K.</p>
            <?php
            $saved_specs = get_post_meta($post->ID, '_computech_wc_specs', true);
            if (!is_array($saved_specs)) {
                $saved_specs = array();
                for ($i = 1; $i <= 200; $i++) {
                    $legacy_label = trim((string) get_post_meta($post->ID, '_computech_wc_spec_label_' . $i, true));
                    $legacy_value = trim((string) get_post_meta($post->ID, '_computech_wc_spec_value_' . $i, true));
                    if ($legacy_label !== '' || $legacy_value !== '') {
                        $saved_specs[] = array('label' => $legacy_label, 'value' => $legacy_value);
                    }
                }
            }
            if (!$saved_specs) {
                $saved_specs = array(array('label' => '', 'value' => ''));
            }
            ?>
            <div id="computech-wc-specs-rows" style="display:grid;gap:10px">
                <?php foreach ($saved_specs as $row) : ?>
                    <div class="computech-wc-spec-row" style="display:grid;grid-template-columns:1fr 2fr auto;gap:8px;align-items:center">
                        <input type="text" name="_computech_wc_spec_label[]" value="<?php echo esc_attr((string) ($row['label'] ?? '')); ?>" class="widefat" placeholder="اسم المواصفة">
                        <input type="text" name="_computech_wc_spec_value[]" value="<?php echo esc_attr((string) ($row['value'] ?? '')); ?>" class="widefat" placeholder="القيمة">
                        <button type="button" class="button computech-remove-spec">حذف</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><button type="button" class="button button-primary" id="computech-add-spec-row">+ إضافة مواصفة</button></p>
            <script>
            jQuery(function($){
                var $rows = $('#computech-wc-specs-rows');
                $('#computech-add-spec-row').on('click', function(e){
                    e.preventDefault();
                    $rows.append('<div class="computech-wc-spec-row" style="display:grid;grid-template-columns:1fr 2fr auto;gap:8px;align-items:center"><input type="text" name="_computech_wc_spec_label[]" value="" class="widefat" placeholder="اسم المواصفة"><input type="text" name="_computech_wc_spec_value[]" value="" class="widefat" placeholder="القيمة"><button type="button" class="button computech-remove-spec">حذف</button></div>');
                });
                $rows.on('click', '.computech-remove-spec', function(e){
                    e.preventDefault();
                    if ($rows.find('.computech-wc-spec-row').length > 1) {
                        $(this).closest('.computech-wc-spec-row').remove();
                    } else {
                        $(this).closest('.computech-wc-spec-row').find('input').val('');
                    }
                });
            });
            </script>
        </div>
    </div>
    <?php
}

function computech_wc_save_product_fields(int $post_id): void {
    if (!isset($_POST['computech_wc_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_wc_product_nonce'])), 'computech_wc_save_product_fields')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }

    update_post_meta($post_id, '_computech_wc_is_featured', !empty($_POST['_computech_wc_is_featured']) ? '1' : '0');
    update_post_meta($post_id, '_computech_wc_featured_order', absint($_POST['_computech_wc_featured_order'] ?? 0));
    $condition = sanitize_key(wp_unslash($_POST['_computech_wc_condition'] ?? 'new'));
    update_post_meta($post_id, '_computech_wc_condition', in_array($condition, array('new', 'imported'), true) ? $condition : 'new');

    $spec_labels = isset($_POST['_computech_wc_spec_label']) && is_array($_POST['_computech_wc_spec_label']) ? wp_unslash($_POST['_computech_wc_spec_label']) : array();
    $spec_values = isset($_POST['_computech_wc_spec_value']) && is_array($_POST['_computech_wc_spec_value']) ? wp_unslash($_POST['_computech_wc_spec_value']) : array();
    $clean_specs = array();
    $max_specs = max(count($spec_labels), count($spec_values));
    for ($i = 0; $i < $max_specs; $i++) {
        $label = sanitize_text_field((string) ($spec_labels[$i] ?? ''));
        $value = sanitize_text_field((string) ($spec_values[$i] ?? ''));
        if ($label !== '' || $value !== '') {
            $clean_specs[] = array('label' => $label, 'value' => $value);
        }
    }
    update_post_meta($post_id, '_computech_wc_specs', $clean_specs);
    for ($i = 1; $i <= 200; $i++) {
        delete_post_meta($post_id, '_computech_wc_spec_label_' . $i);
        delete_post_meta($post_id, '_computech_wc_spec_value_' . $i);
    }

    foreach (array(
        '_computech_wc_primary_category',
        '_computech_wc_card_title',
        '_computech_wc_card_subtitle',
        '_computech_wc_card_note',
        '_computech_wc_whatsapp_number',
        '_computech_wc_whatsapp_message',
        '_computech_wc_highlight_1',
        '_computech_wc_highlight_2',
        '_computech_wc_highlight_3',
        '_computech_wc_highlight_4'
    ) as $removed_key) {
        delete_post_meta($post_id, $removed_key);
    }
}
add_action('save_post_product', 'computech_wc_save_product_fields');
