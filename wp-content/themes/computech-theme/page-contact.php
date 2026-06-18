<?php
/**
 * Template Name: صفحة تواصل معنا - كمبيوتيك
 */
get_header();

// Contact page data must come from Site Identity / Settings > General, not hardcoded template values.
$ct_site_name = function_exists('computech_site_name') ? computech_site_name() : get_bloginfo('name');
$ct_site_description = function_exists('computech_site_description') ? computech_site_description() : get_bloginfo('description');
$ct_contact_intro = $ct_site_description !== ''
    ? $ct_site_description
    : sprintf('فريق %s متاح للإجابة على استفساراتك وتقديم الدعم الفني والاستشارات. تواصل معنا بالطريقة التي تناسبك.', $ct_site_name);

$ct_phone = function_exists('computech_business_phone') ? computech_business_phone() : '';
$ct_phone_url = ($ct_phone !== '' && function_exists('computech_tel_url')) ? computech_tel_url($ct_phone) : '';

$ct_whatsapp_number = function_exists('computech_business_whatsapp_number') ? computech_business_whatsapp_number() : '';
$ct_whatsapp_display = $ct_whatsapp_number !== '' ? '+' . $ct_whatsapp_number : '';
$ct_whatsapp_url = ($ct_whatsapp_number !== '' && function_exists('computech_whatsapp_url'))
    ? computech_whatsapp_url('مرحباً، أحتاج مساعدة من ' . $ct_site_name)
    : '';

$ct_email = function_exists('computech_business_email') ? computech_business_email() : get_option('admin_email', '');
$ct_email_url = ($ct_email !== '' && function_exists('computech_mailto_url')) ? computech_mailto_url($ct_email) : '';

$ct_address = function_exists('computech_business_address') ? computech_business_address() : '';
$ct_hours = function_exists('computech_business_hours') ? computech_business_hours() : '';
$ct_map_url = function_exists('computech_business_map_url') ? computech_business_map_url() : '';
$ct_map_embed = function_exists('computech_business_map_embed_url') ? computech_business_map_embed_url() : '';

