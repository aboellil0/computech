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
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('custom-logo', array('height' => 80, 'width' => 220, 'flex-width' => true, 'flex-height' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    register_nav_menus(array('primary' => __('القائمة الرئيسية', 'computech')));
}
add_action('after_setup_theme', 'computech_setup');


/**
 * Global site identity helpers.
 *
 * These helpers make the theme read shared identity data from WordPress first:
 * Settings > General for Site Title/Tagline/Admin Email and the extra business
 * fields below, and Appearance > Customize > Site Identity for Logo/Site Icon.
 */
function computech_site_name(): string {
    $name = trim((string) get_bloginfo('name'));
    return $name !== '' ? $name : 'كمبيوتك';
}

function computech_site_description(): string {
    return trim((string) get_bloginfo('description'));
}

function computech_site_text(string $text): string {
    $site_name = computech_site_name();
    $replacements = array(
        '{{site_name}}' => $site_name,
        '{site_name}' => $site_name,
        'كمبيوتيك - Computech' => $site_name,
        'Computech - كمبيوتيك' => $site_name,
        'كمبيوتك - Computech' => $site_name,
        'Computech - كمبيوتك' => $site_name,
        'كمبيوتيك' => $site_name,
        'كمبيوتك' => $site_name,
        'Computech' => $site_name,
    );
    return strtr($text, $replacements);
}


function computech_site_text_deep($value, string $key = '') {
    $skip_parts = array('url', 'src', 'image', 'iframe', 'icon', 'platform', 'page_id', 'type', 'show', 'new_tab', 'id');
    foreach ($skip_parts as $part) {
        if ($key !== '' && stripos($key, $part) !== false) {
            return $value;
        }
    }

    if (is_string($value)) {
        return computech_site_text($value);
    }
    if (is_array($value)) {
        $out = array();
        foreach ($value as $k => $v) {
            $out[$k] = computech_site_text_deep($v, is_string($k) ? $k : '');
        }
        return $out;
    }
    return $value;
}

function computech_site_identity_image_url(): string {
    $logo_id = absint(get_theme_mod('custom_logo'));
    if ($logo_id) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        if ($logo_url) {
            return $logo_url;
        }
    }

    $site_icon = get_site_icon_url(512);
    return $site_icon ? (string) $site_icon : '';
}

function computech_general_setting(string $key, string $default = ''): string {
    $value = get_option('computech_general_' . $key, null);
    if ($value === null || $value === false) {
        return $default;
    }
    $value = trim((string) $value);
    return $value !== '' ? $value : $default;
}

function computech_business_phone(): string {
    return computech_general_setting('phone', '');
}

function computech_business_whatsapp_number(): string {
    $number = computech_general_setting('whatsapp_number', '');
    if ($number === '') {
        $number = function_exists('computech_header_setting') ? computech_header_setting('whatsapp_number', '') : '';
    }
    return computech_clean_phone($number);
}

function computech_business_email(): string {
    return computech_general_setting('email', (string) get_option('admin_email', ''));
}

function computech_business_address(): string {
    return computech_general_setting('address', '');
}

function computech_business_hours(): string {
    return computech_general_setting('business_hours', '');
}

function computech_business_map_url(): string {
    return computech_general_setting('map_url', '');
}

function computech_business_map_embed_url(): string {
    return computech_general_setting('map_embed_url', '');
}

function computech_tel_url(string $phone): string {
    $clean = computech_clean_phone($phone);
    return $clean !== '' ? 'tel:+' . $clean : '';
}

function computech_mailto_url(string $email): string {
    $email = sanitize_email($email);
    return $email !== '' ? 'mailto:' . $email : '';
}

function computech_register_general_settings_fields(): void {
    $fields = array(
        'phone' => array('label' => 'رقم الهاتف العام', 'sanitize' => 'sanitize_text_field', 'placeholder' => '+20 10 0000 0000'),
        'whatsapp_number' => array('label' => 'رقم واتساب العام', 'sanitize' => 'sanitize_text_field', 'placeholder' => '201000000000'),
        'email' => array('label' => 'البريد الإلكتروني العام', 'sanitize' => 'sanitize_email', 'placeholder' => get_option('admin_email', '')),
        'address' => array('label' => 'العنوان العام', 'sanitize' => 'sanitize_textarea_field', 'placeholder' => 'اكتب عنوان الشركة'),
        'business_hours' => array('label' => 'مواعيد العمل العامة', 'sanitize' => 'sanitize_text_field', 'placeholder' => 'السبت - الخميس، 9:00 ص - 9:00 م'),
        'map_url' => array('label' => 'رابط خرائط Google العام', 'sanitize' => 'esc_url_raw', 'placeholder' => 'https://maps.google.com/...'),
        'map_embed_url' => array('label' => 'رابط تضمين الخريطة iframe', 'sanitize' => 'esc_url_raw', 'placeholder' => 'https://www.google.com/maps/embed?...'),
    );

    add_settings_section(
        'computech_general_business_section',
        sprintf('بيانات الموقع العامة - %s', computech_site_name()),
        static function (): void {
            echo '<p>هذه البيانات تستخدم في الهيدر، زر واتساب العائم، الفوتر، وصفحات التواصل بدل القيم المكتوبة داخل ملفات القالب.</p>';
        },
        'general'
    );

    foreach ($fields as $key => $field) {
        register_setting('general', 'computech_general_' . $key, array(
            'type' => 'string',
            'sanitize_callback' => $field['sanitize'],
            'default' => '',
        ));

        add_settings_field(
            'computech_general_' . $key,
            $field['label'],
            'computech_render_general_settings_field',
            'general',
            'computech_general_business_section',
            array(
                'key' => $key,
                'placeholder' => $field['placeholder'],
                'textarea' => $key === 'address',
            )
        );
    }
}
add_action('admin_init', 'computech_register_general_settings_fields');

function computech_render_general_settings_field(array $args): void {
    $key = sanitize_key((string) ($args['key'] ?? ''));
    if ($key === '') {
        return;
    }
    $name = 'computech_general_' . $key;
    $value = (string) get_option($name, '');
    $placeholder = (string) ($args['placeholder'] ?? '');
    if (!empty($args['textarea'])) {
        echo '<textarea name="' . esc_attr($name) . '" rows="3" class="large-text" placeholder="' . esc_attr($placeholder) . '">' . esc_textarea($value) . '</textarea>';
        return;
    }
    echo '<input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . esc_attr($placeholder) . '">';
}

function computech_enqueue_assets(): void {
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();

    wp_enqueue_style('computech-theme-root', get_stylesheet_uri(), array(), filemtime($theme_dir . '/style.css'));
    wp_enqueue_style('computech-main', $theme_uri . '/assets/css/style.css', array('computech-theme-root'), filemtime($theme_dir . '/assets/css/style.css'));

    if (function_exists('is_woocommerce')) {
        wp_enqueue_script('wc-add-to-cart');
        if (wp_script_is('wc-cart-fragments', 'registered')) {
            wp_enqueue_script('wc-cart-fragments');
        }
    }

    wp_enqueue_script('computech-main', $theme_uri . '/assets/js/main.js', array('jquery'), filemtime($theme_dir . '/assets/js/main.js'), true);
    wp_localize_script('computech-main', 'computechTheme', array(
        'assetsUrl' => trailingslashit($theme_uri . '/assets/images'),
        'productsUrl' => computech_page_url('products'),
        'categoriesUrl' => computech_page_url('categories'),
        'servicesUrl' => computech_page_url('services'),
        'offersUrl' => computech_page_url('offers'),
        'contactUrl' => computech_page_url('contact'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'liveSearchNonce' => wp_create_nonce('computech_live_search'),
        'cartNonce' => wp_create_nonce('computech_cart_count'),
    ));
}
add_action('wp_enqueue_scripts', 'computech_enqueue_assets');

function computech_cart_badge_fragment(array $fragments): array {
    $count = function_exists('WC') && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
    $fragments['span.cart-badge'] = '<span class="cart-badge">' . esc_html((string) $count) . '</span>';
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'computech_cart_badge_fragment');

function computech_ajax_cart_count(): void {
    check_ajax_referer('computech_cart_count', 'nonce');
    wp_send_json_success(array(
        'count' => function_exists('WC') && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0,
    ));
}
add_action('wp_ajax_computech_cart_count', 'computech_ajax_cart_count');
add_action('wp_ajax_nopriv_computech_cart_count', 'computech_ajax_cart_count');

function computech_ajax_live_product_search(): void {
    check_ajax_referer('computech_live_search', 'nonce');

    if (!class_exists('WooCommerce')) {
        wp_send_json_success(array('items' => array()));
    }

    $term = isset($_GET['term']) ? sanitize_text_field(wp_unslash($_GET['term'])) : '';
    $term = trim($term);
    if ($term === '') {
        wp_send_json_success(array('items' => array()));
    }

    $query = new WP_Query(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 3,
        's' => $term,
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
    ));

    $items = array();
    foreach ($query->posts as $post) {
        $product = wc_get_product($post->ID);
        if (!$product) {
            continue;
        }
        $image = get_the_post_thumbnail_url($post->ID, 'thumbnail');
        $items[] = array(
            'title' => html_entity_decode(get_the_title($post->ID), ENT_QUOTES, get_bloginfo('charset')),
            'url' => get_permalink($post->ID),
            'image' => $image ?: '',
            'price' => wp_strip_all_tags($product->get_price_html()),
        );
    }
    wp_reset_postdata();

    wp_send_json_success(array('items' => $items));
}
add_action('wp_ajax_computech_live_product_search', 'computech_ajax_live_product_search');
add_action('wp_ajax_nopriv_computech_live_product_search', 'computech_ajax_live_product_search');

function computech_register_products_cpt(): void {
    // Products/categories are controlled by WooCommerce only.
    // Old custom CPT/taxonomy disabled intentionally.
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
    if (function_exists('computech_seed_footer_database_options')) { computech_seed_footer_database_options(); }
    computech_seed_default_home_section_options();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'computech_activate');

function computech_admin_ensure_pages(): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    computech_seed_header_database_options();
    if (function_exists('computech_seed_footer_database_options')) { computech_seed_footer_database_options(); }
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
    $number = computech_business_whatsapp_number();
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
    
    $map_url = computech_business_map_url();
    if ($map_url !== '') {
        return $map_url;
    }
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(computech_site_name());
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
        $args['product_cat'] = $category;
    }
    if ($status !== '') {
        $args['stock_status'] = $status;
    }
    $url = function_exists('computech_wc_products_page_url') ? computech_wc_products_page_url() : computech_page_url('products');
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

    if ($slug === 'categories' && (is_tax('product_cat') || is_tax('product_category'))) {
        return true;
    }

    if ($slug === 'products' && (is_singular('product') || is_singular('products') || is_post_type_archive('product'))) {
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

function computech_header_removed_label_keys(): array {
    return array(
        'logo_aria_label',
        'logo_alt_text',
        'nav_aria_label',
        'more_menu_label',
        'search_button_label',
        'account_label',
        'cart_label',
        'mobile_menu_button_label',
        'mobile_menu_title',
        'mobile_menu_close_label',
    );
}

function computech_header_label(string $key, string $fallback = ''): string {
    if (in_array($key, computech_header_removed_label_keys(), true)) {
        return $fallback;
    }
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

    // Keep all WordPress menu items. Children must not be filtered out,
    // otherwise Appearance > Menus subitems disappear from the frontend.
    usort($items, static function ($a, $b): int {
        return ((int) $a->menu_order) <=> ((int) $b->menu_order);
    });

    return $items;
}

function computech_prepare_primary_nav_tree(): array {
    $items = computech_get_primary_nav_menu_items();
    $children = array();
    $roots = array();

    foreach ($items as $item) {
        $parent_id = absint($item->menu_item_parent ?? 0);
        if ($parent_id > 0) {
            if (!isset($children[$parent_id])) {
                $children[$parent_id] = array();
            }
            $children[$parent_id][] = $item;
        } else {
            $roots[] = $item;
        }
    }

    computech_attach_product_categories_to_shop_menu($roots, $children);

    usort($roots, static function ($a, $b): int {
        return ((int) $a->menu_order) <=> ((int) $b->menu_order);
    });
    foreach ($children as &$child_items) {
        usort($child_items, static function ($a, $b): int {
            return ((int) $a->menu_order) <=> ((int) $b->menu_order);
        });
    }
    unset($child_items);

    return array(
        'roots' => $roots,
        'children' => $children,
    );
}


function computech_is_shop_categories_nav_item($item): bool {
    $title = trim(wp_strip_all_tags((string) ($item->title ?? '')));
    $normalized = preg_replace('/\s+/u', '', $title);

    if ($normalized === 'أقسامالمتجر' || $normalized === 'اقسامالمتجر') {
        return true;
    }

    $url_path = trim((string) wp_parse_url((string) ($item->url ?? ''), PHP_URL_PATH), '/');
    return $url_path !== '' && (strpos($url_path, 'categories') !== false || strpos($url_path, 'product-category') !== false);
}

function computech_make_product_category_nav_item(WP_Term $term, int $parent_id, int $order): object {
    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
        $term_link = '#';
    }

    return (object) array(
        'ID' => 900000000 + absint($term->term_id),
        'db_id' => 900000000 + absint($term->term_id),
        'menu_item_parent' => $parent_id,
        'menu_order' => $order,
        'title' => $term->name,
        'url' => $term_link,
        'target' => '',
        'xfn' => '',
        'classes' => array('menu-item', 'menu-item-type-taxonomy', 'menu-item-object-product_cat', 'computech-auto-product-cat'),
    );
}

function computech_existing_menu_child_url_map(array $children, int $parent_id): array {
    $urls = array();
    if (empty($children[$parent_id])) {
        return $urls;
    }

    foreach ($children[$parent_id] as $child) {
        $url = trim((string) ($child->url ?? ''));
        $child_id = computech_nav_item_id($child);
        if ($url !== '' && $child_id > 0) {
            $urls[untrailingslashit($url)] = $child_id;
        }
    }

    return $urls;
}

function computech_attach_product_categories_to_shop_menu(array $roots, array &$children): void {
    if (!taxonomy_exists('product_cat')) {
        return;
    }

    $shop_item = null;
    foreach ($roots as $root) {
        if (computech_is_shop_categories_nav_item($root)) {
            $shop_item = $root;
            break;
        }
    }

    if (!$shop_item) {
        return;
    }

    $shop_item_id = computech_nav_item_id($shop_item);
    if ($shop_item_id <= 0) {
        return;
    }

    $terms = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
        'exclude' => array(get_option('default_product_cat')),
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return;
    }

    $existing_url_map = computech_existing_menu_child_url_map($children, $shop_item_id);
    $terms_by_parent = array();
    foreach ($terms as $term) {
        if (!($term instanceof WP_Term)) {
            continue;
        }
        $parent = absint($term->parent);
        if (!isset($terms_by_parent[$parent])) {
            $terms_by_parent[$parent] = array();
        }
        $terms_by_parent[$parent][] = $term;
    }

    if (empty($children[$shop_item_id])) {
        $children[$shop_item_id] = array();
    }

    $add_terms = static function (int $term_parent, int $menu_parent_id, int $base_order) use (&$add_terms, &$children, $terms_by_parent, $existing_url_map): void {
        if (empty($terms_by_parent[$term_parent])) {
            return;
        }

        $order = $base_order;
        foreach ($terms_by_parent[$term_parent] as $term) {
            $term_link = get_term_link($term);
            if (is_wp_error($term_link)) {
                $term_link = '#';
            }

            $normalized_url = untrailingslashit((string) $term_link);
            $auto_id = 900000000 + absint($term->term_id);
            $render_parent_id = $auto_id;

            if (isset($existing_url_map[$normalized_url])) {
                $render_parent_id = absint($existing_url_map[$normalized_url]);
            } else {
                if (empty($children[$menu_parent_id])) {
                    $children[$menu_parent_id] = array();
                }
                $children[$menu_parent_id][] = computech_make_product_category_nav_item($term, $menu_parent_id, $order);
            }

            $add_terms(absint($term->term_id), $render_parent_id, $order + 1000);
            $order++;
        }
    };

    $add_terms(0, $shop_item_id, 10000);
}

function computech_nav_item_id($item): int {
    if (isset($item->ID)) {
        return absint($item->ID);
    }
    if (isset($item->db_id)) {
        return absint($item->db_id);
    }
    return 0;
}

function computech_nav_item_has_children($item, array $children): bool {
    $id = computech_nav_item_id($item);
    return $id > 0 && !empty($children[$id]);
}

function computech_is_wp_nav_item_self_active($item): bool {
    $classes = is_array($item->classes ?? null) ? $item->classes : array();
    $active_classes = array('current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current-menu-parent', 'current_page_parent');
    if (array_intersect($active_classes, $classes)) {
        return true;
    }

    $url = trim((string) ($item->url ?? ''));
    if ($url === '' || $url === '#') {
        return false;
    }

    $target_path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
    $current_path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

    if ($target_path === '') {
        return is_front_page() || $current_path === '';
    }

    return $current_path === $target_path || strpos($current_path, $target_path . '/') === 0;
}

function computech_is_wp_nav_item_active($item, array $children = array()): bool {
    if (computech_is_wp_nav_item_self_active($item)) {
        return true;
    }

    $id = computech_nav_item_id($item);
    if ($id <= 0 || empty($children[$id])) {
        return false;
    }

    foreach ($children[$id] as $child) {
        if (computech_is_wp_nav_item_active($child, $children)) {
            return true;
        }
    }

    return false;
}

function computech_nav_item_class_attr($item, array $children, bool $active, string $extra = ''): string {
    $classes = array('menu-item');
    $wp_classes = is_array($item->classes ?? null) ? array_filter(array_map('sanitize_html_class', $item->classes)) : array();
    $classes = array_merge($classes, $wp_classes);

    if (computech_nav_item_has_children($item, $children)) {
        $classes[] = 'menu-item-has-children';
    }
    if ($active) {
        $classes[] = 'current-menu-item';
        $classes[] = 'is-active-menu-item';
    }
    if ($extra !== '') {
        $classes[] = sanitize_html_class($extra);
    }

    $classes = array_values(array_unique(array_filter($classes)));
    return ' class="' . esc_attr(implode(' ', $classes)) . '"';
}

function computech_render_nav_arrow(): void {
    echo '<svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
}

function computech_render_wp_nav_menu_item($item, array $children, array $args = array()): void {
    $defaults = array(
        'link_class' => 'nav-link',
        'sub_link_class' => 'nav-sub-link',
        'sub_menu_class' => 'nav-sub-menu',
        'li_class' => '',
        'depth' => 0,
        'max_depth' => 0,
    );
    $args = array_merge($defaults, $args);

    $title = trim((string) ($item->title ?? ''));
    $url = trim((string) ($item->url ?? ''));
    if ($title === '') {
        return;
    }

    $has_children = computech_nav_item_has_children($item, $children);
    $active = computech_is_wp_nav_item_active($item, $children);
    $is_top_level = (int) $args['depth'] === 0;
    $link_class = $is_top_level ? (string) $args['link_class'] : (string) $args['sub_link_class'];
    $link_class .= $active ? ' active' : '';

    $target = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
    $rel_parts = array();
    if (!empty($item->xfn)) {
        $rel_parts[] = trim((string) $item->xfn);
    }
    if (!empty($item->target) && (string) $item->target === '_blank') {
        $rel_parts[] = 'noopener';
    }
    $rel = $rel_parts ? ' rel="' . esc_attr(implode(' ', array_unique($rel_parts))) . '"' : '';
    $href = $url !== '' ? esc_url($url) : '#';
    $li_class_attr = computech_nav_item_class_attr($item, $children, $active, (string) $args['li_class']);

    echo '<li' . $li_class_attr . '>';
    echo '<a href="' . $href . '" class="' . esc_attr($link_class) . '"' . $target . $rel . ($has_children ? ' aria-haspopup="true" aria-expanded="false"' : '') . '>';
    echo '<span>' . esc_html($title) . '</span>';
    if ($has_children) {
        computech_render_nav_arrow();
    }
    echo '</a>';

    $max_depth = (int) $args['max_depth'];
    $depth = (int) $args['depth'];
    if ($has_children && ($max_depth <= 0 || $depth < $max_depth)) {
        $child_id = computech_nav_item_id($item);
        echo '<ul class="' . esc_attr((string) $args['sub_menu_class']) . '">';
        foreach ($children[$child_id] as $child) {
            $child_args = $args;
            $child_args['depth'] = $depth + 1;
            $child_args['li_class'] = '';
            computech_render_wp_nav_menu_item($child, $children, $child_args);
        }
        echo '</ul>';
    }

    echo '</li>';
}

