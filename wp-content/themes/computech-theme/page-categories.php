<?php
/**
 * Template Name: صفحة أقسام المتجر - WooCommerce
 */
get_header();
computech_breadcrumbs('أقسام المتجر');

if (function_exists('computech_wc_render_categories_page')) {
    computech_wc_render_categories_page();
} else {
    echo '<section class="cat-all"><div class="cat-container"><div class="wp-product-empty"><h2>WooCommerce غير جاهز</h2><p>فعّل WooCommerce لعرض الأقسام.</p></div></div></section>';
}

get_footer();
