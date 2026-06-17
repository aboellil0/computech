<?php
/**
 * Template Name: صفحة تواصل معنا - كمبيوتيك
 */
get_header();
$ct_site_name = function_exists('computech_site_name') ? computech_site_name() : get_bloginfo('name');
$ct_phone = function_exists('computech_business_phone') ? computech_business_phone() : '';
$ct_whatsapp_number = function_exists('computech_business_whatsapp_number') ? computech_business_whatsapp_number() : '';
$ct_whatsapp_display = $ct_whatsapp_number !== '' ? '+' . $ct_whatsapp_number : '';
$ct_email = function_exists('computech_business_email') ? computech_business_email() : get_option('admin_email', '');
$ct_address = function_exists('computech_business_address') ? computech_business_address() : '';
$ct_hours = function_exists('computech_business_hours') ? computech_business_hours() : '';
$ct_map_embed = function_exists('computech_business_map_embed_url') ? computech_business_map_embed_url() : '';
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
                <p class="contact-hero-subtitle">فريق <?php echo esc_html($ct_site_name); ?> متاح للإجابة على استفساراتك وتقديم الدعم الفني والاستشارات. تواصل معنا بالطريقة التي تناسبك.</p>
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
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">الهاتف</h3>
                            <p class="contact-info-value"><?php echo esc_html($ct_phone); ?></p>
                            <p class="contact-info-desc"><?php echo esc_html($ct_hours); ?></p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon contact-info-icon-green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">واتساب</h3>
                            <p class="contact-info-value"><?php echo esc_html($ct_whatsapp_display); ?></p>
                            <p class="contact-info-desc">نخدمك عبر واتساب على مدار الساعة</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon contact-info-icon-cyan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <polyline points="22,4 12,13 2,4"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">البريد الإلكتروني</h3>
                            <p class="contact-info-value"><?php echo esc_html($ct_email); ?></p>
                            <p class="contact-info-desc">نرد على البريد الإلكتروني خلال 24 ساعة</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon contact-info-icon-orange">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 2C6.13 2 3 5.13 3 9c0 5.25 7 11 7 11s7-5.75 7-11c0-3.87-3.13-7-7-7z"/>
                                <circle cx="10" cy="9" r="2.5"/>
                            </svg>
                        </div>
                        <div class="contact-info-details">
                            <h3 class="contact-info-title">العنوان</h3>
                            <p class="contact-info-value"><?php echo nl2br(esc_html($ct_address)); ?></p>
                            
                        </div>
                    </div>
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
                <?php if ($ct_map_embed !== '') : ?><iframe class="contact-map-iframe" src="<?php echo esc_url($ct_map_embed); ?>" title="<?php echo esc_attr($ct_site_name); ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><?php else : ?><div class="contact-map-empty">أضف رابط تضمين الخريطة من Settings → General.</div><?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         Social Media Section
         ============================================ -->
    <section class="contact-social">
        <div class="contact-container">
            <div class="contact-social-header">
                <span class="contact-section-badge">تابعنا</span>
                <h2 class="contact-section-title"><?php echo esc_html($ct_site_name); ?> على وسائل التواصل</h2>
                <p class="contact-social-desc">تابعنا على منصات التواصل الاجتماعي لتصلك أحدث المنتجات والعروض</p>
            </div>
            <div class="contact-social-grid">
                <a href="<?php echo esc_url(computech_social_url('facebook')); ?>" class="contact-social-btn" target="_blank" rel="noopener" aria-label="فيسبوك">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span>فيسبوك</span>
                </a>
                <a href="<?php echo esc_url(computech_social_url('instagram')); ?>" class="contact-social-btn" target="_blank" rel="noopener" aria-label="انستقرام">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                    </svg>
                    <span>انستقرام</span>
                </a>
                <a href="<?php echo esc_url(computech_social_url('youtube')); ?>" class="contact-social-btn" target="_blank" rel="noopener" aria-label="يوتيوب">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                    <span>يوتيوب</span>
                </a>
                <a href="<?php echo esc_url(computech_social_url('tiktok')); ?>" class="contact-social-btn" target="_blank" rel="noopener" aria-label="تيك توك">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                    </svg>
                    <span>تيك توك</span>
                </a>
                <a href="<?php echo esc_url(computech_social_url('linkedin')); ?>" class="contact-social-btn" target="_blank" rel="noopener" aria-label="لينكد إن">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    <span>لينكد إن</span>
                </a>
                <a href="<?php echo esc_url(computech_social_url('twitter')); ?>" class="contact-social-btn" target="_blank" rel="noopener" aria-label="إكس تويتر">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    <span>إكس</span>
                </a>
            </div>
        </div>
    </section>

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