function computech_render_primary_links(string $class = 'nav-link'): void {
    $tree = computech_prepare_primary_nav_tree();
    $items = $tree['roots'];
    $children = $tree['children'];

    if (!$items) {
        if (current_user_can('edit_theme_options')) {
            echo '<li><a class="' . esc_attr($class) . '" href="' . esc_url(admin_url('nav-menus.php')) . '">اربط القائمة الرئيسية من المظهر ← القوائم</a></li>';
        }
        return;
    }

    $is_mobile = strpos($class, 'mobile') !== false;
    $desktop_visible_limit = 8;

    $base_args = $is_mobile
        ? array(
            'link_class' => $class,
            'sub_link_class' => 'mobile-nav-link mobile-sub-link',
            'sub_menu_class' => 'mobile-sub-menu',
            'max_depth' => 0,
        )
        : array(
            'link_class' => $class,
            'sub_link_class' => 'nav-sub-link',
            'sub_menu_class' => 'nav-sub-menu',
            'max_depth' => 0,
        );

    if (!$is_mobile && count($items) > $desktop_visible_limit) {
        $main = array_slice($items, 0, $desktop_visible_limit);
        $extra = array_slice($items, $desktop_visible_limit);

        foreach ($main as $item) {
            computech_render_wp_nav_menu_item($item, $children, $base_args);
        }

        $extra_active = false;
        foreach ($extra as $item) {
            if (computech_is_wp_nav_item_active($item, $children)) {
                $extra_active = true;
                break;
            }
        }

        $more_label = computech_header_label('more_menu_label', 'المزيد');
        echo '<li class="nav-more"><button class="' . esc_attr($class . ($extra_active ? ' active' : '')) . ' nav-more-toggle" type="button" aria-haspopup="true" aria-expanded="false">' . esc_html($more_label) . ' <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg></button><ul class="nav-more-menu">';
        foreach ($extra as $item) {
            $more_args = array(
                'link_class' => 'nav-more-link',
                'sub_link_class' => 'nav-more-sub-link',
                'sub_menu_class' => 'nav-more-sub-menu',
                'max_depth' => 0,
            );
            computech_render_wp_nav_menu_item($item, $children, $more_args);
        }
        echo '</ul></li>';
        return;
    }

    foreach ($items as $item) {
        computech_render_wp_nav_menu_item($item, $children, $base_args);
    }
}

function computech_header_logo_html(): string {
    // WordPress Site Identity logo is the source of truth.
    $logo_id = absint(get_theme_mod('custom_logo'));

    // Backward compatibility only: old Computech header logo is used if no Site Identity logo exists.
    if (!$logo_id) {
        $logo_id = absint(get_option('computech_header_logo_id', 0));
    }

    $src = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    if (!$src) {
        $src = get_site_icon_url(512);
    }

    $alt = computech_site_text(computech_header_label('logo_alt_text', computech_site_name()));
    if ($src) {
        return '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" class="logo-img computech-header-logo-img">';
    }

    return '<span class="logo-text-fallback">' . esc_html(computech_site_name()) . '</span>';
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
    if (function_exists('computech_register_customer_need_cards_cpt') && !post_type_exists('computech_need_card')) {
        computech_register_customer_need_cards_cpt();
    }

    if (function_exists('computech_register_home_offer_banner_cpt') && !post_type_exists('ct_offer_banner')) {
        computech_register_home_offer_banner_cpt();
    }

    add_menu_page('General', 'General', computech_admin_capability(), 'computech-settings', 'computech_settings_page', 'dashicons-admin-generic', 58);
    add_submenu_page('computech-settings', 'الهيدر', 'الهيدر', computech_admin_capability(), 'computech-settings', 'computech_settings_page');

    // Explicit submenu. Post type slug must stay <= 20 chars.
    add_submenu_page('computech-settings', 'عروض وبنرات', 'عروض وبنرات', computech_admin_capability(), 'edit.php?post_type=ct_offer_banner');
}
add_action('admin_menu', 'computech_admin_menu');

function computech_reorder_general_admin_submenus(): void {
    global $submenu;

    if (empty($submenu['computech-settings']) || !is_array($submenu['computech-settings'])) {
        return;
    }

    $wanted_order = array(
        'computech-settings' => 0,
        'edit.php?post_type=computech_need_card' => 1,
        'edit.php?post_type=ct_offer_banner' => 2,
    );

    usort($submenu['computech-settings'], static function ($a, $b) use ($wanted_order): int {
        $a_slug = isset($a[2]) ? (string) $a[2] : '';
        $b_slug = isset($b[2]) ? (string) $b[2] : '';
        $a_order = $wanted_order[$a_slug] ?? 100;
        $b_order = $wanted_order[$b_slug] ?? 100;

        if ($a_order === $b_order) {
            return 0;
        }

        return $a_order <=> $b_order;
    });

    // Remove duplicate submenu items that may be added by CPT auto menu + manual menu.
    $seen = array();
    $deduped = array();
    foreach ($submenu['computech-settings'] as $item) {
        $slug = isset($item[2]) ? (string) $item[2] : '';
        if ($slug !== '' && isset($seen[$slug])) {
            continue;
        }
        if ($slug !== '') {
            $seen[$slug] = true;
        }
        $deduped[] = $item;
    }
    $submenu['computech-settings'] = $deduped;

    foreach ($submenu['computech-settings'] as &$item) {
        if (isset($item[2]) && $item[2] === 'computech-settings') {
            $item[0] = 'الهيدر';
            break;
        }
    }
    unset($item);
}
add_action('admin_menu', 'computech_reorder_general_admin_submenus', 999);


function computech_admin_assets(string $hook): void {
    if ($hook !== 'toplevel_page_computech-settings' && strpos($hook, 'computech-settings') === false) {
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
        'show_search' => !empty($_POST['show_search']) ? '1' : '0',
        'show_account' => !empty($_POST['show_account']) ? '1' : '0',
        'show_cart' => !empty($_POST['show_cart']) ? '1' : '0',
        'whatsapp_number' => computech_clean_phone(sanitize_text_field(wp_unslash($_POST['whatsapp_number'] ?? ''))),
        'whatsapp_label' => sanitize_text_field(wp_unslash($_POST['whatsapp_label'] ?? '')),
        'whatsapp_message' => sanitize_textarea_field(wp_unslash($_POST['whatsapp_message'] ?? '')),
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
        wp_die(esc_html__('غير مسموح لك بالدخول إلى إعدادات الهيدر.', 'computech'));
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
        <h1>الهيدر</h1>
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
            'name' => 'سلايدر الرئيسية',
            'singular_name' => 'شريحة سلايدر الرئيسية',
            'menu_name' => 'سلايدر الرئيسية',
            'add_new_item' => 'إضافة شريحة جديدة',
            'edit_item' => 'تعديل شريحة',
            'new_item' => 'شريحة جديدة',
            'view_item' => 'عرض الشريحة',
            'search_items' => 'بحث في الشرائح',
            'not_found' => 'لا توجد شرائح',
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
        '_computech_hero_title' => '',
        '_computech_hero_title_line_1' => '',
        '_computech_hero_title_highlight' => '',
        '_computech_hero_title_line_3' => '',
        '_computech_hero_description' => '',
        '_computech_hero_features' => '',
        '_computech_hero_tags' => array(),
        '_computech_hero_badge_line_1' => '',
        '_computech_hero_badge_line_2' => '',
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
        '_computech_hero_image_url' => '',
        '_computech_hero_image_alt' => '',
        '_computech_hero_buttons' => array(),
    );
}

function computech_seed_default_hero_slides(): void {
    // Disabled intentionally: Hero Section records must be added/edited from the dashboard.
}

