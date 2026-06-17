<?php
/**
 * WooCommerce product category archive.
 */
get_header();

$term = get_queried_object();
if ($term instanceof WP_Term && function_exists('computech_wc_render_category_archive')) {
    computech_wc_render_category_archive($term);
} else {
    echo '<section class="prod-grid-section"><div class="prod-container"><div class="wp-product-empty"><h2>القسم غير موجود</h2></div></div></section>';
}

get_footer();
