<?php
/**
 * Offers page template.
 */
get_header();
$ct_site_name = function_exists('computech_site_name') ? computech_site_name() : get_bloginfo('name');
?>
<main>
    <?php computech_breadcrumbs('العروض'); ?>
    <section class="offer-section" id="offers">
        <div class="offer-container">
            <div class="offer-main-card">
                <div class="offer-main-image-wrap">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/offers-hero-imported-computers.png" alt="عروض أجهزة استيراد" class="offer-main-image">
                    <div class="offer-ribbon"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/offers-featured-ribbon.png" alt="عرض مميز"></div>
                </div>
                <div class="offer-main-content">
                    <span class="offer-badge">عروض <?php echo esc_html($ct_site_name); ?></span>
                    <h1 class="offer-title">عروض مختارة على الأجهزة والإكسسوارات</h1>
                    <p class="offer-desc">تابع أحدث الخصومات والباقات الخاصة، وتواصل معنا لمعرفة السعر الحالي والتوفر قبل الشراء.</p>
                    <div class="offer-actions">
                        <a href="<?php echo esc_url(computech_page_url('products')); ?>" class="offer-main-btn">تصفح المنتجات ←</a>
                        <a href="<?php echo esc_url(computech_whatsapp_url(sprintf('السلام عليكم، أريد الاستفسار عن عروض %s', $ct_site_name))); ?>" class="offer-sub-btn offer-sub-btn-green" target="_blank" rel="noopener">استفسار واتساب ←</a>
                    </div>
                </div>
            </div>

            <div class="offer-side-grid">
                <article class="offer-sub-card offer-sub-blue">
                    <div class="offer-sub-badge"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/offers-best-value-badge.png" alt="أفضل قيمة"></div>
                    <div class="offer-sub-image"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/offers-device-consultation.png" alt="استشارة جهاز"></div>
                    <div class="offer-sub-text">
                        <h2 class="offer-sub-title">استشارة<br>اختيار الجهاز</h2>
                        <p class="offer-sub-subtitle offer-sub-subtitle-blue">مجانية قبل الشراء</p>
                        <p class="offer-sub-desc">ساعد العميل يختار جهاز مناسب لاستخدامه وميزانيته.</p>
                        <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="offer-sub-btn offer-sub-btn-blue">احجز استشارة ←</a>
                    </div>
                </article>

                <article class="offer-sub-card offer-sub-green">
                    <div class="offer-sub-badge"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/offers-discount-icon.png" alt="خصم"></div>
                    <div class="offer-sub-image"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/offers-accessories-bundle.png" alt="عروض الإكسسوارات"></div>
                    <div class="offer-sub-text">
                        <h2 class="offer-sub-title">باقات<br>الإكسسوارات</h2>
                        <p class="offer-sub-subtitle offer-sub-subtitle-green">خصومات حسب التوفر</p>
                        <p class="offer-sub-desc">كيبورد، ماوس، سماعات، كاميرات، وشاشات بأسعار مناسبة.</p>
                        <a href="<?php echo esc_url(computech_page_url('products')); ?>" class="offer-sub-btn offer-sub-btn-green">تسوق الآن ←</a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
