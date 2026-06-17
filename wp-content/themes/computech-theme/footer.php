<?php
/**
 * Theme footer.
 * Footer content is controlled from Dashboard > إعدادات كمبيوتك > إعدادات الفوتر.
 * The layout/design classes stay fixed here to protect the visual design.
 */
$footer_settings = function_exists('computech_footer_settings') ? computech_footer_settings() : array();
$newsletter_title = trim((string) ($footer_settings['newsletter_title'] ?? ''));
$newsletter_subtitle = trim((string) ($footer_settings['newsletter_subtitle'] ?? ''));
$newsletter_placeholder = trim((string) ($footer_settings['newsletter_placeholder'] ?? ''));
$newsletter_button = trim((string) ($footer_settings['newsletter_button_label'] ?? ''));
$footer_logo_text = trim(computech_site_text((string) ($footer_settings['footer_logo_text'] ?? '')));
if ($footer_logo_text === '') { $footer_logo_text = computech_site_name(); }
$brand_description = trim(computech_site_text((string) ($footer_settings['brand_description'] ?? '')));
$quick_title = trim((string) ($footer_settings['quick_links_title'] ?? ''));
$category_title = trim((string) ($footer_settings['category_links_title'] ?? ''));
$service_title = trim((string) ($footer_settings['service_links_title'] ?? ''));
$contact_title = trim((string) ($footer_settings['contact_title'] ?? ''));
$copyright_text = trim(computech_site_text((string) ($footer_settings['copyright_text'] ?? '')));
?>
    <footer class="site-footer">
        <div class="footer-bg-decor"><div class="fbd-circuit fbd-circuit-bl"></div><div class="fbd-circuit fbd-circuit-br"></div></div>
        <div class="footer-container">
            <?php if (function_exists('computech_footer_bool') && computech_footer_bool('show_newsletter', true)) : ?>
                <div class="footer-newsletter">
                    <div class="footer-newsletter-text">
                        <?php if ($newsletter_title !== '') : ?><h3 class="footer-newsletter-title"><?php echo esc_html($newsletter_title); ?></h3><?php endif; ?>
                        <?php if ($newsletter_subtitle !== '') : ?><p class="footer-newsletter-sub"><?php echo esc_html($newsletter_subtitle); ?></p><?php endif; ?>
                    </div>
                    <form class="footer-newsletter-form" action="<?php echo esc_url(function_exists('computech_footer_newsletter_action_url') ? computech_footer_newsletter_action_url() : home_url('/')); ?>" method="get">
                        <div class="footer-newsletter-input-wrap">
                            <svg class="footer-mail-icon-input" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>
                            <input type="email" name="footer_email" placeholder="<?php echo esc_attr($newsletter_placeholder); ?>" class="footer-newsletter-input">
                        </div>
                        <?php if ($newsletter_button !== '') : ?><button class="footer-newsletter-btn" type="submit"><?php echo esc_html($newsletter_button); ?></button><?php endif; ?>
                    </form>
                    <div class="footer-newsletter-icon"><svg viewBox="0 0 80 80" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="16" width="64" height="48" rx="6"/><polyline points="72,16 40,44 8,16"/></svg></div>
                </div>
                <div class="footer-divider"></div>
            <?php endif; ?>

            <div class="footer-columns">
                <div class="footer-col footer-col-brand">
                    <div class="footer-logo">
                        <?php if (function_exists('computech_footer_logo_html')) { echo computech_footer_logo_html(); } ?>
                        <?php if ($footer_logo_text !== '') : ?><span class="footer-logo-text"><?php echo esc_html($footer_logo_text); ?></span><?php endif; ?>
                    </div>
                    <?php if ($brand_description !== '') : ?><p class="footer-brand-desc"><?php echo esc_html($brand_description); ?></p><?php endif; ?>
                    <div class="footer-brand-line"></div>
                </div>

                <?php if (function_exists('computech_footer_columns_has_rows') && computech_footer_columns_has_rows('computech_footer_quick_links')) : ?>
                    <div class="footer-col">
                        <?php if ($quick_title !== '') : ?><h4 class="footer-col-title"><?php echo esc_html($quick_title); ?></h4><?php endif; ?>
                        <?php computech_render_footer_link_list('computech_footer_quick_links'); ?>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('computech_footer_columns_has_rows') && computech_footer_columns_has_rows('computech_footer_category_links')) : ?>
                    <div class="footer-col">
                        <?php if ($category_title !== '') : ?><h4 class="footer-col-title"><?php echo esc_html($category_title); ?></h4><?php endif; ?>
                        <?php computech_render_footer_link_list('computech_footer_category_links'); ?>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('computech_footer_columns_has_rows') && computech_footer_columns_has_rows('computech_footer_service_links')) : ?>
                    <div class="footer-col">
                        <?php if ($service_title !== '') : ?><h4 class="footer-col-title"><?php echo esc_html($service_title); ?></h4><?php endif; ?>
                        <?php computech_render_footer_link_list('computech_footer_service_links'); ?>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('computech_footer_contact_has_rows') && computech_footer_contact_has_rows()) : ?>
                    <div class="footer-col">
                        <?php if ($contact_title !== '') : ?><h4 class="footer-col-title"><?php echo esc_html($contact_title); ?></h4><?php endif; ?>
                        <?php computech_render_footer_contact_items(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (function_exists('computech_render_footer_feature_strip')) { computech_render_footer_feature_strip(); } ?>

            <div class="footer-bottom">
                <?php if (function_exists('computech_render_footer_social_links')) { computech_render_footer_social_links(); } ?>
                <?php if (function_exists('computech_render_footer_bottom_links')) { computech_render_footer_bottom_links(); } ?>
                <?php if ($copyright_text !== '') : ?>
                    <div class="footer-copyright">&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html($copyright_text); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </footer>
<?php wp_footer(); ?>
</body>
</html>
