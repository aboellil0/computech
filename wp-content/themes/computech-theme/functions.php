<?php
/**
 * Computech Theme Functions.
 */

if (!defined('ABSPATH')) {
    exit;
}

function computech_setup(): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height' => 80, 'width' => 220, 'flex-width' => true, 'flex-height' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    register_nav_menus(array('primary' => __('القائمة الرئيسية', 'computech')));
}
add_action('after_setup_theme', 'computech_setup');

function computech_enqueue_assets(): void {
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();

    wp_enqueue_style('computech-theme-root', get_stylesheet_uri(), array(), filemtime($theme_dir . '/style.css'));
    wp_enqueue_style('computech-main', $theme_uri . '/assets/css/style.css', array('computech-theme-root'), filemtime($theme_dir . '/assets/css/style.css'));

    wp_enqueue_script('computech-main', $theme_uri . '/assets/js/main.js', array(), filemtime($theme_dir . '/assets/js/main.js'), true);
    wp_localize_script('computech-main', 'computechTheme', array(
        'assetsUrl' => trailingslashit($theme_uri . '/assets/images'),
        'productsUrl' => computech_page_url('products'),
        'categoriesUrl' => computech_page_url('categories'),
        'servicesUrl' => computech_page_url('services'),
        'offersUrl' => computech_page_url('offers'),
        'contactUrl' => computech_page_url('contact'),
    ));
}
add_action('wp_enqueue_scripts', 'computech_enqueue_assets');

function computech_register_products_cpt(): void {
    register_post_type('products', array(
        'labels' => array(
            'name' => 'المنتجات',
            'singular_name' => 'منتج',
            'add_new_item' => 'إضافة منتج جديد',
            'edit_item' => 'تعديل المنتج',
            'new_item' => 'منتج جديد',
            'view_item' => 'عرض المنتج',
            'search_items' => 'بحث في المنتجات',
            'not_found' => 'لا توجد منتجات',
            'menu_name' => 'منتجات كمبيوتيك',
        ),
        'public' => true,
        'menu_icon' => 'dashicons-desktop',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
        'taxonomies' => array('product_category'),
        'rewrite' => array('slug' => 'product', 'with_front' => false),
        'has_archive' => false,
        // Use the Classic editor for products so the full architecture metaboxes are visible and editable.
        'show_in_rest' => false,
    ));

    register_taxonomy('product_category', 'products', array(
        'labels' => array(
            'name' => 'أقسام المنتجات',
            'singular_name' => 'قسم المنتج',
            'menu_name' => 'أقسام المنتجات',
        ),
        'public' => true,
        'hierarchical' => true,
        'rewrite' => array('slug' => 'product-category', 'with_front' => false),
        'show_in_rest' => true,
    ));
}
add_action('init', 'computech_register_products_cpt');

/**
 * Product edit screen
 *
 * The product architecture depends on traditional WordPress meta boxes.
 * In the block editor these boxes are collapsed into a small "Meta Boxes" drawer,
 * so product fields look missing. Force the products CPT to use the Classic edit
 * screen where price, specs, warranty, buttons, gallery, visibility, featured
 * products settings and primary category are all directly editable.
 */
function computech_use_classic_editor_for_products(bool $use_block_editor, string $post_type): bool {
    if ($post_type === 'products') {
        return false;
    }
    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'computech_use_classic_editor_for_products', 20, 2);

function computech_product_admin_screen_css(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== 'products') {
        return;
    }
    ?>
    <style>
        body.post-type-products #poststuff #post-body.columns-2 #postbox-container-1{margin-left:0}
        body.post-type-products #computech_product_architecture_data .inside{margin:0;padding:0}
        body.post-type-products #computech_product_architecture_data{border-radius:12px;overflow:hidden}
        body.post-type-products #computech_product_architecture_data .hndle,
        body.post-type-products #computech_product_architecture_data h2{font-weight:800}
        body.post-type-products .computech-arch-admin{padding:16px;background:#f6f7f7}
        body.post-type-products .computech-arch-admin input[type="text"],
        body.post-type-products .computech-arch-admin input[type="url"],
        body.post-type-products .computech-arch-admin input[type="number"],
        body.post-type-products .computech-arch-admin select,
        body.post-type-products .computech-arch-admin textarea{max-width:100%}
        body.post-type-products .taxonomy-product_category .categorychecklist{max-height:320px;overflow:auto}
    </style>
    <?php
}
add_action('admin_head-post.php', 'computech_product_admin_screen_css');
add_action('admin_head-post-new.php', 'computech_product_admin_screen_css');


function computech_page_definitions(): array {
    return array(
        'about' => 'من نحن',
        'services' => 'الخدمات',
        'categories' => 'أقسام المتجر',
        'products' => 'المنتجات',
        'product-details' => 'تفاصيل المنتج',
        'offers' => 'العروض',
        'contact' => 'تواصل معنا',
        'sitemap' => 'خريطة الموقع',
        'terms' => 'الشروط والأحكام',
        'privacy-policy' => 'سياسة الخصوصية',
    );
}

function computech_find_page_by_slug(string $slug): ?WP_Post {
    $page = get_page_by_path($slug, OBJECT, 'page');
    return $page instanceof WP_Post ? $page : null;
}

function computech_ensure_theme_pages(): void {
    foreach (computech_page_definitions() as $slug => $title) {
        if (computech_find_page_by_slug($slug)) {
            continue;
        }

        wp_insert_post(array(
            'post_title' => $title,
            'post_name' => $slug,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => '',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ));
    }
}

function computech_activate(): void {
    computech_register_products_cpt();
    computech_register_hero_slides_cpt();
    computech_register_hero_cards_cpt();
    computech_register_customer_need_cards_cpt();
    computech_register_home_category_cards_cpt();
    computech_ensure_theme_pages();
    computech_seed_header_database_options();
    computech_seed_default_home_section_options();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'computech_activate');

function computech_admin_ensure_pages(): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    computech_seed_header_database_options();
    $version_key = '2026-06-16-routes-v3';
    if (get_option('computech_theme_pages_version') === $version_key) {
        return;
    }

    computech_ensure_theme_pages();
    update_option('computech_theme_pages_version', $version_key);
    flush_rewrite_rules(false);
}
add_action('admin_init', 'computech_admin_ensure_pages');

function computech_page_url(string $slug): string {
    $slug = trim($slug, '/');
    if ($slug === '') {
        return home_url('/');
    }

    $page = computech_find_page_by_slug($slug);
    if ($page) {
        return get_permalink($page);
    }

    return home_url('/' . $slug . '/');
}


function computech_whatsapp_url(string $message = ''): string {
    $number = computech_clean_phone(computech_header_setting('whatsapp_number', ''));
    if ($number === '') {
        return '';
    }
    $url = 'https://wa.me/' . $number;
    if ($message !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}

function computech_google_maps_url(): string {
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Computech كمبيوتيك');
}

function computech_social_url(string $platform): string {
    $urls = array(
        'facebook' => 'https://www.facebook.com/',
        'instagram' => 'https://www.instagram.com/',
        'youtube' => 'https://www.youtube.com/',
        'tiktok' => 'https://www.tiktok.com/',
        'linkedin' => 'https://www.linkedin.com/',
        'twitter' => 'https://x.com/',
    );
    return $urls[$platform] ?? computech_page_url('contact');
}

function computech_products_url(string $category = '', string $status = ''): string {
    $args = array();
    if ($category !== '') {
        $args['category'] = $category;
    }
    if ($status !== '') {
        $args['status'] = $status;
    }
    $url = computech_page_url('products');
    return $args ? add_query_arg($args, $url) : $url;
}

function computech_is_active_page(string $slug): bool {
    $slug = trim($slug, '/');
    if ($slug === '') {
        return is_front_page() || is_home();
    }

    if (is_page($slug)) {
        return true;
    }

    if ($slug === 'categories' && is_tax('product_category')) {
        return true;
    }

    if ($slug === 'products' && is_singular('products')) {
        return true;
    }

    $current_path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $target_path = trim((string) parse_url(computech_page_url($slug), PHP_URL_PATH), '/');
    return $target_path !== '' && strpos($current_path, $target_path) === 0;
}

function computech_clean_phone(string $phone): string {
    return preg_replace('/[^0-9]/', '', $phone) ?: '';
}

function computech_header_initial_settings(): array {
    // Empty schema only. Main navigation is edited from Appearance > Menus.
    return computech_header_empty_settings();
}

function computech_header_empty_settings(): array {
    return array(
        'search_placeholder' => '',
        'search_button_label' => '',
        'show_search' => '0',
        'show_account' => '0',
        'show_cart' => '0',
        'whatsapp_number' => '',
        'whatsapp_label' => '',
        'whatsapp_message' => '',
        'logo_aria_label' => '',
        'logo_alt_text' => '',
        'nav_aria_label' => '',
        'account_label' => '',
        'cart_label' => '',
        'more_menu_label' => '',
        'mobile_menu_button_label' => '',
        'mobile_menu_title' => '',
        'mobile_menu_close_label' => '',
    );
}

function computech_header_default_settings(): array {
    return computech_header_empty_settings();
}

function computech_seed_header_database_options(): void {
    // Create empty options only. Do not inject any front-end text/cards/links from code.
    if (get_option('computech_header_settings', null) === null) {
        add_option('computech_header_settings', computech_header_initial_settings(), '', false);
    }
    if (get_option('computech_header_topbar_items', null) === null) {
        add_option('computech_header_topbar_items', array(), '', false);
    }
    if (get_option('computech_header_logo_id', null) === null) {
        add_option('computech_header_logo_id', 0, '', false);
    }
}
add_action('init', 'computech_seed_header_database_options', 20);


function computech_header_settings(): array {
    $saved = get_option('computech_header_settings', array());
    return wp_parse_args(is_array($saved) ? $saved : array(), computech_header_empty_settings());
}

function computech_header_setting(string $key, string $default = ''): string {
    $settings = computech_header_settings();
    return array_key_exists($key, $settings) ? (string) $settings[$key] : $default;
}

function computech_header_bool(string $key, bool $default = false): bool {
    $settings = computech_header_settings();
    if (!array_key_exists($key, $settings)) {
        return $default;
    }
    return (string) $settings[$key] === '1';
}

function computech_header_label(string $key, string $fallback = ''): string {
    $value = trim(computech_header_setting($key, ''));
    return $value !== '' ? $value : $fallback;
}

function computech_admin_capability(): string {
    return 'manage_options';
}

function computech_header_icon_choices(): array {
    return array(
        'delivery' => array(
            'label' => 'توصيل',
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
        ),
        'warranty' => array(
            'label' => 'ضمان',
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
        ),
        'support' => array(
            'label' => 'دعم فني',
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
        ),
        'original' => array(
            'label' => 'أصلي',
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7L9 18l-5-5"/><path d="M12 2l2.6 5.3 5.9.9-4.3 4.2 1 5.9L12 15.5 6.8 18.3l1-5.9-4.3-4.2 5.9-.9L12 2z"/></svg>',
        ),
        'payment' => array(
            'label' => 'دفع',
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>',
        ),
        'maintenance' => array(
            'label' => 'صيانة',
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 0 0-5 5L3 18v3h3l6.7-6.7a4 4 0 0 0 5-5l-2.4 2.4-3-3 2.4-2.4z"/></svg>',
        ),
        'phone' => array(
            'label' => 'هاتف',
            'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.6a2 2 0 0 1-.45 2.11L8 9.66a16 16 0 0 0 6 6l1.23-1.23a2 2 0 0 1 2.11-.45c.83.29 1.7.5 2.6.62A2 2 0 0 1 22 16.92z"/></svg>',
        ),
    );
}

function computech_default_topbar_items(): array {
    return array();
}

function computech_get_topbar_items(): array {
    $items = get_option('computech_header_topbar_items', array());
    if (!is_array($items)) {
        $items = array();
    }
    $clean = array();
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $text = trim((string) ($item['text'] ?? ''));
        if ($text === '') {
            continue;
        }
        $clean[] = array(
            'show' => !empty($item['show']) ? '1' : '0',
            'text' => $text,
            'icon_choice' => sanitize_key((string) ($item['icon_choice'] ?? '')),
            'icon_id' => absint($item['icon_id'] ?? 0),
            'link' => esc_url_raw((string) ($item['link'] ?? '')),
        );
    }
    return $clean;
}

function computech_get_visible_topbar_items(): array {
    return array_values(array_filter(computech_get_topbar_items(), static function ($item): bool {
        return !empty($item['show']) && trim((string) $item['text']) !== '';
    }));
}

function computech_render_header_icon(array $item, string $class = 'benefit-icon-frame'): void {
    $icon_id = absint($item['icon_id'] ?? 0);
    if ($icon_id) {
        $src = wp_get_attachment_image_url($icon_id, 'thumbnail');
        if ($src) {
            echo '<span class="' . esc_attr($class) . '"><img src="' . esc_url($src) . '" alt="" loading="lazy"></span>';
            return;
        }
    }

    $choice = sanitize_key((string) ($item['icon_choice'] ?? ''));
    $icons = computech_header_icon_choices();
    if ($choice !== '' && isset($icons[$choice])) {
        echo '<span class="' . esc_attr($class) . '">' . $icons[$choice]['svg'] . '</span>';
    }
}

function computech_render_header_topbar(): void {
    $items = computech_get_visible_topbar_items();
    if (!$items) {
        return;
    }
    echo '<div class="benefits-strip computech-topbar" data-count="' . esc_attr((string) count($items)) . '"><div class="benefits-slider" data-topbar-slider><div class="benefits-track">';
    foreach ($items as $item) {
        $tag = !empty($item['link']) ? 'a' : 'div';
        $attrs = !empty($item['link']) ? ' href="' . esc_url($item['link']) . '"' : '';
        echo '<' . $tag . ' class="benefit-item topbar-slide"' . $attrs . '>';
        computech_render_header_icon($item);
        echo '<span class="benefit-text">' . esc_html($item['text']) . '</span>';
        echo '</' . $tag . '>';
    }
    echo '</div></div></div>';
}

/**
 * Main header navigation
 *
 * The main header menu is managed only from WordPress native menus:
 * Appearance > Menus, then assign the menu to "القائمة الرئيسية".
 * No header links are read from the custom Computech settings page anymore.
 */
function computech_get_primary_nav_menu_items(): array {
    $locations = get_nav_menu_locations();
    $menu_id = isset($locations['primary']) ? absint($locations['primary']) : 0;
    if (!$menu_id) {
        return array();
    }

    $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'publish'));
    if (!is_array($items)) {
        return array();
    }

    // Render the top level items only to keep the existing header layout stable.
    $items = array_values(array_filter($items, static function ($item): bool {
        return empty($item->menu_item_parent) || (string) $item->menu_item_parent === '0';
    }));

    usort($items, static function ($a, $b): int {
        return ((int) $a->menu_order) <=> ((int) $b->menu_order);
    });

    return $items;
}

function computech_is_wp_nav_item_active($item): bool {
    $classes = is_array($item->classes ?? null) ? $item->classes : array();
    $active_classes = array('current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current-menu-parent', 'current_page_parent');
    if (array_intersect($active_classes, $classes)) {
        return true;
    }

    $url = trim((string) ($item->url ?? ''));
    if ($url === '') {
        return false;
    }

    $target_path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
    $current_path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

    if ($target_path === '') {
        return is_front_page() || $current_path === '';
    }

    return $current_path === $target_path || strpos($current_path, $target_path . '/') === 0;
}

