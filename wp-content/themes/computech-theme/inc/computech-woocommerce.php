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
    $icon_id = absint(get_term_meta($term_id, '_computech_wc_category_icon_id', true));
    if ($icon_id) {
        $url = wp_get_attachment_image_url($icon_id, $size);
        $alt = (string) get_post_meta($icon_id, '_wp_attachment_image_alt', true);
        if ($url) {
            return array('url' => $url, 'alt' => $alt);
        }
    }
    return computech_wc_term_image($term_id, $size);
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

function computech_wc_term_card_item(WP_Term $term, string $context = 'shop'): array {
    $image = computech_wc_term_image((int) $term->term_id, 'large');
    $icon = computech_wc_term_icon((int) $term->term_id, 'thumbnail');
    $count = computech_wc_category_product_count((int) $term->term_id);
    $badge_key = $context === 'featured' ? '_computech_wc_featured_badge_text' : '_computech_wc_shop_badge_text';
    $button_key = $context === 'featured' ? '_computech_wc_featured_button_text' : '_computech_wc_shop_button_text';
    $badge = computech_wc_term_meta((int) $term->term_id, $badge_key, '');
    if ($badge === '') {
        $badge = $count > 0 ? sprintf('+%d منتج', $count) : '';
    }
    $button = computech_wc_term_meta((int) $term->term_id, $button_key, 'استكشف القسم');
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

function computech_wc_get_category_items(string $section = 'shop', int $limit = 0): array {
    if (!taxonomy_exists('product_cat')) {
        return array();
    }

    $show_key = $section === 'featured' ? '_computech_wc_show_featured_categories' : '_computech_wc_show_shop_section';
    $order_key = $section === 'featured' ? '_computech_wc_featured_order' : '_computech_wc_shop_order';

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
        if (!computech_wc_bool_term_meta((int) $term->term_id, $show_key, false)) {
            continue;
        }
        $items[] = array(
            'order' => (int) computech_wc_term_meta((int) $term->term_id, $order_key, (string) $term->term_order),
            'name' => $term->name,
            'item' => computech_wc_term_card_item($term, $section),
        );
    }

    usort($items, static function(array $a, array $b): int {
        return $a['order'] === $b['order'] ? strnatcasecmp($a['name'], $b['name']) : $a['order'] <=> $b['order'];
    });

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
    $items = computech_wc_get_category_items('shop');
    if (!$items) {
        return;
    }
    $title = function_exists('computech_home_section_option') ? computech_home_section_option('shop_title', 'تسوق حسب القسم') : 'تسوق حسب القسم';
    $subtitle = function_exists('computech_home_section_option') ? computech_home_section_option('shop_subtitle', '') : '';
    $top_items = array_slice($items, 0, 2);
    $bottom_items = array_slice($items, 2, 6);
    ?>
    <section class="shop-section computech-wc-shop-categories">
        <div class="shop-container">
            <div class="shop-header">
                <?php if ($title !== '') : ?><h2 class="shop-title"><?php echo esc_html($title); ?></h2><?php endif; ?>
                <?php if ($subtitle !== '') : ?><p class="shop-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
            </div>
            <div class="shop-grid">
                <?php foreach ($top_items as $item) { computech_wc_render_shop_category_card($item, 'shop-card-lg'); } ?>
                <?php foreach ($bottom_items as $item) { computech_wc_render_shop_category_card($item, count($bottom_items) <= 2 ? 'shop-card-xl' : 'shop-card-lg'); } ?>
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

function computech_wc_product_condition_label(WC_Product $product): string {
    $condition = trim((string) $product->get_attribute('pa_condition'));
    if ($condition === '') {
        $condition = trim((string) $product->get_attribute('condition'));
    }
    if ($condition !== '') {
        return $condition;
    }
    if (!$product->is_in_stock()) {
        return 'غير متوفر';
    }
    return '';
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

function computech_wc_product_highlights(WC_Product $product, int $limit = 4): array {
    $manual = array();
    for ($i = 1; $i <= 4; $i++) {
        $value = trim((string) get_post_meta($product->get_id(), '_computech_wc_highlight_' . $i, true));
        if ($value !== '') {
            $manual[] = $value;
        }
    }
    if ($manual) {
        return array_slice($manual, 0, $limit);
    }

    $out = array();
    foreach ($product->get_attributes() as $attribute) {
        if (!$attribute instanceof WC_Product_Attribute || !$attribute->get_visible()) {
            continue;
        }
        $name = wc_attribute_label($attribute->get_name());
        $values = array();
        if ($attribute->is_taxonomy()) {
            $terms = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
            if (!is_wp_error($terms)) {
                $values = $terms;
            }
        } else {
            $values = $attribute->get_options();
        }
        $value = trim(implode(', ', array_map('wp_strip_all_tags', $values)));
        if ($value !== '') {
            $out[] = $name . ': ' . $value;
        }
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
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
    $card_title = trim((string) get_post_meta($product_id, '_computech_wc_card_title', true));
    $subtitle = trim((string) get_post_meta($product_id, '_computech_wc_card_subtitle', true));
    if ($subtitle === '') {
        $subtitle = wp_strip_all_tags($product->get_short_description());
    }
    if ($subtitle === '') {
        $subtitle = wp_trim_words(wp_strip_all_tags($product->get_description()), 20, '...');
    }
    $image_id = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src('large');
    $image_alt = $image_id ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
    $badge = computech_wc_product_condition_label($product);
    $highlights = computech_wc_product_highlights($product, 4);
    $note = trim((string) get_post_meta($product_id, '_computech_wc_card_note', true));
    $whatsapp_url = computech_wc_product_whatsapp_url($product);
    ?>
    <div class="prod-card" data-category="<?php echo esc_attr(computech_wc_product_filter_category_slugs($product_id)); ?>" data-status="<?php echo esc_attr($product->get_stock_status()); ?>" data-price="<?php echo esc_attr((string) computech_wc_product_price_number($product)); ?>" data-name="<?php echo esc_attr($title); ?>">
        <div class="prod-card-image">
            <?php if ($badge !== '') : ?><span class="prod-badge <?php echo esc_attr($product->is_in_stock() ? 'prod-badge-new' : 'prod-badge-imported'); ?>"><?php echo esc_html($badge); ?></span><?php endif; ?>
            <a href="<?php echo esc_url(get_permalink($product_id)); ?>"><img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt !== '' ? $image_alt : $title); ?>" loading="lazy"></a>
        </div>
        <div class="prod-card-body">
            <h3 class="prod-card-title"><a href="<?php echo esc_url(get_permalink($product_id)); ?>"><?php echo esc_html($card_title !== '' ? $card_title : $title); ?></a></h3>
            <?php if ($subtitle !== '') : ?><p class="prod-card-desc"><?php echo esc_html(wp_trim_words($subtitle, 20, '...')); ?></p><?php endif; ?>
            <?php if ($highlights) : ?><div class="prod-card-specs"><?php foreach ($highlights as $highlight) : ?><span class="prod-spec"><?php echo esc_html($highlight); ?></span><?php endforeach; ?></div><?php endif; ?>
            <?php if ($product->get_price_html() !== '') : ?><div class="prod-card-price"><?php echo wp_kses_post($product->get_price_html()); ?></div><?php endif; ?>
            <?php if ($note !== '') : ?><div class="prod-card-warranty"><?php echo esc_html($note); ?></div><?php endif; ?>
            <div class="prod-card-actions">
                <a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="prod-card-btn prod-btn-details"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>عرض التفاصيل</a>
                <?php if ($whatsapp_url !== '') : ?><a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="prod-card-btn prod-btn-whatsapp"><?php echo computech_whatsapp_icon(); ?>واتساب</a><?php endif; ?>
                <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?><a href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-quantity="1" data-product_id="<?php echo esc_attr((string) $product_id); ?>" class="prod-card-btn prod-btn-cart add_to_cart_button ajax_add_to_cart">إضافة للسلة</a><?php endif; ?>
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
        'featured' => true,
        'limit' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'objects',
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
    $title = function_exists('computech_home_section_option') ? computech_home_section_option('featured_title', 'منتجات مميزة') : 'منتجات مميزة';
    $subtitle = function_exists('computech_home_section_option') ? computech_home_section_option('featured_subtitle', '') : '';
    $view_all_label = function_exists('computech_home_section_option') ? computech_home_section_option('featured_view_all_label', 'عرض كل المنتجات') : 'عرض كل المنتجات';
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
            $all[] = computech_wc_term_card_item($term, 'all');
        }
    }
    ?>
    <section class="cat-hero"><div class="cat-hero-bg"><div class="cat-hero-circuit cat-hero-circuit-1"></div><div class="cat-hero-circuit cat-hero-circuit-2"></div><div class="cat-hero-circuit cat-hero-circuit-3"></div><div class="cat-hero-dot cat-hero-dot-1"></div><div class="cat-hero-dot cat-hero-dot-2"></div><div class="cat-hero-dot cat-hero-dot-3"></div><div class="cat-hero-dot cat-hero-dot-4"></div><div class="cat-hero-glow cat-hero-glow-1"></div><div class="cat-hero-glow cat-hero-glow-2"></div></div><div class="cat-container cat-hero-inner"><div class="cat-hero-decorative-dots"><span class="h-dot blue"></span><span class="h-dot cyan"></span><span class="h-dot green"></span></div><h1 class="cat-hero-title">أقسام المتجر</h1><p class="cat-hero-subtitle">كل الأقسام هنا من WooCommerce Product Categories.</p><div class="cat-hero-pills"><span class="cat-hero-pill">WooCommerce</span><span class="cat-hero-pill">أقسام غير محدودة</span><span class="cat-hero-pill">صور وأيقونات من القسم</span></div></div></section>
    <?php if ($featured) : ?><section class="cat-featured"><div class="cat-featured-bg"><div class="cat-feat-glow cat-feat-glow-tr"></div><div class="cat-feat-glow cat-feat-glow-bl"></div><div class="cat-feat-dots cat-feat-dots-tr"></div><div class="cat-feat-dots cat-feat-dots-bl"></div></div><div class="cat-container"><div class="cat-section-header"><div class="cat-section-dots"><span class="sdot blue"></span><span class="sdot cyan"></span><span class="sdot bar"></span><span class="sdot green"></span></div><h2 class="cat-section-title">الأقسام <span class="cat-section-highlight">المميزة</span></h2><p class="cat-section-subtitle">من WooCommerce Categories: Show in Featured Categories</p></div><div class="cat-featured-grid"><?php foreach ($featured as $item) { computech_wc_render_featured_category_card($item); } ?></div></div></section><?php endif; ?>
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
        <a href="<?php echo esc_url(computech_wc_products_page_url()); ?>" class="prod-card-btn prod-btn-whatsapp">مسح</a>
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
            if ($child instanceof WP_Term) {
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
    $icon_id = $term_id ? absint(get_term_meta($term_id, '_computech_wc_category_icon_id', true)) : 0;
    $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'thumbnail') : '';
    $fields = array(
        '_computech_wc_show_shop_section' => 'Show in Shop Section',
        '_computech_wc_show_featured_categories' => 'Show in Featured Categories',
    );
    $orders = array(
        '_computech_wc_shop_order' => 'Shop Section Order',
        '_computech_wc_featured_order' => 'Featured Categories Order',
    );
    $texts = array(
        '_computech_wc_shop_badge_text' => 'Shop Badge Text',
        '_computech_wc_shop_button_text' => 'Shop Button Text',
        '_computech_wc_featured_badge_text' => 'Featured Badge Text',
        '_computech_wc_featured_button_text' => 'Featured Button Text',
    );
    if ($term) {
        ?>
        <tr class="form-field"><th scope="row">Computech Visibility</th><td><select name="_computech_wc_category_visibility"><option value="visible" <?php selected($visibility, 'visible'); ?>>Visible</option><option value="hidden" <?php selected($visibility, 'hidden'); ?>>Hidden</option></select></td></tr>
        <tr class="form-field"><th scope="row">Card Icon</th><td><input type="hidden" name="_computech_wc_category_icon_id" value="<?php echo esc_attr((string) $icon_id); ?>" class="computech-wc-icon-id"><div class="computech-wc-icon-preview"><?php if ($icon_url) : ?><img src="<?php echo esc_url($icon_url); ?>" style="max-width:72px;height:auto;border:1px solid #ddd;border-radius:8px"><?php endif; ?></div><button type="button" class="button computech-wc-icon-upload">اختيار أيقونة</button> <button type="button" class="button computech-wc-icon-remove">حذف</button><p class="description">لو فارغة، يستخدم صورة القسم WooCommerce thumbnail.</p></td></tr>
        <?php foreach ($fields as $key => $label) : ?><tr class="form-field"><th scope="row"><?php echo esc_html($label); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr($key); ?>" value="1" <?php checked(computech_wc_bool_term_meta($term_id, $key)); ?>> Yes</label></td></tr><?php endforeach; ?>
        <?php foreach ($orders as $key => $label) : ?><tr class="form-field"><th scope="row"><?php echo esc_html($label); ?></th><td><input type="number" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr(computech_wc_term_meta($term_id, $key, '0')); ?>"></td></tr><?php endforeach; ?>
        <?php foreach ($texts as $key => $label) : ?><tr class="form-field"><th scope="row"><?php echo esc_html($label); ?></th><td><input type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr(computech_wc_term_meta($term_id, $key, '')); ?>" class="regular-text"></td></tr><?php endforeach; ?>
        <?php
    } else {
        ?>
        <div class="form-field"><label>Computech Visibility</label><select name="_computech_wc_category_visibility"><option value="visible">Visible</option><option value="hidden">Hidden</option></select></div>
        <div class="form-field"><label>Card Icon</label><input type="hidden" name="_computech_wc_category_icon_id" value="" class="computech-wc-icon-id"><div class="computech-wc-icon-preview"></div><button type="button" class="button computech-wc-icon-upload">اختيار أيقونة</button> <button type="button" class="button computech-wc-icon-remove">حذف</button></div>
        <?php foreach ($fields as $key => $label) : ?><div class="form-field"><label><input type="checkbox" name="<?php echo esc_attr($key); ?>" value="1"> <?php echo esc_html($label); ?></label></div><?php endforeach; ?>
        <?php foreach ($orders as $key => $label) : ?><div class="form-field"><label><?php echo esc_html($label); ?></label><input type="number" name="<?php echo esc_attr($key); ?>" value="0"></div><?php endforeach; ?>
        <?php foreach ($texts as $key => $label) : ?><div class="form-field"><label><?php echo esc_html($label); ?></label><input type="text" name="<?php echo esc_attr($key); ?>" value=""></div><?php endforeach; ?>
        <?php
    }
}
add_action('product_cat_add_form_fields', static function(): void { computech_wc_category_fields_markup(null); });
add_action('product_cat_edit_form_fields', static function(WP_Term $term): void { computech_wc_category_fields_markup($term); });

