<?php
/**
 * Template Name: صفحة الخدمات - كمبيوتيك
 */
get_header();
?>
<?php computech_breadcrumbs('الخدمات'); ?>

<?php if (function_exists('computech_render_services_hero_slider')) { computech_render_services_hero_slider(); } ?>

<!-- ============================================
     Main Services Grid
     ============================================ -->
<section class="services-grid-section">
    <div class="services-container">
        <div class="svc-section-header">
            <span class="svc-section-badge">ما الذي نقدمه</span>
            <h2 class="svc-section-title">خدماتنا الرئيسية</h2>
            <p class="svc-section-subtitle">نقدم مجموعة شاملة من الخدمات لتلبية جميع احتياجاتك التقنية</p>
        </div>
        <div class="services-grid">
            <?php $service_posts = function_exists('computech_service_posts') ? computech_service_posts() : array(); ?>
            <?php if ($service_posts) : ?>
                <?php foreach ($service_posts as $service_post) :
                    $service_url = function_exists('computech_service_url') ? computech_service_url($service_post) : '#';
                    $service_target = function_exists('computech_service_target') ? computech_service_target($service_post) : '';
                    $service_desc = function_exists('computech_service_desc') ? computech_service_desc($service_post) : '';
                    $service_image = get_the_post_thumbnail_url($service_post, 'medium_large');
                ?>
                    <div class="svc-card ct-db-service-card">
                        <?php if ($service_image) : ?>
                            <div class="ct-service-card-image">
                                <img src="<?php echo esc_url($service_image); ?>" alt="<?php echo esc_attr(get_the_title($service_post)); ?>" loading="lazy">
                            </div>
                        <?php endif; ?>
                        <div class="svc-card-icon ct-service-card-icon">
                            <?php echo function_exists('computech_service_icon_html') ? computech_service_icon_html($service_post) : ''; ?>
                        </div>
                        <h3 class="svc-card-title"><?php echo esc_html(get_the_title($service_post)); ?></h3>
                        <?php if ($service_desc !== '') : ?><p class="svc-card-desc"><?php echo esc_html($service_desc); ?></p><?php endif; ?>
                        <?php if ($service_url !== '#') : ?>
                            <a href="<?php echo esc_url($service_url); ?>" class="svc-card-link"<?php echo $service_target; ?>>اعرف المزيد
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="svc-card ct-db-service-card">
                    <h3 class="svc-card-title">لا توجد خدمات منشورة</h3>
                    <p class="svc-card-desc">أضف الخدمات من لوحة التحكم من قسم Services ← خدمات.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (function_exists('computech_render_services_featured_section')) { computech_render_services_featured_section(); } ?>

<?php if (function_exists('computech_render_services_process_section')) { computech_render_services_process_section(); } ?>

<?php get_footer(); ?>
