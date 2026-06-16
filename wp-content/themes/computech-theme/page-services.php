<?php
/**
 * Template Name: صفحة الخدمات - كمبيوتيك
 */
get_header();
?>
<?php computech_breadcrumbs('الخدمات'); ?>
    <!-- ============================================
         Services Hero Section
         ============================================ -->
    <section class="services-hero">
        <div class="services-hero-bg">
            <div class="svc-circuit svc-circuit-1"></div>
            <div class="svc-circuit svc-circuit-2"></div>
            <div class="svc-circuit svc-circuit-3"></div>
            <div class="svc-dot svc-dot-1"></div>
            <div class="svc-dot svc-dot-2"></div>
            <div class="svc-dot svc-dot-3"></div>
            <div class="svc-dot svc-dot-4"></div>
            <div class="svc-glow svc-glow-1"></div>
            <div class="svc-glow svc-glow-2"></div>
        </div>
        <div class="services-container services-hero-inner">
            <div class="services-hero-content">
                <span class="svc-section-badge">ماذا نقدم لك</span>
                <h1 class="services-hero-title">خدمات كمبيوتيك</h1>
                <p class="services-hero-subtitle">كل ما تحتاجه من أجهزة كمبيوتر، إكسسوارات، صيانة، دعم فني واستشارة قبل الشراء في مكان واحد.</p>
                <div class="services-hero-pills">
                    <span class="svc-pill">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        أجهزة أصلية
                    </span>
                    <span class="svc-pill">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                        دعم متخصص
                    </span>
                    <span class="svc-pill">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                        خدمة بعد البيع
                    </span>
                </div>
                <div class="services-hero-cta">
                    <a href="<?php echo esc_url(computech_page_url('products')); ?>" class="btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        تصفح المنتجات
                    </a>
                    <a href="<?php echo esc_url(computech_whatsapp_url('السلام عليكم، أريد الاستفسار عن خدمات كمبيوتيك')); ?>" class="btn-whatsapp" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        اطلب استشارة
                    </a>
                </div>
            </div>
            <div class="services-hero-image">
                <div class="services-hero-image-glow"></div>
                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/hero-computer-setup.png" alt="خدمات كمبيوتيك" class="services-hero-img">
            </div>
        </div>
    </section>

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
                <!-- Service 1: New Computers -->
                <div class="svc-card">
                    <div class="svc-card-icon svc-card-icon-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <h3 class="svc-card-title">بيع أجهزة كمبيوتر جديدة</h3>
                    <p class="svc-card-desc">نوفر أحدث أجهزة الكمبيوتر المكتبية والمناسبة للألعاب، العمل، الدراسة، والاستخدام اليومي بمواصفات متنوعة.</p>
                    <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="svc-card-link">اعرف المزيد
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

                <!-- Service 2: Imported Devices -->
                <div class="svc-card">
                    <div class="svc-card-icon svc-card-icon-cyan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                    </div>
                    <h3 class="svc-card-title">بيع أجهزة استيراد خارج</h3>
                    <p class="svc-card-desc">أجهزة مستوردة بحالة ممتازة يتم فحصها بعناية لتقديم أفضل قيمة مقابل السعر.</p>
                    <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="svc-card-link">اعرف المزيد
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

                <!-- Service 3: Accessories -->
                <div class="svc-card">
                    <div class="svc-card-icon svc-card-icon-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                    </div>
                    <h3 class="svc-card-title">بيع إكسسوارات جديدة</h3>
                    <p class="svc-card-desc">ملحقات أصلية ومتنوعة مثل الكيبورد، الماوس، السماعات، الشاشات، والكابلات.</p>
                    <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="svc-card-link">اعرف المزيد
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

                <!-- Service 4: Delivery -->
                <div class="svc-card">
                    <div class="svc-card-icon svc-card-icon-orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <h3 class="svc-card-title">التوصيل لمكان العميل</h3>
                    <p class="svc-card-desc">توصيل سريع وآمن حتى باب العميل مع الحفاظ على سلامة الأجهزة والمنتجات.</p>
                    <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="svc-card-link">اعرف المزيد
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

                <!-- Service 5: Maintenance & Support -->
                <div class="svc-card">
                    <div class="svc-card-icon svc-card-icon-purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <h3 class="svc-card-title">الصيانة والدعم الفني</h3>
                    <p class="svc-card-desc">صيانة احترافية وتشخيص أعطال وترقيات ودعم فني للأجهزة والأنظمة.</p>
                    <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="svc-card-link">اعرف المزيد
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

                <!-- Service 6: After-Sales Service -->
                <div class="svc-card">
                    <div class="svc-card-icon svc-card-icon-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <h3 class="svc-card-title">خدمة ما بعد البيع</h3>
                    <p class="svc-card-desc">متابعة ودعم بعد الشراء لضمان تجربة استخدام مريحة وموثوقة.</p>
                    <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="svc-card-link">اعرف المزيد
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

                <!-- Service 7: Pre-Purchase Consultation -->
                <div class="svc-card svc-card-featured">
                    <div class="svc-card-icon svc-card-icon-gold">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h3 class="svc-card-title">الاستشارة قبل الشراء</h3>
                    <p class="svc-card-desc">نساعدك في اختيار الجهاز أو الإكسسوار الأنسب لاستخدامك وميزانيتك.</p>
                    <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="svc-card-link">اعرف المزيد
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Featured Service Highlight
         ============================================ -->
    <section class="services-featured">
        <div class="services-container">
            <div class="services-featured-inner">
                <div class="services-featured-text">
                    <span class="svc-section-badge">خدمة مميزة</span>
                    <h2 class="svc-section-title">استشارة قبل الشراء لاختيار الجهاز الأنسب</h2>
                    <p class="services-featured-desc">فريق كمبيوتيك يساعدك في تحديد أفضل جهاز حسب استخدامك، سواء للألعاب، العمل، الدراسة، التصميم، أو الاستخدام اليومي.</p>
                    <div class="services-featured-features">
                        <div class="svc-feature-item">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>تحليل احتياجاتك بدقة</span>
                        </div>
                        <div class="svc-feature-item">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>مقارنة بين الأجهزة المتاحة</span>
                        </div>
                        <div class="svc-feature-item">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>نصائح حول الميزانية المناسبة</span>
                        </div>
                    </div>
                    <div class="services-featured-cta">
                        <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            تواصل مع خبير
                        </a>
                        <a href="<?php echo esc_url(computech_whatsapp_url('السلام عليكم، أريد الاستفسار عن خدمات كمبيوتيك')); ?>" class="btn-whatsapp" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            واتساب الآن
                        </a>
                    </div>
                </div>
                <div class="services-featured-image">
                    <div class="services-featured-image-glow"></div>
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/offers-device-consultation.png" alt="استشارة قبل الشراء - كمبيوتيك" class="services-featured-img">
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Work Process Section
         ============================================ -->
    <section class="services-process">
        <div class="services-container">
            <div class="svc-section-header">
                <span class="svc-section-badge">خطوات بسيطة</span>
                <h2 class="svc-section-title">كيف نخدمك؟</h2>
                <p class="svc-section-subtitle">أربع خطوات بسيطة للحصول على خدمتك</p>
            </div>
            <div class="process-steps">
                <div class="process-step">
                    <div class="process-step-connector"></div>
                    <div class="process-step-number">1</div>
                    <div class="process-step-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <h3 class="process-step-title">حدد احتياجك</h3>
                    <p class="process-step-desc">أخبرنا بما تحتاجه من أجهزة أو إكسسوارات أو خدمات</p>
                </div>
                <div class="process-step">
                    <div class="process-step-connector"></div>
                    <div class="process-step-number">2</div>
                    <div class="process-step-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <h3 class="process-step-title">نقترح الأنسب</h3>
                    <p class="process-step-desc">نقدم لك أفضل الخيارات المناسبة لميزانيتك واستخدامك</p>
                </div>
                <div class="process-step">
                    <div class="process-step-connector"></div>
                    <div class="process-step-number">3</div>
                    <div class="process-step-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                    <h3 class="process-step-title">نجهز ونفحص</h3>
                    <p class="process-step-desc">نجهز جهازك ونفحصه بدقة قبل التسليم لضمان جودته</p>
                </div>
                <div class="process-step">
                    <div class="process-step-number">4</div>
                    <div class="process-step-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                    </div>
                    <h3 class="process-step-title">نوصّل وندعمك</h3>
                    <p class="process-step-desc">نوصّل جهازك ونقدم لك الدعم الفني المستمر</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Trust Strip Section
         ============================================ -->
    <section class="services-trust-strip">
        <div class="services-container">
            <div class="trust-strip">
                <div class="trust-strip-item">
                    <div class="trust-strip-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                    <div class="trust-strip-text">
                        <span class="trust-strip-title">فحص قبل البيع</span>
                        <span class="trust-strip-desc">نفحص كل جهاز بدقة</span>
                    </div>
                </div>
                <div class="trust-strip-sep"></div>
                <div class="trust-strip-item">
                    <div class="trust-strip-icon trust-strip-icon-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <div class="trust-strip-text">
                        <span class="trust-strip-title">ضمان حسب المنتج</span>
                        <span class="trust-strip-desc">حماية لاستثمارك</span>
                    </div>
                </div>
                <div class="trust-strip-sep"></div>
                <div class="trust-strip-item">
                    <div class="trust-strip-icon trust-strip-icon-cyan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <div class="trust-strip-text">
                        <span class="trust-strip-title">توصيل سريع</span>
                        <span class="trust-strip-desc">لجميع مناطق المملكة</span>
                    </div>
                </div>
                <div class="trust-strip-sep"></div>
                <div class="trust-strip-item">
                    <div class="trust-strip-icon trust-strip-icon-orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                    </div>
                    <div class="trust-strip-text">
                        <span class="trust-strip-title">دعم فني حقيقي</span>
                        <span class="trust-strip-desc">قبل وبعد البيع</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Final CTA Section
         ============================================ -->
    <section class="services-cta">
        <div class="services-cta-bg">
            <div class="services-cta-circuit services-cta-circuit-1"></div>
            <div class="services-cta-circuit services-cta-circuit-2"></div>
            <div class="services-cta-glow"></div>
        </div>
        <div class="services-container services-cta-inner">
            <h2 class="services-cta-title">محتاج خدمة أو استشارة؟</h2>
            <p class="services-cta-desc">تواصل معنا الآن وسنساعدك في اختيار الحل الأنسب لك من أجهزة، إكسسوارات، صيانة أو دعم فني.</p>
            <div class="services-cta-buttons">
                <a href="<?php echo esc_url(computech_page_url('contact')); ?>" class="btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    تواصل معنا
                </a>
                <a href="<?php echo esc_url(computech_whatsapp_url('السلام عليكم، أريد الاستفسار عن خدمات كمبيوتيك')); ?>" class="btn-whatsapp" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    واتساب
                </a>
            </div>
        </div>
    </section>
<?php get_footer(); ?>
