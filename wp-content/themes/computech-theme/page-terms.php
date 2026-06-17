<?php get_header(); $ct_site_name = function_exists('computech_site_name') ? computech_site_name() : get_bloginfo('name'); ?>
<main>
    <?php computech_breadcrumbs('الشروط والأحكام'); ?>
    <section class="section page-hero"><div class="container"><div class="section-header reveal"><span class="section-kicker">الشروط والأحكام</span><h1>شروط استخدام موقع <?php echo esc_html($ct_site_name); ?></h1><p>هذه صفحة مبدئية يمكن تعديل محتواها من لوحة تحكم WordPress بما يناسب سياسة الشركة.</p></div></div></section>
    <section class="section"><div class="container"><div class="about-text-card"><h2>ملاحظات عامة</h2><p>الأسعار والتوفر والضمانات قابلة للتحديث حسب المنتج والمخزون. برجاء التواصل مع فريق <?php echo esc_html($ct_site_name); ?> لتأكيد التفاصيل قبل الشراء.</p><p>يمكنك تعديل هذه الصفحة لاحقًا من لوحة التحكم وإضافة شروط البيع والاسترجاع والضمان بشكل كامل.</p></div></div></section>
</main>
<?php get_footer(); ?>
