<?php
/**
 * Template Name: صفحة أقسام المتجر - كمبيوتيك
 */
get_header();
computech_breadcrumbs('أقسام المتجر');

if (function_exists('computech_arch_render_categories_page')) {
    computech_arch_render_categories_page();
} else {
    echo '<section class="cat-all"><div class="cat-container"><div class="wp-product-empty"><h2>أقسام المتجر</h2><p>فعّل طبقة معمارية الأقسام لعرض الأقسام من الداشبورد.</p></div></div></section>';
}

get_footer();
