<?php get_header(); $ct_site_name = function_exists('computech_site_name') ? computech_site_name() : get_bloginfo('name'); ?>
<main>
    <?php computech_breadcrumbs('خريطة الموقع'); ?>
    <section class="section page-hero"><div class="container"><div class="section-header reveal"><span class="section-kicker">خريطة الموقع</span><h1>كل روابط <?php echo esc_html($ct_site_name); ?> في مكان واحد</h1><p>استخدم الروابط التالية للوصول السريع لكل صفحات الموقع.</p></div></div></section>
    <section class="section"><div class="container"><div class="category-grid">
        <?php foreach (array('about'=>'من نحن','services'=>'الخدمات','categories'=>'أقسام المتجر','products'=>'المنتجات','offers'=>'العروض','contact'=>'تواصل معنا') as $slug => $label) : ?>
            <a class="category-card" href="<?php echo esc_url(computech_page_url($slug)); ?>"><h3><?php echo esc_html($label); ?></h3><p>انتقل إلى صفحة <?php echo esc_html($label); ?></p></a>
        <?php endforeach; ?>
    </div></div></section>
</main>
<?php get_footer(); ?>
