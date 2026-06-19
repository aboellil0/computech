<?php
/**
 * Template Name: صفحة من نحن - كمبيوتيك
 */
get_header();
?>
<?php computech_breadcrumbs(get_the_title() ?: ''); ?>
<?php
if (function_exists('computech_render_about_page')) {
    computech_render_about_page();
} else {
    echo '<main class="site-main"><div class="container"><p>لا يوجد محتوى.</p></div></main>';
}
?>
<?php get_footer(); ?>