function computech_add_hero_slide_metaboxes(): void {
    add_meta_box('computech_hero_slide_data', 'بيانات سلايدر الرئيسية', 'computech_hero_slide_metabox', 'computech_hero_slide', 'normal', 'high');
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
    $terms = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
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


function computech_hero_full_title(WP_Post $slide): string {
    $title = trim(computech_hero_meta($slide, '_computech_hero_title', ''));
    if ($title !== '') {
        return $title;
    }

    $parts = array_filter(array_map('trim', array(
        computech_hero_meta($slide, '_computech_hero_title_line_1', ''),
        computech_hero_meta($slide, '_computech_hero_title_highlight', ''),
        computech_hero_meta($slide, '_computech_hero_title_line_3', ''),
    )));

    return trim(implode(' ', $parts));
}

function computech_normalize_hero_tag(array $tag): array {
    return array(
        'show' => !empty($tag['show']) ? '1' : '0',
        'text' => sanitize_text_field((string) ($tag['text'] ?? '')),
    );
}

function computech_get_hero_tags(WP_Post $slide): array {
    $stored = get_post_meta($slide->ID, '_computech_hero_tags', true);
    $tags = array();

    if (is_array($stored)) {
        foreach ($stored as $tag) {
            if (!is_array($tag)) {
                continue;
            }
            $clean = computech_normalize_hero_tag($tag);
            if ($clean['text'] !== '') {
                $tags[] = $clean;
            }
        }
    }

    if ($tags) {
        return $tags;
    }

    $legacy = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', computech_hero_meta($slide, '_computech_hero_features', '')))));
    foreach ($legacy as $text) {
        $tags[] = array('show' => '1', 'text' => $text);
    }

    return $tags;
}

function computech_get_hero_display_tags(WP_Post $slide): array {
    $selected = array();
    foreach (computech_get_hero_tags($slide) as $tag) {
        $tag = computech_normalize_hero_tag($tag);
        if ($tag['show'] !== '1' || $tag['text'] === '') {
            continue;
        }
        $selected[] = $tag['text'];
        if (count($selected) >= 4) {
            break;
        }
    }
    return $selected;
}

function computech_hero_tag_row_html(array $tag, $index): string {
    $tag = computech_normalize_hero_tag($tag);
    $base = '_computech_hero_tags[' . $index . ']';
    ob_start();
    ?>
    <div class="ct-hero-repeat-row" data-hero-tag-row>
        <div class="ct-hero-repeat-head">
            <div class="ct-hero-repeat-title"><span class="ct-drag-handle">#</span><span>كلمة دليلية</span></div>
            <div class="ct-repeat-actions">
                <label><input type="checkbox" name="<?php echo esc_attr($base); ?>[show]" value="1" <?php checked($tag['show'], '1'); ?>> تظهر في الواجهة</label>
                <button type="button" class="button-link-delete" data-remove-hero-tag>حذف</button>
            </div>
        </div>
        <div class="ct-hero-repeat-grid" style="grid-template-columns:1fr;">
            <p class="ct-field"><label>النص</label><input type="text" name="<?php echo esc_attr($base); ?>[text]" value="<?php echo esc_attr($tag['text']); ?>" class="widefat" placeholder="مثال: أجهزة جديدة"></p>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
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
                <h2>سلايدر الرئيسية</h2>
                <p>أضف، عدّل، أو احذف شرائح الهيرو من هنا. كل شريحة لها نفس الحقول.</p>
            </div>
            <div class="ct-status-card">
                <strong>الظهور من WordPress</strong>
                <p class="description">الشريحة تظهر فقط عند الحالة Published والرؤية Public. استخدم صندوق Publish الجانبي للتحكم.</p>
            </div>
        </div>

        <div class="ct-hero-dashboard">
            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>1. محتوى الشريحة</h3>
                        <p>كل شريحة لها نفس الحقول: عنوان، وصف، كلمات دليلية، وأزرار. صورة الشريحة من Featured Image.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-grid ct-grid-2">
                        <p class="ct-field"><label>عنوان الشريحة</label><input type="text" name="_computech_hero_title" value="<?php echo esc_attr(computech_hero_full_title($post)); ?>" class="widefat" placeholder="كل ما تحتاجه لعالم الكمبيوتر في مكان واحد"></p>
                        <p class="ct-field"><label>الوصف القصير</label><textarea name="_computech_hero_description" rows="3" class="widefat" maxlength="220"><?php echo esc_textarea(computech_hero_meta($post, '_computech_hero_description', $defaults['_computech_hero_description'])); ?></textarea><span class="ct-help">يفضل ألا يزيد عن سطرين في الواجهة.</span></p>
                    </div>
                    <div class="ct-admin-section" style="margin-top:14px; box-shadow:none;">
                        <div class="ct-admin-section-head">
                            <div>
                                <h3>البادج العائم</h3>
                                <p>اكتب نص البادج الظاهر فوق صورة الهيرو. لو الحقلين فاضيين، البادج لا يظهر.</p>
                            </div>
                        </div>
                        <div class="ct-admin-section-body">
                            <div class="ct-grid ct-grid-2">
                                <p class="ct-field"><label>النص الأول</label><input type="text" name="_computech_hero_badge_line_1" value="<?php echo esc_attr(computech_hero_meta($post, '_computech_hero_badge_line_1', $defaults['_computech_hero_badge_line_1'])); ?>" class="widefat" placeholder="أحدث الأجهزة"></p>
                                <p class="ct-field"><label>النص الثاني</label><input type="text" name="_computech_hero_badge_line_2" value="<?php echo esc_attr(computech_hero_meta($post, '_computech_hero_badge_line_2', $defaults['_computech_hero_badge_line_2'])); ?>" class="widefat" placeholder="أفضل الأسعار"></p>
                            </div>
                        </div>
                    </div>
                    <div class="ct-admin-section" style="margin-top:14px; box-shadow:none;">
                        <div class="ct-admin-section-head">
                            <div>
                                <h3>الكلمات الدليلية Tags</h3>
                                <p>أضف أي عدد كلمات. علّم فقط على التي تظهر. الواجهة تعرض أول 4 كلمات مفعلة فقط.</p>
                            </div>
                            <button type="button" class="button button-primary ct-add-button" id="ct-add-hero-tag">+ إضافة كلمة</button>
                        </div>
                        <div class="ct-admin-section-body">
                            <div id="ct-hero-tags-list" class="ct-repeat-list">
                                <?php $hero_tags = computech_get_hero_tags($post); foreach ($hero_tags as $i => $tag) { echo computech_hero_tag_row_html($tag, (int) $i); } ?>
                            </div>
                            <template id="ct-hero-tag-template"><?php echo computech_hero_tag_row_html(array('show' => '1', 'text' => ''), '__INDEX__'); ?></template>
                        </div>
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
                        <h3>3. صورة الشريحة</h3>
                        <p>استخدم صندوق Featured Image الجانبي في WordPress. الواجهة تقرأ الصورة والـ Alt Text منه مباشرة.</p>
                    </div>
                </div>
            </section>

        </div>
    </div>
    <script>
    (function(){
        var tagList = document.getElementById('ct-hero-tags-list');
        var addTagBtn = document.getElementById('ct-add-hero-tag');
        var tagTemplate = document.getElementById('ct-hero-tag-template');
        if (tagList && addTagBtn && tagTemplate) {
            function bindTags(scope) {
                (scope || tagList).querySelectorAll('[data-remove-hero-tag]').forEach(function(btn){
                    if (btn.dataset.bound === '1') { return; }
                    btn.dataset.bound = '1';
                    btn.addEventListener('click', function(){
                        var row = btn.closest('[data-hero-tag-row]');
                        if (row) { row.remove(); }
                    });
                });
            }
            bindTags(tagList);
            addTagBtn.addEventListener('click', function(){
                var index = Date.now();
                var wrapper = document.createElement('div');
                wrapper.innerHTML = tagTemplate.innerHTML.replace(/__INDEX__/g, index);
                var row = wrapper.firstElementChild;
                tagList.appendChild(row);
                bindTags(row);
            });
        }

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
        '_computech_hero_title',
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

    foreach (array('_computech_hero_description', '_computech_hero_features') as $field) {
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
    $hero_tags = array();
    $tag_rows = $_POST['_computech_hero_tags'] ?? array();
    if (is_array($tag_rows)) {
        foreach ($tag_rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $clean = computech_normalize_hero_tag(array(
                'show' => !empty($row['show']) ? '1' : '0',
                'text' => sanitize_text_field(wp_unslash($row['text'] ?? '')),
            ));
            if ($clean['text'] !== '') {
                $hero_tags[] = $clean;
            }
        }
    }
    update_post_meta($post_id, '_computech_hero_tags', $hero_tags);

    update_post_meta($post_id, '_computech_hero_buttons', $hero_buttons);

    delete_post_meta($post_id, '_computech_hero_image_id');
    delete_post_meta($post_id, '_computech_hero_show');
}
add_action('save_post_computech_hero_slide', 'computech_save_hero_slide');

function computech_get_hero_slides(): array {
    $query = new WP_Query(array(
        'post_type' => 'computech_hero_slide',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'),
        'order' => 'ASC',
        'has_password' => false,
    ));
    return array_values(array_filter($query->posts, static function ($slide): bool {
        return $slide instanceof WP_Post && $slide->post_status === 'publish' && $slide->post_password === '';
    }));
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
            $url = get_term_link($term_id, 'product_cat');
            if (!is_wp_error($url)) {
                return (string) $url;
            }
        }
        return function_exists('computech_wc_products_page_url') ? computech_wc_products_page_url() : computech_page_url('categories');
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
    $title = computech_hero_full_title($slide);
    if ($title === '') {
        return;
    }
    $description = computech_hero_meta($slide, '_computech_hero_description', '');
    $features = computech_get_hero_display_tags($slide);
    $hero_image = computech_attachment_image_data((int) get_post_thumbnail_id($slide), 'full');
    $image = $hero_image['url'];
    $alt = $hero_image['alt'] !== '' ? $hero_image['alt'] : get_the_title($slide);
    $badge_1 = trim(computech_hero_meta($slide, '_computech_hero_badge_line_1', ''));
    $badge_2 = trim(computech_hero_meta($slide, '_computech_hero_badge_line_2', ''));
    ?>
    <div class="hero-slide <?php echo $index === 0 ? 'is-active' : ''; ?>" data-hero-slide>
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-decorative-dots"><span class="h-dot blue"></span><span class="h-dot cyan"></span><span class="h-dot green"></span></div>
                <h1 class="hero-headline"><?php echo esc_html($title); ?></h1>
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

    $slides = array_values(array_filter($slides, static function ($slide): bool {
        return $slide instanceof WP_Post && computech_hero_full_title($slide) !== '';
    }));

    if (!$slides) {
        return;
    }

    $slides_count = count($slides);
    ?>
    <!-- Hero Section -->
    <section class="hero-section computech-dynamic-hero" data-hero-slider="1">
        <div class="hero-bg-pattern">
            <div class="circuit-line circuit-1"></div><div class="circuit-line circuit-2"></div><div class="circuit-line circuit-3"></div><div class="circuit-line circuit-4"></div>
            <div class="circuit-dot dot-1"></div><div class="circuit-dot dot-2"></div><div class="circuit-dot dot-3"></div><div class="circuit-dot dot-4"></div><div class="circuit-dot dot-5"></div><div class="circuit-dot dot-6"></div>
            <div class="glow-circle glow-1"></div><div class="glow-circle glow-2"></div><div class="glow-circle glow-3"></div>
        </div>
        <div class="hero-slides-shell">
            <?php foreach ($slides as $index => $slide) { computech_render_hero_slide($slide, (int) $index); } ?>
        </div>
        <?php if ($slides_count > 1) : ?>
            <div class="hero-slider-controls" aria-label="سلايدر الهيرو">
                <button type="button" class="hero-slider-arrow hero-slider-prev" data-hero-prev aria-label="السلايد السابق">‹</button>
                <div class="hero-slider-dots" role="tablist" aria-label="شرائح الهيرو">
                    <?php foreach ($slides as $index => $slide) : ?>
                        <button type="button" class="hero-slider-dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-hero-dot="<?php echo esc_attr((string) $index); ?>" aria-label="عرض السلايد <?php echo esc_attr((string) ($index + 1)); ?>"></button>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="hero-slider-arrow hero-slider-next" data-hero-next aria-label="السلايد التالي">›</button>
            </div>
        <?php endif; ?>
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
                <p>الظهور من حالة النشر والرؤية في WordPress. الصورة من Featured image. العنوان من Title، والترتيب من Order.</p>
            </div>
            <div class="ct-status-card">
                <strong>ظهور الكارت</strong>
                <p class="description">Published + Public = يظهر. Draft / Pending / Private / Password protected = لا يظهر.</p>
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
                        <div class="ct-mini-info"><strong>الصورة</strong><span>اختارها من Featured image.</span></div>
                        <div class="ct-mini-info"><strong>الترتيب</strong><span>استخدم Order من Page Attributes.</span></div>
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
                        <h3>3. الصورة</h3>
                        <p>ارفع الصورة من صندوق Featured image الموجود في جانب الصفحة.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-note"><span>ℹ️</span><div>واجهة الموقع تستخدم Featured image وAlt Text من Media Library تلقائيًا. لا يوجد حقل صورة مخصص هنا.</div></div>
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
    delete_post_meta($post_id, '_computech_card_show');
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
    delete_post_meta($post_id, '_computech_card_image_id');
    delete_post_meta($post_id, '_computech_card_image_url');
}
add_action('save_post_computech_hero_card', 'computech_save_hero_card');

function computech_get_hero_cards(): array {
    $query = new WP_Query(array(
        'post_type' => 'computech_hero_card',
        'post_status' => 'publish',
        'post_password' => '',
        'has_password' => false,
        'posts_per_page' => 6,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'),
        'order' => 'ASC',
    ));
    $cards = is_array($query->posts) ? $query->posts : array();
    if (count($cards) > 4) {
        $cards = array_slice($cards, -4);
    }
    return $cards;
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
            $thumb_id = (int) get_post_thumbnail_id($card->ID);
            $card_image = $thumb_id ? computech_attachment_image_data($thumb_id, 'full') : array('url' => '', 'alt' => '');
            $image = $card_image['url'] ?? '';
            $alt = !empty($card_image['alt']) ? (string) $card_image['alt'] : $title;
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
    if (!is_array($items)) {
        return array();
    }
    return array_slice($items, 0, 6);
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
        $number = computech_business_whatsapp_number();
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
            'name' => 'ابدأ من احتياجك',
            'singular_name' => 'كارت احتياج',
            'menu_name' => 'ابدأ من احتياجك',
            'add_new_item' => 'إضافة كارت جديد',
            'edit_item' => 'تعديل كارت',
            'new_item' => 'كارت جديد',
            'search_items' => 'بحث في كروت ابدأ من احتياجك',
            'not_found' => 'لا توجد كروت',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'computech-settings',
        'supports' => array('title', 'thumbnail', 'page-attributes'),
        'capability_type' => 'page',
        'map_meta_cap' => true,
        'show_in_rest' => false,
    ));
}
add_action('init', 'computech_register_customer_need_cards_cpt');

function computech_add_customer_need_card_metaboxes(): void {
    add_meta_box('computech_need_card_data', 'بيانات كارت ابدأ من احتياجك', 'computech_need_card_metabox', 'computech_need_card', 'normal', 'high');
}
add_action('add_meta_boxes', 'computech_add_customer_need_card_metaboxes');


function computech_need_pages_options(string $selected = ''): string {
    $pages = get_pages(array('sort_column' => 'menu_order,post_title', 'post_status' => 'publish'));
    $html = '<option value="0">اختر صفحة</option>';
    foreach ($pages as $page) {
        $html .= '<option value="' . esc_attr((string) $page->ID) . '" ' . selected($selected, (string) $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
    }
    return $html;
}

function computech_need_link_type_select(string $name, string $selected): string {
    $types = array(
        'none' => 'بدون رابط',
        'page' => 'صفحة موجودة',
        'category' => 'قسم منتجات موجود',
        'custom' => 'رابط خارجي',
    );
    $html = '<select name="' . esc_attr($name) . '" class="ct-need-link-type widefat">';
    foreach ($types as $key => $label) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($label) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function computech_need_card_metabox(WP_Post $post): void {
    wp_nonce_field('computech_save_need_card', 'computech_need_card_nonce');
    computech_admin_editor_styles_once();
    $link_type = computech_section_meta($post, '_computech_need_link_type', 'custom');
    $page_id = computech_section_meta($post, '_computech_need_page_id', '0');
    $term_id = computech_section_meta($post, '_computech_need_term_id', '0');
    ?>
    <div class="ct-editor ct-hero-admin" dir="rtl">
        <div class="ct-hero-dashboard-head">
            <div>
                <h2>كارت ابدأ من احتياجك</h2>
                <p>المتاح فقط: الاسم، الوصف، الصورة من Featured Image، الرابط، الترتيب.</p>
            </div>
        </div>
        <div class="ct-hero-dashboard">
            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>1. المحتوى</h3>
                        <p>الاسم من خانة العنوان فوق. الترتيب من Order داخل Page Attributes.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <p class="ct-field"><label>الوصف</label><textarea name="_computech_need_text" rows="3" class="widefat" placeholder="وصف قصير لا يزيد عن سطرين"><?php echo esc_textarea(computech_section_meta($post, '_computech_need_text', '')); ?></textarea></p>
                </div>
            </section>
            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>2. الرابط</h3>
                        <p>اختار صفحة موجودة، قسم منتجات موجود، أو رابط خارجي.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-grid ct-grid-2">
                        <p class="ct-field"><label>نوع الرابط</label><?php echo computech_need_link_type_select('_computech_need_link_type', $link_type); ?></p>
                        <p class="ct-field ct-need-page-field"><label>اختيار صفحة</label><select name="_computech_need_page_id" class="widefat"><?php echo computech_need_pages_options($page_id); ?></select></p>
                    </div>
                    <div class="ct-conditional-fields" style="margin-top:14px">
                        <p class="ct-field ct-need-category-field"><label>اختيار قسم منتجات</label><select name="_computech_need_term_id" class="widefat"><?php echo computech_hero_product_category_options($term_id); ?></select></p>
                        <p class="ct-field ct-need-url-field"><label>رابط خارجي</label><input type="url" name="_computech_need_url" value="<?php echo esc_attr(computech_section_meta($post, '_computech_need_url', '')); ?>" class="widefat" placeholder="https://example.com"></p>
                    </div>
                    <p class="ct-field" style="margin-top:14px"><label><input type="checkbox" name="_computech_need_new_tab" value="1" <?php checked(computech_section_meta($post, '_computech_need_new_tab', '0'), '1'); ?>> فتح في تبويب جديد</label></p>
                </div>
            </section>
            <section class="ct-admin-section">
                <div class="ct-admin-section-head">
                    <div>
                        <h3>3. الصورة</h3>
                        <p>صورة الكارت من Featured Image الخاصة بهذا الكارت.</p>
                    </div>
                </div>
                <div class="ct-admin-section-body">
                    <div class="ct-admin-note">استخدم صندوق Featured Image الموجود في جانب صفحة التعديل لاختيار صورة الكارت.</div>
                </div>
            </section>
        </div>
    </div>
    <script>
    (function(){
        var root = document.currentScript.previousElementSibling;
        if (!root) { return; }
        function updateFields(){
            var typeSelect = root.querySelector('.ct-need-link-type');
            var type = typeSelect ? typeSelect.value : 'none';
            var pageField = root.querySelector('.ct-need-page-field');
            var categoryField = root.querySelector('.ct-need-category-field');
            var urlField = root.querySelector('.ct-need-url-field');
            if (pageField) { pageField.style.display = type === 'page' ? '' : 'none'; }
            if (categoryField) { categoryField.style.display = type === 'category' ? '' : 'none'; }
            if (urlField) { urlField.style.display = type === 'custom' ? '' : 'none'; }
        }
        var select = root.querySelector('.ct-need-link-type');
        if (select) { select.addEventListener('change', updateFields); }
        updateFields();
    })();
    </script>
    <?php
}

function computech_save_need_card(int $post_id): void {
    if (!isset($_POST['computech_need_card_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_need_card_nonce'])), 'computech_save_need_card')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    update_post_meta($post_id, '_computech_need_show', '1');
    update_post_meta($post_id, '_computech_need_text', sanitize_textarea_field(wp_unslash($_POST['_computech_need_text'] ?? '')));
    $type = sanitize_key(wp_unslash($_POST['_computech_need_link_type'] ?? 'none'));
    $type = in_array($type, array('none', 'page', 'category', 'custom'), true) ? $type : 'none';
    update_post_meta($post_id, '_computech_need_link_type', $type);
    update_post_meta($post_id, '_computech_need_page_id', (string) absint($_POST['_computech_need_page_id'] ?? 0));
    update_post_meta($post_id, '_computech_need_term_id', (string) absint($_POST['_computech_need_term_id'] ?? 0));
    update_post_meta($post_id, '_computech_need_url', esc_url_raw(wp_unslash($_POST['_computech_need_url'] ?? '')));
    update_post_meta($post_id, '_computech_need_new_tab', !empty($_POST['_computech_need_new_tab']) ? '1' : '0');
    delete_post_meta($post_id, '_computech_need_image_id');
    delete_post_meta($post_id, '_computech_need_image_url');
}
add_action('save_post_computech_need_card', 'computech_save_need_card');

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
        <p>هنا تتحكم في عناوين ووصف سكشن <strong>ابدأ من احتياجك</strong> وسكشن <strong>تسوق حسب القسم</strong>. كروت تسوق حسب القسم تُسحب عشوائيًا من <strong>أقسام WooCommerce</strong> بحد أقصى 5 أقسام.</p>
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
        'posts_per_page' => 6,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'ASC'),
        'order' => 'ASC',
        'no_found_rows' => true,
    ));
    $items = $query->posts;
    wp_reset_postdata();
    if (!is_array($items)) {
        return array();
    }
    return array_slice($items, 0, 6);
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
                    if (post_password_required($card)) {
                        continue;
                    }
                    $title = get_the_title($card);
                    $text = computech_section_meta($card, '_computech_need_text', '');
                    $link_type = computech_section_meta($card, '_computech_need_link_type', '');
                    if ($link_type !== '') {
                        $url = computech_section_link_url($card, '_computech_need');
                        $target = computech_section_link_target($card, '_computech_need');
                    } else {
                        $url = computech_section_meta($card, '_computech_need_url', '');
                        $target = '';
                    }
                    $thumb_id = get_post_thumbnail_id((int) $card->ID);
                    $image = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'full') : '';
                    $alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
                    $alt = $alt !== '' ? $alt : $title;
                    ?>
                    <div class="need-card">
                        <div class="need-card-text">
                            <?php if ($title !== '') : ?><h3 class="need-card-title"><?php echo esc_html($title); ?></h3><?php endif; ?>
                            <?php if (trim($text) !== '') : ?><p class="need-card-desc"><?php echo esc_html($text); ?></p><?php endif; ?>
                            <?php if ($url !== '') : ?><a href="<?php echo esc_url($url); ?>" class="need-card-link"<?php echo $target; ?>>استكشف الأنسب</a><?php endif; ?>
                        </div>
                        <?php if ($image !== '') : ?>
                            <div class="need-card-image">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>">
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


/* ============================================
   Computech Dashboard Editable Footer
   Content is editable from Dashboard. Layout/design stays locked in footer.php + CSS.
   ============================================ */

function computech_footer_default_page_link(string $label, string $slug): array {
    $slug = trim($slug, '/');
    if ($slug === '') {
        return computech_footer_custom_link($label, home_url('/'), '0');
    }
    $page = computech_find_page_by_slug($slug);
    return array(
        'show' => '1',
        'label' => $label,
        'type' => $page ? 'page' : 'custom',
        'page_id' => $page ? (int) $page->ID : 0,
        'url' => $page ? '' : home_url('/' . $slug . '/'),
        'new_tab' => '0',
    );
}

function computech_footer_custom_link(string $label, string $url = '', string $new_tab = '0'): array {
    return array(
        'show' => '1',
        'label' => $label,
        'type' => 'custom',
        'page_id' => 0,
        'url' => $url,
        'new_tab' => $new_tab === '1' ? '1' : '0',
    );
}

function computech_footer_default_settings(): array {
    return array(
        'show_newsletter' => '1',
        'newsletter_title' => 'اشترك ليصلك الجديد',
        'newsletter_subtitle' => 'اشترك في نشرتنا البريدية لتصلك أحدث المنتجات والعروض والأخبار',
        'newsletter_placeholder' => 'أدخل بريدك الإلكتروني',
        'newsletter_button_label' => 'اشترك الآن',
        'newsletter_action_type' => 'page',
        'newsletter_action_page_id' => (computech_find_page_by_slug('contact') ? (int) computech_find_page_by_slug('contact')->ID : 0),
        'newsletter_action_url' => '',
        'footer_logo_text' => '{site_name}',
        'footer_logo_alt' => '{site_name}',
        'brand_description' => '{site_name} هي وجهتك الموثوقة لحلول الكمبيوتر والإلكترونيات. نقدم أحدث المنتجات، ملحقات عالية الجودة، دعم فني متخصص، وخدمة ما بعد البيع لضمان رضاك وثقتك.',
        'quick_links_title' => 'روابط سريعة',
        'category_links_title' => 'التصنيفات',
        'service_links_title' => 'خدماتنا',
        'contact_title' => 'تواصل معنا',
        'show_feature_strip' => '1',
        'show_social_links' => '1',
        'show_bottom_links' => '1',
        'copyright_text' => '{site_name}. جميع الحقوق محفوظة.',
    );
}

function computech_footer_default_quick_links(): array {
    return array(
        computech_footer_default_page_link('الرئيسية', ''),
        computech_footer_default_page_link('من نحن', 'about'),
        computech_footer_default_page_link('المنتجات', 'products'),
        computech_footer_default_page_link('الخدمات', 'services'),
        computech_footer_default_page_link('تواصل معنا', 'contact'),
    );
}

function computech_footer_default_category_links(): array {
    return array(
        computech_footer_default_page_link('أجهزة كمبيوتر', 'categories'),
        computech_footer_default_page_link('لابتوبات', 'categories'),
        computech_footer_default_page_link('شاشات', 'categories'),
        computech_footer_default_page_link('ملحقات', 'categories'),
        computech_footer_default_page_link('مكونات الكمبيوتر', 'categories'),
        computech_footer_default_page_link('استيراد خارج', 'categories'),
    );
}

function computech_footer_default_service_links(): array {
    return array(
        computech_footer_default_page_link('صيانة ودعم فني', 'services'),
        computech_footer_default_page_link('استشارة قبل الشراء', 'services'),
        computech_footer_default_page_link('خدمة ما بعد البيع', 'services'),
        computech_footer_default_page_link('توصيل', 'services'),
        computech_footer_default_page_link('فحص قبل البيع', 'services'),
    );
}

function computech_footer_default_contact_items(): array {
    $phone = computech_business_phone();
    $whatsapp = computech_business_whatsapp_number();
    $email = computech_business_email();
    $address = computech_business_address();
    $hours = computech_business_hours();
    return array(
        array('show' => $phone !== '' ? '1' : '0', 'icon' => 'phone', 'text' => $phone, 'url' => computech_tel_url($phone), 'new_tab' => '0'),
        array('show' => $whatsapp !== '' ? '1' : '0', 'icon' => 'whatsapp', 'text' => $whatsapp !== '' ? '+' . $whatsapp : '', 'url' => computech_whatsapp_url(), 'new_tab' => '1'),
        array('show' => $email !== '' ? '1' : '0', 'icon' => 'email', 'text' => $email, 'url' => computech_mailto_url($email), 'new_tab' => '0'),
        array('show' => $address !== '' ? '1' : '0', 'icon' => 'location', 'text' => $address, 'url' => computech_business_map_url(), 'new_tab' => '1'),
        array('show' => $hours !== '' ? '1' : '0', 'icon' => 'clock', 'text' => $hours, 'url' => '', 'new_tab' => '0'),
    );
}

function computech_footer_default_feature_items(): array {
    return array(
        array('show' => '1', 'icon' => 'check', 'text' => 'فحص قبل البيع'),
        array('show' => '1', 'icon' => 'warranty', 'text' => 'ضمان حسب المنتج'),
        array('show' => '1', 'icon' => 'delivery', 'text' => 'توصيل سريع'),
        array('show' => '1', 'icon' => 'support', 'text' => 'دعم فني'),
    );
}

function computech_footer_default_social_links(): array {
    return array(
        array('show' => '1', 'platform' => 'youtube', 'url' => 'https://www.youtube.com/'),
        array('show' => '1', 'platform' => 'instagram', 'url' => 'https://www.instagram.com/'),
        array('show' => '1', 'platform' => 'twitter', 'url' => 'https://x.com/'),
        array('show' => '1', 'platform' => 'linkedin', 'url' => 'https://www.linkedin.com/'),
    );
}

function computech_footer_default_bottom_links(): array {
    return array(
        computech_footer_default_page_link('خريطة الموقع', 'sitemap'),
        computech_footer_default_page_link('الشروط والأحكام', 'terms'),
        computech_footer_default_page_link('سياسة الخصوصية', 'privacy-policy'),
    );
}

function computech_seed_footer_database_options(): void {
    if (get_option('computech_footer_settings', null) === null) {
        add_option('computech_footer_settings', computech_footer_default_settings(), '', false);
    }
    if (get_option('computech_footer_logo_id', null) === null) {
        add_option('computech_footer_logo_id', 0, '', false);
    }
    $row_options = array(
        'computech_footer_quick_links' => 'computech_footer_default_quick_links',
        'computech_footer_category_links' => 'computech_footer_default_category_links',
        'computech_footer_service_links' => 'computech_footer_default_service_links',
        'computech_footer_contact_items' => 'computech_footer_default_contact_items',
        'computech_footer_feature_items' => 'computech_footer_default_feature_items',
        'computech_footer_social_links' => 'computech_footer_default_social_links',
        'computech_footer_bottom_links' => 'computech_footer_default_bottom_links',
    );
    foreach ($row_options as $option_name => $callback) {
        if (get_option($option_name, null) === null && is_callable($callback)) {
            add_option($option_name, call_user_func($callback), '', false);
        }
    }
}
add_action('init', 'computech_seed_footer_database_options', 24);
add_action('admin_init', 'computech_seed_footer_database_options', 24);

function computech_footer_settings(): array {
    $saved = get_option('computech_footer_settings', array());
    return wp_parse_args(is_array($saved) ? $saved : array(), computech_footer_default_settings());
}

function computech_footer_setting(string $key, string $default = ''): string {
    $settings = computech_footer_settings();
    return array_key_exists($key, $settings) ? (string) $settings[$key] : $default;
}

function computech_footer_bool(string $key, bool $default = false): bool {
    $settings = computech_footer_settings();
    if (!array_key_exists($key, $settings)) {
        return $default;
    }
    return (string) $settings[$key] === '1';
}

function computech_footer_rows(string $option_name): array {
    $rows = get_option($option_name, array());
    return is_array($rows) ? array_values($rows) : array();
}

function computech_footer_visible_rows(string $option_name): array {
    return array_values(array_filter(computech_footer_rows($option_name), static function ($item): bool {
        return !empty($item['show']);
    }));
}

function computech_footer_link_url(array $item): string {
    if (($item['type'] ?? '') === 'page' && !empty($item['page_id'])) {
        $url = get_permalink((int) $item['page_id']);
        return $url ?: home_url('/');
    }
    $url = trim((string) ($item['url'] ?? ''));
    return $url !== '' ? $url : home_url('/');
}