$ct_social_links = function_exists('computech_site_identity_social_links') ? computech_site_identity_social_links() : array();
$ct_social_labels = array(
    'facebook' => 'فيسبوك',
    'instagram' => 'انستقرام',
    'linkedin' => 'لينكد إن',
    'youtube' => 'يوتيوب',
    'tiktok' => 'تيك توك',
    'twitter' => 'إكس',
    'whatsapp' => 'واتساب',
);
?>
<?php computech_breadcrumbs('تواصل معنا'); ?>
    <!-- ============================================
         Contact Hero Section
         ============================================ -->
    <section class="contact-hero">
        <div class="contact-hero-bg">
            <div class="contact-circuit contact-circuit-1"></div>
            <div class="contact-circuit contact-circuit-2"></div>
            <div class="contact-circuit contact-circuit-3"></div>
            <div class="contact-dot contact-dot-1"></div>
            <div class="contact-dot contact-dot-2"></div>
            <div class="contact-dot contact-dot-3"></div>
            <div class="contact-dot contact-dot-4"></div>
            <div class="contact-glow contact-glow-1"></div>
            <div class="contact-glow contact-glow-2"></div>
        </div>
        <div class="contact-container contact-hero-inner">
            <div class="contact-hero-content">
                <span class="contact-section-badge">تواصل معنا</span>
                <h1 class="contact-hero-title">نحن هنا لمساعدتك</h1>
                <p class="contact-hero-subtitle"><?php echo esc_html($ct_contact_intro); ?></p>
                <div class="contact-hero-pills">
                    <span class="contact-pill">رد سريع</span>
                    <span class="contact-pill">دعم فني</span>
                    <span class="contact-pill">استشارة مجانية</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Main Contact Section
         ============================================ -->
    <section class="contact-main">
        <div class="contact-container">
            <div class="contact-main-inner">
                <!-- Contact Form (Left side in RTL) -->
                <div class="contact-form-wrap">
                    <div class="contact-form-card">
                        <h2 class="contact-form-title">أرسل لنا رسالة</h2>
                        <p class="contact-form-subtitle">املأ النموذج وسنعود إليك في أقرب وقت ممكن</p>
                        <form class="contact-form" id="contactForm">
                            <div class="contact-form-group">
                                <label class="contact-form-label">الاسم الكامل</label>
                                <input type="text" class="contact-form-input" placeholder="أدخل اسمك الكامل" required>
                            </div>
                            <div class="contact-form-row">
                                <div class="contact-form-group">
                                    <label class="contact-form-label">رقم الهاتف</label>
                                    <input type="tel" class="contact-form-input" placeholder="05xxxxxxxx" required>
                                </div>
                                <div class="contact-form-group">
                                    <label class="contact-form-label">البريد الإلكتروني</label>
                                    <input type="email" class="contact-form-input" placeholder="example@domain.com" required>
                                </div>
                            </div>
                            <div class="contact-form-group">
                                <label class="contact-form-label">نوع الطلب</label>
                                <div class="contact-select-wrap">
                                    <select class="contact-form-select" required>
                                        <option value="">اختر نوع الطلب</option>
                                        <option value="استعلام">استعلام عن منتج</option>
                                        <option value="شراء">طلب شراء</option>
                                        <option value="صيانة">طلب صيانة</option>
                                        <option value="استشارة">استشارة قبل الشراء</option>
                                        <option value="دعم">دعم فني</option>
                                        <option value="شكوى">شكوى أو اقتراح</option>
                                        <option value="أخرى">أخرى</option>
                                    </select>
                                    <svg class="contact-select-arrow" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 5 6 8 9 5"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="contact-form-group">
                                <label class="contact-form-label">الرسالة</label>
                                <textarea class="contact-form-textarea" placeholder="اكتب رسالتك هنا..." rows="5" required></textarea>
                            </div>
                            <button type="submit" class="contact-form-submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13"/>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                </svg>
                                إرسال الرسالة
                            </button>
                        </form>
                        <div class="contact-form-success" id="contactFormSuccess">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <h3>تم إرسال رسالتك بنجاح!</h3>
                            <p>شكراً لتواصلك مع <?php echo esc_html($ct_site_name); ?>. سنعود إليك في أقرب وقت ممكن.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info Cards (Right side in RTL) -->
                <div class="contact-info-wrap">
                    <?php if ($ct_phone !== '') : ?>
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">الهاتف</h3>
                            <?php if ($ct_phone_url !== '') : ?>
                                <a class="contact-info-value" href="<?php echo esc_url($ct_phone_url); ?>"><?php echo esc_html($ct_phone); ?></a>
                            <?php else : ?>
                                <p class="contact-info-value"><?php echo esc_html($ct_phone); ?></p>
                            <?php endif; ?>
                            <?php if ($ct_hours !== '') : ?><p class="contact-info-desc"><?php echo esc_html($ct_hours); ?></p><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($ct_whatsapp_display !== '') : ?>
                    <div class="contact-info-card">
                        <div class="contact-info-icon contact-info-icon-green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">واتساب</h3>
                            <?php if ($ct_whatsapp_url !== '') : ?>
                                <a class="contact-info-value" href="<?php echo esc_url($ct_whatsapp_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($ct_whatsapp_display); ?></a>
                            <?php else : ?>
                                <p class="contact-info-value"><?php echo esc_html($ct_whatsapp_display); ?></p>
                            <?php endif; ?>
                            <p class="contact-info-desc">نخدمك عبر واتساب على مدار الساعة</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($ct_email !== '') : ?>
                    <div class="contact-info-card">
                        <div class="contact-info-icon contact-info-icon-cyan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <polyline points="22,4 12,13 2,4"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">البريد الإلكتروني</h3>
                            <?php if ($ct_email_url !== '') : ?>
                                <a class="contact-info-value" href="<?php echo esc_url($ct_email_url); ?>"><?php echo esc_html($ct_email); ?></a>
                            <?php else : ?>
                                <p class="contact-info-value"><?php echo esc_html($ct_email); ?></p>
                            <?php endif; ?>
                            <p class="contact-info-desc">نرد على البريد الإلكتروني خلال 24 ساعة</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($ct_address !== '') : ?>
                    <div class="contact-info-card">
                        <div class="contact-info-icon contact-info-icon-orange">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 2C6.13 2 3 5.13 3 9c0 5.25 7 11 7 11s7-5.75 7-11c0-3.87-3.13-7-7-7z"/>
                                <circle cx="10" cy="9" r="2.5"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">العنوان</h3>
                            <?php if ($ct_map_url !== '') : ?>
                                <a class="contact-info-value" href="<?php echo esc_url($ct_map_url); ?>" target="_blank" rel="noopener"><?php echo nl2br(esc_html($ct_address)); ?></a>
                            <?php else : ?>
                                <p class="contact-info-value"><?php echo nl2br(esc_html($ct_address)); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Map Section
         ============================================ -->
    <section class="contact-map">
        <div class="contact-container">
            <div class="contact-map-header">
                <span class="contact-section-badge">موقعنا</span>
                <h2 class="contact-section-title">تجدنا هنا</h2>
            </div>
            <div class="contact-map-wrap">
                <?php if ($ct_map_embed !== '') : ?>
                    <iframe class="contact-map-iframe" src="<?php echo esc_url($ct_map_embed); ?>" title="<?php echo esc_attr($ct_site_name); ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                <?php elseif ($ct_map_url !== '') : ?>
                    <div class="contact-map-empty">
                        <span>تم إضافة رابط الخريطة من Site Identity / Settings > General.</span>
                        <a class="contact-map-link" href="<?php echo esc_url($ct_map_url); ?>" target="_blank" rel="noopener">فتح الموقع على خرائط Google</a>
                    </div>
                <?php else : ?>
                    <div class="contact-map-empty">أضف رابط الخريطة أو رابط التضمين من Site Identity / Settings > General.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         Social Media Section
         ============================================ -->
    <?php if (!empty($ct_social_links)) : ?>
    <section class="contact-social">
        <div class="contact-container">
            <div class="contact-social-header">
                <span class="contact-section-badge">تابعنا</span>
                <h2 class="contact-section-title"><?php echo esc_html($ct_site_name); ?> على وسائل التواصل</h2>
                <p class="contact-social-desc">روابط التواصل هنا تظهر فقط من البيانات المسجلة في Site Identity / Settings > General.</p>
            </div>
            <div class="contact-social-grid">
                <?php foreach ($ct_social_links as $social) : ?>
                    <?php
                    $platform = sanitize_key((string) ($social['platform'] ?? ''));
                    $url = trim((string) ($social['url'] ?? ''));
                    if ($platform === '' || $url === '') {
                        continue;
                    }
                    $label = $ct_social_labels[$platform] ?? ucfirst($platform);
                    ?>
                    <a href="<?php echo esc_url($url); ?>" class="contact-social-btn" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($label); ?>">
                        <?php echo function_exists('computech_footer_social_svg') ? computech_footer_social_svg($platform) : '<span aria-hidden="true">•</span>'; ?>
                        <span><?php echo esc_html($label); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         Quick Support Strip
         ============================================ -->
    <section class="contact-support">
        <div class="contact-container">
            <div class="contact-support-strip">
                <div class="contact-support-item">
                    <div class="contact-support-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div class="contact-support-text">
                        <span class="contact-support-title">استشارة قبل الشراء</span>
                        <span class="contact-support-desc">نساعدك في اختيار الأنسب</span>
                    </div>
                </div>
                <div class="contact-support-divider"></div>
                <div class="contact-support-item">
                    <div class="contact-support-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                    </div>
                    <div class="contact-support-text">
                        <span class="contact-support-title">دعم فني متخصص</span>
                        <span class="contact-support-desc">فريق متاح لحل مشكلاتك</span>
                    </div>
                </div>
                <div class="contact-support-divider"></div>
                <div class="contact-support-item">
                    <div class="contact-support-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13" rx="2"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <div class="contact-support-text">
                        <span class="contact-support-title">توصيل سريع</span>
                        <span class="contact-support-desc">لجميع مدن المملكة</span>
                    </div>
                </div>
                <div class="contact-support-divider"></div>
                <div class="contact-support-item">
                    <div class="contact-support-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <div class="contact-support-text">
                        <span class="contact-support-title">خدمة ما بعد البيع</span>
                        <span class="contact-support-desc">نضمن رضاك بعد الشراء</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php get_footer(); ?>