function computech_wc_save_category_fields(int $term_id): void {
    $keys = array('_computech_wc_category_visibility','_computech_wc_category_icon_id','_computech_wc_shop_order','_computech_wc_featured_order','_computech_wc_shop_badge_text','_computech_wc_shop_button_text','_computech_wc_featured_badge_text','_computech_wc_featured_button_text');
    update_term_meta($term_id, '_computech_wc_show_shop_section', !empty($_POST['_computech_wc_show_shop_section']) ? '1' : '0');
    update_term_meta($term_id, '_computech_wc_show_featured_categories', !empty($_POST['_computech_wc_show_featured_categories']) ? '1' : '0');
    foreach ($keys as $key) {
        if (!isset($_POST[$key])) { continue; }
        $raw = wp_unslash($_POST[$key]);
        if (strpos($key, 'order') !== false || strpos($key, 'icon_id') !== false) {
            update_term_meta($term_id, $key, (string) absint($raw));
        } else {
            update_term_meta($term_id, $key, sanitize_text_field($raw));
        }
    }
}
add_action('created_product_cat', 'computech_wc_save_category_fields');
add_action('edited_product_cat', 'computech_wc_save_category_fields');

function computech_wc_admin_media_script(string $hook): void {
    if (!is_admin()) { return; }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->taxonomy !== 'product_cat') { return; }
    wp_enqueue_media();
    $script = <<<'JS'
