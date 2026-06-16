<?php
/**
 * Template Name: صفحة من نحن - كمبيوتيك
 */
get_header();
?>
<?php computech_breadcrumbs('من نحن'); ?>
    <!-- ============================================
         About Hero Section
         ============================================ -->
    <section class="about-hero">
        <div class="about-hero-bg">
            <div class="about-circuit about-circuit-1"></div>
            <div class="about-circuit about-circuit-2"></div>
            <div class="about-circuit about-circuit-3"></div>
            <div class="about-dot about-dot-1"></div>
            <div class="about-dot about-dot-2"></div>
            <div class="about-dot about-dot-3"></div>
            <div class="about-dot about-dot-4"></div>
            <div class="about-glow about-glow-1"></div>
            <div class="about-glow about-glow-2"></div>
        </div>
        <div class="about-container about-hero-inner">
            <div class="about-hero-content">
                <h1 class="about-hero-title">من نحن</h1>
                <p class="about-hero-subtitle">كمبيوتيك وجهتك الموثوقة لأجهزة الكمبيوتر، الاستيراد الخارجي، الإكسسوارات، الصيانة والدعم الفني.</p>
                <div class="about-hero-stats">
                    <div class="about-stat-card">
                        <div class="about-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <span class="about-stat-number">+10</span>
                        <span class="about-stat-label">سنوات خبرة</span>
                    </div>
                    <div class="about-stat-card">
                        <div class="about-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <span class="about-stat-number">+5000</span>
                        <span class="about-stat-label">عميل</span>
                    </div>
                    <div class="about-stat-card">
                        <div class="about-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                            </svg>
                        </div>
                        <span class="about-stat-number">دعم فني</span>
                        <span class="about-stat-label">موثوق</span>
                    </div>
                </div>
            </div>
            <div class="about-hero-image">
                <div class="about-hero-image-glow"></div>
                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/hero-computer-setup.png" alt="كمبيوتيك - حلول الكمبيوتر والإلكترونيات" class="about-hero-img">
            </div>
        </div>
    </section>

    <!-- ============================================
         Company Intro Section
         ============================================ -->
    <section class="about-intro">
        <div class="about-container">
            <div class="about-intro-inner">
                <div class="about-intro-text">
                    <span class="about-section-badge">تعرف علينا</span>
                    <h2 class="about-section-title">نبذة عن كمبيوتيك</h2>
                    <p class="about-intro-desc">كمبيوتيك شركة متخصصة في بيع أجهزة الكمبيوتر الجديدة، أجهزة الاستيراد الخارجي، اللابتوبات، الإكسسوارات، ومكونات الكمبيوتر، مع توفير خدمات الصيانة والدعم الفني والاستشارة قبل الشراء. نهدف إلى تقديم حلول تقنية موثوقة تناسب احتياجات العملاء المختلفة بجودة عالية وتجربة شراء مريحة.</p>
                    <div class="about-intro-chips">
                        <span class="about-chip">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="16" height="12" rx="2"/>
                                <path d="M6 8h8"/>
                            </svg>
                            أجهزة جديدة
                        </span>
                        <span class="about-chip">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="10" cy="10" r="8"/>
                                <path d="M2 10h16"/>
                                <path d="M10 2c2.5 2.5 4 5.5 4 8s-1.5 5.5-4 8"/>
                                <path d="M10 2c-2.5 2.5-4 5.5-4 8s1.5 5.5 4 8"/>
                            </svg>
                            استيراد خارج
                        </span>
                        <span class="about-chip">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 3h6v4H9z"/>
                                <rect x="3" y="7" width="14" height="10" rx="1"/>
                                <path d="M7 17v2"/>
                                <path d="M13 17v2"/>
                            </svg>
                            إكسسوارات
                        </span>
                        <span class="about-chip">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                            صيانة ودعم
                        </span>
                    </div>
                </div>
                <div class="about-intro-image">
                    <div class="about-intro-image-card">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-maintenance-support.png" alt="خدمات كمبيوتيك" class="about-intro-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Vision Section
         ============================================ -->
    <section class="about-vision">
        <div class="about-container">
            <div class="about-vision-card">
                <div class="about-vision-bg"></div>
                <div class="about-vision-content">
                    <div class="about-vision-icon">
                        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="24" cy="24" r="20"/>
                            <circle cx="24" cy="24" r="12"/>
                            <circle cx="24" cy="24" r="4"/>
                            <line x1="24" y1="4" x2="24" y2="8"/>
                            <line x1="24" y1="40" x2="24" y2="44"/>
                            <line x1="4" y1="24" x2="8" y2="24"/>
                            <line x1="40" y1="24" x2="44" y2="24"/>
                        </svg>
                    </div>
                    <span class="about-section-badge about-section-badge-light">اتجاهنا</span>
                    <h2 class="about-vision-title">رؤيتنا</h2>
                    <p class="about-vision-text">أن نكون من الوجهات الرائدة في توفير حلول الكمبيوتر والإلكترونيات، من خلال منتجات موثوقة، أسعار مناسبة، وخدمة احترافية قبل وبعد البيع.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Goals Section
         ============================================ -->
    <section class="about-goals">
        <div class="about-container">
            <div class="about-goals-header">
                <span class="about-section-badge">ما نسعى لتحقيقه</span>
                <h2 class="about-section-title">أهدافنا</h2>
            </div>
            <div class="about-goals-grid">
                <div class="about-goal-card">
                    <div class="about-goal-icon about-goal-icon-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <h3 class="about-goal-title">توفير منتجات أصلية وموثوقة</h3>
                    <p class="about-goal-desc">نحرص على توفير منتجات أصلية من مصادر موثوقة لضمان جودة عالية وعمر افتراضي طويل لأجهزتك.</p>
                </div>
                <div class="about-goal-card">
                    <div class="about-goal-icon about-goal-icon-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <h3 class="about-goal-title">تقديم أسعار مناسبة لكل الميزانيات</h3>
                    <p class="about-goal-desc">نقدم مجموعة متنوعة من المنتجات بأسعار تنافسية تناسب مختلف الميزانيات والاحتياجات.</p>
                </div>
                <div class="about-goal-card">
                    <div class="about-goal-icon about-goal-icon-cyan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <h3 class="about-goal-title">مساعدة العميل في اختيار الجهاز الأنسب</h3>
                    <p class="about-goal-desc">فريقنا المتخصص يساعدك في اختيار الجهاز أو الإكسسوار الأنسب لاحتياجاتك واستخدامك.</p>
                </div>
                <div class="about-goal-card">
                    <div class="about-goal-icon about-goal-icon-orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                    </div>
                    <h3 class="about-goal-title">تقديم دعم فني وخدمة ما بعد البيع</h3>
                    <p class="about-goal-desc">نوفر دعم فني متخصص وخدمة ما بعد البيع لضمان رضاك وحل أي مشكلة تواجهك.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Why Choose Us Section
         ============================================ -->
    <section class="about-why">
        <div class="about-container">
            <div class="about-why-header">
                <span class="about-section-badge">مميزاتنا</span>
                <h2 class="about-section-title">لماذا تختار كمبيوتيك؟</h2>
            </div>
            <div class="about-why-grid">
                <div class="about-why-card">
                    <div class="about-why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <h3 class="about-why-title">خبرة في اختيار الأجهزة</h3>
                    <p class="about-why-desc">أكثر من 10 سنوات من الخبرة في اختيار أفضل الأجهزة والمكونات لتلبية احتياجاتك.</p>
                </div>
                <div class="about-why-card">
                    <div class="about-why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                    <h3 class="about-why-title">فحص قبل البيع</h3>
                    <p class="about-why-desc">نفحص كل جهاز بدقة قبل التسليم لضمان عمله بشكل مثالي وخالٍ من أي عيوب.</p>
                </div>
                <div class="about-why-card">
                    <div class="about-why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <h3 class="about-why-title">ضمان حسب المنتج</h3>
                    <p class="about-why-desc">نوفر ضمانًا على جميع منتجاتنا يختلف حسب نوع المنتج لحماية استثمارك.</p>
                </div>
                <div class="about-why-card">
                    <div class="about-why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13" rx="2"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <h3 class="about-why-title">توصيل سريع</h3>
                    <p class="about-why-desc">خدمة توصيل سريعة وآمنة لجميع مناطق المملكة العربية السعودية.</p>
                </div>
                <div class="about-why-card">
                    <div class="about-why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                    </div>
                    <h3 class="about-why-title">دعم فني متخصص</h3>
                    <p class="about-why-desc">فريق دعم فني متاح لمساعدتك في أي وقت قبل الشراء وبعده.</p>
                </div>
                <div class="about-why-card">
                    <div class="about-why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <h3 class="about-why-title">استشارة قبل الشراء</h3>
                    <p class="about-why-desc">نقدم استشارة مجانية قبل الشراء لمساعدتك في اتخاذ القرار الصحيح.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         Trust & Experience Section
         ============================================ -->
    <section class="about-trust">
        <div class="about-container">
            <div class="about-trust-header">
                <span class="about-section-badge">إنجازاتنا</span>
                <h2 class="about-section-title">ثقة وخبرة في خدمتك</h2>
            </div>
            <div class="about-trust-stats">
                <div class="about-trust-stat">
                    <div class="about-trust-stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="about-trust-stat-number">+5000</span>
                    <span class="about-trust-stat-label">عميل راضٍ</span>
                </div>
                <div class="about-trust-divider"></div>
                <div class="about-trust-stat">
                    <div class="about-trust-stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <span class="about-trust-stat-number">+1200</span>
                    <span class="about-trust-stat-label">جهاز تم فحصه</span>
                </div>
                <div class="about-trust-divider"></div>
                <div class="about-trust-stat">
                    <div class="about-trust-stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                    </div>
                    <span class="about-trust-stat-number">+300</span>
                    <span class="about-trust-stat-label">منتج متاح</span>
                </div>
                <div class="about-trust-divider"></div>
                <div class="about-trust-stat">
                    <div class="about-trust-stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <span class="about-trust-stat-text">دعم قبل وبعد البيع</span>
                </div>
            </div>
            <p class="about-trust-desc">نحرص في كمبيوتيك على بناء علاقة طويلة المدى مع عملائنا من خلال الشفافية، جودة المنتجات، سرعة الاستجابة، والالتزام بتقديم حلول مناسبة لكل احتياج.</p>
        </div>
    </section>
<?php get_footer(); ?>