function computech_render_wp_nav_menu_link($item, string $class = 'nav-link', string $li_class = ''): void {
    $title = trim((string) ($item->title ?? ''));
    $url = trim((string) ($item->url ?? ''));
    if ($title === '' || $url === '') {
        return;
    }

    $active = computech_is_wp_nav_item_active($item) ? ' active' : '';
    $target = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
    $rel = !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
    $li_class_attr = $li_class !== '' ? ' class="' . esc_attr($li_class) . '"' : '';

    printf(
        '<li%s><a href="%s" class="%s%s"%s%s>%s</a></li>',
        $li_class_attr,
        esc_url($url),
        esc_attr($class),
        esc_attr($active),
        $target,
        $rel,
        esc_html($title)
    );
}

function computech_render_primary_links(string $class = 'nav-link'): void {
    $items = computech_get_primary_nav_menu_items();
    if (!$items) {
        if (current_user_can('edit_theme_options')) {
            echo '<li><a class="' . esc_attr($class) . '" href="' . esc_url(admin_url('nav-menus.php')) . '">اربط القائمة الرئيسية من المظهر ← القوائم</a></li>';
        }
        return;
    }

    $is_mobile = strpos($class, 'mobile') !== false;
    if (!$is_mobile && count($items) > 6) {
        $main = array_slice($items, 0, 6);
        $extra = array_slice($items, 6);

        foreach ($main as $item) {
            computech_render_wp_nav_menu_link($item, $class);
        }

        $extra_active = false;
        foreach ($extra as $item) {
            if (computech_is_wp_nav_item_active($item)) {
                $extra_active = true;
                break;
            }
        }

        $more_label = computech_header_label('more_menu_label', 'المزيد');
        echo '<li class="nav-more"><button class="' . esc_attr($class . ($extra_active ? ' active' : '')) . ' nav-more-toggle" type="button">' . esc_html($more_label) . ' <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></button><ul class="nav-more-menu">';
        foreach ($extra as $item) {
            computech_render_wp_nav_menu_link($item, 'nav-more-link');
        }
        echo '</ul></li>';
        return;
    }

    foreach ($items as $item) {
        computech_render_wp_nav_menu_link($item, $class);
    }
}

function computech_header_logo_html(): string {
    $logo_id = absint(get_option('computech_header_logo_id', 0));
    if (!$logo_id) {
        $logo_id = absint(get_theme_mod('custom_logo'));
    }
    $src = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    $alt = computech_header_label('logo_alt_text', get_bloginfo('name'));
    if ($src) {
        return '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" class="logo-img computech-header-logo-img">';
    }
    $site_name = trim((string) get_bloginfo('name'));
    if ($site_name !== '') {
        return '<span class="logo-text-fallback">' . esc_html($site_name) . '</span>';
    }
    return '';
}

function computech_admin_header_pages_options(): string {
    $pages = get_pages(array('sort_column' => 'menu_order,post_title', 'post_status' => 'publish'));
    $html = '<option value="0">اختر صفحة</option>';
    foreach ($pages as $page) {
        $html .= '<option value="' . esc_attr((string) $page->ID) . '">' . esc_html($page->post_title) . '</option>';
    }
    return $html;
}

function computech_admin_menu(): void {
    add_menu_page('إعدادات كمبيوتك', 'إعدادات كمبيوتك', computech_admin_capability(), 'computech-settings', 'computech_settings_page', 'dashicons-admin-customizer', 58);
}
add_action('admin_menu', 'computech_admin_menu');

function computech_admin_assets(string $hook): void {
    if ($hook !== 'toplevel_page_computech-settings') {
        return;
    }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'computech_admin_assets');

function computech_sanitize_topbar_items_from_post(array $rows): array {
    $items = array();
    foreach ($rows as $row) {
        if (!is_array($row)) { continue; }
        $text = sanitize_text_field(wp_unslash($row['text'] ?? ''));
        if ($text === '') { continue; }
        $items[] = array(
            'show' => !empty($row['show']) ? '1' : '0',
            'text' => $text,
            'icon_choice' => sanitize_key(wp_unslash($row['icon_choice'] ?? '')),
            'icon_id' => absint($row['icon_id'] ?? 0),
            'link' => esc_url_raw(wp_unslash($row['link'] ?? '')),
        );
    }
    return $items;
}


function computech_handle_settings_save(): void {
    if (!isset($_POST['computech_header_settings_nonce'])) {
        return;
    }
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_header_settings_nonce'])), 'computech_save_header_settings')) {
        wp_die(esc_html__('طلب غير آمن. برجاء إعادة تحميل الصفحة والمحاولة مرة أخرى.', 'computech'));
    }
    if (!current_user_can(computech_admin_capability())) {
        wp_die(esc_html__('غير مسموح لك بتعديل إعدادات الهيدر.', 'computech'));
    }

    $settings = array(
        'search_placeholder' => sanitize_text_field(wp_unslash($_POST['search_placeholder'] ?? '')),
        'search_button_label' => sanitize_text_field(wp_unslash($_POST['search_button_label'] ?? '')),
        'show_search' => !empty($_POST['show_search']) ? '1' : '0',
        'show_account' => !empty($_POST['show_account']) ? '1' : '0',
        'show_cart' => !empty($_POST['show_cart']) ? '1' : '0',
        'whatsapp_number' => computech_clean_phone(sanitize_text_field(wp_unslash($_POST['whatsapp_number'] ?? ''))),
        'whatsapp_label' => sanitize_text_field(wp_unslash($_POST['whatsapp_label'] ?? '')),
        'whatsapp_message' => sanitize_textarea_field(wp_unslash($_POST['whatsapp_message'] ?? '')),
        'logo_aria_label' => sanitize_text_field(wp_unslash($_POST['logo_aria_label'] ?? '')),
        'logo_alt_text' => sanitize_text_field(wp_unslash($_POST['logo_alt_text'] ?? '')),
        'nav_aria_label' => sanitize_text_field(wp_unslash($_POST['nav_aria_label'] ?? '')),
        'account_label' => sanitize_text_field(wp_unslash($_POST['account_label'] ?? '')),
        'cart_label' => sanitize_text_field(wp_unslash($_POST['cart_label'] ?? '')),
        'more_menu_label' => sanitize_text_field(wp_unslash($_POST['more_menu_label'] ?? '')),
        'mobile_menu_button_label' => sanitize_text_field(wp_unslash($_POST['mobile_menu_button_label'] ?? '')),
        'mobile_menu_title' => sanitize_text_field(wp_unslash($_POST['mobile_menu_title'] ?? '')),
        'mobile_menu_close_label' => sanitize_text_field(wp_unslash($_POST['mobile_menu_close_label'] ?? '')),
    );

    update_option('computech_header_settings', $settings);
    update_option('computech_header_logo_id', absint($_POST['header_logo_id'] ?? 0));
    update_option('computech_header_topbar_items', computech_sanitize_topbar_items_from_post(isset($_POST['topbar']) && is_array($_POST['topbar']) ? $_POST['topbar'] : array()));

    add_settings_error('computech_messages', 'computech_saved', 'تم حفظ إعدادات الهيدر بنجاح.', 'updated');
}
add_action('admin_init', 'computech_handle_settings_save');

