<?php
/**
 * Template Name: حسابي - كمبيوتك
 */
get_header();
?>
<section class="ct-account-section ct-account-section-clean">
    <div class="container">
        <?php if (!function_exists('WC')) : ?>
            <div class="ct-account-empty"><h2>WooCommerce غير مفعل</h2><p>فعّل WooCommerce لاستخدام صفحة الحساب.</p></div>
        <?php else : ?>
            <?php echo do_shortcode('[woocommerce_my_account]'); ?>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
