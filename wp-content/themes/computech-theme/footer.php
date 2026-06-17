<?php
/**
 * Theme footer.
 * Dynamic sources:
 * - Brand/contact/social/newsletter email/footer fixed links: General > Site Identity
 * - Quick links: WordPress Primary Menu
 * - Categories: WooCommerce product categories
 * - Services: خدمات posts
 * - Feature strip: fixed footer promises
 */
$footer_site_name = function_exists('computech_site_name') ? computech_site_name() : get_bloginfo('name');
$footer_site_desc = function_exists('computech_site_description') ? computech_site_description() : get_bloginfo('description');
$footer_settings = function_exists('computech_footer_settings') ? computech_footer_settings() : array();
$newsletter_title = trim((string) ($footer_settings['newsletter_title'] ?? 'اشترك ليصلك الجديد'));
$newsletter_subtitle = trim((string) ($footer_settings['newsletter_subtitle'] ?? 'اشترك في نشرتنا البريدية لتصلك أحدث المنتجات والعروض والأخبار'));
$newsletter_placeholder = trim((string) ($footer_settings['newsletter_placeholder'] ?? 'أدخل بريدك الإلكتروني'));
$newsletter_button = trim((string) ($footer_settings['newsletter_button_label'] ?? 'اشترك الآن'));
$footer_contact_title = 'تواصل معنا';
?>
    <footer class="site-footer">
        <div class="footer-bg-decor"><div class="fbd-circuit fbd-circuit-bl"></div><div class="fbd-circuit fbd-circuit-br"></div></div>
        <div class="footer-container">
            <div class="footer-newsletter">
                <div class="footer-newsletter-text">
                    <?php if ($newsletter_title !== '') : ?><h3 class="footer-newsletter-title"><?php echo esc_html($newsletter_title); ?></h3><?php endif; ?>
                    <?php if ($newsletter_subtitle !== '') : ?><p class="footer-newsletter-sub"><?php echo esc_html($newsletter_subtitle); ?></p><?php endif; ?>
                </div>
                <form class="footer-newsletter-form" action="<?php echo esc_url(function_exists('computech_footer_newsletter_mailto_action_url') ? computech_footer_newsletter_mailto_action_url() : home_url('/')); ?>" method="post" enctype="text/plain">
                    <div class="footer-newsletter-input-wrap">
                        <svg class="footer-mail-icon-input" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>
                        <input type="email" name="email" placeholder="<?php echo esc_attr($newsletter_placeholder); ?>" class="footer-newsletter-input">
                    </div>
                    <?php if ($newsletter_button !== '') : ?><button class="footer-newsletter-btn" type="submit"><?php echo esc_html($newsletter_button); ?></button><?php endif; ?>
                </form>
                <div class="footer-newsletter-icon"><svg viewBox="0 0 80 80" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="16" width="64" height="48" rx="6"/><polyline points="72,16 40,44 8,16"/></svg></div>
            </div>
            <div class="footer-divider"></div>

            <div class="footer-columns">
                <div class="footer-col footer-col-brand">
                    <div class="footer-logo">
                        <?php if (function_exists('computech_footer_logo_html')) { echo computech_footer_logo_html(); } ?>
                        <?php if ($footer_site_name !== '') : ?><span class="footer-logo-text"><?php echo esc_html($footer_site_name); ?></span><?php endif; ?>
                    </div>
                    <?php if ($footer_site_desc !== '') : ?><p class="footer-brand-desc"><?php echo esc_html($footer_site_desc); ?></p><?php endif; ?>
                    <div class="footer-brand-line"></div>
                </div>

                <?php if (function_exists('computech_footer_has_wp_menu_links') && computech_footer_has_wp_menu_links()) : ?>
                    <div class="footer-col">
                        <h4 class="footer-col-title">روابط سريعة</h4>
                        <?php computech_render_footer_wp_menu_links(); ?>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('computech_footer_has_wc_categories') && computech_footer_has_wc_categories()) : ?>
                    <div class="footer-col">
                        <h4 class="footer-col-title">التصنيفات</h4>
                        <?php computech_render_footer_wc_categories(); ?>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('computech_footer_has_service_posts') && computech_footer_has_service_posts()) : ?>
                    <div class="footer-col">
                        <h4 class="footer-col-title">خدماتنا</h4>
                        <?php computech_render_footer_service_posts(); ?>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('computech_footer_has_site_identity_contact') && computech_footer_has_site_identity_contact()) : ?>
                    <div class="footer-col">
                        <h4 class="footer-col-title"><?php echo esc_html($footer_contact_title); ?></h4>
                        <?php computech_render_footer_site_identity_contact(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (function_exists('computech_render_footer_static_feature_strip')) { computech_render_footer_static_feature_strip(); } ?>

            <div class="footer-bottom">
                <?php if (function_exists('computech_render_footer_social_links')) { computech_render_footer_social_links(); } ?>
                <?php if (function_exists('computech_render_footer_site_identity_bottom_links')) { computech_render_footer_site_identity_bottom_links(); } ?>
                <div class="footer-copyright">&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html($footer_site_name); ?>. جميع الحقوق محفوظة.</div>
            </div>
        </div>
    </footer>
<?php wp_footer(); ?>
</body>
</html>
