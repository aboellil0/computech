<?php
/**
 * Front page template.
 * All rendered sections below read from WordPress database/dashboard data only.
 */
get_header();
?>

<?php computech_render_home_hero_section(); ?>
<?php computech_render_customer_needs_section(); ?>
<?php computech_wc_render_shop_categories_section(); ?>
<?php computech_wc_render_featured_products_section(); ?>
<?php computech_render_home_offers_section(); ?>
<?php computech_render_home_payment_section(); ?>
<?php computech_render_home_contact_section(); ?>
<?php computech_render_home_final_cta_section(); ?>

<?php get_footer(); ?>