function computech_admin_icon_select(string $name, string $selected): string {
    $html = '<select name="' . esc_attr($name) . '" class="ct-icon-choice"><option value="">بدون أيقونة جاهزة</option>';
    foreach (computech_header_icon_choices() as $key => $icon) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($icon['label']) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function computech_settings_page(): void {
    if (!current_user_can(computech_admin_capability())) {
        wp_die(esc_html__('غير مسموح لك بالدخول إلى إعدادات كمبيوتك.', 'computech'));
    }
    computech_seed_header_database_options();
    $settings = computech_header_settings();
    $logo_id = absint(get_option('computech_header_logo_id', 0));
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';
    $topbar_items = computech_get_topbar_items();
    $icons = computech_header_icon_choices();
    settings_errors('computech_messages');
    ?>
    <div class="wrap computech-admin-wrap" dir="rtl">
        <h1>إعدادات كمبيوتك - الهيدر</h1>
        <p>من هنا الأدمن يقدر يعدل الشريط العلوي، اللوجو، البحث، السلة، والواتساب. روابط الـ Main Header يتم تعديلها من المظهر ← القوائم وليس من هذه الصفحة.</p>
        <form method="post">
            <?php wp_nonce_field('computech_save_header_settings', 'computech_header_settings_nonce'); ?>

            <div class="ct-panel">
                <h2>اللوجو</h2>
                <p>اللوجو هنا له أولوية على WordPress Custom Logo. لو سيبته فاضي، الموقع يستخدم لوجو WordPress المحفوظ في الداشبورد، ولو لا يوجد لوجو يظهر اسم الموقع فقط.</p>
                <input type="hidden" name="header_logo_id" id="ct-header-logo-id" value="<?php echo esc_attr((string) $logo_id); ?>">
                <div class="ct-logo-preview" id="ct-header-logo-preview"><?php if ($logo_url) : ?><img src="<?php echo esc_url($logo_url); ?>" alt=""><?php else : ?><span>لا يوجد لوجو مخصص من هذه الصفحة</span><?php endif; ?></div>
                <button type="button" class="button" data-ct-media="logo">اختيار / تغيير اللوجو</button>
                <button type="button" class="button" data-ct-remove-logo>إزالة اللوجو</button>
            </div>

            <div class="ct-panel">
                <h2>Top Bar - شريط المميزات</h2>
                <p>مفيش حد أقصى للعناصر. أي عدد هيتعرض كسلايدر. لو مفيش عناصر ظاهرة، الشريط يختفي تلقائيًا.</p>
                <div id="ct-topbar-list">
                    <?php foreach ($topbar_items as $i => $item) : $preview = !empty($item['icon_id']) ? wp_get_attachment_image_url((int) $item['icon_id'], 'thumbnail') : ''; ?>
                        <div class="ct-row ct-topbar-row">
                            <div class="ct-row-head"><strong>عنصر Top Bar</strong><button type="button" class="button-link-delete ct-remove-row">حذف</button></div>
                            <label><input type="checkbox" name="topbar[<?php echo esc_attr((string) $i); ?>][show]" value="1" <?php checked(!empty($item['show'])); ?>> إظهار العنصر</label>
                            <label>النص<input type="text" name="topbar[<?php echo esc_attr((string) $i); ?>][text]" value="<?php echo esc_attr($item['text']); ?>" placeholder="مثال: توصيل سريع لجميع المدن"></label>
                            <label>رابط اختياري<input type="url" name="topbar[<?php echo esc_attr((string) $i); ?>][link]" value="<?php echo esc_attr($item['link']); ?>" placeholder="https://example.com"></label>
                            <label>اختيار أيقونة جاهزة<?php echo computech_admin_icon_select('topbar[' . esc_attr((string) $i) . '][icon_choice]', $item['icon_choice']); ?></label>
                            <input type="hidden" class="ct-icon-id" name="topbar[<?php echo esc_attr((string) $i); ?>][icon_id]" value="<?php echo esc_attr((string) $item['icon_id']); ?>">
                            <div class="ct-icon-preview"><?php if ($preview) : ?><img src="<?php echo esc_url($preview); ?>" alt=""><?php else : ?><span>لا توجد أيقونة مرفوعة</span><?php endif; ?></div>
                            <button type="button" class="button ct-upload-icon">رفع أيقونة خاصة</button>
                            <button type="button" class="button ct-remove-icon">إزالة الأيقونة المرفوعة</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" id="ct-add-topbar">+ إضافة عنصر Top Bar</button>
            </div>

            <div class="ct-panel">
                <h2>نصوص الهيدر الأساسية</h2>
                <p>هذه النصوص محفوظة في قاعدة بيانات WordPress وتستخدم بدل أي نص ثابت داخل الهيدر.</p>
                <label>Label اللوجو / aria-label<input type="text" name="logo_aria_label" value="<?php echo esc_attr($settings['logo_aria_label']); ?>"></label>
                <label>Alt اللوجو<input type="text" name="logo_alt_text" value="<?php echo esc_attr($settings['logo_alt_text']); ?>"></label>
                <label>Label القائمة الرئيسية<input type="text" name="nav_aria_label" value="<?php echo esc_attr($settings['nav_aria_label']); ?>"></label>
                <label>نص زر المزيد في القائمة<input type="text" name="more_menu_label" value="<?php echo esc_attr($settings['more_menu_label']); ?>"></label>
                <label>Label البحث<input type="text" name="search_button_label" value="<?php echo esc_attr($settings['search_button_label']); ?>"></label>
                <label>Label أيقونة الحساب<input type="text" name="account_label" value="<?php echo esc_attr($settings['account_label']); ?>"></label>
                <label>Label أيقونة السلة<input type="text" name="cart_label" value="<?php echo esc_attr($settings['cart_label']); ?>"></label>
                <label>Label زر منيو الموبايل<input type="text" name="mobile_menu_button_label" value="<?php echo esc_attr($settings['mobile_menu_button_label']); ?>"></label>
                <label>عنوان منيو الموبايل<input type="text" name="mobile_menu_title" value="<?php echo esc_attr($settings['mobile_menu_title']); ?>"></label>
                <label>Label زر إغلاق منيو الموبايل<input type="text" name="mobile_menu_close_label" value="<?php echo esc_attr($settings['mobile_menu_close_label']); ?>"></label>
            </div>

            <div class="ct-panel">
                <h2>البحث / السلة / واتساب</h2>
                <label><input type="checkbox" name="show_search" value="1" <?php checked(computech_header_bool('show_search')); ?>> إظهار البحث</label>
                <label>Placeholder البحث<input type="text" name="search_placeholder" value="<?php echo esc_attr($settings['search_placeholder']); ?>"></label>
                <label><input type="checkbox" name="show_account" value="1" <?php checked(computech_header_bool('show_account')); ?>> إظهار أيقونة الحساب</label>
                <label><input type="checkbox" name="show_cart" value="1" <?php checked(computech_header_bool('show_cart')); ?>> إظهار أيقونة السلة</label>
                <label>رقم واتساب بدون +<input type="text" name="whatsapp_number" value="<?php echo esc_attr($settings['whatsapp_number']); ?>" placeholder="966501234567"></label>
                <label>نص زر واتساب<input type="text" name="whatsapp_label" value="<?php echo esc_attr($settings['whatsapp_label']); ?>"></label>
                <label>رسالة واتساب الافتراضية<textarea name="whatsapp_message" rows="3"><?php echo esc_textarea($settings['whatsapp_message']); ?></textarea></label>
            </div>

            <?php submit_button('حفظ إعدادات الهيدر'); ?>
        </form>
    </div>

    <template id="ct-topbar-template">
        <div class="ct-row ct-topbar-row">
            <div class="ct-row-head"><strong>عنصر Top Bar</strong><button type="button" class="button-link-delete ct-remove-row">حذف</button></div>
            <label><input type="checkbox" name="topbar[__i__][show]" value="1" checked> إظهار العنصر</label>
            <label>النص<input type="text" name="topbar[__i__][text]" value="" placeholder="مثال: توصيل سريع لجميع المدن"></label>
            <label>رابط اختياري<input type="url" name="topbar[__i__][link]" value="" placeholder="https://example.com"></label>
            <label>اختيار أيقونة جاهزة<?php echo computech_admin_icon_select('topbar[__i__][icon_choice]', ''); ?></label>
            <input type="hidden" class="ct-icon-id" name="topbar[__i__][icon_id]" value="0">
            <div class="ct-icon-preview"><span>لا توجد أيقونة مرفوعة</span></div>
            <button type="button" class="button ct-upload-icon">رفع أيقونة خاصة</button>
            <button type="button" class="button ct-remove-icon">إزالة الأيقونة المرفوعة</button>
        </div>
    </template>

    <style>
        .computech-admin-wrap { max-width: 1120px; }
        .ct-panel { background:#fff; border:1px solid #dcdcde; border-radius:14px; padding:18px; margin:18px 0; box-shadow:0 6px 20px rgba(0,0,0,.03); }
        .ct-panel h2 { margin-top:0; }
        .ct-row { border:1px solid #e5e7eb; border-radius:12px; padding:14px; margin:12px 0; background:#f9fafb; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; align-items:end; }
        .ct-row-head { grid-column:1 / -1; display:flex; justify-content:space-between; align-items:center; }
        .ct-row label, .ct-panel label { display:block; font-weight:700; margin:8px 0; }
        .ct-row input[type=text], .ct-row input[type=url], .ct-row select, .ct-panel input[type=text], .ct-panel textarea { width:100%; margin-top:6px; }
        .ct-logo-preview, .ct-icon-preview { width:120px; height:70px; display:flex; align-items:center; justify-content:center; background:#fff; border:1px dashed #ccd0d4; border-radius:10px; margin:10px 0; overflow:hidden; color:#64748b; font-size:12px; text-align:center; }
        .ct-logo-preview img, .ct-icon-preview img { max-width:100%; max-height:100%; object-fit:contain; }
        @media (max-width: 782px) { .ct-row { grid-template-columns:1fr; } }
    </style>
    <script>
    (function($){
        var topIndex = <?php echo (int) count($topbar_items); ?>;
        $('#ct-add-topbar').on('click', function(){
            var html = $('#ct-topbar-template').html().replaceAll('__i__', topIndex++);
            $('#ct-topbar-list').append(html);
        });
        $(document).on('click', '.ct-remove-row', function(){ $(this).closest('.ct-row').remove(); });
        $('[data-ct-media="logo"]').on('click', function(e){
            e.preventDefault();
            var frame = wp.media({ title:'اختر اللوجو', button:{ text:'استخدام اللوجو' }, multiple:false });
            frame.on('select', function(){
                var file = frame.state().get('selection').first().toJSON();
                $('#ct-header-logo-id').val(file.id);
                $('#ct-header-logo-preview').html('<img src="' + (file.sizes && file.sizes.thumbnail ? file.sizes.thumbnail.url : file.url) + '" alt="">');
            });
            frame.open();
        });
        $('[data-ct-remove-logo]').on('click', function(){ $('#ct-header-logo-id').val('0'); $('#ct-header-logo-preview').html('<span>لا يوجد لوجو مخصص من هذه الصفحة</span>'); });
        $(document).on('click', '.ct-upload-icon', function(e){
            e.preventDefault();
            var row = $(this).closest('.ct-row');
            var frame = wp.media({ title:'اختر أيقونة', button:{ text:'استخدام الأيقونة' }, multiple:false });
            frame.on('select', function(){
                var file = frame.state().get('selection').first().toJSON();
                row.find('.ct-icon-id').val(file.id);
                row.find('.ct-icon-preview').html('<img src="' + (file.sizes && file.sizes.thumbnail ? file.sizes.thumbnail.url : file.url) + '" alt="">');
            });
            frame.open();
        });
        $(document).on('click', '.ct-remove-icon', function(){
            var row = $(this).closest('.ct-row');
            row.find('.ct-icon-id').val('0');
            row.find('.ct-icon-preview').html('<span>لا توجد أيقونة مرفوعة</span>');
        });
    })(jQuery);
    </script>
    <?php
}



/* ============================================
   Computech Admin Image Upload Helpers
   ============================================ */
function computech_admin_enqueue_media_tools($hook): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $post_types = array('computech_hero_slide', 'computech_hero_card', 'computech_need_card', 'computech_home_cat_card');
    if ($screen && !empty($screen->post_type) && in_array($screen->post_type, $post_types, true)) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'computech_admin_enqueue_media_tools');

function computech_attachment_image_data(int $attachment_id, string $size = 'full'): array {
    if ($attachment_id <= 0) {
        return array('id' => 0, 'url' => '', 'alt' => '');
    }
    $url = wp_get_attachment_image_url($attachment_id, $size);
    if (!$url) {
        $url = wp_get_attachment_url($attachment_id);
    }
    $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    if ($alt === '') {
        $attachment = get_post($attachment_id);
        if ($attachment) {
            $alt = trim((string) $attachment->post_title);
        }
    }
    return array('id' => $attachment_id, 'url' => $url ? (string) $url : '', 'alt' => $alt);
}

function computech_post_image_data(int $post_id, string $custom_meta_key, string $size = 'full', string $legacy_url_key = ''): array {
    $custom_id = absint(get_post_meta($post_id, $custom_meta_key, true));
    if ($custom_id > 0) {
        return computech_attachment_image_data($custom_id, $size);
    }
    $thumb_id = absint(get_post_thumbnail_id($post_id));
    if ($thumb_id > 0) {
        return computech_attachment_image_data($thumb_id, $size);
    }
    if ($legacy_url_key !== '') {
        $legacy_url = trim((string) get_post_meta($post_id, $legacy_url_key, true));
        if ($legacy_url !== '') {
            return array('id' => 0, 'url' => $legacy_url, 'alt' => get_the_title($post_id));
        }
    }
    return array('id' => 0, 'url' => '', 'alt' => '');
}

function computech_admin_media_script_once(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <script>
    (function(){
        if (window.computechMediaFieldReady) { return; }
        window.computechMediaFieldReady = true;
        document.addEventListener('click', function(e){
            var selectBtn = e.target.closest('[data-ct-select-media]');
            var removeBtn = e.target.closest('[data-ct-remove-media]');
            if (removeBtn) {
                e.preventDefault();
                var removeField = removeBtn.closest('[data-ct-media-field]');
                if (!removeField) { return; }
                var hiddenRemove = removeField.querySelector('input[type="hidden"]');
                var previewRemove = removeField.querySelector('[data-ct-media-preview]');
                var infoRemove = removeField.querySelector('[data-ct-media-info]');
                if (hiddenRemove) { hiddenRemove.value = ''; }
                if (previewRemove) { previewRemove.innerHTML = '<span class="ct-media-empty">لم يتم اختيار صورة</span>'; }
                if (infoRemove) { infoRemove.textContent = 'سيتم استخدام بيانات الصورة من Media Library تلقائيًا بعد اختيارها.'; }
                return;
            }
            if (!selectBtn) { return; }
            e.preventDefault();
            var field = selectBtn.closest('[data-ct-media-field]');
            if (!field || !window.wp || !wp.media) { return; }
            var frame = wp.media({
                title: field.getAttribute('data-title') || 'اختيار صورة',
                button: { text: field.getAttribute('data-button') || 'استخدام هذه الصورة' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                var hidden = field.querySelector('input[type="hidden"]');
                var preview = field.querySelector('[data-ct-media-preview]');
                var info = field.querySelector('[data-ct-media-info]');
                var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                if (hidden) { hidden.value = attachment.id || ''; }
                if (preview) { preview.innerHTML = '<img src="' + url + '" alt="">'; }
                if (info) {
                    var alt = attachment.alt || attachment.title || attachment.filename || '';
                    info.textContent = alt ? ('سيتم استخدام Alt/Title من الصورة: ' + alt) : 'الصورة مختارة، ويمكنك تعديل Alt Text من Media Library.';
                }
            });
            frame.open();
        });
    })();
    </script>
    <?php
}

function computech_admin_image_upload_field(string $label, string $meta_key, int $post_id, string $help = ''): void {
    $image_id = absint(get_post_meta($post_id, $meta_key, true));
    $image = $image_id > 0 ? computech_attachment_image_data($image_id, 'medium') : array('id' => 0, 'url' => '', 'alt' => '');
    ?>
    <div class="ct-field ct-media-field" data-ct-media-field data-title="<?php echo esc_attr($label); ?>" data-button="استخدام هذه الصورة">
        <label><?php echo esc_html($label); ?></label>
        <input type="hidden" name="<?php echo esc_attr($meta_key); ?>" value="<?php echo esc_attr((string) $image_id); ?>">
        <div class="ct-media-preview" data-ct-media-preview>
            <?php if ($image['url'] !== '') : ?>
                <img src="<?php echo esc_url($image['url']); ?>" alt="">
            <?php else : ?>
                <span class="ct-media-empty">لم يتم اختيار صورة</span>
            <?php endif; ?>
        </div>
        <div class="ct-media-actions">
            <button type="button" class="button" data-ct-select-media>اختيار / تغيير الصورة</button>
            <button type="button" class="button-link-delete" data-ct-remove-media>إزالة الصورة</button>
        </div>
        <span class="ct-help" data-ct-media-info><?php echo esc_html($help !== '' ? $help : 'ارفع أو اختر صورة من Media Library. سيتم استخدام Alt Text وTitle من بيانات الصورة تلقائيًا.'); ?></span>
    </div>
    <?php
    computech_admin_media_script_once();
}

/* ============================================
   Dynamic Hero Section
   ============================================ */
function computech_register_hero_slides_cpt(): void {
    register_post_type('computech_hero_slide', array(
        'labels' => array(
            'name' => 'Hero Section',
            'singular_name' => 'Hero Section',
            'menu_name' => 'Hero Section',
            'add_new_item' => 'إضافة Hero Section',
            'edit_item' => 'تعديل Hero Section',
            'new_item' => 'Hero Section',
            'view_item' => 'عرض Hero Section',
            'search_items' => 'بحث في Hero Section',
            'not_found' => 'لا يوجد Hero Section',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-cover-image',
        'supports' => array('title', 'thumbnail', 'page-attributes'),
        'capability_type' => 'page',
        'map_meta_cap' => true,
        'show_in_rest' => false,
    ));
}
add_action('init', 'computech_register_hero_slides_cpt');

function computech_default_hero_slide_meta(): array {
    // Empty dashboard defaults only. The front end reads saved Hero Section posts/meta.
    return array(
        '_computech_hero_show' => '1',
        '_computech_hero_title_line_1' => '',
        '_computech_hero_title_highlight' => '',
        '_computech_hero_title_line_3' => '',
        '_computech_hero_description' => '',
        '_computech_hero_features' => '',
        '_computech_hero_primary_text' => '',
        '_computech_hero_primary_link_type' => 'none',
        '_computech_hero_primary_page_slug' => '',
        '_computech_hero_primary_page_id' => '0',
        '_computech_hero_primary_url' => '',
        '_computech_hero_primary_new_tab' => '0',
        '_computech_hero_secondary_text' => '',
        '_computech_hero_secondary_link_type' => 'none',
        '_computech_hero_secondary_page_slug' => '',
        '_computech_hero_secondary_page_id' => '0',
        '_computech_hero_secondary_url' => '',
        '_computech_hero_secondary_new_tab' => '0',
        '_computech_hero_whatsapp_message' => '',
        '_computech_hero_image_url' => '',
        '_computech_hero_image_alt' => '',
        '_computech_hero_badge_line_1' => '',
        '_computech_hero_badge_line_2' => '',
        '_computech_hero_buttons' => array(),
    );
}

function computech_seed_default_hero_slides(): void {
    // Disabled intentionally: Hero Section records must be added/edited from the dashboard.
}

function computech_add_hero_slide_metaboxes(): void {
    add_meta_box('computech_hero_slide_data', 'بيانات Hero Section', 'computech_hero_slide_metabox', 'computech_hero_slide', 'normal', 'high');
}
add_action('add_meta_boxes', 'computech_add_hero_slide_metaboxes');

function computech_hero_meta(WP_Post $post, string $key, string $default = ''): string {
    $value = get_post_meta($post->ID, $key, true);
    return $value === '' ? $default : (string) $value;
}

function computech_hero_pages_options(string $selected = ''): string {
    $pages = get_pages(array('sort_column' => 'menu_order,post_title', 'post_status' => 'publish'));
    $html = '<option value="0">اختر صفحة</option>';
    $html .= '<option value="home" ' . selected($selected, 'home', false) . '>الصفحة الرئيسية</option>';
    foreach ($pages as $page) {
        $html .= '<option value="' . esc_attr((string) $page->ID) . '" ' . selected($selected, (string) $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
    }
    return $html;
}

function computech_hero_link_type_select(string $name, string $selected): string {
    $types = array(
        'none' => 'بدون رابط',
        'home' => 'الصفحة الرئيسية',
        'page' => 'صفحة داخل الموقع',
        'category' => 'قسم منتجات',
        'custom' => 'رابط خارجي / عام',
        'whatsapp' => 'واتساب',
    );
    $html = '<select name="' . esc_attr($name) . '" class="ct-hero-link-type">';
    foreach ($types as $key => $label) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function computech_hero_button_style_select(string $name, string $selected): string {
    $styles = array(
        'primary' => 'زر أساسي أزرق',
        'secondary' => 'زر ثانوي أبيض',
        'whatsapp' => 'زر واتساب',
    );
    $html = '<select name="' . esc_attr($name) . '" class="widefat">';
    foreach ($styles as $key => $label) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function computech_hero_product_category_options(string $selected = ''): string {
    $terms = get_terms(array('taxonomy' => 'product_category', 'hide_empty' => false));
    $html = '<option value="0">اختر قسم</option>';
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $html .= '<option value="' . esc_attr((string) $term->term_id) . '" ' . selected($selected, (string) $term->term_id, false) . '>' . esc_html($term->name) . '</option>';
        }
    }
    return $html;
}

function computech_default_hero_buttons(): array {
    return array();
}

function computech_normalize_hero_button(array $button): array {
    $style = sanitize_key((string) ($button['style'] ?? 'secondary'));
    $type = sanitize_key((string) ($button['link_type'] ?? 'none'));
    return array(
        'show' => !empty($button['show']) ? '1' : '0',
        'text' => sanitize_text_field((string) ($button['text'] ?? '')),
        'style' => in_array($style, array('primary', 'secondary', 'whatsapp'), true) ? $style : 'secondary',
        'link_type' => in_array($type, array('none', 'home', 'page', 'category', 'custom', 'whatsapp'), true) ? $type : 'none',
        'page_slug' => sanitize_title((string) ($button['page_slug'] ?? '')),
        'page_id' => (string) absint($button['page_id'] ?? 0),
        'term_id' => (string) absint($button['term_id'] ?? 0),
        'url' => esc_url_raw((string) ($button['url'] ?? '')),
        'new_tab' => !empty($button['new_tab']) ? '1' : '0',
    );
}

function computech_get_hero_buttons(WP_Post $slide): array {
    $stored = get_post_meta($slide->ID, '_computech_hero_buttons', true);
    if (is_array($stored)) {
        $buttons = array();
        foreach ($stored as $button) {
            if (!is_array($button)) {
                continue;
            }
            $clean = computech_normalize_hero_button($button);
            if ($clean['text'] !== '') {
                $buttons[] = $clean;
            }
        }
        return $buttons;
    }

    $buttons = array();

    foreach (array('primary', 'secondary') as $prefix) {
        $text = computech_hero_meta($slide, '_computech_hero_' . $prefix . '_text', '');
        if (trim($text) === '') {
            continue;
        }
        $buttons[] = computech_normalize_hero_button(array(
            'show' => '1',
            'text' => $text,
            'style' => $prefix === 'primary' ? 'primary' : 'whatsapp',
            'link_type' => computech_hero_meta($slide, '_computech_hero_' . $prefix . '_link_type', 'none'),
            'page_id' => computech_hero_meta($slide, '_computech_hero_' . $prefix . '_page_id', '0'),
            'page_slug' => computech_hero_meta($slide, '_computech_hero_' . $prefix . '_page_slug', ''),
            'term_id' => '0',
            'url' => computech_hero_meta($slide, '_computech_hero_' . $prefix . '_url', ''),
            'new_tab' => computech_hero_meta($slide, '_computech_hero_' . $prefix . '_new_tab', '0'),
        ));
    }
    return $buttons ?: computech_default_hero_buttons();
}

function computech_admin_editor_styles_once(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <style>
        .ct-editor { direction: rtl; color:#111827; }
        .ct-editor * { box-sizing:border-box; }
        .ct-editor .ct-hero-dashboard { display:grid; gap:18px; }
        .ct-editor .ct-hero-dashboard-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:18px; border-radius:18px; background:linear-gradient(135deg,#eef4ff,#ffffff); border:1px solid #dbe7ff; margin-bottom:16px; }
        .ct-editor .ct-hero-dashboard-head h2 { margin:0 0 8px; font-size:20px; line-height:1.4; color:#12326b; }
        .ct-editor .ct-hero-dashboard-head p { margin:0; color:#526070; max-width:760px; }
        .ct-editor .ct-status-card { min-width:230px; background:#fff; border:1px solid #d7e2f2; border-radius:14px; padding:12px 14px; box-shadow:0 8px 24px rgba(15,23,42,.06); }
        .ct-editor .ct-status-card label { display:flex; align-items:center; gap:8px; font-weight:700; margin:0; }
        .ct-editor .ct-status-card .description { margin:8px 0 0; color:#64748b; font-size:12px; }
        .ct-editor .ct-admin-section { background:#fff; border:1px solid #dfe5ef; border-radius:18px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,.045); }
        .ct-editor .ct-admin-section-head { padding:16px 18px; border-bottom:1px solid #eef2f7; background:#fbfdff; display:flex; align-items:flex-start; justify-content:space-between; gap:14px; }
        .ct-editor .ct-admin-section-head h3 { margin:0; font-size:16px; color:#0f2d5c; }
        .ct-editor .ct-admin-section-head p { margin:6px 0 0; color:#64748b; font-size:13px; }
        .ct-editor .ct-admin-section-body { padding:18px; }
        .ct-editor .ct-grid { display:grid; gap:14px; }
        .ct-editor .ct-grid-2 { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .ct-editor .ct-grid-3 { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .ct-editor .ct-field { margin:0; }
        .ct-editor .ct-field label, .ct-editor label.ct-field-label { display:block; margin:0 0 7px; font-weight:700; color:#1f2937; }
        .ct-editor .ct-field input[type="text"], .ct-editor .ct-field input[type="url"], .ct-editor .ct-field select, .ct-editor .ct-field textarea { width:100%; min-height:38px; border-color:#cfd7e3; border-radius:10px; }
        .ct-editor .ct-field textarea { min-height:92px; }
        .ct-editor .ct-media-field { background:#fff; border:1px solid #dfe7f2; border-radius:16px; padding:14px; }
        .ct-editor .ct-media-preview { min-height:120px; border:1px dashed #b9c7d9; border-radius:14px; display:flex; align-items:center; justify-content:center; background:#f8fafc; overflow:hidden; margin-top:8px; }
        .ct-editor .ct-media-preview img { max-width:100%; max-height:160px; width:auto; height:auto; object-fit:contain; display:block; }
        .ct-editor .ct-media-empty { color:#64748b; font-size:13px; }
        .ct-editor .ct-media-actions { display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:10px; }

        .ct-editor .ct-help { display:block; margin-top:6px; color:#64748b; font-size:12px; line-height:1.6; }
        .ct-editor .ct-note { display:flex; gap:10px; align-items:flex-start; padding:12px 14px; border-radius:14px; margin-bottom:14px; background:#fff8e6; border:1px solid #f2d99b; color:#59450d; }
        .ct-editor .ct-note strong { color:#3f3007; }
        .ct-editor .ct-repeat-list { display:grid; gap:12px; }
        .ct-editor .ct-hero-repeat-row { border:1px solid #dfe7f2; border-radius:16px; background:#f8fafc; overflow:hidden; }
        .ct-editor .ct-hero-repeat-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:13px 14px; background:#fff; border-bottom:1px solid #e8eef6; }
        .ct-editor .ct-hero-repeat-title { display:flex; align-items:center; gap:10px; font-weight:800; color:#172554; }
        .ct-editor .ct-drag-handle { width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; background:#edf4ff; color:#1d4ed8; font-size:16px; }
        .ct-editor .ct-repeat-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .ct-editor .ct-hero-repeat-grid { padding:14px; display:grid; grid-template-columns:1.1fr .7fr .8fr; gap:14px; align-items:end; }
        .ct-editor .ct-hero-repeat-grid .ct-span-2 { grid-column:span 2; }
        .ct-editor .ct-hero-repeat-grid .ct-span-full { grid-column:1/-1; }
        .ct-editor .ct-conditional-fields { grid-column:1/-1; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
        .ct-editor .button.ct-add-button { border-radius:10px; min-height:38px; padding:2px 16px; font-weight:700; }
        .ct-editor .button-link-delete { color:#b42318; text-decoration:none; font-weight:700; }
        .ct-editor .button-link-delete:hover { color:#7a271a; }
        .ct-editor .ct-card-preview-info { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .ct-editor .ct-mini-info { padding:12px; border-radius:14px; background:#f8fafc; border:1px solid #e5eaf2; }
        .ct-editor .ct-mini-info strong { display:block; color:#0f2d5c; margin-bottom:4px; }
        @media (max-width: 1100px) { .ct-editor .ct-grid-3, .ct-editor .ct-hero-repeat-grid, .ct-editor .ct-conditional-fields, .ct-editor .ct-card-preview-info { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width: 782px) { .ct-editor .ct-hero-dashboard-head { flex-direction:column; } .ct-editor .ct-status-card { min-width:0; width:100%; } .ct-editor .ct-grid-2, .ct-editor .ct-grid-3, .ct-editor .ct-hero-repeat-grid, .ct-editor .ct-conditional-fields, .ct-editor .ct-card-preview-info { grid-template-columns:1fr; } .ct-editor .ct-hero-repeat-grid .ct-span-2 { grid-column:auto; } }
    </style>
    <?php
}

function computech_hero_button_row_html(array $button, $index): string {
    $button = computech_normalize_hero_button($button);
    $base = '_computech_hero_buttons[' . $index . ']';
    ob_start();
    ?>
    <div class="ct-hero-repeat-row" data-hero-button-row>
        <div class="ct-hero-repeat-head">
            <div class="ct-hero-repeat-title">
                <span class="ct-drag-handle">☰</span>
                <span>زر في الهيرو</span>
            </div>
            <div class="ct-repeat-actions">
                <label><input type="checkbox" name="<?php echo esc_attr($base); ?>[show]" value="1" <?php checked($button['show'], '1'); ?>> إظهار الزر</label>
                <button type="button" class="button-link-delete" data-remove-hero-button>حذف الزر</button>
            </div>
        </div>
        <div class="ct-hero-repeat-grid">
            <p class="ct-field ct-span-2">
                <label>نص الزر</label>
                <input type="text" name="<?php echo esc_attr($base); ?>[text]" value="<?php echo esc_attr($button['text']); ?>" class="widefat" placeholder="مثال: تصفح المنتجات">
                <span class="ct-help">لو تركت النص فاضي، الزر لن يظهر في الموقع.</span>
            </p>
            <p class="ct-field">
                <label>شكل الزر</label>
                <?php echo computech_hero_button_style_select($base . '[style]', $button['style']); ?>
            </p>
            <p class="ct-field">
                <label>نوع الرابط</label>
                <?php echo computech_hero_link_type_select($base . '[link_type]', $button['link_type']); ?>
            </p>
            <p class="ct-field">
                <label><input type="checkbox" name="<?php echo esc_attr($base); ?>[new_tab]" value="1" <?php checked($button['new_tab'], '1'); ?>> فتح في تبويب جديد</label>
                <span class="ct-help">يفضل تفعيلها للروابط الخارجية فقط.</span>
            </p>
            <div class="ct-conditional-fields">
                <p class="ct-field ct-link-page-field">
                    <label>اختيار صفحة</label>
                    <select name="<?php echo esc_attr($base); ?>[page_id]" class="widefat"><?php echo computech_hero_pages_options($button['link_type'] === 'home' ? 'home' : $button['page_id']); ?></select>
                </p>
                <p class="ct-field ct-link-category-field">
                    <label>اختيار قسم منتجات</label>
                    <select name="<?php echo esc_attr($base); ?>[term_id]" class="widefat"><?php echo computech_hero_product_category_options($button['term_id']); ?></select>
                </p>
                <p class="ct-field ct-link-url-field">
                    <label>رابط خارجي / عام</label>
                    <input type="url" name="<?php echo esc_attr($base); ?>[url]" value="<?php echo esc_attr($button['url']); ?>" class="widefat" placeholder="https://example.com أو /products/">
                </p>
            </div>
        </div>
        <input type="hidden" name="<?php echo esc_attr($base); ?>[page_slug]" value="<?php echo esc_attr($button['page_slug']); ?>">
    </div>
    <?php
    return (string) ob_get_clean();
}

function computech_hero_slide_metabox(WP_Post $post): void {
    wp_nonce_field('computech_save_hero_slide', 'computech_hero_slide_nonce');
    computech_admin_editor_styles_once();
    $defaults = computech_default_hero_slide_meta();
    $buttons = computech_get_hero_buttons($post);
    $empty_button = array('show' => '1', 'text' => '', 'style' => 'secondary', 'link_type' => 'none', 'page_id' => '0', 'term_id' => '0', 'url' => '', 'new_tab' => '0');
    ?>
    <div class="ct-editor ct-hero-admin" dir="rtl">
        <div class="ct-hero-dashboard-head">
            <div>
                <h2>ترتيب محتوى Hero Section</h2>
                <p>رتبتلك الحقول حسب طريقة تعديل الأدمن: حالة الهيرو، النص، الأزرار، الصورة، البادج، وواتساب.</p>
            </div>
            <div class="ct-status-card">
                <label><input type="checkbox" name="_computech_hero_show" value="1" <?php checked(computech_hero_meta($post, '_computech_hero_show', '1'), '1'); ?>> إظهار Hero Section</label>
                <p class="description">لو اتقفلت، الهيرو مش هيظهر في الصفحة الرئيسية.</p>
            </div>
        </div>

        <div class="ct-hero-dashboard">
            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>1. النص الرئيسي</h3>
                        <p>هنا النصوص اللي بتظهر في الجزء الكبير من الهيرو.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-grid ct-grid-3">
                        <p class="ct-field"><label>سطر العنوان الأول</label><input type="text" name="_computech_hero_title_line_1" value="<?php echo esc_attr(computech_hero_meta($post, '_computech_hero_title_line_1', $defaults['_computech_hero_title_line_1'])); ?>" class="widefat"></p>
                        <p class="ct-field"><label>الكلمة / السطر المميز باللون</label><input type="text" name="_computech_hero_title_highlight" value="<?php echo esc_attr(computech_hero_meta($post, '_computech_hero_title_highlight', $defaults['_computech_hero_title_highlight'])); ?>" class="widefat"></p>
                        <p class="ct-field"><label>سطر العنوان الثالث</label><input type="text" name="_computech_hero_title_line_3" value="<?php echo esc_attr(computech_hero_meta($post, '_computech_hero_title_line_3', $defaults['_computech_hero_title_line_3'])); ?>" class="widefat"></p>
                    </div>
                    <div class="ct-grid ct-grid-2" style="margin-top:14px">
                        <p class="ct-field"><label>الوصف</label><textarea name="_computech_hero_description" rows="4" class="widefat"><?php echo esc_textarea(computech_hero_meta($post, '_computech_hero_description', $defaults['_computech_hero_description'])); ?></textarea><span class="ct-help">يفضل وصف قصير وواضح؛ التصميم يحمي النص الطويل لكن الأفضل مايزيدش قوي.</span></p>
                        <p class="ct-field"><label>Feature Pills / البادجات الصغيرة</label><textarea name="_computech_hero_features" rows="4" class="widefat"><?php echo esc_textarea(computech_hero_meta($post, '_computech_hero_features', $defaults['_computech_hero_features'])); ?></textarea><span class="ct-help">اكتب كل badge في سطر منفصل.</span></p>
                    </div>
                </div>
            </section>

            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>2. أزرار الهيرو</h3>
                        <p>تقدر تضيف أي عدد أزرار، تخفي زر، أو تحذفه. الروابط بتظهر حسب نوع الرابط المختار.</p>
                    </div>
                    <button type="button" class="button button-primary ct-add-button" id="ct-add-hero-button">+ إضافة زر</button>
                </div>
                <div class="ct-admin-section-body">
                    <div id="ct-hero-buttons-list" class="ct-repeat-list">
                        <?php foreach ($buttons as $i => $button) { echo computech_hero_button_row_html($button, (int) $i); } ?>
                    </div>
                    <template id="ct-hero-button-template"><?php echo computech_hero_button_row_html($empty_button, '__INDEX__'); ?></template>
                </div>
            </section>

            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>3. الصورة والبادج العائم</h3>
                        <p>ارفع صورة الهيرو من هنا، وسيتم أخذ Alt Text وTitle من بيانات الصورة داخل Media Library تلقائيًا.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-note"><span>ℹ️</span><div>ارفع أو اختر صورة الهيرو من Media Library. الواجهة ستستخدم Alt Text وTitle من بيانات الصورة تلقائيًا بدل إدخالهم يدويًا.</div></div>
                    <div class="ct-grid ct-grid-2">
                        <?php computech_admin_image_upload_field('صورة Hero Section', '_computech_hero_image_id', $post->ID, 'اختار صورة الهيرو من Media Library. عدّل Alt Text من صفحة الصورة نفسها لو محتاج SEO أفضل.'); ?>
                        <div class="ct-grid ct-grid-1">
                            <p class="ct-field"><label>السطر الأول في البادج العائم</label><input type="text" name="_computech_hero_badge_line_1" value="<?php echo esc_attr(computech_hero_meta($post, '_computech_hero_badge_line_1', $defaults['_computech_hero_badge_line_1'])); ?>" class="widefat"></p>
                            <p class="ct-field"><label>السطر الثاني في البادج العائم</label><input type="text" name="_computech_hero_badge_line_2" value="<?php echo esc_attr(computech_hero_meta($post, '_computech_hero_badge_line_2', $defaults['_computech_hero_badge_line_2'])); ?>" class="widefat"></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>4. إعدادات واتساب</h3>
                        <p>هذه الرسالة تستخدم لأي زر نوع رابطه واتساب داخل Hero Section.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <p class="ct-field"><label>رسالة واتساب</label><textarea name="_computech_hero_whatsapp_message" rows="3" class="widefat"><?php echo esc_textarea(computech_hero_meta($post, '_computech_hero_whatsapp_message', $defaults['_computech_hero_whatsapp_message'])); ?></textarea></p>
                </div>
            </section>
        </div>
    </div>
    <script>
    (function(){
        var list = document.getElementById('ct-hero-buttons-list');
        var addBtn = document.getElementById('ct-add-hero-button');
        var template = document.getElementById('ct-hero-button-template');
        if (!list || !addBtn || !template) { return; }
        function updateConditionalFields(scope) {
            (scope || list).querySelectorAll('[data-hero-button-row]').forEach(function(row){
                var typeSelect = row.querySelector('.ct-hero-link-type');
                var type = typeSelect ? typeSelect.value : 'none';
                var pageField = row.querySelector('.ct-link-page-field');
                var categoryField = row.querySelector('.ct-link-category-field');
                var urlField = row.querySelector('.ct-link-url-field');
                if (pageField) { pageField.style.display = (type === 'page' || type === 'home') ? '' : 'none'; }
                if (categoryField) { categoryField.style.display = type === 'category' ? '' : 'none'; }
                if (urlField) { urlField.style.display = type === 'custom' ? '' : 'none'; }
            });
        }
        function bindRow(scope) {
            (scope || list).querySelectorAll('[data-remove-hero-button]').forEach(function(btn){
                if (btn.dataset.bound === '1') { return; }
                btn.dataset.bound = '1';
                btn.addEventListener('click', function(){
                    var row = btn.closest('[data-hero-button-row]');
                    if (row) { row.remove(); }
                });
            });
            (scope || list).querySelectorAll('.ct-hero-link-type').forEach(function(select){
                if (select.dataset.bound === '1') { return; }
                select.dataset.bound = '1';
                select.addEventListener('change', function(){ updateConditionalFields(select.closest('[data-hero-button-row]')); });
            });
            updateConditionalFields(scope || list);
        }
        bindRow(list);
        addBtn.addEventListener('click', function(){
            var index = Date.now();
            var wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.replace(/__INDEX__/g, index);
            var row = wrapper.firstElementChild;
            list.appendChild(row);
            bindRow(row);
        });
    })();
    </script>
    <?php
}

function computech_save_hero_slide(int $post_id): void {
    if (!isset($_POST['computech_hero_slide_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_hero_slide_nonce'])), 'computech_save_hero_slide')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = array(
        '_computech_hero_title_line_1',
        '_computech_hero_title_highlight',
        '_computech_hero_title_line_3',
        '_computech_hero_primary_text',
        '_computech_hero_secondary_text',
        '_computech_hero_badge_line_1',
        '_computech_hero_badge_line_2',
    );
    foreach ($text_fields as $field) {
        update_post_meta($post_id, $field, sanitize_text_field(wp_unslash($_POST[$field] ?? '')));
    }

    foreach (array('_computech_hero_description', '_computech_hero_features', '_computech_hero_whatsapp_message') as $field) {
        update_post_meta($post_id, $field, sanitize_textarea_field(wp_unslash($_POST[$field] ?? '')));
    }

    foreach (array('primary', 'secondary') as $prefix) {
        $type_key = '_computech_hero_' . $prefix . '_link_type';
        $type = sanitize_key(wp_unslash($_POST[$type_key] ?? 'none'));
        $type = in_array($type, array('none', 'home', 'page', 'custom', 'whatsapp'), true) ? $type : 'none';
        update_post_meta($post_id, $type_key, $type);

        $page_value = sanitize_text_field(wp_unslash($_POST['_computech_hero_' . $prefix . '_page_id'] ?? '0'));
        update_post_meta($post_id, '_computech_hero_' . $prefix . '_page_id', $page_value === 'home' ? '0' : (string) absint($page_value));
        update_post_meta($post_id, '_computech_hero_' . $prefix . '_url', esc_url_raw(wp_unslash($_POST['_computech_hero_' . $prefix . '_url'] ?? '')));
        update_post_meta($post_id, '_computech_hero_' . $prefix . '_new_tab', !empty($_POST['_computech_hero_' . $prefix . '_new_tab']) ? '1' : '0');
    }

    $hero_buttons = array();
    $button_rows = $_POST['_computech_hero_buttons'] ?? array();
    if (is_array($button_rows)) {
        foreach ($button_rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $page_value = sanitize_text_field(wp_unslash($row['page_id'] ?? '0'));
            $clean = computech_normalize_hero_button(array(
                'show' => !empty($row['show']) ? '1' : '0',
                'text' => sanitize_text_field(wp_unslash($row['text'] ?? '')),
                'style' => sanitize_key(wp_unslash($row['style'] ?? 'secondary')),
                'link_type' => sanitize_key(wp_unslash($row['link_type'] ?? 'none')),
                'page_slug' => sanitize_title(wp_unslash($row['page_slug'] ?? '')),
                'page_id' => $page_value === 'home' ? 0 : absint($page_value),
                'term_id' => absint($row['term_id'] ?? 0),
                'url' => esc_url_raw(wp_unslash($row['url'] ?? '')),
                'new_tab' => !empty($row['new_tab']) ? '1' : '0',
            ));
            if ($clean['text'] !== '') {
                $hero_buttons[] = $clean;
            }
        }
    }
    update_post_meta($post_id, '_computech_hero_buttons', $hero_buttons);

    update_post_meta($post_id, '_computech_hero_image_id', (string) absint($_POST['_computech_hero_image_id'] ?? 0));
    update_post_meta($post_id, '_computech_hero_show', !empty($_POST['_computech_hero_show']) ? '1' : '0');
}
add_action('save_post_computech_hero_slide', 'computech_save_hero_slide');

function computech_get_hero_slides(): array {
    $query = new WP_Query(array(
        'post_type' => 'computech_hero_slide',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'),
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => '_computech_hero_show',
                'value' => '1',
                'compare' => '=',
            ),
        ),
    ));
    return $query->posts;
}

function computech_hero_feature_svg(int $index): string {
    $svgs = array(
        '<svg viewBox="0 0 20 20" fill="none" stroke="#2563eb" stroke-width="1.5"><rect x="2" y="4" width="16" height="12" rx="2"/><path d="M6 16v2M14 16v2M4 18h12"/></svg>',
        '<svg viewBox="0 0 20 20" fill="none" stroke="#06b6d4" stroke-width="1.5"><circle cx="10" cy="10" r="8"/><path d="M10 2a8 8 0 0 1 0 16"/><path d="M2 10h16"/></svg>',
        '<svg viewBox="0 0 20 20" fill="none" stroke="#16a34a" stroke-width="1.5"><path d="M3 15v-3a7 7 0 0 1 14 0v3"/><rect x="6" y="15" width="8" height="3" rx="1"/></svg>',
        '<svg viewBox="0 0 20 20" fill="none" stroke="#2563eb" stroke-width="1.5"><path d="M10 2l2 5 5 .5-4 3.5 1.5 5L10 13l-4.5 3 1.5-5-4-3.5 5-.5z"/></svg>',
    );
    return $svgs[$index % count($svgs)];
}

function computech_hero_link_url_from_data(WP_Post $slide, array $item): string {
    $type = sanitize_key((string) ($item['link_type'] ?? 'none'));
    if ($type === 'home') {
        return home_url('/');
    }
    if ($type === 'whatsapp') {
        return computech_whatsapp_url(computech_hero_meta($slide, '_computech_hero_whatsapp_message', ''));
    }
    if ($type === 'page') {
        $page_id = absint($item['page_id'] ?? 0);
        if ($page_id) {
            $url = get_permalink($page_id);
            return $url ?: '';
        }
        $slug = sanitize_title((string) ($item['page_slug'] ?? ''));
        return $slug !== '' ? computech_page_url($slug) : '';
    }
    if ($type === 'category') {
        $term_id = absint($item['term_id'] ?? 0);
        if ($term_id) {
            $url = get_term_link($term_id, 'product_category');
            if (!is_wp_error($url)) {
                return (string) $url;
            }
        }
        return computech_page_url('categories');
    }
    if ($type === 'custom') {
        return esc_url_raw((string) ($item['url'] ?? ''));
    }
    return '';
}

function computech_hero_button_target_from_data(array $item): string {
    $type = sanitize_key((string) ($item['link_type'] ?? 'none'));
    $new_tab = !empty($item['new_tab']);
    if ($type === 'whatsapp') {
        $new_tab = true;
    }
    return $new_tab ? ' target="_blank" rel="noopener"' : '';
}

function computech_hero_button_icon_svg(string $style): string {
    if ($style === 'whatsapp') {
        return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
    }
    if ($style === 'primary') {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
    }
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>';
}

function computech_render_hero_buttons(WP_Post $slide): void {
    $buttons = computech_get_hero_buttons($slide);
    foreach ($buttons as $button) {
        $button = computech_normalize_hero_button($button);
        if ($button['show'] !== '1' || $button['text'] === '') {
            continue;
        }
        $url = computech_hero_link_url_from_data($slide, $button);
        if ($url === '') {
            continue;
        }
        $class = $button['style'] === 'primary' ? 'btn-primary' : ($button['style'] === 'whatsapp' ? 'btn-whatsapp' : 'btn-secondary');
        echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '"' . computech_hero_button_target_from_data($button) . '>' . computech_hero_button_icon_svg($button['style']) . '<span>' . esc_html($button['text']) . '</span></a>';
    }
}

function computech_render_hero_slide(WP_Post $slide, int $index): void {
    $title_1 = trim(computech_hero_meta($slide, '_computech_hero_title_line_1', ''));
    $highlight = trim(computech_hero_meta($slide, '_computech_hero_title_highlight', ''));
    $title_3 = trim(computech_hero_meta($slide, '_computech_hero_title_line_3', ''));
    if ($title_1 === '' && $highlight === '' && $title_3 === '') {
        return;
    }
    $description = computech_hero_meta($slide, '_computech_hero_description', '');
    $features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', computech_hero_meta($slide, '_computech_hero_features', '')))));
    $hero_image = computech_post_image_data((int) $slide->ID, '_computech_hero_image_id', 'full', '_computech_hero_image_url');
    $image = $hero_image['url'];
    $alt = $hero_image['alt'] !== '' ? $hero_image['alt'] : get_the_title($slide);
    $badge_1 = trim(computech_hero_meta($slide, '_computech_hero_badge_line_1', ''));
    $badge_2 = trim(computech_hero_meta($slide, '_computech_hero_badge_line_2', ''));
    ?>
    <div class="hero-slide <?php echo $index === 0 ? 'is-active' : ''; ?>" data-hero-slide>
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-decorative-dots"><span class="h-dot blue"></span><span class="h-dot cyan"></span><span class="h-dot green"></span></div>
                <h1 class="hero-headline">
                    <?php if ($title_1 !== '') : ?><?php echo esc_html($title_1); ?><br><?php endif; ?>
                    <?php if ($highlight !== '') : ?><span class="headline-highlight"><?php echo esc_html($highlight); ?></span><br><?php endif; ?>
                    <?php if ($title_3 !== '') : ?><?php echo esc_html($title_3); ?><?php endif; ?>
                </h1>
                <?php if (trim($description) !== '') : ?><p class="hero-description"><?php echo esc_html($description); ?></p><?php endif; ?>
                <?php if ($features) : ?>
                    <div class="hero-feature-pills">
                        <?php foreach ($features as $feature_index => $feature) : ?>
                            <div class="feature-pill"><?php echo computech_hero_feature_svg($feature_index); ?><span><?php echo esc_html($feature); ?></span></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="hero-cta-buttons">
                    <?php computech_render_hero_buttons($slide); ?>
                </div>
            </div>
            <?php if ($image !== '') : ?>
                <div class="hero-image-wrapper">
                    <div class="hero-image-glow"></div>
                    <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" class="hero-image">
                    <?php if ($badge_1 !== '' || $badge_2 !== '') : ?>
                        <div class="hero-floating-badge">
                            <?php if ($badge_1 !== '') : ?><span><?php echo esc_html($badge_1); ?></span><?php endif; ?>
                            <?php if ($badge_2 !== '') : ?><span><?php echo esc_html($badge_2); ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="floating-dots"><span class="f-dot d1"></span><span class="f-dot d2"></span><span class="f-dot d3"></span><span class="f-dot d4"></span><span class="f-dot d5"></span></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function computech_render_home_hero_section(): void {
    $slides = computech_get_hero_slides();
    if (!$slides) {
        return;
    }
    $hero = $slides[0];
    ?>
    <!-- Hero Section -->
    <section class="hero-section computech-dynamic-hero" data-hero-single="1">
        <div class="hero-bg-pattern">
            <div class="circuit-line circuit-1"></div><div class="circuit-line circuit-2"></div><div class="circuit-line circuit-3"></div><div class="circuit-line circuit-4"></div>
            <div class="circuit-dot dot-1"></div><div class="circuit-dot dot-2"></div><div class="circuit-dot dot-3"></div><div class="circuit-dot dot-4"></div><div class="circuit-dot dot-5"></div><div class="circuit-dot dot-6"></div>
            <div class="glow-circle glow-1"></div><div class="glow-circle glow-2"></div><div class="glow-circle glow-3"></div>
        </div>
        <div class="hero-slides-shell">
            <?php computech_render_hero_slide($hero, 0); ?>
        </div>
        <?php computech_render_hero_quick_cards(); ?>
    </section>
    <?php
}

/* ============================================
   Dynamic Hero Quick Cards
   ============================================ */
function computech_register_hero_cards_cpt(): void {
    register_post_type('computech_hero_card', array(
        'labels' => array(
            'name' => 'Hero Cards',
            'singular_name' => 'Hero Card',
            'menu_name' => 'Hero Cards',
            'add_new_item' => 'إضافة كارت Hero جديد',
            'edit_item' => 'تعديل كارت Hero',
            'new_item' => 'كارت جديد',
            'search_items' => 'بحث في الكروت',
            'not_found' => 'لا توجد كروت',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-screenoptions',
        'supports' => array('title', 'thumbnail', 'page-attributes'),
        'capability_type' => 'page',
        'map_meta_cap' => true,
        'show_in_rest' => false,
    ));
}
add_action('init', 'computech_register_hero_cards_cpt');

function computech_default_hero_cards(): array {
    return array();
}

function computech_seed_default_hero_cards(): void {
    // Disabled intentionally: Hero Cards must be added/edited/deleted from the dashboard.
}

function computech_add_hero_card_metaboxes(): void {
    add_meta_box('computech_hero_card_data', 'بيانات كارت Hero', 'computech_hero_card_metabox', 'computech_hero_card', 'normal', 'high');
}
add_action('add_meta_boxes', 'computech_add_hero_card_metaboxes');

function computech_card_meta(WP_Post $post, string $key, string $default = ''): string {
    $value = get_post_meta($post->ID, $key, true);
    return $value === '' ? $default : (string) $value;
}

function computech_hero_card_metabox(WP_Post $post): void {
    wp_nonce_field('computech_save_hero_card', 'computech_hero_card_nonce');
    computech_admin_editor_styles_once();
    $type = computech_card_meta($post, '_computech_card_link_type', 'page');
    $page_id = computech_card_meta($post, '_computech_card_page_id', '0');
    ?>
    <div class="ct-editor ct-hero-admin" dir="rtl">
        <div class="ct-hero-dashboard-head">
            <div>
                <h2>ترتيب بيانات كارت الهيرو</h2>
                <p>اسم الكارت بيتعدل من عنوان البوست بالأعلى، والصورة ترفعها من خانة الصورة داخل الكارت. هنا بتتحكم في الظهور، الوصف، الرابط، وبيانات الصورة.</p>
            </div>
            <div class="ct-status-card">
                <label><input type="checkbox" name="_computech_card_show" value="1" <?php checked(computech_card_meta($post, '_computech_card_show', '1'), '1'); ?>> إظهار هذا الكارت</label>
                <p class="description">لو اتقفلت، الكارت مش هيظهر أسفل الهيرو.</p>
            </div>
        </div>

        <div class="ct-hero-dashboard">
            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>1. محتوى الكارت</h3>
                        <p>العنوان من خانة Title فوق. الوصف هنا يظهر تحت العنوان داخل الكارت.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-card-preview-info" style="margin-bottom:14px">
                        <div class="ct-mini-info"><strong>العنوان</strong><span>عدّله من خانة العنوان أعلى الصفحة.</span></div>
                        <div class="ct-mini-info"><strong>الصورة</strong><span>اختارها من خانة الصورة داخل هذا الكارت.</span></div>
                        <div class="ct-mini-info"><strong>الترتيب</strong><span>استخدم Order من Page Attributes لو ظهر عندك.</span></div>
                    </div>
                    <p class="ct-field"><label>الوصف</label><textarea name="_computech_card_text" rows="3" class="widefat" placeholder="مثال: لابتوبات، شاشات، مكونات، سيرفرات وملحقاتها"><?php echo esc_textarea(computech_card_meta($post, '_computech_card_text', '')); ?></textarea></p>
                </div>
            </section>

            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>2. رابط الكارت</h3>
                        <p>اختار نوع الرابط، وبعدها املأ الحقل المناسب فقط. الحقول غير المناسبة لن تؤثر.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-grid ct-grid-2">
                        <p class="ct-field"><label>نص الرابط</label><input type="text" name="_computech_card_link_text" value="<?php echo esc_attr(computech_card_meta($post, '_computech_card_link_text', '')); ?>" class="widefat" placeholder="اكتب نص الرابط من الداشبورد"></p>
                        <p class="ct-field"><label>نوع الرابط</label><?php echo computech_hero_link_type_select('_computech_card_link_type', $type); ?></p>
                    </div>
                    <div class="ct-conditional-fields" style="margin-top:14px">
                        <p class="ct-field ct-link-page-field"><label>اختيار صفحة</label><select name="_computech_card_page_id" class="widefat"><?php echo computech_hero_pages_options($type === 'home' ? 'home' : $page_id); ?></select></p>
                        <p class="ct-field ct-link-category-field"><label>اختيار قسم منتجات</label><select name="_computech_card_term_id" class="widefat"><?php echo computech_hero_product_category_options(computech_card_meta($post, '_computech_card_term_id', '0')); ?></select></p>
                        <p class="ct-field ct-link-url-field"><label>رابط خارجي / عام</label><input type="url" name="_computech_card_url" value="<?php echo esc_attr(computech_card_meta($post, '_computech_card_url', '')); ?>" class="widefat" placeholder="https://example.com أو /products/"></p>
                    </div>
                    <p class="ct-field" style="margin-top:14px"><label><input type="checkbox" name="_computech_card_new_tab" value="1" <?php checked(computech_card_meta($post, '_computech_card_new_tab', '0'), '1'); ?>> فتح في تبويب جديد</label><span class="ct-help">يفضل تفعيلها للروابط الخارجية فقط.</span></p>
                </div>
            </section>

            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>3. الصورة وبياناتها</h3>
                        <p>الواجهة بتحمي حجم الصورة مهما كانت كبيرة، لكن الأفضل استخدام صورة واضحة وخلفية شفافة إن أمكن.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-note"><span>ℹ️</span><div>اختار صورة الكارت من Media Library. سيتم استخدام Alt Text وTitle من بيانات الصورة نفسها تلقائيًا.</div></div>
                    <?php computech_admin_image_upload_field('صورة الكارت', '_computech_card_image_id', $post->ID, 'ارفع أو اختر صورة الكارت. لا تحتاج لإدخال رابط أو Alt Text يدويًا.'); ?>
                </div>
            </section>
        </div>
    </div>
    <script>
    (function(){
        var root = document.currentScript.previousElementSibling;
        if (!root) { return; }
        function updateFields(){
            var typeSelect = root.querySelector('.ct-hero-link-type');
            var type = typeSelect ? typeSelect.value : 'none';
            var pageField = root.querySelector('.ct-link-page-field');
            var categoryField = root.querySelector('.ct-link-category-field');
            var urlField = root.querySelector('.ct-link-url-field');
            if (pageField) { pageField.style.display = (type === 'page' || type === 'home') ? '' : 'none'; }
            if (categoryField) { categoryField.style.display = type === 'category' ? '' : 'none'; }
            if (urlField) { urlField.style.display = type === 'custom' ? '' : 'none'; }
        }
        var select = root.querySelector('.ct-hero-link-type');
        if (select) { select.addEventListener('change', updateFields); }
        updateFields();
    })();
    </script>
    <?php
}

function computech_save_hero_card(int $post_id): void {
    if (!isset($_POST['computech_hero_card_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_hero_card_nonce'])), 'computech_save_hero_card')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    update_post_meta($post_id, '_computech_card_show', !empty($_POST['_computech_card_show']) ? '1' : '0');
    update_post_meta($post_id, '_computech_card_text', sanitize_textarea_field(wp_unslash($_POST['_computech_card_text'] ?? '')));
    update_post_meta($post_id, '_computech_card_link_text', sanitize_text_field(wp_unslash($_POST['_computech_card_link_text'] ?? '')));
    $type = sanitize_key(wp_unslash($_POST['_computech_card_link_type'] ?? 'none'));
    $type = in_array($type, array('none', 'home', 'page', 'category', 'custom', 'whatsapp'), true) ? $type : 'none';
    update_post_meta($post_id, '_computech_card_link_type', $type);
    $page_value = sanitize_text_field(wp_unslash($_POST['_computech_card_page_id'] ?? '0'));
    update_post_meta($post_id, '_computech_card_page_id', $page_value === 'home' ? '0' : (string) absint($page_value));
    update_post_meta($post_id, '_computech_card_term_id', (string) absint($_POST['_computech_card_term_id'] ?? 0));
    update_post_meta($post_id, '_computech_card_url', esc_url_raw(wp_unslash($_POST['_computech_card_url'] ?? '')));
    update_post_meta($post_id, '_computech_card_new_tab', !empty($_POST['_computech_card_new_tab']) ? '1' : '0');
    update_post_meta($post_id, '_computech_card_image_id', (string) absint($_POST['_computech_card_image_id'] ?? 0));
}
add_action('save_post_computech_hero_card', 'computech_save_hero_card');

function computech_get_hero_cards(): array {
    $query = new WP_Query(array(
        'post_type' => 'computech_hero_card',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'),
        'order' => 'ASC',
        'meta_query' => array(
            array('key' => '_computech_card_show', 'value' => '1', 'compare' => '='),
        ),
    ));
    return $query->posts;
}

function computech_hero_card_url(WP_Post $card): string {
    $fake_slide = $card;
    return computech_hero_link_url_from_data($fake_slide, array(
        'link_type' => computech_card_meta($card, '_computech_card_link_type', 'none'),
        'page_id' => computech_card_meta($card, '_computech_card_page_id', '0'),
        'page_slug' => computech_card_meta($card, '_computech_card_page_slug', ''),
        'term_id' => computech_card_meta($card, '_computech_card_term_id', '0'),
        'url' => computech_card_meta($card, '_computech_card_url', ''),
        'new_tab' => computech_card_meta($card, '_computech_card_new_tab', '0'),
    ));
}

function computech_hero_card_target(WP_Post $card): string {
    return computech_hero_button_target_from_data(array(
        'link_type' => computech_card_meta($card, '_computech_card_link_type', 'none'),
        'new_tab' => computech_card_meta($card, '_computech_card_new_tab', '0'),
    ));
}

function computech_render_hero_quick_cards(): void {
    $cards = computech_get_hero_cards();
    if (!$cards) {
        return;
    }
    ?>
    <div class="hero-cards-container">
        <?php foreach ($cards as $card) :
            $title = get_the_title($card);
            $text = computech_card_meta($card, '_computech_card_text', '');
            $link_text = computech_card_meta($card, '_computech_card_link_text', '');
            $url = computech_hero_card_url($card);
            $card_image = computech_post_image_data((int) $card->ID, '_computech_card_image_id', 'full', '_computech_card_image_url');
            $image = $card_image['url'];
            $alt = $card_image['alt'] !== '' ? $card_image['alt'] : $title;
            ?>
            <div class="quick-card">
                <?php if ($image !== '') : ?><div class="quick-card-image"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>"></div><?php endif; ?>
                <div class="quick-card-content">
                    <?php if ($title !== '') : ?><h3 class="quick-card-title"><?php echo esc_html($title); ?></h3><?php endif; ?>
                    <?php if (trim($text) !== '') : ?><p class="quick-card-text"><?php echo esc_html($text); ?></p><?php endif; ?>
                    <?php if ($url !== '' && trim($link_text) !== '') : ?><a href="<?php echo esc_url($url); ?>" class="quick-card-link"<?php echo computech_hero_card_target($card); ?>><?php echo esc_html($link_text); ?></a><?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function computech_get_meta(int $post_id, string $key, string $default = ''): string {
    $value = get_post_meta($post_id, $key, true);
    return $value === '' ? $default : (string) $value;
}

function computech_product_category_slug(int $post_id): string {
    $terms = get_the_terms($post_id, 'product_category');
    if (!is_wp_error($terms) && !empty($terms)) {
        return sanitize_html_class($terms[0]->slug);
    }
    return '';
}

function computech_price_number(int $post_id): int {
    $price = computech_get_meta($post_id, '_computech_sale_price', computech_get_meta($post_id, '_computech_price', ''));
    if ($price === '') {
        $price = computech_get_meta($post_id, '_computech_regular_price', computech_get_meta($post_id, '_computech_old_price', '0'));
    }
    return (int) preg_replace('/[^0-9]/', '', $price);
}

function computech_add_product_metaboxes(): void {
    // Legacy product metabox disabled. Product data is now managed by the architecture metabox in inc/computech-architecture.php.
}

function computech_product_data_metabox(WP_Post $post): void {
    wp_nonce_field('computech_save_product_data', 'computech_product_nonce');
    $featured_home = get_post_meta($post->ID, '_computech_featured_home', true);
    $featured_order = get_post_meta($post->ID, '_computech_featured_order', true);
    $status = get_post_meta($post->ID, '_computech_status', true) ?: 'new';
    ?>
    <div class="computech-product-admin" style="direction:rtl;display:grid;gap:18px">
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:16px">
            <h3 style="margin-top:0">ظهور المنتج في الصفحة الرئيسية</h3>
            <p><label><input type="checkbox" name="_computech_featured_home" value="1" <?php checked($featured_home, '1'); ?>> إظهار هذا المنتج داخل سكشن <strong>منتجات مميزة</strong></label></p>
            <p style="max-width:320px"><label style="display:block;font-weight:700;margin-bottom:6px">ترتيب المنتج داخل السكشن</label><input type="number" name="_computech_featured_order" value="<?php echo esc_attr($featured_order !== '' ? $featured_order : (string) $post->menu_order); ?>" class="widefat" min="0" step="1"></p>
            <p style="color:#646970;margin-bottom:0">الصورة الرئيسية للمنتج تتعدل من صندوق <strong>الصورة البارزة / Featured Image</strong>. الـ Alt Text يتسحب من بيانات الصورة في Media Library.</p>
        </div>

        <div style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:16px">
            <h3 style="margin-top:0">بيانات كارت المنتج</h3>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px">
                <p><label style="display:block;font-weight:700;margin-bottom:6px">السعر</label><input type="text" name="_computech_price" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_price', true)); ?>" class="widefat" placeholder="$799.99"></p>
                <p><label style="display:block;font-weight:700;margin-bottom:6px">السعر قبل الخصم</label><input type="text" name="_computech_old_price" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_old_price', true)); ?>" class="widefat" placeholder="$1,099.99"></p>
                <p><label style="display:block;font-weight:700;margin-bottom:6px">Label الخصم</label><input type="text" name="_computech_discount_label" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_discount_label', true)); ?>" class="widefat" placeholder="خصم 18%"></p>
                <p><label style="display:block;font-weight:700;margin-bottom:6px">الحالة</label><select name="_computech_status" class="widefat">
                    <option value="new" <?php selected($status, 'new'); ?>>جديد</option>
                    <option value="imported" <?php selected($status, 'imported'); ?>>استيراد خارج</option>
                    <option value="in-stock" <?php selected($status, 'in-stock'); ?>>متوفر</option>
                </select></p>
                <p><label style="display:block;font-weight:700;margin-bottom:6px">الضمان / الملاحظة</label><input type="text" name="_computech_warranty" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_warranty', true)); ?>" class="widefat" placeholder="ضمان 12 شهر"></p>
                <p><label style="display:block;font-weight:700;margin-bottom:6px">رقم واتساب خاص بالمنتج</label><input type="text" name="_computech_whatsapp" value="<?php echo esc_attr(get_post_meta($post->ID, '_computech_whatsapp', true)); ?>" class="widefat" placeholder="201xxxxxxxxx"></p>
            </div>
            <p><label style="display:block;font-weight:700;margin-bottom:6px">المواصفات المختصرة - كل سطر يظهر كبادج داخل الكارت</label><textarea name="_computech_specs" rows="5" class="widefat" style="direction:rtl"><?php echo esc_textarea(get_post_meta($post->ID, '_computech_specs', true)); ?></textarea></p>
            <p><label style="display:block;font-weight:700;margin-bottom:6px">المواصفات التفصيلية - اكتب كل سطر بالشكل: الاسم: القيمة</label><textarea name="_computech_full_specs" rows="7" class="widefat" style="direction:rtl"><?php echo esc_textarea(get_post_meta($post->ID, '_computech_full_specs', true)); ?></textarea></p>
            <p style="color:#646970;margin-bottom:0">الوصف القصير الذي يظهر تحت اسم المنتج يتعدل من صندوق <strong>Excerpt / المقتطف</strong>. لو مش ظاهر افتحه من Screen Options.</p>
        </div>
    </div>
    <?php
}

function computech_save_product_data(int $post_id): void {
    if (!isset($_POST['computech_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_product_nonce'])), 'computech_save_product_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    update_post_meta($post_id, '_computech_featured_home', !empty($_POST['_computech_featured_home']) ? '1' : '0');
    update_post_meta($post_id, '_computech_featured_order', absint($_POST['_computech_featured_order'] ?? 0));

    $text_fields = array('_computech_price','_computech_old_price','_computech_discount_label','_computech_status','_computech_warranty','_computech_whatsapp','_computech_rating');
    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field(wp_unslash($_POST[$field])));
        }
    }
    foreach (array('_computech_specs','_computech_full_specs') as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_textarea_field(wp_unslash($_POST[$field])));
        }
    }
}
add_action('save_post_products', 'computech_save_product_data');

function computech_whatsapp_icon(): string {
    return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>';
}

function computech_product_card(?WP_Post $post = null): void {
    $post = $post ?: get_post();
    if (!$post) { return; }
    $post_id = $post->ID;
    if (function_exists('computech_arch_is_product_visible') && !computech_arch_is_product_visible($post_id)) { return; }
    $title = get_the_title($post_id);
    $card_title = computech_get_meta($post_id, '_computech_card_title_override', $title);
    $subtitle = computech_get_meta($post_id, '_computech_card_subtitle', '');
    $show_price = computech_get_meta($post_id, '_computech_show_price', '1') !== '0';
    $price = $show_price ? computech_get_meta($post_id, '_computech_sale_price', computech_get_meta($post_id, '_computech_price', '')) : '';
    if ($price === '' && $show_price) {
        $price = computech_get_meta($post_id, '_computech_regular_price', '');
    }
    $status = computech_get_meta($post_id, '_computech_condition', computech_get_meta($post_id, '_computech_status', ''));
    $availability = computech_get_meta($post_id, '_computech_availability', '');
    $status_label = computech_get_meta($post_id, '_computech_badge_text', '');
    if ($status_label === '' && $status !== '') { $status_label = $status === 'imported' ? 'استيراد خارج' : ($status === 'used' ? 'مستعمل' : ($status === 'refurbished' ? 'مجدد' : ($status === 'new' ? 'جديد' : $status))); }
    if ($availability === 'out-of-stock') { $status_label = 'غير متوفر'; } elseif ($availability === 'coming-soon') { $status_label = 'قريبًا'; }
    $badge_class = $status === 'imported' ? 'prod-badge-imported' : 'prod-badge-new';
    $specs = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', computech_get_meta($post_id, '_computech_specs', ''))));
    $warranty = computech_get_meta($post_id, '_computech_card_note', computech_get_meta($post_id, '_computech_warranty', ''));
    $cat_slug = function_exists('computech_product_category_filter_slugs') ? computech_product_category_filter_slugs($post_id) : computech_product_category_slug($post_id);
    $thumb_id = get_post_thumbnail_id($post_id);
    $img = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
    $img_alt = $thumb_id ? (string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
    $whatsapp_url = function_exists('computech_arch_product_whatsapp_url') ? computech_arch_product_whatsapp_url($post_id, $title) : '';
    $show_details = computech_get_meta($post_id, '_computech_show_details_button', '1') !== '0';
    $details_text = computech_get_meta($post_id, '_computech_details_button_text', '');
    $show_whatsapp = computech_get_meta($post_id, '_computech_show_whatsapp_button', '1') !== '0';
    $whatsapp_text = computech_get_meta($post_id, '_computech_whatsapp_button_text', '');
    ?>
    <div class="prod-card" data-category="<?php echo esc_attr($cat_slug); ?>" data-status="<?php echo esc_attr(trim($status . ' ' . $availability)); ?>" data-price="<?php echo esc_attr(computech_price_number($post_id)); ?>" data-name="<?php echo esc_attr($title); ?>">
        <?php if ($img !== '') : ?><div class="prod-card-image"><?php if ($status_label !== '') : ?><span class="prod-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($status_label); ?></span><?php endif; ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($img_alt !== '' ? $img_alt : $title); ?>"></div><?php endif; ?>
        <div class="prod-card-body"><h3 class="prod-card-title"><?php echo esc_html($card_title); ?></h3><p class="prod-card-desc"><?php echo esc_html($subtitle !== '' ? $subtitle : (get_the_excerpt($post_id) ?: wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 20, '...'))); ?></p>
            <?php if ($specs) : ?><div class="prod-card-specs"><?php foreach ($specs as $spec) : ?><span class="prod-spec"><?php echo esc_html($spec); ?></span><?php endforeach; ?></div><?php endif; ?>
            <?php if ($price !== '') : ?><div class="prod-card-price"><?php echo esc_html($price); ?></div><?php endif; ?>
            <?php if ($warranty !== '') : ?><div class="prod-card-warranty"><?php echo esc_html($warranty); ?></div><?php endif; ?>
            <div class="prod-card-actions"><?php if ($show_details && $details_text !== '') : ?><a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="prod-card-btn prod-btn-details"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><?php echo esc_html($details_text); ?></a><?php endif; ?><?php if ($show_whatsapp && $whatsapp_url !== '' && $whatsapp_text !== '') : ?><a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="prod-card-btn prod-btn-whatsapp"><?php echo computech_whatsapp_icon(); ?><?php echo esc_html($whatsapp_text); ?></a><?php endif; ?></div>
        </div>
    </div>
    <?php
}


function computech_default_featured_products(): array {
    // No hard-coded products. Featured products are read only from WP database.
    return array();
}

function computech_seed_default_featured_products(): void {
    // Disabled intentionally: products must be added/managed from the dashboard.
}

function computech_get_featured_products(): array {
    $visibility_query = function_exists('computech_arch_visibility_meta_query') ? computech_arch_visibility_meta_query() : array('key' => '_computech_product_visibility', 'compare' => 'NOT EXISTS');
    $query = new WP_Query(array(
        'post_type' => 'products',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'meta_query' => array(
            'relation' => 'AND',
            $visibility_query,
            array('key' => '_computech_show_featured_products', 'value' => '1', 'compare' => '='),
        ),
        'meta_key' => '_computech_featured_order',
        'orderby' => array('meta_value_num' => 'ASC', 'menu_order' => 'ASC', 'date' => 'DESC'),
        'no_found_rows' => true,
    ));
    $items = $query->posts;
    wp_reset_postdata();
    return is_array($items) ? $items : array();
}

function computech_featured_status_label(string $status): array {
    if ($status === 'imported') {
        return array('label' => 'استيراد خارج', 'class' => 'feat-badge-import');
    }
    if ($status === 'used') {
        return array('label' => 'مستعمل', 'class' => 'feat-badge-import');
    }
    if ($status === 'refurbished') {
        return array('label' => 'مجدد', 'class' => 'feat-badge-import');
    }
    if ($status === 'new') {
        return array('label' => 'جديد', 'class' => 'feat-badge-new');
    }
    return array('label' => '', 'class' => '');
}

function computech_featured_product_image(int $post_id, string $title): array {
    $thumb_id = get_post_thumbnail_id($post_id);
    if ($thumb_id) {
        $url = wp_get_attachment_image_url($thumb_id, 'large');
        if ($url) {
            $alt = (string) get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
            return array('url' => $url, 'alt' => $alt !== '' ? $alt : $title);
        }
    }
    $fallback = computech_get_meta($post_id, '_computech_featured_image_url', '');
    return array('url' => $fallback, 'alt' => $title);
}

function computech_featured_whatsapp_url(int $post_id, string $title): string {
    $number = computech_clean_phone(computech_get_meta($post_id, '_computech_whatsapp', ''));
    if ($number === '') {
        $number = computech_clean_phone(computech_header_setting('whatsapp_number', ''));
    }
    if ($number === '') {
        return '';
    }
    $message = computech_get_meta($post_id, '_computech_whatsapp_message', '');
    $url = 'https://wa.me/' . $number;
    if ($message !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}

function computech_render_featured_product_card(WP_Post $product): void {
    $post_id = (int) $product->ID;
    $title = get_the_title($post_id);
    $excerpt = get_the_excerpt($post_id);
    if ($excerpt === '') {
        $excerpt = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 16, '...');
    }
    $status = computech_get_meta($post_id, '_computech_condition', computech_get_meta($post_id, '_computech_status', ''));
    $status_data = computech_featured_status_label($status);
    $price = computech_get_meta($post_id, '_computech_sale_price', computech_get_meta($post_id, '_computech_price', ''));
    if ($price === '') { $price = computech_get_meta($post_id, '_computech_regular_price', ''); }
    $old_price = computech_get_meta($post_id, '_computech_regular_price', computech_get_meta($post_id, '_computech_old_price', ''));
    $discount = computech_get_meta($post_id, '_computech_discount_label', '');
    $warranty = computech_get_meta($post_id, '_computech_warranty', '');
    $specs = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', computech_get_meta($post_id, '_computech_specs', ''))));
    $image = computech_featured_product_image($post_id, $title);
    $details_label = computech_home_section_option('featured_details_label', '');
    $whatsapp_label = computech_home_section_option('featured_whatsapp_label', '');
    $whatsapp_url = function_exists('computech_arch_product_whatsapp_url') ? computech_arch_product_whatsapp_url($post_id, $title) : computech_featured_whatsapp_url($post_id, $title);
    ?>
    <div class="feat-card">
        <div class="feat-card-top">
            <?php if ($status_data['label'] !== '') : ?><span class="feat-badge <?php echo esc_attr($status_data['class']); ?>"><?php echo esc_html($status_data['label']); ?></span><?php endif; ?>
            <button class="feat-heart" aria-label="أضف للمفضلة" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </button>
        </div>
        <?php if ($image['url'] !== '') : ?><div class="feat-card-img"><img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>"></div><?php endif; ?>
        <div class="feat-card-body">
            <h3 class="feat-card-title"><?php echo esc_html($title); ?></h3>
            <?php if (trim($excerpt) !== '') : ?><p class="feat-card-spec"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
            <?php if (!empty($specs)) : ?><div class="feat-specs"><?php foreach ($specs as $spec) : ?><span class="feat-spec-chip"><?php echo esc_html($spec); ?></span><?php endforeach; ?></div><?php endif; ?>
            <?php if ($price !== '' || $old_price !== '' || $discount !== '') : ?>
                <div class="feat-price-row">
                    <?php if ($price !== '') : ?><span class="feat-price"><?php echo esc_html($price); ?></span><?php endif; ?>
                    <?php if ($old_price !== '') : ?><span class="feat-old-price"><?php echo esc_html($old_price); ?></span><?php endif; ?>
                    <?php if ($discount !== '') : ?><span class="feat-discount"><?php echo esc_html($discount); ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($warranty !== '') : ?><p class="feat-note"><?php echo esc_html($warranty); ?></p><?php endif; ?>
            <div class="feat-card-actions">
                <?php if ($details_label !== '') : ?><a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="feat-btn-details">
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 3 11 8 6 13"/></svg>
                    <?php echo esc_html($details_label); ?>
                </a><?php endif; ?>
                <?php if ($whatsapp_url !== '' && $whatsapp_label !== '') : ?>
                    <a href="<?php echo esc_url($whatsapp_url); ?>" class="feat-btn-whatsapp" target="_blank" rel="noopener">
                        <?php echo computech_whatsapp_icon(); ?>
                        <?php echo esc_html($whatsapp_label); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

function computech_render_featured_products_section(): void {
    if (computech_home_section_option('featured_show', '0') !== '1') {
        return;
    }
    $products = computech_get_featured_products();
    if (empty($products)) {
        return;
    }
    $title = computech_home_section_option('featured_title', '');
    $subtitle = computech_home_section_option('featured_subtitle', '');
    $view_all_label = computech_home_section_option('featured_view_all_label', '');
    ?>
    <!-- Featured Products Section - منتجات مميزة -->
    <section class="featured-section computech-dynamic-featured-products">
        <div class="featured-bg-pattern">
            <div class="feat-glow feat-glow-tr"></div>
            <div class="feat-glow feat-glow-bl"></div>
            <div class="feat-dots feat-dots-tr"></div>
            <div class="feat-dots feat-dots-bl"></div>
        </div>

        <div class="featured-container">
            <div class="featured-header">
                <div class="featured-header-right">
                    <?php if ($title !== '') : ?>
                        <h2 class="featured-title">
                            <svg class="featured-title-arrow" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 2 8 6 4 10"/></svg>
                            <?php echo esc_html($title); ?>
                        </h2>
                    <?php endif; ?>
                    <?php if ($subtitle !== '') : ?><p class="featured-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
                </div>
                <?php if ($view_all_label !== '') : ?>
                    <a href="<?php echo esc_url(computech_page_url('products')); ?>" class="featured-view-all">
                        <?php echo esc_html($view_all_label); ?>
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="10 3 5 8 10 13"/></svg>
                    </a>
                <?php endif; ?>
            </div>
            <div class="featured-grid">
                <?php foreach ($products as $product) : ?>
                    <?php if ($product instanceof WP_Post) { computech_render_featured_product_card($product); } ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function computech_breadcrumbs(string $current = '', array $parents = array()): void {
    if (is_front_page()) {
        return;
    }

    $current = $current ?: (is_singular() ? get_the_title() : single_term_title('', false));

    echo '<div class="site-breadcrumb"><div class="site-breadcrumb-inner"><nav class="breadcrumb-nav" aria-label="مسار الصفحة">';
    echo '<a href="' . esc_url(home_url('/')) . '" class="breadcrumb-link breadcrumb-home"><span class="breadcrumb-home-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg></span><span>الرئيسية</span></a>';

    foreach ($parents as $parent) {
        if (empty($parent['label'])) {
            continue;
        }
        echo '<span class="breadcrumb-sep" aria-hidden="true">/</span>';
        $url = $parent['url'] ?? '';
        if ($url) {
            echo '<a href="' . esc_url($url) . '" class="breadcrumb-link">' . esc_html($parent['label']) . '</a>';
        } else {
            echo '<span class="breadcrumb-link breadcrumb-muted">' . esc_html($parent['label']) . '</span>';
        }
    }

    echo '<span class="breadcrumb-sep" aria-hidden="true">/</span>';
    echo '<span class="breadcrumb-current" aria-current="page">' . esc_html($current) . '</span>';
    echo '</nav></div></div>';
}

/* ============================================
   Dynamic Home Sections: Customer Needs + Shop Categories
   ============================================ */
function computech_register_customer_need_cards_cpt(): void {
    register_post_type('computech_need_card', array(
        'labels' => array(
            'name' => 'احتياجات العملاء',
            'singular_name' => 'كارت احتياج',
            'menu_name' => 'احتياجات العملاء',
            'add_new_item' => 'إضافة كارت احتياج جديد',
            'edit_item' => 'تعديل كارت الاحتياج',
            'new_item' => 'كارت احتياج جديد',
            'search_items' => 'بحث في احتياجات العملاء',
            'not_found' => 'لا توجد كروت احتياجات',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-groups',
        'supports' => array('title', 'thumbnail', 'page-attributes'),
        'capability_type' => 'page',
        'map_meta_cap' => true,
        'show_in_rest' => false,
    ));
}
add_action('init', 'computech_register_customer_need_cards_cpt');

function computech_register_home_category_cards_cpt(): void {
    // Disabled intentionally: home shop cards are now real Product Categories controlled by taxonomy meta fields.
}

function computech_default_home_section_options(): array {
    // Empty schema only. Titles/labels/subtitles are stored in wp_options from the dashboard.
    return array(
        'needs_show' => '0',
        'needs_title_before' => '',
        'needs_title_highlight' => '',
        'needs_subtitle' => '',
        'shop_show' => '0',
        'shop_title_before' => '',
        'shop_title_highlight' => '',
        'shop_subtitle' => '',
        'featured_show' => '0',
        'featured_title' => '',
        'featured_subtitle' => '',
        'featured_view_all_label' => '',
        'featured_details_label' => '',
        'featured_whatsapp_label' => '',
    );
}

function computech_seed_default_home_section_options(): void {
    if (get_option('computech_home_section_options', null) === null) {
        update_option('computech_home_section_options', computech_default_home_section_options(), false);
    }
}

add_action('admin_init', 'computech_seed_default_home_section_options');
add_action('init', 'computech_seed_default_home_section_options', 37);

function computech_home_section_option(string $key, string $default = ''): string {
    $options = get_option('computech_home_section_options');
    if (!is_array($options)) {
        $options = array();
    }
    $defaults = computech_default_home_section_options();
    $value = array_key_exists($key, $options) ? $options[$key] : ($defaults[$key] ?? $default);
    return is_scalar($value) ? (string) $value : $default;
}

function computech_home_sections_admin_menu(): void {
    add_submenu_page('computech-settings', 'إعدادات أقسام الرئيسية', 'أقسام الرئيسية', computech_admin_capability(), 'computech-home-sections', 'computech_home_sections_settings_page');
}
add_action('admin_menu', 'computech_home_sections_admin_menu');

function computech_home_sections_settings_page(): void {
    if (!current_user_can(computech_admin_capability())) {
        wp_die('غير مصرح لك بتعديل هذه الإعدادات.');
    }

    $defaults = computech_default_home_section_options();
    $options = get_option('computech_home_section_options');
    if (!is_array($options)) {
        $options = $defaults;
    } else {
        $options = array_merge($defaults, $options);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('computech_save_home_sections', 'computech_home_sections_nonce');
        if (!current_user_can(computech_admin_capability())) {
            wp_die('غير مصرح لك بحفظ هذه الإعدادات.');
        }
        $options = array(
            'needs_show' => !empty($_POST['needs_show']) ? '1' : '0',
            'needs_title_before' => sanitize_text_field(wp_unslash($_POST['needs_title_before'] ?? '')),
            'needs_title_highlight' => sanitize_text_field(wp_unslash($_POST['needs_title_highlight'] ?? '')),
            'needs_subtitle' => sanitize_textarea_field(wp_unslash($_POST['needs_subtitle'] ?? '')),
            'shop_show' => !empty($_POST['shop_show']) ? '1' : '0',
            'shop_title_before' => sanitize_text_field(wp_unslash($_POST['shop_title_before'] ?? '')),
            'shop_title_highlight' => sanitize_text_field(wp_unslash($_POST['shop_title_highlight'] ?? '')),
            'shop_subtitle' => sanitize_textarea_field(wp_unslash($_POST['shop_subtitle'] ?? '')),
            'featured_show' => !empty($_POST['featured_show']) ? '1' : '0',
            'featured_title' => sanitize_text_field(wp_unslash($_POST['featured_title'] ?? '')),
            'featured_subtitle' => sanitize_textarea_field(wp_unslash($_POST['featured_subtitle'] ?? '')),
            'featured_view_all_label' => sanitize_text_field(wp_unslash($_POST['featured_view_all_label'] ?? '')),
            'featured_details_label' => sanitize_text_field(wp_unslash($_POST['featured_details_label'] ?? '')),
            'featured_whatsapp_label' => sanitize_text_field(wp_unslash($_POST['featured_whatsapp_label'] ?? '')),
        );
        update_option('computech_home_section_options', $options, false);
        echo '<div class="notice notice-success is-dismissible"><p>تم حفظ إعدادات أقسام الصفحة الرئيسية.</p></div>';
    }

    ?>
    <div class="wrap computech-admin-wrap" dir="rtl">
        <h1>إعدادات أقسام الصفحة الرئيسية</h1>
        <p>هنا تتحكم في عناوين ووصف سكشن <strong>ابدأ من احتياجك</strong> وسكشن <strong>تسوق حسب القسم</strong>. كروت تسوق حسب القسم تُسحب الآن من <strong>أقسام المنتجات</strong> حسب Show in Shop Section والترتيب.</p>
        <form method="post">
            <?php wp_nonce_field('computech_save_home_sections', 'computech_home_sections_nonce'); ?>
            <div class="computech-settings-card" style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;margin:18px 0;max-width:1040px">
                <h2>سكشن ابدأ من احتياجك</h2>
                <p><label><input type="checkbox" name="needs_show" value="1" <?php checked($options['needs_show'], '1'); ?>> إظهار السكشن</label></p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">نص العنوان قبل الكلمة المميزة</label><input type="text" name="needs_title_before" value="<?php echo esc_attr($options['needs_title_before']); ?>" class="widefat"></p>
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">الكلمة المميزة باللون</label><input type="text" name="needs_title_highlight" value="<?php echo esc_attr($options['needs_title_highlight']); ?>" class="widefat"></p>
                </div>
                <p style="max-width:900px"><label style="font-weight:700;display:block;margin-bottom:6px">الوصف</label><textarea name="needs_subtitle" rows="3" class="widefat"><?php echo esc_textarea($options['needs_subtitle']); ?></textarea></p>
            </div>
            <div class="computech-settings-card" style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;margin:18px 0;max-width:1040px">
                <h2>سكشن تسوق حسب القسم</h2>
                <p><label><input type="checkbox" name="shop_show" value="1" <?php checked($options['shop_show'], '1'); ?>> إظهار السكشن</label></p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">نص العنوان قبل الكلمة المميزة</label><input type="text" name="shop_title_before" value="<?php echo esc_attr($options['shop_title_before']); ?>" class="widefat"></p>
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">الكلمة المميزة باللون</label><input type="text" name="shop_title_highlight" value="<?php echo esc_attr($options['shop_title_highlight']); ?>" class="widefat"></p>
                </div>
                <p style="max-width:900px"><label style="font-weight:700;display:block;margin-bottom:6px">الوصف</label><textarea name="shop_subtitle" rows="3" class="widefat"><?php echo esc_textarea($options['shop_subtitle']); ?></textarea></p>
            </div>
            <div class="computech-settings-card" style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;margin:18px 0;max-width:1040px">
                <h2>سكشن منتجات مميزة</h2>
                <p><label><input type="checkbox" name="featured_show" value="1" <?php checked($options['featured_show'], '1'); ?>> إظهار السكشن</label></p>
                <p style="max-width:900px;color:#646970">المنتجات نفسها تتعدل من <strong>منتجات كمبيوتك</strong>. افتح أي منتج وفعل خيار <strong>إظهاره داخل منتجات مميزة</strong> ثم عدّل السعر والصورة والمواصفات.</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">عنوان السكشن</label><input type="text" name="featured_title" value="<?php echo esc_attr($options['featured_title']); ?>" class="widefat"></p>
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">نص زر عرض الكل</label><input type="text" name="featured_view_all_label" value="<?php echo esc_attr($options['featured_view_all_label']); ?>" class="widefat"></p>
                </div>
                <p style="max-width:900px"><label style="font-weight:700;display:block;margin-bottom:6px">الوصف</label><textarea name="featured_subtitle" rows="3" class="widefat"><?php echo esc_textarea($options['featured_subtitle']); ?></textarea></p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:900px">
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">نص زر التفاصيل داخل الكارت</label><input type="text" name="featured_details_label" value="<?php echo esc_attr($options['featured_details_label']); ?>" class="widefat"></p>
                    <p><label style="font-weight:700;display:block;margin-bottom:6px">نص زر واتساب داخل الكارت</label><input type="text" name="featured_whatsapp_label" value="<?php echo esc_attr($options['featured_whatsapp_label']); ?>" class="widefat"></p>
                </div>
            </div>
            <?php submit_button('حفظ إعدادات الأقسام'); ?>
        </form>
    </div>
    <?php
}

function computech_section_meta(WP_Post $post, string $key, string $default = ''): string {
    $value = get_post_meta($post->ID, $key, true);
    return $value === '' ? $default : (string) $value;
}

function computech_section_icon_svg(string $icon): string {
    $icon = sanitize_key($icon);
    $svgs = array(
        'gaming' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="3"/><circle cx="8" cy="12" r="2"/><circle cx="16" cy="12" r="2"/><line x1="12" y1="6" x2="12" y2="10"/><line x1="10" y1="8" x2="14" y2="8"/></svg>',
        'study' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.1 2.7 3 6 3s6-1.9 6-3v-5"/></svg>',
        'work' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
        'accessories' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
        'maintenance' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06A2 2 0 0 1 6.96 3.2l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c0 .65.38 1.24.98 1.51H21a2 2 0 0 1 0 4h-.09c-.65 0-1.24.38-1.51 1z"/></svg>',
        'tag' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
        'desktop' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        'globe' => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'offer' => '<svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    );
    return $svgs[$icon] ?? $svgs['desktop'];
}

function computech_section_icon_options(string $selected): string {
    $icons = array(
        'gaming' => 'ألعاب',
        'study' => 'دراسة',
        'work' => 'عمل',
        'accessories' => 'إكسسوارات',
        'maintenance' => 'صيانة',
        'tag' => 'استيراد / قيمة',
        'desktop' => 'كمبيوتر / لابتوب',
        'globe' => 'استيراد خارج',
        'offer' => 'عروض',
    );
    $html = '<select name="_computech_section_icon" class="widefat">';
    foreach ($icons as $key => $label) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function computech_section_link_url(WP_Post $post, string $prefix): string {
    return computech_hero_link_url_from_data($post, array(
        'link_type' => computech_section_meta($post, $prefix . '_link_type', 'none'),
        'page_id' => computech_section_meta($post, $prefix . '_page_id', '0'),
        'page_slug' => computech_section_meta($post, $prefix . '_page_slug', ''),
        'term_id' => computech_section_meta($post, $prefix . '_term_id', '0'),
        'url' => computech_section_meta($post, $prefix . '_url', ''),
        'new_tab' => computech_section_meta($post, $prefix . '_new_tab', '0'),
    ));
}

function computech_section_link_target(WP_Post $post, string $prefix): string {
    return computech_hero_button_target_from_data(array(
        'link_type' => computech_section_meta($post, $prefix . '_link_type', 'none'),
        'new_tab' => computech_section_meta($post, $prefix . '_new_tab', '0'),
    ));
}

function computech_default_customer_need_cards(): array {
    // No hard-coded customer need cards. This section reads only cards added from the dashboard.
    return array();
}

function computech_seed_default_customer_need_cards(): void {
    // Disabled intentionally: do not auto-create hard-coded customer need cards.
}
add_action('admin_init', 'computech_seed_default_customer_need_cards');
add_action('init', 'computech_seed_default_customer_need_cards', 38);

function computech_default_home_category_cards(): array {
    // No hard-coded category cards. Home shop section reads from product_category term meta only.
    return array();
}

function computech_seed_default_home_category_cards(): void {
    // Disabled intentionally: categories are managed from Product Categories taxonomy.
}

function computech_force_restore_default_home_category_cards(): void {
    // Disabled intentionally: do not create fallback category cards.
}

function computech_get_home_category_cards(): array {
    // Kept for backward compatibility only; not used for the home shop section.
    return array();
}

function computech_home_category_card_render_item($card): array {
    return array();
}


function computech_get_customer_need_cards(): array {
    $query = new WP_Query(array(
        'post_type' => 'computech_need_card',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'),
        'order' => 'ASC',
        'meta_query' => array(array('key' => '_computech_need_show', 'value' => '1', 'compare' => '=')),
        'no_found_rows' => true,
    ));
    $items = $query->posts;
    wp_reset_postdata();
    return is_array($items) ? $items : array();
}

function computech_render_customer_needs_section(): void {
    if (computech_home_section_option('needs_show', '0') !== '1') {
        return;
    }

    $cards = computech_get_customer_need_cards();
    if (empty($cards)) {
        return;
    }

    $title_before = computech_home_section_option('needs_title_before', '');
    $title_highlight = computech_home_section_option('needs_title_highlight', '');
    $subtitle = computech_home_section_option('needs_subtitle', '');
    ?>
    <section class="needs-section computech-dynamic-needs">
        <div class="needs-bg-pattern">
            <div class="needs-circuit needs-circuit-right"></div>
            <div class="needs-circuit needs-circuit-left"></div>
            <div class="needs-dots-pattern dots-right"></div>
            <div class="needs-dots-pattern dots-left"></div>
            <div class="needs-glow glow-right"></div>
            <div class="needs-glow glow-left"></div>
        </div>
        <div class="needs-container">
            <div class="needs-header">
                <div class="needs-decorative-dots"><span class="n-dot blue"></span><span class="n-dot cyan"></span><span class="n-pill"></span><span class="n-dot green"></span></div>
                <h2 class="needs-title"><?php echo esc_html($title_before); ?> <span class="title-highlight"><?php echo esc_html($title_highlight); ?></span></h2>
                <?php if ($subtitle !== '') : ?><p class="needs-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
            </div>
            <div class="needs-grid">
                <?php foreach ($cards as $card) :
                    if (!$card instanceof WP_Post) {
                        continue;
                    }
                    $title = get_the_title($card);
                    $text = computech_section_meta($card, '_computech_need_text', '');
                    $link_text = computech_section_meta($card, '_computech_need_link_text', '');
                    $url = computech_section_link_url($card, '_computech_need');
                    $target = computech_section_link_target($card, '_computech_need');
                    $need_image = computech_post_image_data((int) $card->ID, '_computech_need_image_id', 'full', '_computech_need_image_url');
                    $image = $need_image['url'];
                    $alt = $need_image['alt'] !== '' ? $need_image['alt'] : $title;
                    $badge_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', computech_section_meta($card, '_computech_need_badge', '')))));
                    $icon = computech_section_meta($card, '_computech_section_icon', 'desktop');
                    ?>
                    <div class="need-card">
                        <div class="need-card-text">
                            <div class="need-card-icon"><?php echo computech_section_icon_svg($icon); ?></div>
                            <?php if ($title !== '') : ?><h3 class="need-card-title"><?php echo esc_html($title); ?></h3><?php endif; ?>
                            <?php if (trim($text) !== '') : ?><p class="need-card-desc"><?php echo esc_html($text); ?></p><?php endif; ?>
                            <?php if ($url !== '' && trim($link_text) !== '') : ?><a href="<?php echo esc_url($url); ?>" class="need-card-link"<?php echo $target; ?>><?php echo esc_html($link_text); ?></a><?php endif; ?>
                        </div>
                        <?php if ($image !== '') : ?>
                            <div class="need-card-image">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>">
                                <?php if ($badge_lines) : ?><div class="need-card-badge"><?php foreach ($badge_lines as $line) : ?><span><?php echo esc_html($line); ?></span><?php endforeach; ?></div><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function computech_render_shop_category_card(array $item, string $card_size_class): void {
    $title = trim((string) ($item['title'] ?? ''));
    $text = trim((string) ($item['text'] ?? ''));
    $pill = trim((string) ($item['pill'] ?? ''));
    $link_text = trim((string) ($item['link_text'] ?? ''));
    $url = trim((string) ($item['url'] ?? ''));
    if ($url === '') {
        $url = computech_page_url('categories');
    }
    $target = (string) ($item['target'] ?? '');
    $image = trim((string) ($item['image'] ?? ''));
    $alt = trim((string) ($item['alt'] ?? $title));
    $icon = sanitize_key((string) ($item['icon'] ?? 'desktop'));
    $is_offer = $icon === 'offer';
    ?>
    <div class="shop-card <?php echo esc_attr($card_size_class); ?>">
        <div class="shop-card-content">
            <div class="shop-card-icon-wrap <?php echo $is_offer ? 'shop-icon-offer' : ''; ?>"><?php echo computech_section_icon_svg($icon); ?></div>
            <?php if ($title !== '') : ?><h3 class="shop-card-heading"><?php echo esc_html($title); ?></h3><?php endif; ?>
            <?php if ($text !== '') : ?><p class="shop-card-text"><?php echo esc_html($text); ?></p><?php endif; ?>
            <?php if ($pill !== '') : ?><span class="<?php echo $is_offer ? 'shop-card-offer-badge' : 'shop-card-pill'; ?>"><?php echo esc_html($pill); ?></span><?php endif; ?>
            <?php if ($link_text !== '') : ?><a href="<?php echo esc_url($url); ?>" class="shop-card-link"<?php echo $target; ?>><?php echo esc_html($link_text); ?></a><?php endif; ?>
        </div>
        <?php if ($image !== '') : ?><div class="shop-card-image"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt !== '' ? $alt : $title); ?>"></div><?php endif; ?>
    </div>
    <?php
}

function computech_render_shop_categories_section(): void {
    if (computech_home_section_option('shop_show', '0') !== '1') {
        return;
    }

    $items = function_exists('computech_get_shop_section_category_items') ? computech_get_shop_section_category_items() : array();
    if (empty($items)) {
        return;
    }

    $title_before = computech_home_section_option('shop_title_before', '');
    $title_highlight = computech_home_section_option('shop_title_highlight', '');
    $subtitle = computech_home_section_option('shop_subtitle', '');
    $top_items = array_slice($items, 0, 3);
    $bottom_items = array_slice($items, 3);
    ?>
    <section class="shop-section computech-dynamic-shop" id="shop-by-category">
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
                <div class="shop-decorative-dots"><span></span><span></span><span></span></div>
                <h2 class="shop-title"><?php echo esc_html($title_before); ?> <span class="shop-title-highlight"><?php echo esc_html($title_highlight); ?></span></h2>
                <?php if ($subtitle !== '') : ?><p class="shop-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
            </div>
            <div class="shop-cards-layout">
                <?php if (!empty($top_items)) : ?>
                    <div class="shop-row shop-row-3">
                        <?php foreach ($top_items as $item) { computech_render_shop_category_card($item, 'shop-card-lg'); } ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($bottom_items)) : ?>
                    <div class="shop-row shop-row-2">
                        <?php foreach ($bottom_items as $item) { computech_render_shop_category_card($item, count($bottom_items) <= 2 ? 'shop-card-xl' : 'shop-card-lg'); } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

// Computech categories/products architecture layer.
require_once get_template_directory() . '/inc/computech-architecture.php';
