<?php
/**
 * Theme header.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$computech_logo_label = computech_site_text(computech_header_label('logo_aria_label', computech_site_name()));
$computech_nav_label = computech_header_label('nav_aria_label', '');
$computech_search_label = computech_header_label('search_button_label', '');
$computech_cart_label = computech_header_label('cart_label', '');
$computech_mobile_menu_label = computech_header_label('mobile_menu_button_label', '');
$computech_mobile_menu_title = computech_header_label('mobile_menu_title', '');
$computech_mobile_close_label = computech_header_label('mobile_menu_close_label', '');
$computech_whatsapp_number = computech_business_whatsapp_number();
$computech_whatsapp_url = $computech_whatsapp_number !== '' ? computech_whatsapp_url(computech_header_setting('whatsapp_message', '')) : '';
?>

    <div class="top-blue-line"></div>

    <?php computech_render_header_topbar(); ?>

    <header class="main-header">
        <div class="header-container">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo" aria-label="<?php echo esc_attr($computech_logo_label); ?>">
                <?php echo computech_header_logo_html(); ?>
            </a>

            <nav class="main-nav" aria-label="<?php echo esc_attr($computech_nav_label); ?>">
                <ul class="nav-list">
                    <?php computech_render_primary_links('nav-link'); ?>
                </ul>
            </nav>

            <div class="header-actions">
                <?php if (computech_header_bool('show_search')) : ?>
                    <form class="search-box" role="search" method="get" action="<?php echo esc_url(function_exists('computech_wc_products_page_url') ? computech_wc_products_page_url() : computech_page_url('products')); ?>">
                        <input type="text" name="<?php echo esc_attr(function_exists('computech_wc_product_search_query_var') ? computech_wc_product_search_query_var() : 's'); ?>" placeholder="<?php echo esc_attr(computech_header_setting('search_placeholder', '')); ?>" class="search-input" value="<?php echo esc_attr(function_exists('computech_wc_get_request') ? computech_wc_get_request(function_exists('computech_wc_product_search_query_var') ? computech_wc_product_search_query_var() : 's', get_search_query()) : get_search_query()); ?>">
                        <button class="search-btn" aria-label="<?php echo esc_attr($computech_search_label); ?>" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                    </form>
                <?php endif; ?>
                <?php if (computech_header_bool('show_cart')) : ?>
                    <div class="action-icons">
                        <?php if (computech_header_bool('show_cart')) : ?>
                            <a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : computech_page_url('products')); ?>" class="action-icon cart-icon" aria-label="<?php echo esc_attr($computech_cart_label); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                <span class="cart-badge"><?php echo esc_html(function_exists('WC') && WC()->cart ? (string) WC()->cart->get_cart_contents_count() : '0'); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" aria-label="<?php echo esc_attr($computech_mobile_menu_label); ?>" type="button"><span></span><span></span><span></span></button>
        </div>

        <div class="mobile-menu">
            <div class="mobile-menu-overlay"></div>
            <div class="mobile-menu-content">
                <div class="mobile-menu-header">
                    <span class="mobile-menu-title"><?php echo esc_html($computech_mobile_menu_title); ?></span>
                    <button class="mobile-menu-close" aria-label="<?php echo esc_attr($computech_mobile_close_label); ?>" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                </div>
                <?php if (computech_header_bool('show_search')) : ?>
                    <form class="mobile-search" role="search" method="get" action="<?php echo esc_url(function_exists('computech_wc_products_page_url') ? computech_wc_products_page_url() : computech_page_url('products')); ?>">
                        <input type="text" name="<?php echo esc_attr(function_exists('computech_wc_product_search_query_var') ? computech_wc_product_search_query_var() : 's'); ?>" placeholder="<?php echo esc_attr(computech_header_setting('search_placeholder', '')); ?>" value="<?php echo esc_attr(function_exists('computech_wc_get_request') ? computech_wc_get_request(function_exists('computech_wc_product_search_query_var') ? computech_wc_product_search_query_var() : 's', get_search_query()) : get_search_query()); ?>">
                        <button aria-label="<?php echo esc_attr($computech_search_label); ?>" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>
                    </form>
                <?php endif; ?>
                <ul class="mobile-nav-list">
                    <?php computech_render_primary_links('mobile-nav-link'); ?>
                </ul>
            </div>
        </div>
    </header>

    <?php if ($computech_whatsapp_url !== '') : ?>
        <a href="<?php echo esc_url($computech_whatsapp_url); ?>" class="whatsapp-btn floating-whatsapp-btn" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(computech_header_setting('whatsapp_label', 'واتساب')); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <span><?php echo esc_html(computech_header_setting('whatsapp_label', '')); ?></span>
        </a>
    <?php endif; ?>