function computech_footer_link_target(array $item): string {
    return !empty($item['new_tab']) ? ' target="_blank" rel="noopener"' : '';
}

function computech_footer_newsletter_action_url(): string {
    $settings = computech_footer_settings();
    if (($settings['newsletter_action_type'] ?? '') === 'page' && !empty($settings['newsletter_action_page_id'])) {
        $url = get_permalink((int) $settings['newsletter_action_page_id']);
        return $url ?: computech_page_url('contact');
    }
    $url = trim((string) ($settings['newsletter_action_url'] ?? ''));
    return $url !== '' ? $url : computech_page_url('contact');
}

function computech_footer_logo_html(): string {
    // WordPress Site Identity logo is the source of truth for the footer too.
    $logo_id = absint(get_theme_mod('custom_logo'));

    // Backward compatibility only: old Computech footer/header logos are used if no Site Identity logo exists.
    if (!$logo_id) {
        $logo_id = absint(get_option('computech_footer_logo_id', 0));
    }
    if (!$logo_id) {
        $logo_id = absint(get_option('computech_header_logo_id', 0));
    }

    $src = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    if (!$src) {
        $src = get_site_icon_url(512);
    }

    $alt = trim(computech_site_text(computech_footer_setting('footer_logo_alt', computech_site_name())));
    if ($alt === '') {
        $alt = computech_site_name();
    }
    if ($src) {
        return '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" class="logo-img computech-footer-logo-img">';
    }

    return '<span class="logo-text-fallback">' . esc_html(computech_site_name()) . '</span>';
}

