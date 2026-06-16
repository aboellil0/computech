/* ============================================
   Computech RTL Website - Main JavaScript
   ============================================ */

(function () {
    'use strict';


    function computechAsset(path) {
        var base = (window.computechTheme && window.computechTheme.assetsUrl) ? window.computechTheme.assetsUrl : 'assets/images/';
        return base + path;
    }

    function computechPageUrl(key) {
        var fallbackMap = {
            products: '/products/',
            categories: '/categories/',
            services: '/services/',
            offers: '/offers/',
            contact: '/contact/'
        };
        if (!window.computechTheme) { return fallbackMap[key] || '/'; }
        return window.computechTheme[key + 'Url'] || fallbackMap[key] || '/';
    }

    /* Mobile Menu Toggle */
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');

    function openMobileMenu() {
        mobileMenu.classList.add('active');
        mobileMenuBtn.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        mobileMenu.classList.remove('active');
        mobileMenuBtn.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function () {
            if (mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
    }

    /* Header scroll effect */
    const header = document.querySelector('.main-header');
    let lastScroll = 0;

    function handleScroll() {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    }

    window.addEventListener('scroll', handleScroll, { passive: true });

    /* Search box focus animation */
    const searchInput = document.querySelector('.search-input');
    const searchBox = document.querySelector('.search-box');

    if (searchInput && searchBox) {
        searchInput.addEventListener('focus', function () {
            searchBox.style.borderColor = 'var(--color-primary)';
            searchBox.style.boxShadow = '0 0 0 3px rgba(37, 99, 235, 0.1)';
        });

        searchInput.addEventListener('blur', function () {
            searchBox.style.borderColor = '';
            searchBox.style.boxShadow = '';
        });
    }

    /* Nav link active state */
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
            navLinks.forEach(function (l) {
                l.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    /* Mobile nav link active state */
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-list a');

    mobileNavLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
            mobileNavLinks.forEach(function (l) {
                l.classList.remove('active');
            });
            this.classList.add('active');
            closeMobileMenu();
        });
    });

    /* Card hover sound-like subtle effect via CSS class */
    const quickCards = document.querySelectorAll('.quick-card');

    quickCards.forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            this.style.borderColor = 'rgba(37, 99, 235, 0.15)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.borderColor = '';
        });
    });

    /* Intersection Observer for scroll animations */
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        quickCards.forEach(function (card) {
            observer.observe(card);
        });

        /* Needs cards staggered animation */
        const needCards = document.querySelectorAll('.need-card');
        const needsObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    needsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        needCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
            needsObserver.observe(card);
        });

        /* Shop mini cards staggered animation */
        const shopMiniCards = document.querySelectorAll('.shop-mini-card');
        const shopMiniObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    shopMiniObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2, rootMargin: '0px 0px -30px 0px' });

        shopMiniCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.45s ease ' + (index * 0.08) + 's, transform 0.45s ease ' + (index * 0.08) + 's';
            shopMiniObserver.observe(card);
        });

        /* Shop main cards staggered animation */
        const shopCards = document.querySelectorAll('.shop-card');
        const shopObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    shopObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -35px 0px' });

        shopCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.5s ease ' + (index * 0.07) + 's, transform 0.5s ease ' + (index * 0.07) + 's';
            shopObserver.observe(card);
        });

        /* Featured cards staggered animation */
        const featCards = document.querySelectorAll('.feat-card');
        const featObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    featObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -35px 0px' });

        featCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
            featObserver.observe(card);
        });

        /* Offers section staggered animation */
        const offerMainBanner = document.querySelector('.offer-main-banner');
        const offerSubCards = document.querySelectorAll('.offer-sub-card');
        const offersObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    offersObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -35px 0px' });

        if (offerMainBanner) {
            offerMainBanner.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            offersObserver.observe(offerMainBanner);
        }

        offerSubCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.5s ease ' + (index * 0.12) + 's, transform 0.5s ease ' + (index * 0.12) + 's';
            offersObserver.observe(card);
        });

        /* Contact section staggered animation */
        const contactTopCard = document.querySelector('.contact-top-card');
        const contactCtaStrip = document.querySelector('.contact-cta-strip');
        const contactObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    contactObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -35px 0px' });

        if (contactTopCard) {
            contactTopCard.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            contactObserver.observe(contactTopCard);
        }

        if (contactCtaStrip) {
            contactCtaStrip.style.transition = 'opacity 0.6s ease 0.15s, transform 0.6s ease 0.15s';
            contactObserver.observe(contactCtaStrip);
        }
    }

    /* Smooth scroll for anchor links */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    /* About Page - Scroll Animations */
    var aboutSections = document.querySelectorAll('.about-animate');
    if (aboutSections.length > 0 && 'IntersectionObserver' in window) {
        var aboutObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    aboutObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        aboutSections.forEach(function (el) {
            aboutObserver.observe(el);
        });
    }

    /* About Page - Staggered card animations */
    var aboutCardSets = [
        '.about-stat-card',
        '.about-goal-card',
        '.about-why-card',
        '.about-trust-stat'
    ];

    aboutCardSets.forEach(function (selector) {
        var cards = document.querySelectorAll(selector);
        if (cards.length > 0 && 'IntersectionObserver' in window) {
            var cardObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        cardObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -30px 0px' });

            cards.forEach(function (card, index) {
                card.classList.add('about-animate');
                card.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
                cardObserver.observe(card);
            });
        }
    });

    /* About Page - Section headers animation */
    var aboutHeaders = document.querySelectorAll('.about-section-title, .about-section-badge, .about-hero-title, .about-hero-subtitle, .about-intro-desc, .about-vision-text, .about-trust-desc, .about-cta-title, .about-cta-desc');
    if (aboutHeaders.length > 0 && 'IntersectionObserver' in window) {
        var headerObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('about-animate', 'visible');
                    headerObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2, rootMargin: '0px 0px -20px 0px' });

        aboutHeaders.forEach(function (el) {
            if (!el.classList.contains('about-animate')) {
                el.classList.add('about-animate');
                headerObserver.observe(el);
            }
        });
    }

    /* Services Page - Scroll Animations */
    var svcCards = document.querySelectorAll('.svc-card');
    if (svcCards.length > 0 && 'IntersectionObserver' in window) {
        var svcCardObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    svcCardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        svcCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.5s ease ' + (index * 0.08) + 's, transform 0.5s ease ' + (index * 0.08) + 's';
            svcCardObserver.observe(card);
        });
    }

    /* Services Page - Process Steps Animation */
    var processSteps = document.querySelectorAll('.process-step');
    if (processSteps.length > 0 && 'IntersectionObserver' in window) {
        var processObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    processObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -30px 0px' });

        processSteps.forEach(function (step, index) {
            step.style.transition = 'opacity 0.5s ease ' + (index * 0.12) + 's, transform 0.5s ease ' + (index * 0.12) + 's';
            processObserver.observe(step);
        });
    }

    /* Services Page - Trust Strip Items Animation */
    var trustItems = document.querySelectorAll('.trust-strip-item');
    if (trustItems.length > 0 && 'IntersectionObserver' in window) {
        var trustObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    trustObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -30px 0px' });

        trustItems.forEach(function (item, index) {
            item.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
            trustObserver.observe(item);
        });
    }

    /* Services Page - Section Headers Animation */
    var svcHeaders = document.querySelectorAll('.svc-section-badge, .svc-section-title, .svc-section-subtitle, .services-hero-title, .services-hero-subtitle, .services-featured-desc, .services-cta-title, .services-cta-desc');
    if (svcHeaders.length > 0 && 'IntersectionObserver' in window) {
        var svcHeaderObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('svc-animate', 'visible');
                    svcHeaderObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2, rootMargin: '0px 0px -20px 0px' });

        svcHeaders.forEach(function (el) {
            if (!el.classList.contains('svc-animate')) {
                el.classList.add('svc-animate');
                svcHeaderObserver.observe(el);
            }
        });
    }

    /* Services Page - Featured Section Animation */
    var svcFeatured = document.querySelector('.services-featured-inner');
    if (svcFeatured && 'IntersectionObserver' in window) {
        var svcFeaturedObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('svc-animate', 'visible');
                    svcFeaturedObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -35px 0px' });

        svcFeatured.classList.add('svc-animate');
        svcFeaturedObserver.observe(svcFeatured);
    }

    /* Categories Page - Scroll Animations */
    var catFeatCards = document.querySelectorAll('.cat-feat-card');
    if (catFeatCards.length > 0 && 'IntersectionObserver' in window) {
        var catFeatObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    catFeatObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -35px 0px' });

        catFeatCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
            catFeatObserver.observe(card);
        });
    }

    var catCards = document.querySelectorAll('.cat-card');
    if (catCards.length > 0 && 'IntersectionObserver' in window) {
        var catCardObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    catCardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

        catCards.forEach(function (card, index) {
            card.style.transition = 'opacity 0.45s ease ' + (index * 0.07) + 's, transform 0.45s ease ' + (index * 0.07) + 's';
            catCardObserver.observe(card);
        });
    }

    var catTrustItems = document.querySelectorAll('.cat-trust-item');
    if (catTrustItems.length > 0 && 'IntersectionObserver' in window) {
        var catTrustObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    catTrustObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -30px 0px' });

        catTrustItems.forEach(function (item, index) {
            item.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
            catTrustObserver.observe(item);
        });
    }

    /* Categories Page - Section Headers Animation */
    var catHeaders = document.querySelectorAll('.cat-section-title, .cat-section-subtitle, .cat-hero-title, .cat-hero-subtitle, .cat-help-title, .cat-help-desc');
    if (catHeaders.length > 0 && 'IntersectionObserver' in window) {
        var catHeaderObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('cat-animate', 'visible');
                    catHeaderObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2, rootMargin: '0px 0px -20px 0px' });

        catHeaders.forEach(function (el) {
            if (!el.classList.contains('cat-animate')) {
                el.classList.add('cat-animate');
                catHeaderObserver.observe(el);
            }
        });
    }

    /* Keyboard accessibility for mobile menu */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
            closeMobileMenu();
        }
    });

    /* Products Page - Filtering, Search, and Sort */
    var prodSearchInput = document.getElementById('prodSearchInput');
    var prodCategoryFilter = document.getElementById('prodCategoryFilter');
    var prodStatusFilter = document.getElementById('prodStatusFilter');
    var prodSortFilter = document.getElementById('prodSortFilter');
    var prodGrid = document.getElementById('prodGrid');

    if (prodGrid) {
        var prodCards = Array.from(prodGrid.querySelectorAll('.prod-card'));
        var prodUrlParams = new URLSearchParams(window.location.search);
        if (prodSearchInput && prodUrlParams.get('s')) {
            prodSearchInput.value = prodUrlParams.get('s');
        }
        if (prodCategoryFilter && prodUrlParams.get('category')) {
            prodCategoryFilter.value = prodUrlParams.get('category');
        }
        if (prodStatusFilter && prodUrlParams.get('status')) {
            prodStatusFilter.value = prodUrlParams.get('status');
        }

        function filterProducts() {
            var searchTerm = prodSearchInput ? prodSearchInput.value.trim().toLowerCase() : '';
            var category = prodCategoryFilter ? prodCategoryFilter.value : 'all';
            var status = prodStatusFilter ? prodStatusFilter.value : 'all';
            var sort = prodSortFilter ? prodSortFilter.value : 'newest';

            var filtered = prodCards.filter(function (card) {
                var name = (card.getAttribute('data-name') || '').toLowerCase();
                var cardCategory = card.getAttribute('data-category') || '';
                var cardStatus = card.getAttribute('data-status') || '';

                var matchSearch = !searchTerm || name.indexOf(searchTerm) !== -1;
                var matchCategory = category === 'all' || cardCategory.split(/\s+/).indexOf(category) !== -1;
                var matchStatus = status === 'all' || cardStatus.split(/\s+/).indexOf(status) !== -1;

                return matchSearch && matchCategory && matchStatus;
            });

            if (sort === 'price-asc') {
                filtered.sort(function (a, b) {
                    return parseInt(a.getAttribute('data-price') || '0') - parseInt(b.getAttribute('data-price') || '0');
                });
            } else if (sort === 'price-desc') {
                filtered.sort(function (a, b) {
                    return parseInt(b.getAttribute('data-price') || '0') - parseInt(a.getAttribute('data-price') || '0');
                });
            }

            prodCards.forEach(function (card) {
                card.style.display = 'none';
            });

            filtered.forEach(function (card, index) {
                card.style.display = '';
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(function () {
                    card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 60);
            });
        }

        if (prodSearchInput) {
            prodSearchInput.addEventListener('input', filterProducts);
        }
        if (prodCategoryFilter) {
            prodCategoryFilter.addEventListener('change', filterProducts);
        }
        if (prodStatusFilter) {
            prodStatusFilter.addEventListener('change', filterProducts);
        }
        if (prodSortFilter) {
            prodSortFilter.addEventListener('change', filterProducts);
        }
        filterProducts();
    }

    /* Products Page - Scroll Animations */
    var prodCardEls = document.querySelectorAll('.prod-card');
    if (prodCardEls.length > 0 && 'IntersectionObserver' in window) {
        var prodCardObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    prodCardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

        prodCardEls.forEach(function (card, index) {
            card.style.transition = 'opacity 0.45s ease ' + (index * 0.06) + 's, transform 0.45s ease ' + (index * 0.06) + 's';
            prodCardObserver.observe(card);
        });
    }

    /* Products Page - Trust Items Animation */
    var prodTrustItems = document.querySelectorAll('.prod-trust-item');
    if (prodTrustItems.length > 0 && 'IntersectionObserver' in window) {
        var prodTrustObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    prodTrustObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -30px 0px' });

        prodTrustItems.forEach(function (item, index) {
            item.style.transition = 'opacity 0.5s ease ' + (index * 0.1) + 's, transform 0.5s ease ' + (index * 0.1) + 's';
            prodTrustObserver.observe(item);
        });
    }

    /* Products Page - Pagination (visual only) */
    var prodPageNums = document.querySelectorAll('.prod-page-num');
    prodPageNums.forEach(function (btn) {
        btn.addEventListener('click', function () {
            prodPageNums.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    /* ===== Product Details Page ===== */
    var pdApp = document.getElementById('pd-app');
    if (pdApp && pdApp.children.length === 0) {
        pdApp.innerHTML = '<div class="pd-error"><h2 class="pd-error-title">صفحة تفاصيل المنتجات تعتمد الآن على رابط المنتج الحقيقي</h2><p class="pd-error-desc">افتح المنتج من صفحة المنتجات أو من كارت المنتج حتى يتم عرض بياناته من قاعدة بيانات ووردبريس.</p><a href="' + computechPageUrl('products') + '" class="pd-error-btn">العودة إلى المنتجات</a></div>';
    }

    var pdThumbs = document.querySelectorAll('.pd-thumb');
    var pdMainImg = document.getElementById('pdMainImg');
    pdThumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            pdThumbs.forEach(function (item) { item.classList.remove('active'); });
            thumb.classList.add('active');
        });
    });
    if (pdMainImg) {
        pdMainImg.style.transition = 'opacity 0.25s ease';
    }

    /* ============================================
       Contact Form Handling
       ============================================ */
    var contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var successMsg = document.getElementById('contactFormSuccess');
            contactForm.style.display = 'none';
            successMsg.classList.add('visible');
        });
    }


    /* Dynamic top bar slider - handles any number of admin items */
    const topbarSlider = document.querySelector('[data-topbar-slider]');
    if (topbarSlider) {
        const track = topbarSlider.querySelector('.benefits-track');
        const slides = track ? Array.prototype.slice.call(track.querySelectorAll('.topbar-slide')) : [];
        let topbarIndex = 0;
        let topbarTimer = null;

        function topbarVisibleCount() {
            if (window.matchMedia('(max-width: 640px)').matches) { return 1; }
            if (window.matchMedia('(max-width: 1024px)').matches) { return 2; }
            return 3;
        }

        function updateTopbarSlider() {
            if (!track || slides.length === 0) { return; }
            const visible = Math.min(topbarVisibleCount(), slides.length);
            const maxIndex = Math.max(0, slides.length - visible);
            if (topbarIndex > maxIndex) { topbarIndex = 0; }
            const slideWidth = slides[0].getBoundingClientRect().width || 0;
            track.style.transform = 'translateX(' + (-topbarIndex * slideWidth) + 'px)';
        }

        function startTopbarSlider() {
            if (!track || slides.length <= topbarVisibleCount()) {
                updateTopbarSlider();
                return;
            }
            if (topbarTimer) { window.clearInterval(topbarTimer); }
            topbarTimer = window.setInterval(function () {
                const visible = Math.min(topbarVisibleCount(), slides.length);
                const maxIndex = Math.max(0, slides.length - visible);
                topbarIndex = topbarIndex >= maxIndex ? 0 : topbarIndex + 1;
                updateTopbarSlider();
            }, 3200);
        }

        window.addEventListener('resize', function () {
            updateTopbarSlider();
            startTopbarSlider();
        }, { passive: true });

        topbarSlider.addEventListener('mouseenter', function () {
            if (topbarTimer) { window.clearInterval(topbarTimer); }
        });

        topbarSlider.addEventListener('mouseleave', startTopbarSlider);
        updateTopbarSlider();
        startTopbarSlider();
    }


    /* Dynamic hero slider from WordPress Dashboard */
    const heroSlider = document.querySelector('[data-hero-slider]');
    if (heroSlider) {
        const slides = Array.prototype.slice.call(heroSlider.querySelectorAll('[data-hero-slide]'));
        const dots = Array.prototype.slice.call(heroSlider.querySelectorAll('[data-hero-dot]'));
        const prev = heroSlider.querySelector('[data-hero-prev]');
        const next = heroSlider.querySelector('[data-hero-next]');
        let heroIndex = 0;
        let heroTimer = null;

        function setHeroSlide(index) {
            if (!slides.length) { return; }
            heroIndex = (index + slides.length) % slides.length;
            slides.forEach(function (slide, i) {
                slide.classList.toggle('is-active', i === heroIndex);
            });
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === heroIndex);
            });
        }

        function startHeroAuto() {
            if (slides.length <= 1) { return; }
            if (heroTimer) { window.clearInterval(heroTimer); }
            heroTimer = window.setInterval(function () {
                setHeroSlide(heroIndex + 1);
            }, 5200);
        }

        if (prev) {
            prev.addEventListener('click', function () {
                setHeroSlide(heroIndex - 1);
                startHeroAuto();
            });
        }
        if (next) {
            next.addEventListener('click', function () {
                setHeroSlide(heroIndex + 1);
                startHeroAuto();
            });
        }
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                const index = parseInt(dot.getAttribute('data-hero-dot') || '0', 10);
                setHeroSlide(index);
                startHeroAuto();
            });
        });
        heroSlider.addEventListener('mouseenter', function () {
            if (heroTimer) { window.clearInterval(heroTimer); }
        });
        heroSlider.addEventListener('mouseleave', startHeroAuto);
        setHeroSlide(0);
        startHeroAuto();
    }

    /* Sticky breadcrumb offset - match actual header height */
    function updateBreadcrumbTop() {
        var header = document.querySelector('.main-header');
        var breadcrumbs = document.querySelectorAll('.site-breadcrumb, .about-breadcrumb, .services-breadcrumb, .cat-breadcrumb, .prod-breadcrumb, .pd-breadcrumb, .contact-breadcrumb');
        if (!header || !breadcrumbs.length) return;
        var h = header.offsetHeight;
        var adminBar = document.getElementById('wpadminbar');
        if (adminBar) { h += adminBar.offsetHeight; }
        breadcrumbs.forEach(function (el) { el.style.top = h + 'px'; });
    }
    updateBreadcrumbTop();
    window.addEventListener('resize', updateBreadcrumbTop);


})();