jQuery(function($){
    $(document).on('click','.computech-wc-icon-upload',function(e){
        e.preventDefault();
        var wrap=$(this).closest('td,.form-field');
        var frame=wp.media({title:'اختيار أيقونة القسم',button:{text:'استخدام'},multiple:false});
        frame.on('select',function(){
            var a=frame.state().get('selection').first().toJSON();
            var src=(a.sizes&&a.sizes.thumbnail)?a.sizes.thumbnail.url:a.url;
            wrap.find('.computech-wc-icon-id').val(a.id);
            wrap.find('.computech-wc-icon-preview').html('<img src="'+src+'" style="max-width:72px;height:auto;border:1px solid #ddd;border-radius:8px">');
        });
        frame.open();
    });
    $(document).on('click','.computech-wc-icon-remove',function(e){
        e.preventDefault();
        var wrap=$(this).closest('td,.form-field');
        wrap.find('.computech-wc-icon-id').val('');
        wrap.find('.computech-wc-icon-preview').html('');
    });
});
JS;
    wp_add_inline_script('jquery-core', $script);
}
add_action('admin_enqueue_scripts', 'computech_wc_admin_media_script');

/* WooCommerce product extra card fields */
function computech_wc_product_metabox(): void {
    add_meta_box('computech_wc_product_card', 'Computech Card / Display', 'computech_wc_product_metabox_html', 'product', 'normal', 'default');
}
add_action('add_meta_boxes_product', 'computech_wc_product_metabox');