function computech_footer_icon_choices(): array {
    return array(
        'phone' => array('label' => 'هاتف', 'svg' => '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.3 2.8l2 3.8-1.6 1.5c.9 1.9 2.4 3.4 4.3 4.3l1.5-1.6 3.8 2c.4.2.6.6.5 1l-.7 3.1c-.1.5-.5.8-1 .8C7.9 17.7 2.3 12.1 2.3 4.9c0-.5.3-.9.8-1l3.1-.7c.4-.1.8.1 1.1.5z"/></svg>'),
        'whatsapp' => array('label' => 'واتساب', 'svg' => '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16.8 9.6a6.7 6.7 0 0 1-9.9 5.9L3 16.7l1.2-3.7A6.7 6.7 0 1 1 16.8 9.6z"/><path d="M7.5 6.7c.2-.4.4-.4.7-.4h.5c.2 0 .4.1.5.4l.5 1.1c.1.2.1.4-.1.6l-.4.5c.6 1 1.3 1.7 2.4 2.3l.5-.5c.2-.2.4-.2.6-.1l1.1.5c.3.1.4.3.4.6v.5c0 .3-.1.5-.4.7-.5.3-1 .4-1.6.3-2.5-.5-4.7-2.4-5.4-4.8-.1-.6 0-1.2.3-1.7z"/></svg>'),
        'email' => array('label' => 'بريد إلكتروني', 'svg' => '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="16" height="12" rx="2"/><polyline points="2,5 10,11 18,5"/></svg>'),
        'location' => array('label' => 'عنوان', 'svg' => '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2C6.7 2 4 4.7 4 8c0 4.5 6 10 6 10s6-5.5 6-10c0-3.3-2.7-6-6-6z"/><circle cx="10" cy="8" r="2"/></svg>'),
        'clock' => array('label' => 'مواعيد العمل', 'svg' => '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="8"/><polyline points="10,5 10,10 13,12"/></svg>'),
        'check' => array('label' => 'فحص / تحقق', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>'),
        'warranty' => array('label' => 'ضمان', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>'),
        'delivery' => array('label' => 'توصيل', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>'),
        'support' => array('label' => 'دعم فني', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>'),
        'link' => array('label' => 'رابط', 'svg' => '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 11.5a3 3 0 0 0 4.2 0l2.8-2.8a3 3 0 0 0-4.2-4.2l-1 1"/><path d="M11.5 8.5a3 3 0 0 0-4.2 0l-2.8 2.8a3 3 0 0 0 4.2 4.2l1-1"/></svg>'),
    );
}

function computech_footer_icon_svg(string $icon): string {
    $choices = computech_footer_icon_choices();
    return $choices[$icon]['svg'] ?? $choices['link']['svg'];
}

function computech_footer_social_platforms(): array {
    return array(
        'facebook' => array('label' => 'Facebook', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.44 2.9h-2.34V22C18.34 21.24 22 17.08 22 12.06z"/></svg>'),
        'instagram' => array('label' => 'Instagram', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.16c3.2 0 3.58.02 4.85.08 3.25.14 4.77 1.69 4.92 4.92.06 1.26.07 1.64.07 4.84s-.01 3.59-.07 4.85c-.15 3.23-1.67 4.77-4.92 4.92-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-3.25-.15-4.77-1.69-4.92-4.92-.06-1.26-.07-1.65-.07-4.85s.01-3.58.07-4.84c.15-3.23 1.67-4.78 4.92-4.92 1.27-.06 1.65-.08 4.85-.08zm0 1.78c-3.15 0-3.52.01-4.77.07-2.34.1-3.12 1.22-3.23 3.23-.06 1.25-.07 1.62-.07 4.76s.01 3.52.07 4.77c.11 2.01.89 3.13 3.23 3.23 1.25.06 1.62.07 4.77.07s3.52-.01 4.77-.07c2.34-.1 3.12-1.22 3.23-3.23.06-1.25.07-1.62.07-4.77s-.01-3.51-.07-4.76c-.11-2.01-.89-3.13-3.23-3.23-1.25-.06-1.62-.07-4.77-.07zm0 3.9a4.16 4.16 0 1 1 0 8.32 4.16 4.16 0 0 1 0-8.32zm0 6.86a2.7 2.7 0 1 0 0-5.4 2.7 2.7 0 0 0 0 5.4zm4.36-7.93a.97.97 0 1 1 0-1.94.97.97 0 0 1 0 1.94z"/></svg>'),
        'youtube' => array('label' => 'YouTube', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19C0 8.07 0 12 0 12s0 3.93.5 5.81a3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14C24 15.93 24 12 24 12s0-3.93-.5-5.81zM9.55 15.57V8.43L15.82 12l-6.27 3.57z"/></svg>'),
        'twitter' => array('label' => 'X / Twitter', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.97 6.82H1.67l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23zm-1.16 17.52h1.83L7.08 4.13H5.12l11.96 15.64z"/></svg>'),
        'linkedin' => array('label' => 'LinkedIn', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>'),
        'tiktok' => array('label' => 'TikTok', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.7 2c.35 3.02 2.04 4.82 5.03 5.01v3.4c-1.73.17-3.25-.4-4.93-1.45v6.36c0 8.08-8.8 10.6-12.35 4.81-2.28-3.72-.88-10.25 6.42-10.51v3.58c-.57.09-1.18.24-1.73.43-1.66.56-2.6 1.6-2.34 3.43.5 3.51 6.94 4.55 6.4-2.31V2h3.5z"/></svg>'),
    );
}

function computech_footer_social_svg(string $platform): string {
    $platforms = computech_footer_social_platforms();
    return $platforms[$platform]['svg'] ?? $platforms['facebook']['svg'];
}

function computech_render_footer_link_list(string $option_name): void {
    $items = computech_footer_visible_rows($option_name);
    if (empty($items)) {
        return;
    }
    echo '<ul class="footer-links">';
    foreach ($items as $item) {
        $label = trim((string) ($item['label'] ?? ''));
        if ($label === '') {
            continue;
        }
        echo '<li><a href="' . esc_url(computech_footer_link_url($item)) . '"' . computech_footer_link_target($item) . '>' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';
}

function computech_render_footer_contact_items(): void {
    $items = computech_footer_visible_rows('computech_footer_contact_items');
    if (empty($items)) {
        return;
    }
    echo '<ul class="footer-contact">';
    foreach ($items as $item) {
        $icon = sanitize_key((string) ($item['icon'] ?? 'link'));
        $text = trim((string) ($item['text'] ?? ''));
        $url = trim((string) ($item['url'] ?? ''));
        $target = !empty($item['new_tab']) ? ' target="_blank" rel="noopener"' : '';

        if ($icon === 'phone' && computech_business_phone() !== '') {
            $text = computech_business_phone();
            $url = computech_tel_url($text);
            $target = '';
        } elseif ($icon === 'whatsapp' && computech_business_whatsapp_number() !== '') {
            $text = '+' . computech_business_whatsapp_number();
            $url = computech_whatsapp_url();
            $target = ' target="_blank" rel="noopener"';
        } elseif ($icon === 'email' && computech_business_email() !== '') {
            $text = computech_business_email();
            $url = computech_mailto_url($text);
            $target = '';
        } elseif ($icon === 'location' && computech_business_address() !== '') {
            $text = computech_business_address();
            $url = computech_business_map_url();
            $target = $url !== '' ? ' target="_blank" rel="noopener"' : '';
        } elseif ($icon === 'clock' && computech_business_hours() !== '') {
            $text = computech_business_hours();
            $url = '';
            $target = '';
        }

        $text = trim(computech_site_text($text));
        if ($text === '') {
            continue;
        }
        echo '<li>' . computech_footer_icon_svg($icon);
        if ($url !== '') {
            echo '<a href="' . esc_url($url) . '"' . $target . '>' . esc_html($text) . '</a>';
        } else {
            echo '<span>' . esc_html($text) . '</span>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

function computech_render_footer_feature_strip(): void {
    if (!computech_footer_bool('show_feature_strip', true)) {
        return;
    }
    $items = computech_footer_visible_rows('computech_footer_feature_items');
    if (empty($items)) {
        return;
    }
    echo '<div class="footer-services">';
    $last_index = count($items) - 1;
    foreach ($items as $index => $item) {
        $text = trim((string) ($item['text'] ?? ''));
        if ($text === '') {
            continue;
        }
        $icon = sanitize_key((string) ($item['icon'] ?? 'check'));
        echo '<div class="footer-service-item"><div class="footer-service-icon">' . computech_footer_icon_svg($icon) . '</div><span>' . esc_html(computech_site_text($text)) . '</span></div>';
        if ($index < $last_index) {
            echo '<div class="footer-service-sep"></div>';
        }
    }
    echo '</div>';
}

function computech_render_footer_social_links(): void {
    if (!computech_footer_bool('show_social_links', true)) {
        return;
    }
    $items = computech_footer_visible_rows('computech_footer_social_links');
    if (empty($items)) {
        return;
    }
    echo '<div class="footer-social">';
    $platforms = computech_footer_social_platforms();
    foreach ($items as $item) {
        $platform = sanitize_key((string) ($item['platform'] ?? 'facebook'));
        $url = trim((string) ($item['url'] ?? ''));
        if ($url === '') {
            continue;
        }
        $label = $platforms[$platform]['label'] ?? ucfirst($platform);
        echo '<a href="' . esc_url($url) . '" class="footer-social-icon" aria-label="' . esc_attr($label) . '" target="_blank" rel="noopener">' . computech_footer_social_svg($platform) . '</a>';
    }
    echo '</div>';
}

function computech_render_footer_bottom_links(): void {
    if (!computech_footer_bool('show_bottom_links', true)) {
        return;
    }
    $items = computech_footer_visible_rows('computech_footer_bottom_links');
    if (empty($items)) {
        return;
    }
    echo '<div class="footer-bottom-links">';
    foreach ($items as $item) {
        $label = trim((string) ($item['label'] ?? ''));
        if ($label === '') {
            continue;
        }
        echo '<a href="' . esc_url(computech_footer_link_url($item)) . '"' . computech_footer_link_target($item) . '>' . esc_html($label) . '</a>';
    }
    echo '</div>';
}

function computech_footer_columns_has_rows(string $option_name): bool {
    foreach (computech_footer_visible_rows($option_name) as $item) {
        if (trim((string) ($item['label'] ?? '')) !== '') {
            return true;
        }
    }
    return false;
}

function computech_footer_contact_has_rows(): bool {
    foreach (computech_footer_visible_rows('computech_footer_contact_items') as $item) {
        if (trim((string) ($item['text'] ?? '')) !== '') {
            return true;
        }
    }
    return false;
}

function computech_admin_footer_pages_options(int $selected = 0): string {
    $pages = get_pages(array('sort_column' => 'menu_order,post_title', 'post_status' => 'publish'));
    $html = '<option value="0">اختر صفحة</option>';
    foreach ($pages as $page) {
        $html .= '<option value="' . esc_attr((string) $page->ID) . '" ' . selected($selected, (int) $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
    }
    return $html;
}

function computech_footer_sanitize_link_rows($rows): array {
    $items = array();
    if (!is_array($rows)) {
        return $items;
    }
    foreach ($rows as $row) {
        if (!is_array($row)) { continue; }
        $label = sanitize_text_field(wp_unslash($row['label'] ?? ''));
        if ($label === '') { continue; }
        $type = sanitize_key(wp_unslash($row['type'] ?? 'page'));
        $type = in_array($type, array('page', 'custom'), true) ? $type : 'page';
        $items[] = array(
            'show' => !empty($row['show']) ? '1' : '0',
            'label' => $label,
            'type' => $type,
            'page_id' => absint($row['page_id'] ?? 0),
            'url' => esc_url_raw(wp_unslash($row['url'] ?? '')),
            'new_tab' => !empty($row['new_tab']) ? '1' : '0',
        );
    }
    return $items;
}

function computech_footer_sanitize_contact_rows($rows): array {
    $items = array();
    if (!is_array($rows)) {
        return $items;
    }
    foreach ($rows as $row) {
        if (!is_array($row)) { continue; }
        $text = sanitize_text_field(wp_unslash($row['text'] ?? ''));
        if ($text === '') { continue; }
        $items[] = array(
            'show' => !empty($row['show']) ? '1' : '0',
            'icon' => sanitize_key(wp_unslash($row['icon'] ?? 'link')),
            'text' => $text,
            'url' => esc_url_raw(wp_unslash($row['url'] ?? '')),
            'new_tab' => !empty($row['new_tab']) ? '1' : '0',
        );
    }
    return $items;
}

function computech_footer_sanitize_feature_rows($rows): array {
    $items = array();
    if (!is_array($rows)) {
        return $items;
    }
    foreach ($rows as $row) {
        if (!is_array($row)) { continue; }
        $text = sanitize_text_field(wp_unslash($row['text'] ?? ''));
        if ($text === '') { continue; }
        $items[] = array(
            'show' => !empty($row['show']) ? '1' : '0',
            'icon' => sanitize_key(wp_unslash($row['icon'] ?? 'check')),
            'text' => $text,
        );
    }
    return $items;
}

function computech_footer_sanitize_social_rows($rows): array {
    $items = array();
    $platforms = array_keys(computech_footer_social_platforms());
    if (!is_array($rows)) {
        return $items;
    }
    foreach ($rows as $row) {
        if (!is_array($row)) { continue; }
        $platform = sanitize_key(wp_unslash($row['platform'] ?? 'facebook'));
        if (!in_array($platform, $platforms, true)) {
            $platform = 'facebook';
        }
        $url = esc_url_raw(wp_unslash($row['url'] ?? ''));
        if ($url === '') { continue; }
        $items[] = array(
            'show' => !empty($row['show']) ? '1' : '0',
            'platform' => $platform,
            'url' => $url,
        );
    }
    return $items;
}

function computech_admin_menu_footer(): void {
    add_submenu_page('computech-settings', 'إعدادات الفوتر', 'إعدادات الفوتر', computech_admin_capability(), 'computech-footer-settings', 'computech_footer_settings_page');
}
add_action('admin_menu', 'computech_admin_menu_footer', 20);

function computech_footer_admin_assets(string $hook): void {
    if (strpos($hook, 'computech-footer-settings') === false) {
        return;
    }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'computech_footer_admin_assets');

function computech_handle_footer_settings_save(): void {
    if (!isset($_POST['computech_footer_settings_nonce'])) {
        return;
    }
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_footer_settings_nonce'])), 'computech_save_footer_settings')) {
        wp_die(esc_html__('طلب غير آمن. برجاء إعادة تحميل الصفحة والمحاولة مرة أخرى.', 'computech'));
    }
    if (!current_user_can(computech_admin_capability())) {
        wp_die(esc_html__('غير مسموح لك بتعديل إعدادات الفوتر.', 'computech'));
    }

    $settings = array(
        'show_newsletter' => !empty($_POST['show_newsletter']) ? '1' : '0',
        'newsletter_title' => sanitize_text_field(wp_unslash($_POST['newsletter_title'] ?? '')),
        'newsletter_subtitle' => sanitize_textarea_field(wp_unslash($_POST['newsletter_subtitle'] ?? '')),
        'newsletter_placeholder' => sanitize_text_field(wp_unslash($_POST['newsletter_placeholder'] ?? '')),
        'newsletter_button_label' => sanitize_text_field(wp_unslash($_POST['newsletter_button_label'] ?? '')),
        'newsletter_action_type' => in_array(sanitize_key(wp_unslash($_POST['newsletter_action_type'] ?? 'page')), array('page', 'custom'), true) ? sanitize_key(wp_unslash($_POST['newsletter_action_type'] ?? 'page')) : 'page',
        'newsletter_action_page_id' => absint($_POST['newsletter_action_page_id'] ?? 0),
        'newsletter_action_url' => esc_url_raw(wp_unslash($_POST['newsletter_action_url'] ?? '')),
        'footer_logo_text' => sanitize_text_field(wp_unslash($_POST['footer_logo_text'] ?? '')),
        'footer_logo_alt' => sanitize_text_field(wp_unslash($_POST['footer_logo_alt'] ?? '')),
        'brand_description' => sanitize_textarea_field(wp_unslash($_POST['brand_description'] ?? '')),
        'quick_links_title' => sanitize_text_field(wp_unslash($_POST['quick_links_title'] ?? '')),
        'category_links_title' => sanitize_text_field(wp_unslash($_POST['category_links_title'] ?? '')),
        'service_links_title' => sanitize_text_field(wp_unslash($_POST['service_links_title'] ?? '')),
        'contact_title' => sanitize_text_field(wp_unslash($_POST['contact_title'] ?? '')),
        'show_feature_strip' => !empty($_POST['show_feature_strip']) ? '1' : '0',
        'show_social_links' => !empty($_POST['show_social_links']) ? '1' : '0',
        'show_bottom_links' => !empty($_POST['show_bottom_links']) ? '1' : '0',
        'copyright_text' => sanitize_text_field(wp_unslash($_POST['copyright_text'] ?? '')),
    );

    update_option('computech_footer_settings', $settings, false);
    update_option('computech_footer_logo_id', absint($_POST['footer_logo_id'] ?? 0), false);
    update_option('computech_footer_quick_links', computech_footer_sanitize_link_rows($_POST['footer_quick'] ?? array()), false);
    update_option('computech_footer_category_links', computech_footer_sanitize_link_rows($_POST['footer_categories'] ?? array()), false);
    update_option('computech_footer_service_links', computech_footer_sanitize_link_rows($_POST['footer_services'] ?? array()), false);
    update_option('computech_footer_bottom_links', computech_footer_sanitize_link_rows($_POST['footer_bottom'] ?? array()), false);
    update_option('computech_footer_contact_items', computech_footer_sanitize_contact_rows($_POST['footer_contact'] ?? array()), false);
    update_option('computech_footer_feature_items', computech_footer_sanitize_feature_rows($_POST['footer_features'] ?? array()), false);
    update_option('computech_footer_social_links', computech_footer_sanitize_social_rows($_POST['footer_social'] ?? array()), false);

    add_settings_error('computech_footer_messages', 'computech_footer_saved', 'تم حفظ إعدادات الفوتر بنجاح.', 'updated');
}
add_action('admin_init', 'computech_handle_footer_settings_save');

function computech_admin_footer_icon_select(string $name, string $selected): string {
    $html = '<select name="' . esc_attr($name) . '">';
    foreach (computech_footer_icon_choices() as $key => $icon) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($icon['label']) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function computech_admin_footer_platform_select(string $name, string $selected): string {
    $html = '<select name="' . esc_attr($name) . '">';
    foreach (computech_footer_social_platforms() as $key => $platform) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($platform['label']) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function computech_admin_footer_render_link_rows(string $field_name, array $items): void {
    foreach ($items as $i => $item) : ?>
        <div class="ct-row cf-link-row">
            <div class="ct-row-head"><strong>رابط</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div>
            <label><input type="checkbox" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr((string) $i); ?>][show]" value="1" <?php checked(!empty($item['show'])); ?>> إظهار الرابط</label>
            <label>النص<input type="text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr((string) $i); ?>][label]" value="<?php echo esc_attr($item['label'] ?? ''); ?>"></label>
            <label>نوع الرابط<select name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr((string) $i); ?>][type]" class="cf-link-type"><option value="page" <?php selected($item['type'] ?? 'page', 'page'); ?>>صفحة داخل الموقع</option><option value="custom" <?php selected($item['type'] ?? 'page', 'custom'); ?>>رابط مخصص</option></select></label>
            <label class="cf-page-field">اختر الصفحة<select name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr((string) $i); ?>][page_id]"><?php echo computech_admin_footer_pages_options(absint($item['page_id'] ?? 0)); ?></select></label>
            <label class="cf-url-field">الرابط المخصص<input type="text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr((string) $i); ?>][url]" value="<?php echo esc_attr($item['url'] ?? ''); ?>" placeholder="https://example.com"></label>
            <label><input type="checkbox" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr((string) $i); ?>][new_tab]" value="1" <?php checked(!empty($item['new_tab'])); ?>> فتح في تبويب جديد</label>
        </div>
    <?php endforeach;
}

function computech_footer_settings_page(): void {
    if (!current_user_can(computech_admin_capability())) {
        wp_die(esc_html__('غير مسموح لك بالدخول إلى إعدادات الفوتر.', 'computech'));
    }
    computech_seed_footer_database_options();
    $settings = computech_footer_settings();
    $logo_id = absint(get_option('computech_footer_logo_id', 0));
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';
    $quick_links = computech_footer_rows('computech_footer_quick_links');
    $category_links = computech_footer_rows('computech_footer_category_links');
    $service_links = computech_footer_rows('computech_footer_service_links');
    $bottom_links = computech_footer_rows('computech_footer_bottom_links');
    $contact_items = computech_footer_rows('computech_footer_contact_items');
    $feature_items = computech_footer_rows('computech_footer_feature_items');
    $social_links = computech_footer_rows('computech_footer_social_links');
    settings_errors('computech_footer_messages');
    ?>
    <div class="wrap computech-admin-wrap" dir="rtl">
        <h1>إعدادات كمبيوتك - الفوتر</h1>
        <p>هذه الصفحة لتعديل محتوى الفوتر فقط. التصميم، الألوان، المسافات، الأعمدة، والـ hover effects ثابتة داخل ملفات الثيم حتى لا يكسر الأدمن شكل الموقع.</p>
        <form method="post">
            <?php wp_nonce_field('computech_save_footer_settings', 'computech_footer_settings_nonce'); ?>

            <div class="ct-panel">
                <h2>الهوية والوصف</h2>
                <input type="hidden" name="footer_logo_id" id="ct-footer-logo-id" value="<?php echo esc_attr((string) $logo_id); ?>">
                <div class="ct-logo-preview" id="ct-footer-logo-preview"><?php if ($logo_url) : ?><img src="<?php echo esc_url($logo_url); ?>" alt=""><?php else : ?><span>سيتم استخدام لوجو الهيدر أو اللوجو الافتراضي</span><?php endif; ?></div>
                <button type="button" class="button" id="ct-footer-upload-logo">اختيار / تغيير لوجو الفوتر</button>
                <button type="button" class="button" id="ct-footer-remove-logo">إزالة لوجو الفوتر</button>
                <label>نص اللوجو بجانب الصورة<input type="text" name="footer_logo_text" value="<?php echo esc_attr($settings['footer_logo_text']); ?>"></label>
                <label>Alt اللوجو<input type="text" name="footer_logo_alt" value="<?php echo esc_attr($settings['footer_logo_alt']); ?>"></label>
                <label>وصف الشركة<textarea name="brand_description" rows="4"><?php echo esc_textarea($settings['brand_description']); ?></textarea></label>
            </div>

            <div class="ct-panel">
                <h2>Newsletter - الاشتراك بالبريد</h2>
                <label><input type="checkbox" name="show_newsletter" value="1" <?php checked($settings['show_newsletter'], '1'); ?>> إظهار جزء الاشتراك</label>
                <label>العنوان<input type="text" name="newsletter_title" value="<?php echo esc_attr($settings['newsletter_title']); ?>"></label>
                <label>الوصف<textarea name="newsletter_subtitle" rows="3"><?php echo esc_textarea($settings['newsletter_subtitle']); ?></textarea></label>
                <label>Placeholder البريد<input type="text" name="newsletter_placeholder" value="<?php echo esc_attr($settings['newsletter_placeholder']); ?>"></label>
                <label>نص الزر<input type="text" name="newsletter_button_label" value="<?php echo esc_attr($settings['newsletter_button_label']); ?>"></label>
                <label>إرسال الفورم إلى<select name="newsletter_action_type" class="cf-link-type"><option value="page" <?php selected($settings['newsletter_action_type'], 'page'); ?>>صفحة داخل الموقع</option><option value="custom" <?php selected($settings['newsletter_action_type'], 'custom'); ?>>رابط مخصص</option></select></label>
                <label class="cf-page-field">صفحة الفورم<select name="newsletter_action_page_id"><?php echo computech_admin_footer_pages_options(absint($settings['newsletter_action_page_id'])); ?></select></label>
                <label class="cf-url-field">رابط مخصص للفورم<input type="text" name="newsletter_action_url" value="<?php echo esc_attr($settings['newsletter_action_url']); ?>"></label>
            </div>

            <div class="ct-panel">
                <h2>عناوين الأعمدة</h2>
                <label>عنوان عمود الروابط السريعة<input type="text" name="quick_links_title" value="<?php echo esc_attr($settings['quick_links_title']); ?>"></label>
                <label>عنوان عمود التصنيفات<input type="text" name="category_links_title" value="<?php echo esc_attr($settings['category_links_title']); ?>"></label>
                <label>عنوان عمود الخدمات<input type="text" name="service_links_title" value="<?php echo esc_attr($settings['service_links_title']); ?>"></label>
                <label>عنوان عمود التواصل<input type="text" name="contact_title" value="<?php echo esc_attr($settings['contact_title']); ?>"></label>
            </div>

            <div class="ct-panel"><h2>روابط سريعة</h2><div id="cf-quick-list"><?php computech_admin_footer_render_link_rows('footer_quick', $quick_links); ?></div><button type="button" class="button button-secondary cf-add-link" data-target="cf-quick-list" data-name="footer_quick">+ إضافة رابط</button></div>
            <div class="ct-panel"><h2>التصنيفات</h2><div id="cf-category-list"><?php computech_admin_footer_render_link_rows('footer_categories', $category_links); ?></div><button type="button" class="button button-secondary cf-add-link" data-target="cf-category-list" data-name="footer_categories">+ إضافة تصنيف</button></div>
            <div class="ct-panel"><h2>الخدمات</h2><div id="cf-service-list"><?php computech_admin_footer_render_link_rows('footer_services', $service_links); ?></div><button type="button" class="button button-secondary cf-add-link" data-target="cf-service-list" data-name="footer_services">+ إضافة خدمة</button></div>

            <div class="ct-panel">
                <h2>بيانات التواصل</h2>
                <div id="cf-contact-list">
                    <?php foreach ($contact_items as $i => $item) : ?>
                        <div class="ct-row cf-contact-row">
                            <div class="ct-row-head"><strong>بيان تواصل</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div>
                            <label><input type="checkbox" name="footer_contact[<?php echo esc_attr((string) $i); ?>][show]" value="1" <?php checked(!empty($item['show'])); ?>> إظهار البيان</label>
                            <label>الأيقونة<?php echo computech_admin_footer_icon_select('footer_contact[' . esc_attr((string) $i) . '][icon]', $item['icon'] ?? 'link'); ?></label>
                            <label>النص<input type="text" name="footer_contact[<?php echo esc_attr((string) $i); ?>][text]" value="<?php echo esc_attr($item['text'] ?? ''); ?>"></label>
                            <label>رابط اختياري<input type="text" name="footer_contact[<?php echo esc_attr((string) $i); ?>][url]" value="<?php echo esc_attr($item['url'] ?? ''); ?>" placeholder="tel: / mailto: / https://"></label>
                            <label><input type="checkbox" name="footer_contact[<?php echo esc_attr((string) $i); ?>][new_tab]" value="1" <?php checked(!empty($item['new_tab'])); ?>> فتح في تبويب جديد</label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" id="cf-add-contact">+ إضافة بيان تواصل</button>
            </div>

            <div class="ct-panel">
                <h2>شريط المميزات داخل الفوتر</h2>
                <label><input type="checkbox" name="show_feature_strip" value="1" <?php checked($settings['show_feature_strip'], '1'); ?>> إظهار الشريط</label>
                <div id="cf-feature-list">
                    <?php foreach ($feature_items as $i => $item) : ?>
                        <div class="ct-row cf-feature-row">
                            <div class="ct-row-head"><strong>ميزة</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div>
                            <label><input type="checkbox" name="footer_features[<?php echo esc_attr((string) $i); ?>][show]" value="1" <?php checked(!empty($item['show'])); ?>> إظهار الميزة</label>
                            <label>الأيقونة<?php echo computech_admin_footer_icon_select('footer_features[' . esc_attr((string) $i) . '][icon]', $item['icon'] ?? 'check'); ?></label>
                            <label>النص<input type="text" name="footer_features[<?php echo esc_attr((string) $i); ?>][text]" value="<?php echo esc_attr($item['text'] ?? ''); ?>"></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" id="cf-add-feature">+ إضافة ميزة</button>
            </div>

            <div class="ct-panel">
                <h2>روابط السوشيال ميديا</h2>
                <label><input type="checkbox" name="show_social_links" value="1" <?php checked($settings['show_social_links'], '1'); ?>> إظهار أيقونات السوشيال</label>
                <div id="cf-social-list">
                    <?php foreach ($social_links as $i => $item) : ?>
                        <div class="ct-row cf-social-row">
                            <div class="ct-row-head"><strong>رابط سوشيال</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div>
                            <label><input type="checkbox" name="footer_social[<?php echo esc_attr((string) $i); ?>][show]" value="1" <?php checked(!empty($item['show'])); ?>> إظهار الرابط</label>
                            <label>المنصة<?php echo computech_admin_footer_platform_select('footer_social[' . esc_attr((string) $i) . '][platform]', $item['platform'] ?? 'facebook'); ?></label>
                            <label>الرابط<input type="text" name="footer_social[<?php echo esc_attr((string) $i); ?>][url]" value="<?php echo esc_attr($item['url'] ?? ''); ?>" placeholder="https://"></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" id="cf-add-social">+ إضافة رابط سوشيال</button>
            </div>

            <div class="ct-panel">
                <h2>أسفل الفوتر</h2>
                <label><input type="checkbox" name="show_bottom_links" value="1" <?php checked($settings['show_bottom_links'], '1'); ?>> إظهار روابط أسفل الفوتر</label>
                <label>نص الحقوق بعد السنة الحالية<input type="text" name="copyright_text" value="<?php echo esc_attr($settings['copyright_text']); ?>"></label>
                <div id="cf-bottom-list"><?php computech_admin_footer_render_link_rows('footer_bottom', $bottom_links); ?></div>
                <button type="button" class="button button-secondary cf-add-link" data-target="cf-bottom-list" data-name="footer_bottom">+ إضافة رابط سفلي</button>
            </div>

            <?php submit_button('حفظ إعدادات الفوتر'); ?>
        </form>
    </div>

    <template id="cf-link-template">
        <div class="ct-row cf-link-row">
            <div class="ct-row-head"><strong>رابط</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div>
            <label><input type="checkbox" name="__name__[__i__][show]" value="1" checked> إظهار الرابط</label>
            <label>النص<input type="text" name="__name__[__i__][label]" value=""></label>
            <label>نوع الرابط<select name="__name__[__i__][type]" class="cf-link-type"><option value="page">صفحة داخل الموقع</option><option value="custom">رابط مخصص</option></select></label>
            <label class="cf-page-field">اختر الصفحة<select name="__name__[__i__][page_id]"><?php echo computech_admin_footer_pages_options(0); ?></select></label>
            <label class="cf-url-field">الرابط المخصص<input type="text" name="__name__[__i__][url]" value="" placeholder="https://example.com"></label>
            <label><input type="checkbox" name="__name__[__i__][new_tab]" value="1"> فتح في تبويب جديد</label>
        </div>
    </template>
    <template id="cf-contact-template">
        <div class="ct-row cf-contact-row"><div class="ct-row-head"><strong>بيان تواصل</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div><label><input type="checkbox" name="footer_contact[__i__][show]" value="1" checked> إظهار البيان</label><label>الأيقونة<?php echo computech_admin_footer_icon_select('footer_contact[__i__][icon]', 'phone'); ?></label><label>النص<input type="text" name="footer_contact[__i__][text]" value=""></label><label>رابط اختياري<input type="text" name="footer_contact[__i__][url]" value="" placeholder="tel: / mailto: / https://"></label><label><input type="checkbox" name="footer_contact[__i__][new_tab]" value="1"> فتح في تبويب جديد</label></div>
    </template>
    <template id="cf-feature-template">
        <div class="ct-row cf-feature-row"><div class="ct-row-head"><strong>ميزة</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div><label><input type="checkbox" name="footer_features[__i__][show]" value="1" checked> إظهار الميزة</label><label>الأيقونة<?php echo computech_admin_footer_icon_select('footer_features[__i__][icon]', 'check'); ?></label><label>النص<input type="text" name="footer_features[__i__][text]" value=""></label></div>
    </template>
    <template id="cf-social-template">
        <div class="ct-row cf-social-row"><div class="ct-row-head"><strong>رابط سوشيال</strong><button type="button" class="button-link-delete cf-remove-row">حذف</button></div><label><input type="checkbox" name="footer_social[__i__][show]" value="1" checked> إظهار الرابط</label><label>المنصة<?php echo computech_admin_footer_platform_select('footer_social[__i__][platform]', 'facebook'); ?></label><label>الرابط<input type="text" name="footer_social[__i__][url]" value="" placeholder="https://"></label></div>
    </template>

    <style>
        .computech-admin-wrap { max-width: 1120px; }
        .ct-panel { background:#fff; border:1px solid #dcdcde; border-radius:14px; padding:18px; margin:18px 0; box-shadow:0 6px 20px rgba(0,0,0,.03); }
        .ct-panel h2 { margin-top:0; }
        .ct-row { border:1px solid #e5e7eb; border-radius:12px; padding:14px; margin:12px 0; background:#f9fafb; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; align-items:end; }
        .ct-row-head { grid-column:1 / -1; display:flex; justify-content:space-between; align-items:center; }
        .ct-row label, .ct-panel label { display:block; font-weight:700; margin:8px 0; }
        .ct-row input[type=text], .ct-row input[type=url], .ct-row select, .ct-panel input[type=text], .ct-panel textarea, .ct-panel select { width:100%; margin-top:6px; }
        .ct-logo-preview { width:120px; height:70px; display:flex; align-items:center; justify-content:center; background:#fff; border:1px dashed #ccd0d4; border-radius:10px; margin:10px 0; overflow:hidden; color:#64748b; font-size:12px; text-align:center; }
        .ct-logo-preview img { max-width:100%; max-height:100%; object-fit:contain; }
        @media (max-width: 782px) { .ct-row { grid-template-columns:1fr; } }
    </style>
    <script>
    (function(){
        var indexes = {
            footer_quick: <?php echo (int) count($quick_links); ?>,
            footer_categories: <?php echo (int) count($category_links); ?>,
            footer_services: <?php echo (int) count($service_links); ?>,
            footer_bottom: <?php echo (int) count($bottom_links); ?>,
            footer_contact: <?php echo (int) count($contact_items); ?>,
            footer_features: <?php echo (int) count($feature_items); ?>,
            footer_social: <?php echo (int) count($social_links); ?>
        };
        function refreshLinkFields(scope){
            (scope || document).querySelectorAll('.cf-link-row, .ct-panel').forEach(function(row){
                row.querySelectorAll('.cf-link-type').forEach(function(select){
                    var container = select.closest('.cf-link-row') || select.closest('.ct-panel');
                    if (!container) { return; }
                    container.querySelectorAll('.cf-page-field').forEach(function(el){ el.style.display = select.value === 'page' ? '' : 'none'; });
                    container.querySelectorAll('.cf-url-field').forEach(function(el){ el.style.display = select.value === 'custom' ? '' : 'none'; });
                });
            });
        }
        refreshLinkFields(document);
        document.addEventListener('change', function(e){ if (e.target.classList.contains('cf-link-type')) { refreshLinkFields(e.target.closest('.cf-link-row') || e.target.closest('.ct-panel')); } });
        document.addEventListener('click', function(e){
            var remove = e.target.closest('.cf-remove-row');
            if (remove) { e.preventDefault(); remove.closest('.ct-row').remove(); return; }
            var addLink = e.target.closest('.cf-add-link');
            if (addLink) {
                e.preventDefault();
                var target = document.getElementById(addLink.dataset.target);
                var name = addLink.dataset.name;
                var index = indexes[name] || 0;
                indexes[name] = index + 1;
                target.insertAdjacentHTML('beforeend', document.getElementById('cf-link-template').innerHTML.replaceAll('__name__', name).replaceAll('__i__', index));
                refreshLinkFields(target.lastElementChild);
                return;
            }
            if (e.target.id === 'cf-add-contact') { e.preventDefault(); var c = indexes.footer_contact++; document.getElementById('cf-contact-list').insertAdjacentHTML('beforeend', document.getElementById('cf-contact-template').innerHTML.replaceAll('__i__', c)); return; }
            if (e.target.id === 'cf-add-feature') { e.preventDefault(); var f = indexes.footer_features++; document.getElementById('cf-feature-list').insertAdjacentHTML('beforeend', document.getElementById('cf-feature-template').innerHTML.replaceAll('__i__', f)); return; }
            if (e.target.id === 'cf-add-social') { e.preventDefault(); var s = indexes.footer_social++; document.getElementById('cf-social-list').insertAdjacentHTML('beforeend', document.getElementById('cf-social-template').innerHTML.replaceAll('__i__', s)); return; }
            if (e.target.id === 'ct-footer-remove-logo') { e.preventDefault(); document.getElementById('ct-footer-logo-id').value = '0'; document.getElementById('ct-footer-logo-preview').innerHTML = '<span>سيتم استخدام لوجو الهيدر أو اللوجو الافتراضي</span>'; return; }
            if (e.target.id === 'ct-footer-upload-logo') {
                e.preventDefault();
                if (!window.wp || !wp.media) { return; }
                var frame = wp.media({ title:'اختر لوجو الفوتر', button:{ text:'استخدام اللوجو' }, multiple:false });
                frame.on('select', function(){
                    var file = frame.state().get('selection').first().toJSON();
                    document.getElementById('ct-footer-logo-id').value = file.id;
                    document.getElementById('ct-footer-logo-preview').innerHTML = '<img src="' + ((file.sizes && file.sizes.thumbnail) ? file.sizes.thumbnail.url : file.url) + '" alt="">';
                });
                frame.open();
            }
        });
    })();
    </script>
    <?php
}


/* ============================================
   Restored editable home sections
   عروض خاصة / طرق الدفع / تواصل معنا / CTA
   Content is editable from Dashboard. Design remains locked in PHP markup + CSS classes.
   ============================================ */

function computech_home_extra_asset(string $file): string {
    return get_template_directory_uri() . '/assets/images/' . ltrim($file, '/');
}

function computech_home_extra_default_settings(): array {
    return array(
        'offers_show' => '1',
        'offers_title_before' => 'عروض خاصة',
        'offers_title_highlight' => 'وبنرات ترويجية',
        'offers_subtitle' => 'اكتشف أفضل العروض على أجهزة الكمبيوتر المستوردة والإكسسوارات الأصلية',
        'offers_ribbon_image' => computech_home_extra_asset('offers-featured-ribbon.png'),
        'offers_value_badge_image' => computech_home_extra_asset('offers-best-value-badge.png'),
        'offers_main_title' => 'أجهزة استيراد',
        'offers_main_title_highlight' => 'بحالة ممتازة',
        'offers_main_desc' => 'أجهزة مستوردة عالية الجودة تم فحصها واختبارها بدقة، تقدم لك أداءً ممتازًا وقيمة لا تضاهى.',
        'offers_main_button_label' => 'تصفح الآن ←',
        'offers_main_button_url' => computech_page_url('products'),
        'offers_main_image' => computech_home_extra_asset('offers-hero-imported-computers.png'),
        'payment_show' => '1',
        'payment_title' => 'طرق الدفع المتاحة',
        'contact_show' => '1',
        'contact_title_highlight' => 'تواصل معنا',
        'contact_title_after' => 'نحن هنا لخدمتك',
        'contact_subtitle' => 'لديك استفسار أو تحتاج لمساعدة؟ فريق {site_name} جاهز للرد عليك بأسرع وقت وتقديم أفضل الحلول.',
        'contact_social_label' => 'تابعنا على',
        'contact_map_show' => '1',
        'contact_map_title' => 'موقعنا على الخريطة',
        'contact_map_subtitle' => 'يمكنك الوصول إلينا بسهولة عبر الخريطة',
        'contact_map_iframe_src' => computech_business_map_embed_url(),
        'contact_map_business_name' => '{site_name}',
        'contact_map_rating' => '4.8 ★★★★★ (126)',
        'contact_map_address' => computech_business_address(),
        'contact_map_link_label' => 'عرض في خرائط Google',
        'contact_map_link_url' => computech_business_map_url(),
        'cta_show' => '1',
        'cta_title_before' => 'جاهز',
        'cta_title_highlight' => 'لتجربة',
        'cta_title_after' => 'أداء أفضل؟',
        'cta_desc' => 'تصفح أحدث أجهزة الكمبيوتر والإكسسوارات بأفضل تنافسية وضمان حقيقي.',
        'cta_button_label' => 'تسوق الآن',
        'cta_button_url' => computech_page_url('products'),
        'cta_image' => computech_home_extra_asset('hero-computer-setup.png'),
    );
}

function computech_home_extra_default_offer_pills(): array {
    return array(
        array('show' => '1', 'icon' => 'search', 'text' => 'فحص شامل'),
        array('show' => '1', 'icon' => 'shield', 'text' => 'أداء موثوق'),
        array('show' => '1', 'icon' => 'price', 'text' => 'أسعار أفضل'),
    );
}

function computech_home_extra_default_offer_cards(): array {
    return array(
        array('show' => '1', 'title' => "اختر جهازك\nحسب ميزانيتك", 'subtitle' => 'استشارة مجانية قبل الشراء', 'desc' => 'لا تتردد في اختيارك، فريقنا يساعدك في اختيار الجهاز الأنسب لاحتياجاتك وميزانيتك.', 'button_label' => 'اعرف المزيد ←', 'button_url' => computech_page_url('contact'), 'image' => computech_home_extra_asset('offers-device-consultation.png'), 'float_image' => computech_home_extra_asset('offers-chat-icon.png'), 'alt' => 'اختر جهازك حسب ميزانيتك'),
        array('show' => '1', 'title' => "عروض\nالإكسسوارات", 'subtitle' => 'خصومات حتى 30%', 'desc' => 'إكسسوارات أصلية لأداء أفضل وتجربة مثالية', 'button_label' => 'تسوق الآن ←', 'button_url' => computech_page_url('products'), 'image' => computech_home_extra_asset('offers-accessories-bundle.png'), 'float_image' => computech_home_extra_asset('offers-discount-icon.png'), 'alt' => 'عروض الإكسسوارات'),
    );
}

function computech_home_extra_default_payment_methods(): array {
    return array(
        array('show' => '1', 'icon' => 'credit_card', 'name' => 'بطاقة ائتمان'),
        array('show' => '1', 'icon' => 'card', 'name' => 'مدى'),
        array('show' => '1', 'icon' => 'bank_transfer', 'name' => 'تحويل بنكي'),
        array('show' => '1', 'icon' => 'whatsapp', 'name' => 'الدفع عند الاستلام'),
        array('show' => '1', 'icon' => 'installment', 'name' => 'تقسيط بدون فوائد'),
    );
}

function computech_home_extra_default_contact_cards(): array {
    $phone = computech_business_phone();
    $whatsapp = computech_business_whatsapp_number();
    $email = computech_business_email();
    $address = computech_business_address();
    $hours = computech_business_hours();
    return array(
        array('show' => $phone !== '' ? '1' : '0', 'icon' => 'phone', 'label' => 'الهاتف', 'value' => $phone, 'note_1' => $hours, 'note_2' => '', 'url' => computech_tel_url($phone)),
        array('show' => $whatsapp !== '' ? '1' : '0', 'icon' => 'whatsapp', 'label' => 'واتساب', 'value' => $whatsapp !== '' ? '+' . $whatsapp : '', 'note_1' => 'للرد السريع', 'note_2' => '', 'url' => computech_whatsapp_url()),
        array('show' => $email !== '' ? '1' : '0', 'icon' => 'email', 'label' => 'البريد الإلكتروني', 'value' => $email, 'note_1' => 'نرد خلال 24 ساعة', 'note_2' => '', 'url' => computech_mailto_url($email)),
        array('show' => $address !== '' ? '1' : '0', 'icon' => 'location', 'label' => 'العنوان', 'value' => $address, 'note_1' => '', 'note_2' => '', 'url' => computech_business_map_url()),
    );
}

function computech_home_extra_default_contact_social_links(): array {
    return array(
        array('show' => '1', 'platform' => 'facebook', 'url' => 'https://www.facebook.com/'),
        array('show' => '1', 'platform' => 'instagram', 'url' => 'https://www.instagram.com/'),
        array('show' => '1', 'platform' => 'youtube', 'url' => 'https://www.youtube.com/'),
        array('show' => '1', 'platform' => 'tiktok', 'url' => 'https://www.tiktok.com/'),
        array('show' => '1', 'platform' => 'linkedin', 'url' => 'https://www.linkedin.com/'),
    );
}

function computech_home_extra_default_cta_features(): array {
    return array(
        array('show' => '1', 'icon' => 'shield', 'title' => 'ضمان حقيقي', 'subtitle' => 'على جميع الأجهزة'),
        array('show' => '1', 'icon' => 'delivery', 'title' => 'توصيل سريع', 'subtitle' => 'إلى باب بيتك'),
        array('show' => '1', 'icon' => 'support', 'title' => 'دعم فني متخصص', 'subtitle' => 'قبل وبعد الشراء'),
    );
}

function computech_seed_home_extra_sections(): void {
    if (get_option('computech_home_extra_settings', null) === null) {
        add_option('computech_home_extra_settings', computech_home_extra_default_settings(), '', false);
    }
    $lists = array(
        'computech_home_offer_pills' => 'computech_home_extra_default_offer_pills',
        'computech_home_offer_cards' => 'computech_home_extra_default_offer_cards',
        'computech_home_payment_methods' => 'computech_home_extra_default_payment_methods',
        'computech_home_contact_cards' => 'computech_home_extra_default_contact_cards',
        'computech_home_contact_social_links' => 'computech_home_extra_default_contact_social_links',
        'computech_home_cta_features' => 'computech_home_extra_default_cta_features',
    );
    foreach ($lists as $option => $callback) {
        if (get_option($option, null) === null) {
            add_option($option, call_user_func($callback), '', false);
        }
    }
}
add_action('init', 'computech_seed_home_extra_sections', 38);
add_action('admin_init', 'computech_seed_home_extra_sections', 38);

function computech_home_extra_settings(): array {
    $settings = get_option('computech_home_extra_settings');
    $merged = array_merge(computech_home_extra_default_settings(), is_array($settings) ? $settings : array());
    return computech_site_text_deep($merged);
}

function computech_home_extra_rows(string $option, callable $default_callback): array {
    $items = get_option($option);
    $rows = is_array($items) ? array_values($items) : call_user_func($default_callback);
    return computech_site_text_deep($rows);
}

function computech_home_extra_visible_rows(string $option, callable $default_callback): array {
    return array_values(array_filter(computech_home_extra_rows($option, $default_callback), static function($row) {
        return is_array($row) && !empty($row['show']);
    }));
}

function computech_home_extra_url(string $url, string $fallback = '#'): string {
    $url = trim($url);
    return $url !== '' ? $url : $fallback;
}

function computech_home_extra_text_lines(string $text): array {
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text)), static fn($line) => $line !== ''));
}

function computech_home_extra_icon_choices(): array {
    return array(
        'credit_card' => array('label' => 'بطاقة ائتمان', 'svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>'),
        'card' => array('label' => 'كارت', 'svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>'),
        'bank_transfer' => array('label' => 'تحويل بنكي', 'svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>'),
        'installment' => array('label' => 'تقسيط', 'svg' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'),
        'phone' => array('label' => 'هاتف', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'),
        'whatsapp' => array('label' => 'واتساب / استلام', 'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>'),
        'email' => array('label' => 'بريد', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>'),
        'location' => array('label' => 'عنوان', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>'),
        'shield' => array('label' => 'ضمان', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>'),
        'delivery' => array('label' => 'توصيل', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>'),
        'support' => array('label' => 'دعم', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>'),
        'search' => array('label' => 'فحص', 'svg' => '<svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="8" cy="8" r="6"/><path d="M13 13l3.5 3.5"/></svg>'),
        'price' => array('label' => 'أسعار', 'svg' => '<svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 2v14M5 6l4-4 4 4"/><path d="M14 10v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4"/></svg>'),
    );
}

function computech_home_extra_icon_svg(string $icon): string {
    $icons = computech_home_extra_icon_choices();
    return $icons[$icon]['svg'] ?? $icons['shield']['svg'];
}

function computech_home_extra_icon_select(string $name, string $selected): string {
    $html = '<select name="' . esc_attr($name) . '">';
    foreach (computech_home_extra_icon_choices() as $key => $icon) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($icon['label']) . '</option>';
    }
    return $html . '</select>';
}



/* ============================================
   Home Promo Banners CPT
   Title / image / visibility use native WordPress fields.
   ============================================ */
function computech_register_home_offer_banner_cpt(): void {
    register_post_type('ct_offer_banner', array(
        'labels' => array(
            'name' => 'عروض خاصة وبنرات ترويجية',
            'singular_name' => 'بنر ترويجي',
            'menu_name' => 'عروض وبنرات',
            'add_new_item' => 'إضافة بنر جديد',
            'edit_item' => 'تعديل بنر',
            'new_item' => 'بنر جديد',
            'search_items' => 'بحث في البنرات',
            'not_found' => 'لا توجد بنرات',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'computech-settings',
        'supports' => array('title', 'thumbnail', 'page-attributes'),
        'capability_type' => 'page',
        'map_meta_cap' => true,
        'show_in_rest' => false,
    ));
}
add_action('init', 'computech_register_home_offer_banner_cpt');

function computech_home_offer_color_choices(): array {
    return array(
        'blue' => 'أزرق',
        'green' => 'أخضر',
        'orange' => 'برتقالي',
        'purple' => 'بنفسجي',
        'dark' => 'داكن',
    );
}

function computech_home_offer_color_select(string $name, string $selected): string {
    $html = '<select name="' . esc_attr($name) . '" class="widefat">';
    foreach (computech_home_offer_color_choices() as $key => $label) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($label) . '</option>';
    }
    return $html . '</select>';
}

function computech_home_offer_link_type_select(string $name, string $selected): string {
    $types = array(
        'none' => 'بدون رابط',
        'page' => 'صفحة موجودة',
        'custom' => 'رابط خارجي',
    );
    $html = '<select name="' . esc_attr($name) . '" class="ct-offer-link-type widefat">';
    foreach ($types as $key => $label) {
        $html .= '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($label) . '</option>';
    }
    return $html . '</select>';
}

function computech_add_home_offer_banner_metaboxes(): void {
    add_meta_box('ct_offer_banner_data', 'بيانات البنر الترويجي', 'computech_home_offer_banner_metabox', 'ct_offer_banner', 'normal', 'high');
}
add_action('add_meta_boxes', 'computech_add_home_offer_banner_metaboxes');

function computech_home_offer_banner_metabox(WP_Post $post): void {
    wp_nonce_field('computech_save_home_offer_banner', 'computech_home_offer_banner_nonce');
    computech_admin_editor_styles_once();
    $link_type = computech_section_meta($post, '_computech_offer_link_type', 'none');
    $page_id = computech_section_meta($post, '_computech_offer_page_id', '0');
    $color = computech_section_meta($post, '_computech_offer_color', 'blue');
    $icon = computech_section_meta($post, '_computech_offer_icon', 'offer');
    ?>
    <div class="ct-editor ct-hero-admin" dir="rtl">
        <div class="ct-hero-dashboard-head">
            <div>
                <h2>بنر ترويجي</h2>
                <p>العنوان من عنوان WordPress. الصورة من Featured Image. الظهور من Published/Public فقط.</p>
            </div>
        </div>
        <div class="ct-hero-dashboard">
            <section class="ct-admin-section">
                <div class="ct-admin-section-head"><div><h3>1. المحتوى</h3><p>لا تستخدم صورة مخصصة هنا. استخدم Featured Image من يمين الشاشة.</p></div></div>
                <div class="ct-admin-section-body">
                    <p class="ct-field"><label>الوصف</label><textarea name="_computech_offer_desc" rows="3" class="widefat" placeholder="وصف قصير للبانر"><?php echo esc_textarea(computech_section_meta($post, '_computech_offer_desc', '')); ?></textarea></p>
                    <div class="ct-grid ct-grid-2">
                        <p class="ct-field"><label>اللون</label><?php echo computech_home_offer_color_select('_computech_offer_color', $color); ?></p>
                        <p class="ct-field"><label>الأيقونة</label><?php echo computech_home_extra_icon_select('_computech_offer_icon', $icon); ?></p>
                    </div>
                </div>
            </section>
            <section class="ct-admin-section">
                <div class="ct-admin-section-head"><div><h3>2. الرابط</h3><p>اختار صفحة موجودة أو رابط خارجي.</p></div></div>
                <div class="ct-admin-section-body">
                    <div class="ct-grid ct-grid-2">
                        <p class="ct-field"><label>نوع الرابط</label><?php echo computech_home_offer_link_type_select('_computech_offer_link_type', $link_type); ?></p>
                        <p class="ct-field ct-offer-page-field"><label>اختيار صفحة</label><select name="_computech_offer_page_id" class="widefat"><?php echo computech_need_pages_options($page_id); ?></select></p>
                    </div>
                    <p class="ct-field ct-offer-url-field" style="margin-top:14px"><label>رابط خارجي</label><input type="url" name="_computech_offer_url" value="<?php echo esc_attr(computech_section_meta($post, '_computech_offer_url', '')); ?>" class="widefat" placeholder="https://example.com"></p>
                    <p class="ct-field" style="margin-top:14px"><label><input type="checkbox" name="_computech_offer_new_tab" value="1" <?php checked(computech_section_meta($post, '_computech_offer_new_tab', '0'), '1'); ?>> فتح في تبويب جديد</label></p>
                </div>
            </section>
            <section class="ct-admin-section">
                <div class="ct-admin-section-head"><div><h3>3. الصورة والظهور</h3></div></div>
                <div class="ct-admin-section-body">
                    <div class="ct-admin-note">الصورة من Featured Image. الظهور من Publish/Public. الترتيب من Order داخل Page Attributes. أول بنر في الترتيب يظهر كبير بعرض الصفحة.</div>
                </div>
            </section>
        </div>
    </div>
    <script>
    (function(){
        var root = document.currentScript.previousElementSibling;
        if (!root) { return; }
        function updateFields(){
            var select = root.querySelector('.ct-offer-link-type');
            var type = select ? select.value : 'none';
            var pageField = root.querySelector('.ct-offer-page-field');
            var urlField = root.querySelector('.ct-offer-url-field');
            if (pageField) { pageField.style.display = type === 'page' ? '' : 'none'; }
            if (urlField) { urlField.style.display = type === 'custom' ? '' : 'none'; }
        }
        var select = root.querySelector('.ct-offer-link-type');
        if (select) { select.addEventListener('change', updateFields); }
        updateFields();
    })();
    </script>
    <?php
}

function computech_save_home_offer_banner(int $post_id): void {
    if (!isset($_POST['computech_home_offer_banner_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['computech_home_offer_banner_nonce'])), 'computech_save_home_offer_banner')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    update_post_meta($post_id, '_computech_offer_desc', sanitize_textarea_field(wp_unslash($_POST['_computech_offer_desc'] ?? '')));
    $type = sanitize_key(wp_unslash($_POST['_computech_offer_link_type'] ?? 'none'));
    $type = in_array($type, array('none', 'page', 'custom'), true) ? $type : 'none';
    update_post_meta($post_id, '_computech_offer_link_type', $type);
    update_post_meta($post_id, '_computech_offer_page_id', (string) absint($_POST['_computech_offer_page_id'] ?? 0));
    update_post_meta($post_id, '_computech_offer_url', esc_url_raw(wp_unslash($_POST['_computech_offer_url'] ?? '')));
    update_post_meta($post_id, '_computech_offer_new_tab', !empty($_POST['_computech_offer_new_tab']) ? '1' : '0');
    $color = sanitize_key(wp_unslash($_POST['_computech_offer_color'] ?? 'blue'));
    $color = array_key_exists($color, computech_home_offer_color_choices()) ? $color : 'blue';
    update_post_meta($post_id, '_computech_offer_color', $color);
    $icon = sanitize_key(wp_unslash($_POST['_computech_offer_icon'] ?? 'offer'));
    $icon = array_key_exists($icon, computech_home_extra_icon_choices()) ? $icon : 'offer';
    update_post_meta($post_id, '_computech_offer_icon', $icon);
    delete_post_meta($post_id, '_computech_offer_show');
    delete_post_meta($post_id, '_computech_offer_image');
    delete_post_meta($post_id, '_computech_offer_image_id');
}
add_action('save_post_ct_offer_banner', 'computech_save_home_offer_banner');

function computech_home_offer_banner_posts(): array {
    $posts = get_posts(array(
        'post_type' => 'ct_offer_banner',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
        'order' => 'ASC',
        'post_password' => '',
        'no_found_rows' => true,
        'suppress_filters' => false,
    ));
    return array_values(array_filter($posts, static function($post): bool {
        return $post instanceof WP_Post && $post->post_status === 'publish' && $post->post_password === '';
    }));
}

function computech_home_offer_banner_url(WP_Post $post): string {
    $type = computech_section_meta($post, '_computech_offer_link_type', 'none');
    if ($type === 'page') {
        $page_id = absint(computech_section_meta($post, '_computech_offer_page_id', '0'));
        $url = $page_id ? get_permalink($page_id) : '';
        return $url ? $url : '#';
    }
    if ($type === 'custom') {
        $url = computech_section_meta($post, '_computech_offer_url', '');
        return $url !== '' ? $url : '#';
    }
    return '#';
}

function computech_home_offer_banner_desc(WP_Post $post): string {
    $desc = computech_section_meta($post, '_computech_offer_desc', '');
    if ($desc !== '') {
        return $desc;
    }
    return $post->post_excerpt !== '' ? $post->post_excerpt : wp_strip_all_tags($post->post_content);
}

function computech_home_offer_banner_target(WP_Post $post): string {
    return computech_section_meta($post, '_computech_offer_new_tab', '0') === '1' ? ' target="_blank" rel="noopener"' : '';
}

function computech_home_extra_sanitize_settings(array $data): array {
    $defaults = computech_home_extra_default_settings();
    $out = array();
    foreach ($defaults as $key => $default) {
        if (substr($key, -5) === '_show') {
            $out[$key] = !empty($data[$key]) ? '1' : '0';
        } elseif (strpos($key, 'iframe_src') !== false || substr($key, -4) === '_url' || substr($key, -6) === '_image') {
            $out[$key] = esc_url_raw(wp_unslash($data[$key] ?? ''));
        } elseif (strpos($key, 'address') !== false) {
            $out[$key] = sanitize_textarea_field(wp_unslash($data[$key] ?? ''));
        } elseif (strpos($key, 'desc') !== false || strpos($key, 'subtitle') !== false) {
            $out[$key] = sanitize_textarea_field(wp_unslash($data[$key] ?? ''));
        } else {
            $out[$key] = sanitize_text_field(wp_unslash($data[$key] ?? ''));
        }
    }
    return $out;
}

function computech_home_extra_sanitize_simple_rows($rows, array $fields): array {
    $clean = array();
    if (!is_array($rows)) {
        return $clean;
    }
    foreach ($rows as $row) {
        if (!is_array($row)) { continue; }
        $item = array('show' => !empty($row['show']) ? '1' : '0');
        foreach ($fields as $field => $type) {
            $value = wp_unslash($row[$field] ?? '');
            if ($type === 'url') {
                $item[$field] = esc_url_raw($value);
            } elseif ($type === 'textarea') {
                $item[$field] = sanitize_textarea_field($value);
            } elseif ($type === 'icon') {
                $key = sanitize_key($value);
                $item[$field] = array_key_exists($key, computech_home_extra_icon_choices()) ? $key : 'shield';
            } elseif ($type === 'platform') {
                $platforms = function_exists('computech_footer_social_platforms') ? computech_footer_social_platforms() : array('facebook' => array());
                $key = sanitize_key($value);
                $item[$field] = array_key_exists($key, $platforms) ? $key : 'facebook';
            } else {
                $item[$field] = sanitize_text_field($value);
            }
        }
        $has_content = false;
        foreach ($item as $k => $v) {
            if ($k !== 'show' && trim((string) $v) !== '') { $has_content = true; break; }
        }
        if ($has_content) {
            $clean[] = $item;
        }
    }
    return $clean;
}

function computech_home_extra_admin_menu(): void {
    add_submenu_page('computech-settings', 'السكشنات المسترجعة', 'السكشنات المسترجعة', computech_admin_capability(), 'computech-restored-home-sections', 'computech_home_extra_settings_page');
}
add_action('admin_menu', 'computech_home_extra_admin_menu');

function computech_home_extra_settings_page(): void {
    if (!current_user_can(computech_admin_capability())) {
        wp_die('غير مصرح لك بتعديل هذه الإعدادات.');
    }
    $settings = computech_home_extra_settings();
    $offer_pills = computech_home_extra_rows('computech_home_offer_pills', 'computech_home_extra_default_offer_pills');
    $offer_cards = computech_home_extra_rows('computech_home_offer_cards', 'computech_home_extra_default_offer_cards');
    $payment_methods = computech_home_extra_rows('computech_home_payment_methods', 'computech_home_extra_default_payment_methods');
    $contact_cards = computech_home_extra_rows('computech_home_contact_cards', 'computech_home_extra_default_contact_cards');
    $social_links = computech_home_extra_rows('computech_home_contact_social_links', 'computech_home_extra_default_contact_social_links');
    $cta_features = computech_home_extra_rows('computech_home_cta_features', 'computech_home_extra_default_cta_features');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('computech_save_home_extra_sections', 'computech_home_extra_sections_nonce');
        $settings = computech_home_extra_sanitize_settings($_POST);
        $offer_pills = computech_home_extra_sanitize_simple_rows($_POST['offer_pills'] ?? array(), array('icon' => 'icon', 'text' => 'text'));
        $offer_cards = computech_home_extra_sanitize_simple_rows($_POST['offer_cards'] ?? array(), array('title' => 'textarea', 'subtitle' => 'text', 'desc' => 'textarea', 'button_label' => 'text', 'button_url' => 'url', 'image' => 'url', 'float_image' => 'url', 'alt' => 'text'));
        $payment_methods = computech_home_extra_sanitize_simple_rows($_POST['payment_methods'] ?? array(), array('icon' => 'icon', 'name' => 'text'));
        $contact_cards = computech_home_extra_sanitize_simple_rows($_POST['contact_cards'] ?? array(), array('icon' => 'icon', 'label' => 'text', 'value' => 'text', 'note_1' => 'text', 'note_2' => 'text', 'url' => 'url'));
        $social_links = computech_home_extra_sanitize_simple_rows($_POST['contact_social'] ?? array(), array('platform' => 'platform', 'url' => 'url'));
        $cta_features = computech_home_extra_sanitize_simple_rows($_POST['cta_features'] ?? array(), array('icon' => 'icon', 'title' => 'text', 'subtitle' => 'text'));
        update_option('computech_home_extra_settings', $settings, false);
        update_option('computech_home_offer_pills', $offer_pills, false);
        update_option('computech_home_offer_cards', $offer_cards, false);
        update_option('computech_home_payment_methods', $payment_methods, false);
        update_option('computech_home_contact_cards', $contact_cards, false);
        update_option('computech_home_contact_social_links', $social_links, false);
        update_option('computech_home_cta_features', $cta_features, false);
        echo '<div class="notice notice-success is-dismissible"><p>تم حفظ السكشنات المسترجعة بنجاح.</p></div>';
    }
    ?>
    <div class="wrap computech-admin-wrap" dir="rtl">
        <h1>السكشنات المسترجعة في الصفحة الرئيسية</h1>
        <p>هذه الصفحة لتعديل المحتوى فقط. التصميم، الألوان، التوزيع، الحركات، وأسماء الكلاسات ثابتة داخل الثيم حتى لا يفسد شكل الموقع.</p>
        <form method="post">
            <?php wp_nonce_field('computech_save_home_extra_sections', 'computech_home_extra_sections_nonce'); ?>

            <div class="ct-panel"><h2>1. عروض خاصة وبنرات ترويجية</h2>
                <label><input type="checkbox" name="offers_show" value="1" <?php checked($settings['offers_show'], '1'); ?>> إظهار السكشن</label>
                <div class="ct-grid"><label>نص العنوان<input type="text" name="offers_title_before" value="<?php echo esc_attr($settings['offers_title_before']); ?>"></label><label>الكلمة المميزة<input type="text" name="offers_title_highlight" value="<?php echo esc_attr($settings['offers_title_highlight']); ?>"></label></div>
                <label>الوصف<textarea name="offers_subtitle" rows="2"><?php echo esc_textarea($settings['offers_subtitle']); ?></textarea></label>
                <div class="ct-admin-note" style="background:#f8fafc;border:1px solid #dbeafe;border-radius:12px;padding:14px;margin-top:14px">
                    البنرات نفسها أصبحت من قائمة <strong>General → عروض وبنرات</strong>.
                    العنوان من عنوان WordPress، الصورة من Featured Image، والظهور من Published/Public.
                    أول بنر حسب Order يظهر كبير بعرض الصفحة، والباقي يظهر كبنرات صغيرة بنفس تصميم السكشن.
                </div>
                <p style="margin-top:14px"><a href="<?php echo esc_url(admin_url('post-new.php?post_type=ct_offer_banner')); ?>" class="button button-primary">+ إضافة بنر</a> <a href="<?php echo esc_url(admin_url('edit.php?post_type=ct_offer_banner')); ?>" class="button">إدارة البنرات</a></p>
            </div>

            <div class="ct-panel"><h2>2. طرق الدفع المتاحة</h2><label><input type="checkbox" name="payment_show" value="1" <?php checked($settings['payment_show'], '1'); ?>> إظهار السكشن</label><label>العنوان<input type="text" name="payment_title" value="<?php echo esc_attr($settings['payment_title']); ?>"></label><div id="che-payment-list"><?php foreach ($payment_methods as $i => $row) : ?><div class="ct-row"><div class="ct-row-head"><strong>طريقة دفع</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="payment_methods[<?php echo esc_attr((string)$i); ?>][show]" value="1" <?php checked(!empty($row['show'])); ?>> إظهار</label><label>الأيقونة<?php echo computech_home_extra_icon_select('payment_methods[' . esc_attr((string)$i) . '][icon]', $row['icon'] ?? 'credit_card'); ?></label><label>الاسم<input type="text" name="payment_methods[<?php echo esc_attr((string)$i); ?>][name]" value="<?php echo esc_attr($row['name'] ?? ''); ?>"></label></div><?php endforeach; ?></div><button type="button" class="button che-add-row" data-target="che-payment-list" data-template="che-payment-template" data-name="payment_methods">+ إضافة طريقة دفع</button></div>

            <div class="ct-panel"><h2>3. تواصل معنا — نحن هنا لخدمتك</h2><label><input type="checkbox" name="contact_show" value="1" <?php checked($settings['contact_show'], '1'); ?>> إظهار السكشن</label><div class="ct-grid"><label>الكلمة المميزة<input type="text" name="contact_title_highlight" value="<?php echo esc_attr($settings['contact_title_highlight']); ?>"></label><label>باقي العنوان<input type="text" name="contact_title_after" value="<?php echo esc_attr($settings['contact_title_after']); ?>"></label></div><label>الوصف<textarea name="contact_subtitle" rows="3"><?php echo esc_textarea($settings['contact_subtitle']); ?></textarea></label><h3>كروت التواصل</h3><div id="che-contact-list"><?php foreach ($contact_cards as $i => $row) : ?><div class="ct-row"><div class="ct-row-head"><strong>كارت تواصل</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="contact_cards[<?php echo esc_attr((string)$i); ?>][show]" value="1" <?php checked(!empty($row['show'])); ?>> إظهار</label><label>الأيقونة<?php echo computech_home_extra_icon_select('contact_cards[' . esc_attr((string)$i) . '][icon]', $row['icon'] ?? 'phone'); ?></label><label>العنوان<input type="text" name="contact_cards[<?php echo esc_attr((string)$i); ?>][label]" value="<?php echo esc_attr($row['label'] ?? ''); ?>"></label><label>القيمة<input type="text" name="contact_cards[<?php echo esc_attr((string)$i); ?>][value]" value="<?php echo esc_attr($row['value'] ?? ''); ?>"></label><label>ملاحظة 1<input type="text" name="contact_cards[<?php echo esc_attr((string)$i); ?>][note_1]" value="<?php echo esc_attr($row['note_1'] ?? ''); ?>"></label><label>ملاحظة 2<input type="text" name="contact_cards[<?php echo esc_attr((string)$i); ?>][note_2]" value="<?php echo esc_attr($row['note_2'] ?? ''); ?>"></label><label>رابط اختياري<input type="text" name="contact_cards[<?php echo esc_attr((string)$i); ?>][url]" value="<?php echo esc_attr($row['url'] ?? ''); ?>"></label></div><?php endforeach; ?></div><button type="button" class="button che-add-row" data-target="che-contact-list" data-template="che-contact-template" data-name="contact_cards">+ إضافة كارت تواصل</button><h3>السوشيال</h3><label>عنوان السوشيال<input type="text" name="contact_social_label" value="<?php echo esc_attr($settings['contact_social_label']); ?>"></label><div id="che-social-list"><?php foreach ($social_links as $i => $row) : ?><div class="ct-row"><div class="ct-row-head"><strong>رابط سوشيال</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="contact_social[<?php echo esc_attr((string)$i); ?>][show]" value="1" <?php checked(!empty($row['show'])); ?>> إظهار</label><label>المنصة<?php echo function_exists('computech_admin_footer_platform_select') ? computech_admin_footer_platform_select('contact_social[' . esc_attr((string)$i) . '][platform]', $row['platform'] ?? 'facebook') : '<input type="text" name="contact_social[' . esc_attr((string)$i) . '][platform]" value="facebook">'; ?></label><label>الرابط<input type="text" name="contact_social[<?php echo esc_attr((string)$i); ?>][url]" value="<?php echo esc_attr($row['url'] ?? ''); ?>"></label></div><?php endforeach; ?></div><button type="button" class="button che-add-row" data-target="che-social-list" data-template="che-social-template" data-name="contact_social">+ إضافة رابط سوشيال</button><h3>الخريطة</h3><label><input type="checkbox" name="contact_map_show" value="1" <?php checked($settings['contact_map_show'], '1'); ?>> إظهار الخريطة</label><div class="ct-grid"><label>عنوان الخريطة<input type="text" name="contact_map_title" value="<?php echo esc_attr($settings['contact_map_title']); ?>"></label><label>وصف الخريطة<input type="text" name="contact_map_subtitle" value="<?php echo esc_attr($settings['contact_map_subtitle']); ?>"></label></div><label>رابط iframe للخريطة<textarea name="contact_map_iframe_src" rows="2"><?php echo esc_textarea($settings['contact_map_iframe_src']); ?></textarea></label><div class="ct-grid"><label>اسم النشاط<input type="text" name="contact_map_business_name" value="<?php echo esc_attr($settings['contact_map_business_name']); ?>"></label><label>التقييم<input type="text" name="contact_map_rating" value="<?php echo esc_attr($settings['contact_map_rating']); ?>"></label></div><label>عنوان النشاط<textarea name="contact_map_address" rows="2"><?php echo esc_textarea($settings['contact_map_address']); ?></textarea></label><div class="ct-grid"><label>نص رابط Google Maps<input type="text" name="contact_map_link_label" value="<?php echo esc_attr($settings['contact_map_link_label']); ?>"></label><label>رابط Google Maps<input type="text" name="contact_map_link_url" value="<?php echo esc_attr($settings['contact_map_link_url']); ?>"></label></div></div>

            <div class="ct-panel"><h2>4. جاهز لتجربة أداء أفضل؟</h2><label><input type="checkbox" name="cta_show" value="1" <?php checked($settings['cta_show'], '1'); ?>> إظهار السكشن</label><div class="ct-grid"><label>بداية العنوان<input type="text" name="cta_title_before" value="<?php echo esc_attr($settings['cta_title_before']); ?>"></label><label>الكلمة المميزة<input type="text" name="cta_title_highlight" value="<?php echo esc_attr($settings['cta_title_highlight']); ?>"></label></div><label>نهاية العنوان<input type="text" name="cta_title_after" value="<?php echo esc_attr($settings['cta_title_after']); ?>"></label><label>الوصف<textarea name="cta_desc" rows="2"><?php echo esc_textarea($settings['cta_desc']); ?></textarea></label><div class="ct-grid"><label>نص الزر<input type="text" name="cta_button_label" value="<?php echo esc_attr($settings['cta_button_label']); ?>"></label><label>رابط الزر<input type="text" name="cta_button_url" value="<?php echo esc_attr($settings['cta_button_url']); ?>"></label></div><label>الصورة<input type="text" name="cta_image" value="<?php echo esc_attr($settings['cta_image']); ?>"></label><h3>مميزات CTA</h3><div id="che-cta-features-list"><?php foreach ($cta_features as $i => $row) : ?><div class="ct-row"><div class="ct-row-head"><strong>ميزة</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="cta_features[<?php echo esc_attr((string)$i); ?>][show]" value="1" <?php checked(!empty($row['show'])); ?>> إظهار</label><label>الأيقونة<?php echo computech_home_extra_icon_select('cta_features[' . esc_attr((string)$i) . '][icon]', $row['icon'] ?? 'shield'); ?></label><label>العنوان<input type="text" name="cta_features[<?php echo esc_attr((string)$i); ?>][title]" value="<?php echo esc_attr($row['title'] ?? ''); ?>"></label><label>الوصف الصغير<input type="text" name="cta_features[<?php echo esc_attr((string)$i); ?>][subtitle]" value="<?php echo esc_attr($row['subtitle'] ?? ''); ?>"></label></div><?php endforeach; ?></div><button type="button" class="button che-add-row" data-target="che-cta-features-list" data-template="che-cta-feature-template" data-name="cta_features">+ إضافة ميزة</button></div>

            <?php submit_button('حفظ السكشنات المسترجعة'); ?>
        </form>
    </div>

    <template id="che-pill-template"><div class="ct-row"><div class="ct-row-head"><strong>نقطة</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="offer_pills[__i__][show]" value="1" checked> إظهار</label><label>الأيقونة<?php echo computech_home_extra_icon_select('offer_pills[__i__][icon]', 'search'); ?></label><label>النص<input type="text" name="offer_pills[__i__][text]" value=""></label></div></template>
    <template id="che-offer-card-template"><div class="ct-row"><div class="ct-row-head"><strong>بنر صغير</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="offer_cards[__i__][show]" value="1" checked> إظهار</label><label>العنوان<textarea name="offer_cards[__i__][title]" rows="2"></textarea></label><label>العنوان الفرعي<input type="text" name="offer_cards[__i__][subtitle]" value=""></label><label>الوصف<textarea name="offer_cards[__i__][desc]" rows="2"></textarea></label><label>نص الزر<input type="text" name="offer_cards[__i__][button_label]" value=""></label><label>رابط الزر<input type="text" name="offer_cards[__i__][button_url]" value=""></label><label>الصورة<input type="text" name="offer_cards[__i__][image]" value=""></label><label>الصورة العائمة<input type="text" name="offer_cards[__i__][float_image]" value=""></label><label>Alt الصورة<input type="text" name="offer_cards[__i__][alt]" value=""></label></div></template>
    <template id="che-payment-template"><div class="ct-row"><div class="ct-row-head"><strong>طريقة دفع</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="payment_methods[__i__][show]" value="1" checked> إظهار</label><label>الأيقونة<?php echo computech_home_extra_icon_select('payment_methods[__i__][icon]', 'credit_card'); ?></label><label>الاسم<input type="text" name="payment_methods[__i__][name]" value=""></label></div></template>
    <template id="che-contact-template"><div class="ct-row"><div class="ct-row-head"><strong>كارت تواصل</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="contact_cards[__i__][show]" value="1" checked> إظهار</label><label>الأيقونة<?php echo computech_home_extra_icon_select('contact_cards[__i__][icon]', 'phone'); ?></label><label>العنوان<input type="text" name="contact_cards[__i__][label]" value=""></label><label>القيمة<input type="text" name="contact_cards[__i__][value]" value=""></label><label>ملاحظة 1<input type="text" name="contact_cards[__i__][note_1]" value=""></label><label>ملاحظة 2<input type="text" name="contact_cards[__i__][note_2]" value=""></label><label>رابط اختياري<input type="text" name="contact_cards[__i__][url]" value=""></label></div></template>
    <template id="che-social-template"><div class="ct-row"><div class="ct-row-head"><strong>رابط سوشيال</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="contact_social[__i__][show]" value="1" checked> إظهار</label><label>المنصة<?php echo function_exists('computech_admin_footer_platform_select') ? computech_admin_footer_platform_select('contact_social[__i__][platform]', 'facebook') : '<input type="text" name="contact_social[__i__][platform]" value="facebook">'; ?></label><label>الرابط<input type="text" name="contact_social[__i__][url]" value=""></label></div></template>
    <template id="che-cta-feature-template"><div class="ct-row"><div class="ct-row-head"><strong>ميزة</strong><button type="button" class="button-link-delete che-remove-row">حذف</button></div><label><input type="checkbox" name="cta_features[__i__][show]" value="1" checked> إظهار</label><label>الأيقونة<?php echo computech_home_extra_icon_select('cta_features[__i__][icon]', 'shield'); ?></label><label>العنوان<input type="text" name="cta_features[__i__][title]" value=""></label><label>الوصف الصغير<input type="text" name="cta_features[__i__][subtitle]" value=""></label></div></template>
    <style>.computech-admin-wrap{max-width:1160px}.ct-panel{background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:18px;margin:18px 0;box-shadow:0 6px 20px rgba(0,0,0,.03)}.ct-panel h2{margin-top:0}.ct-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.ct-row{border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin:12px 0;background:#f9fafb;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;align-items:end}.ct-row-head{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between}.ct-row label,.ct-panel label{display:block;font-weight:700;margin:8px 0}.ct-row input[type=text],.ct-row select,.ct-row textarea,.ct-panel input[type=text],.ct-panel textarea,.ct-panel select{width:100%;margin-top:6px}@media(max-width:782px){.ct-grid,.ct-row{grid-template-columns:1fr}}</style>
    <script>(function(){var indexes={offer_pills:<?php echo (int)count($offer_pills); ?>,offer_cards:<?php echo (int)count($offer_cards); ?>,payment_methods:<?php echo (int)count($payment_methods); ?>,contact_cards:<?php echo (int)count($contact_cards); ?>,contact_social:<?php echo (int)count($social_links); ?>,cta_features:<?php echo (int)count($cta_features); ?>};document.addEventListener('click',function(e){var remove=e.target.closest('.che-remove-row');if(remove){e.preventDefault();remove.closest('.ct-row').remove();return;}var add=e.target.closest('.che-add-row');if(add){e.preventDefault();var target=document.getElementById(add.dataset.target);var tpl=document.getElementById(add.dataset.template);var name=add.dataset.name;var i=indexes[name]||0;indexes[name]=i+1;target.insertAdjacentHTML('beforeend',tpl.innerHTML.replaceAll('__i__',i));}});})();</script>
    <?php
}

function computech_render_home_offers_section(): void {
    $s = computech_home_extra_settings();
    if ($s['offers_show'] !== '1') { return; }
    $banners = computech_home_offer_banner_posts();

    if (!$banners) {
        $pills = computech_home_extra_visible_rows('computech_home_offer_pills', 'computech_home_extra_default_offer_pills');
        $cards = computech_home_extra_visible_rows('computech_home_offer_cards', 'computech_home_extra_default_offer_cards');
        ?>
        <section class="offers-section">
            <div class="offers-bg-decor"><div class="obg-circuit obg-circuit-right"></div><div class="obg-circuit obg-circuit-left"></div><div class="obg-dots obg-dots-right"></div><div class="obg-dots obg-dots-left"></div><div class="obg-circle obg-circle-right"></div><div class="obg-circle obg-circle-left"></div></div>
            <div class="offers-container">
                <div class="offers-header"><div class="offers-header-dots"><span class="ohd blue"></span><span class="ohd cyan"></span><span class="ohd pill"></span><span class="ohd green"></span></div><h2 class="offers-heading"><?php echo esc_html($s['offers_title_before']); ?> <span class="offers-heading-blue"><?php echo esc_html($s['offers_title_highlight']); ?></span></h2><?php if ($s['offers_subtitle'] !== '') : ?><p class="offers-heading-sub"><?php echo esc_html($s['offers_subtitle']); ?></p><?php endif; ?></div>
                <div class="offer-main-banner">
                    <?php if ($s['offers_ribbon_image'] !== '') : ?><div class="offer-ribbon"><img src="<?php echo esc_url($s['offers_ribbon_image']); ?>" alt="عرض مميز"></div><?php endif; ?>
                    <?php if ($s['offers_value_badge_image'] !== '') : ?><div class="offer-value-badge"><img src="<?php echo esc_url($s['offers_value_badge_image']); ?>" alt="أفضل قيمة"></div><?php endif; ?>
                    <div class="offer-main-inner"><div class="offer-main-text"><h3 class="offer-main-title"><?php echo esc_html($s['offers_main_title']); ?><br><span><?php echo esc_html($s['offers_main_title_highlight']); ?></span></h3><?php if ($s['offers_main_desc'] !== '') : ?><p class="offer-main-desc"><?php echo esc_html($s['offers_main_desc']); ?></p><?php endif; ?><div class="offer-main-pills"><?php foreach ($pills as $pill) : ?><span class="omp"><?php echo computech_home_extra_icon_svg((string)($pill['icon'] ?? 'shield')); ?><?php echo esc_html($pill['text'] ?? ''); ?></span><?php endforeach; ?></div><?php if ($s['offers_main_button_label'] !== '') : ?><a href="<?php echo esc_url(computech_home_extra_url($s['offers_main_button_url'], computech_page_url('products'))); ?>" class="offer-main-btn"><?php echo esc_html($s['offers_main_button_label']); ?></a><?php endif; ?></div><?php if ($s['offers_main_image'] !== '') : ?><div class="offer-main-image"><img src="<?php echo esc_url($s['offers_main_image']); ?>" alt="<?php echo esc_attr($s['offers_main_title']); ?>"></div><?php endif; ?></div>
                </div>
                <?php if ($cards) : ?><div class="offer-dual-row"><?php foreach ($cards as $i => $card) : $is_green = $i % 2 === 1; $title_lines = computech_home_extra_text_lines((string)($card['title'] ?? '')); ?><div class="offer-sub-card <?php echo $is_green ? 'offer-sub-green' : 'offer-sub-blue'; ?>"><?php if (!empty($card['float_image'])) : ?><div class="<?php echo $is_green ? 'offer-sub-float-badge' : 'offer-sub-float-icon'; ?>"><img src="<?php echo esc_url($card['float_image']); ?>" alt=""></div><?php endif; ?><?php if (!empty($card['image'])) : ?><div class="offer-sub-image"><img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr($card['alt'] ?? ($card['title'] ?? '')); ?>"></div><?php endif; ?><div class="offer-sub-text"><?php if ($title_lines) : ?><h3 class="offer-sub-title"><?php echo implode('<br>', array_map('esc_html', $title_lines)); ?></h3><?php endif; ?><?php if (!empty($card['subtitle'])) : ?><p class="offer-sub-subtitle <?php echo $is_green ? 'offer-sub-subtitle-green' : ''; ?>"><?php echo esc_html($card['subtitle']); ?></p><?php endif; ?><?php if (!empty($card['desc'])) : ?><p class="offer-sub-desc"><?php echo esc_html($card['desc']); ?></p><?php endif; ?><?php if (!empty($card['button_label'])) : ?><a href="<?php echo esc_url(computech_home_extra_url((string)($card['button_url'] ?? ''), computech_page_url('products'))); ?>" class="offer-sub-btn <?php echo $is_green ? 'offer-sub-btn-green' : 'offer-sub-btn-blue'; ?>"><?php echo esc_html($card['button_label']); ?></a><?php endif; ?></div></div><?php endforeach; ?></div><?php endif; ?>
            </div>
        </section>
        <?php
        return;
    }

    $main = array_shift($banners);
    $main_color = sanitize_key(computech_section_meta($main, '_computech_offer_color', 'blue'));
    $main_icon = sanitize_key(computech_section_meta($main, '_computech_offer_icon', 'offer'));
    $main_url = computech_home_offer_banner_url($main);
    $main_desc = computech_home_offer_banner_desc($main);
    $main_image = get_the_post_thumbnail_url($main, 'full');
    ?>
    <section class="offers-section">
        <div class="offers-bg-decor"><div class="obg-circuit obg-circuit-right"></div><div class="obg-circuit obg-circuit-left"></div><div class="obg-dots obg-dots-right"></div><div class="obg-dots obg-dots-left"></div><div class="obg-circle obg-circle-right"></div><div class="obg-circle obg-circle-left"></div></div>
        <div class="offers-container">
            <div class="offers-header"><div class="offers-header-dots"><span class="ohd blue"></span><span class="ohd cyan"></span><span class="ohd pill"></span><span class="ohd green"></span></div><h2 class="offers-heading"><?php echo esc_html($s['offers_title_before']); ?> <span class="offers-heading-blue"><?php echo esc_html($s['offers_title_highlight']); ?></span></h2><?php if ($s['offers_subtitle'] !== '') : ?><p class="offers-heading-sub"><?php echo esc_html($s['offers_subtitle']); ?></p><?php endif; ?></div>
            <div class="offer-main-banner offer-color-<?php echo esc_attr($main_color); ?>">
                <div class="offer-promo-icon" aria-hidden="true"><?php echo computech_home_extra_icon_svg($main_icon); ?></div>
                <div class="offer-main-inner">
                    <div class="offer-main-text">
                        <h3 class="offer-main-title"><?php echo esc_html(get_the_title($main)); ?></h3>
                        <?php if ($main_desc !== '') : ?><p class="offer-main-desc"><?php echo esc_html($main_desc); ?></p><?php endif; ?>
                        <?php if ($main_url !== '#') : ?><a href="<?php echo esc_url($main_url); ?>" class="offer-main-btn"<?php echo computech_home_offer_banner_target($main); ?>>تصفح الآن ←</a><?php endif; ?>
                    </div>
                    <?php if ($main_image) : ?><div class="offer-main-image"><img src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr(get_the_title($main)); ?>"></div><?php endif; ?>
                </div>
            </div>
            <?php if ($banners) : ?><div class="offer-dual-row"><?php foreach ($banners as $banner) : $color = sanitize_key(computech_section_meta($banner, '_computech_offer_color', 'blue')); $icon = sanitize_key(computech_section_meta($banner, '_computech_offer_icon', 'offer')); $desc = computech_home_offer_banner_desc($banner); $url = computech_home_offer_banner_url($banner); $img = get_the_post_thumbnail_url($banner, 'large'); ?><div class="offer-sub-card offer-color-<?php echo esc_attr($color); ?>"><div class="offer-sub-float-icon"><?php echo computech_home_extra_icon_svg($icon); ?></div><?php if ($img) : ?><div class="offer-sub-image"><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($banner)); ?>"></div><?php endif; ?><div class="offer-sub-text"><h3 class="offer-sub-title"><?php echo esc_html(get_the_title($banner)); ?></h3><?php if ($desc !== '') : ?><p class="offer-sub-desc"><?php echo esc_html($desc); ?></p><?php endif; ?><?php if ($url !== '#') : ?><a href="<?php echo esc_url($url); ?>" class="offer-sub-btn"<?php echo computech_home_offer_banner_target($banner); ?>>اعرف المزيد ←</a><?php endif; ?></div></div><?php endforeach; ?></div><?php endif; ?>
        </div>
    </section>
    <?php
}

function computech_render_home_payment_section(): void {
    $s = computech_home_extra_settings();
    if ($s['payment_show'] !== '1') { return; }
    $methods = computech_home_extra_visible_rows('computech_home_payment_methods', 'computech_home_extra_default_payment_methods');
    if (!$methods) { return; }
    ?><section class="payment-section"><div class="payment-container"><?php if ($s['payment_title'] !== '') : ?><h3 class="payment-title"><?php echo esc_html($s['payment_title']); ?></h3><?php endif; ?><div class="payment-methods"><?php foreach ($methods as $i => $m) : if ($i > 0) : ?><div class="payment-divider"></div><?php endif; ?><div class="payment-method"><div class="payment-icon"><?php echo computech_home_extra_icon_svg((string)($m['icon'] ?? 'credit_card')); ?></div><span class="payment-name"><?php echo esc_html($m['name'] ?? ''); ?></span></div><?php endforeach; ?></div></div></section><?php
}

function computech_home_contact_icon_class(string $icon): string {
    if ($icon === 'whatsapp') { return 'contact-icon-green'; }
    if ($icon === 'location') { return 'contact-icon-orange'; }
    return 'contact-icon-blue';
}


function computech_home_contact_card_with_general_settings(array $card): array {
    $icon = sanitize_key((string) ($card['icon'] ?? 'phone'));
    if ($icon === 'phone' && computech_business_phone() !== '') {
        $card['value'] = computech_business_phone();
        $card['url'] = computech_tel_url((string) $card['value']);
        if (computech_business_hours() !== '') {
            $card['note_1'] = computech_business_hours();
        }
    } elseif ($icon === 'whatsapp' && computech_business_whatsapp_number() !== '') {
        $card['value'] = '+' . computech_business_whatsapp_number();
        $card['url'] = computech_whatsapp_url();
    } elseif ($icon === 'email' && computech_business_email() !== '') {
        $card['value'] = computech_business_email();
        $card['url'] = computech_mailto_url((string) $card['value']);
    } elseif ($icon === 'location' && computech_business_address() !== '') {
        $card['value'] = computech_business_address();
        $card['url'] = computech_business_map_url();
    }
    return computech_site_text_deep($card);
}

function computech_render_home_contact_section(): void {
    $s = computech_home_extra_settings();
    if (computech_business_map_embed_url() !== '') { $s['contact_map_iframe_src'] = computech_business_map_embed_url(); }
    if (computech_business_address() !== '') { $s['contact_map_address'] = computech_business_address(); }
    if (computech_business_map_url() !== '') { $s['contact_map_link_url'] = computech_business_map_url(); }
    $s['contact_map_business_name'] = computech_site_name();
    if ($s['contact_show'] !== '1') { return; }
    $cards = computech_home_extra_visible_rows('computech_home_contact_cards', 'computech_home_extra_default_contact_cards');
    $socials = computech_home_extra_visible_rows('computech_home_contact_social_links', 'computech_home_extra_default_contact_social_links');
    ?>
    <section class="contact-section">
        <div class="contact-container"><div class="contact-top-card"><div class="contact-panel"><div class="contact-panel-header"><h2 class="contact-heading"><span class="contact-heading-blue"><?php echo esc_html($s['contact_title_highlight']); ?></span><br><?php echo esc_html($s['contact_title_after']); ?></h2><?php if ($s['contact_subtitle'] !== '') : ?><p class="contact-subtitle"><?php echo esc_html($s['contact_subtitle']); ?></p><?php endif; ?></div><div class="contact-cards"><?php foreach ($cards as $card) : $card = computech_home_contact_card_with_general_settings($card); $icon = sanitize_key((string)($card['icon'] ?? 'phone')); $value = trim((string)($card['value'] ?? '')); ?><div class="contact-card"><div class="contact-card-icon <?php echo esc_attr(computech_home_contact_icon_class($icon)); ?>"><?php echo computech_home_extra_icon_svg($icon); ?></div><div class="contact-card-body"><span class="contact-card-label"><?php echo esc_html($card['label'] ?? ''); ?></span><?php if (!empty($card['url'])) : ?><a class="contact-card-value <?php echo $icon === 'location' ? 'contact-card-value-sm' : ''; ?>" href="<?php echo esc_url($card['url']); ?>"><?php echo esc_html($value); ?></a><?php else : ?><span class="contact-card-value <?php echo $icon === 'location' ? 'contact-card-value-sm' : ''; ?>"><?php echo esc_html($value); ?></span><?php endif; ?></div><div class="contact-card-note"><?php if (!empty($card['note_1'])) : ?><span><?php echo esc_html($card['note_1']); ?></span><?php endif; ?><?php if (!empty($card['note_2'])) : ?><span><?php echo esc_html($card['note_2']); ?></span><?php endif; ?></div></div><?php endforeach; ?></div><?php if ($socials) : ?><div class="contact-social"><span class="contact-social-label"><?php echo esc_html($s['contact_social_label']); ?></span><div class="contact-social-icons"><?php foreach ($socials as $social) : $platform = sanitize_key((string)($social['platform'] ?? 'facebook')); $platforms = function_exists('computech_footer_social_platforms') ? computech_footer_social_platforms() : array(); $label = $platforms[$platform]['label'] ?? ucfirst($platform); ?><a href="<?php echo esc_url(computech_home_extra_url((string)($social['url'] ?? ''), '#')); ?>" class="contact-social-btn" aria-label="<?php echo esc_attr($label); ?>" target="_blank" rel="noopener"><?php echo function_exists('computech_footer_social_svg') ? computech_footer_social_svg($platform) : computech_home_extra_icon_svg('phone'); ?></a><?php endforeach; ?></div></div><?php endif; ?></div><?php if ($s['contact_map_show'] === '1') : ?><div class="contact-divider"></div><div class="map-panel"><div class="map-panel-header"><h3 class="map-title"><?php echo esc_html($s['contact_map_title']); ?></h3><?php if ($s['contact_map_subtitle'] !== '') : ?><p class="map-subtitle"><?php echo esc_html($s['contact_map_subtitle']); ?></p><?php endif; ?></div><div class="map-wrapper"><?php if ($s['contact_map_iframe_src'] !== '') : ?><iframe src="<?php echo esc_url($s['contact_map_iframe_src']); ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo esc_attr(computech_site_name()); ?>"></iframe><?php endif; ?><div class="map-biz-card"><div class="map-biz-header"><div class="map-biz-logo"><svg viewBox="0 0 28 28" fill="none"><rect x="2" y="6" width="24" height="16" rx="3" fill="#2563eb" opacity="0.9"/><rect x="5" y="9" width="18" height="10" rx="2" fill="#0f172a"/><circle cx="14" cy="14" r="3" fill="#2563eb"/></svg></div><div class="map-biz-info"><strong><?php echo esc_html($s['contact_map_business_name']); ?></strong><span class="map-biz-rating"><?php echo esc_html($s['contact_map_rating']); ?></span></div></div><p class="map-biz-address"><?php echo nl2br(esc_html($s['contact_map_address'])); ?></p><?php if ($s['contact_map_link_label'] !== '') : ?><a href="<?php echo esc_url(computech_home_extra_url($s['contact_map_link_url'], '#')); ?>" class="map-biz-link" target="_blank" rel="noopener"><?php echo esc_html($s['contact_map_link_label']); ?></a><?php endif; ?></div><div class="map-zoom"><button class="map-zoom-btn" aria-label="تكبير" type="button">+</button><button class="map-zoom-btn" aria-label="تصغير" type="button">−</button></div></div></div><?php endif; ?></div></div>
    </section>
    <?php
}

function computech_render_home_final_cta_section(): void {
    $s = computech_home_extra_settings();
    if ($s['cta_show'] !== '1') { return; }
    $features = computech_home_extra_visible_rows('computech_home_cta_features', 'computech_home_extra_default_cta_features');
    ?><section class="contact-section contact-cta-only-section"><div class="contact-container"><div class="contact-cta-strip"><div class="contact-cta-text"><h3 class="contact-cta-title"><?php echo esc_html($s['cta_title_before']); ?> <span class="contact-cta-blue"><?php echo esc_html($s['cta_title_highlight']); ?></span> <?php echo esc_html($s['cta_title_after']); ?></h3><?php if ($s['cta_desc'] !== '') : ?><p class="contact-cta-desc"><?php echo esc_html($s['cta_desc']); ?></p><?php endif; ?><?php if ($s['cta_button_label'] !== '') : ?><a href="<?php echo esc_url(computech_home_extra_url($s['cta_button_url'], computech_page_url('products'))); ?>" class="contact-cta-btn"><?php echo esc_html($s['cta_button_label']); ?></a><?php endif; ?></div><?php if ($s['cta_image'] !== '') : ?><div class="contact-cta-image"><img src="<?php echo esc_url($s['cta_image']); ?>" alt="<?php echo esc_attr($s['cta_title_after']); ?>"></div><?php endif; ?><?php if ($features) : ?><div class="contact-cta-features"><?php foreach ($features as $i => $feature) : if ($i > 0) : ?><div class="contact-cta-sep"></div><?php endif; ?><div class="contact-cta-feature"><div class="contact-cta-feature-icon"><?php echo computech_home_extra_icon_svg((string)($feature['icon'] ?? 'shield')); ?></div><div class="contact-cta-feature-text"><strong><?php echo esc_html($feature['title'] ?? ''); ?></strong><span><?php echo esc_html($feature['subtitle'] ?? ''); ?></span></div></div><?php endforeach; ?></div><?php endif; ?></div></div></section><?php
}

// Computech categories/products architecture layer.
require_once get_template_directory() . '/inc/computech-architecture.php';
require_once get_template_directory() . '/inc/computech-woocommerce.php';