function computech_wc_product_term_options(int $selected = 0): string {
    $terms = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
    $html = '<option value="0">—</option>';
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            if ($term instanceof WP_Term) {
                $html .= '<option value="' . esc_attr((string) $term->term_id) . '" ' . selected($selected, (int) $term->term_id, false) . '>' . esc_html($term->name) . '</option>';
            }
        }
    }
    return $html;
}

function computech_wc_product_metabox_html(WP_Post $post): void {
    wp_nonce_field('computech_wc_save_product_fields', 'computech_wc_product_nonce');
    $primary = absint(get_post_meta($post->ID, '_computech_wc_primary_category', true));
    ?>
    <div class="computech-product-admin" style="direction:rtl;display:grid;gap:14px">
        <p><strong>WooCommerce controls main data.</strong> Price, stock, images, gallery, categories, attributes from WooCommerce product editor.</p>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px">
            <p><label>Primary Category</label><select name="_computech_wc_primary_category" class="widefat"><?php echo computech_wc_product_term_options($primary); ?></select></p>
            <p><label>Featured Products Order</label><input type="number" name="_computech_wc_featured_order" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_wc_featured_order', true)); ?>" class="widefat"></p>
            <p><label>Card Title Override</label><input type="text" name="_computech_wc_card_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_wc_card_title', true)); ?>" class="widefat"></p>
            <p><label>Card Subtitle</label><input type="text" name="_computech_wc_card_subtitle" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_wc_card_subtitle', true)); ?>" class="widefat"></p>
            <p><label>Card Note</label><input type="text" name="_computech_wc_card_note" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_wc_card_note', true)); ?>" class="widefat" placeholder="ضمان 12 شهر"></p>
            <p><label>WhatsApp Number Override</label><input type="text" name="_computech_wc_whatsapp_number" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_wc_whatsapp_number', true)); ?>" class="widefat"></p>
        </div>
        <p><label>WhatsApp Message</label><textarea name="_computech_wc_whatsapp_message" rows="2" class="widefat"><?php echo esc_textarea(get_post_meta($post->ID, '_computech_wc_whatsapp_message', true)); ?></textarea></p>
        <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px">
            <?php for ($i = 1; $i <= 4; $i++) : ?><p><label>Highlight <?php echo esc_html((string) $i); ?></label><input type="text" name="_computech_wc_highlight_<?php echo esc_attr((string) $i); ?>" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_wc_highlight_' . $i, true)); ?>" class="widefat"></p><?php endfor; ?>
        </div>
    </div>
    <?php
}

function computech_wc_save_product_fields(int $post_id): void {
    if (!isset($_POST['computech_wc_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_wc_product_nonce'])), 'computech_wc_save_product_fields')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    $fields = array('_computech_wc_card_title','_computech_wc_card_subtitle','_computech_wc_card_note','_computech_wc_whatsapp_number','_computech_wc_whatsapp_message');
    foreach ($fields as $field) {
        update_post_meta($post_id, $field, isset($_POST[$field]) ? sanitize_text_field(wp_unslash($_POST[$field])) : '');
    }
    update_post_meta($post_id, '_computech_wc_primary_category', absint($_POST['_computech_wc_primary_category'] ?? 0));
    update_post_meta($post_id, '_computech_wc_featured_order', absint($_POST['_computech_wc_featured_order'] ?? 0));
    for ($i = 1; $i <= 4; $i++) {
        $key = '_computech_wc_highlight_' . $i;
        update_post_meta($post_id, $key, isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '');
    }
}
add_action('save_post_product', 'computech_wc_save_product_fields');
