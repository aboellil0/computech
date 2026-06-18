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

    /* WooCommerce cart count: instant header badge update after AJAX add-to-cart */
    function updateCartBadges(count) {
        document.querySelectorAll('.cart-badge').forEach(function (badge) {
            badge.textContent = String(count || 0);
        });
    }

    function refreshCartCount() {
        if (!window.computechTheme || !computechTheme.ajaxUrl || !computechTheme.cartNonce) { return; }
        var data = new FormData();
        data.append('action', 'computech_cart_count');
        data.append('nonce', computechTheme.cartNonce);
        fetch(computechTheme.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (response) {
            return response.json();
        }).then(function (payload) {
            if (payload && payload.success && payload.data && typeof payload.data.count !== 'undefined') {
                updateCartBadges(payload.data.count);
            }
        }).catch(function () {});
    }

    if (window.jQuery) {
        window.jQuery(document.body).on('added_to_cart removed_from_cart wc_fragments_loaded wc_fragments_refreshed', function (event, fragments) {
            if (fragments && fragments['span.cart-badge']) {
                var temp = document.createElement('div');
                temp.innerHTML = fragments['span.cart-badge'];
                var badge = temp.querySelector('.cart-badge');
                if (badge) {
                    updateCartBadges(badge.textContent.trim());
                    return;
                }
            }
            refreshCartCount();
        });
    }

    /* Header live product search: 3 WooCommerce products below search bar */
    function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char];
        });
    }

    function setupLiveSearch(form) {
        var input = form.querySelector('.computech-live-search-input');
        var results = form.querySelector('.computech-live-search-results');
        if (!input || !results || !window.computechTheme || !computechTheme.ajaxUrl || !computechTheme.liveSearchNonce) { return; }

        var controller = null;
        var timer = null;

        function hideResults() {
            results.classList.remove('is-visible');
            results.innerHTML = '';
        }

        function renderResults(items) {
            if (!items || !items.length) {
                results.innerHTML = '<div class="computech-live-search-empty">لا توجد منتجات</div>';
                results.classList.add('is-visible');
                return;
            }

            results.innerHTML = items.slice(0, 3).map(function (item) {
                var image = item.image ? '<span class="computech-live-search-img"><img src="' + escapeHtml(item.image) + '" alt=""></span>' : '<span class="computech-live-search-img computech-live-search-placeholder"></span>';
                var price = item.price ? '<span class="computech-live-search-price">' + escapeHtml(item.price) + '</span>' : '';
                return '<a class="computech-live-search-item" href="' + escapeHtml(item.url) + '">' + image + '<span class="computech-live-search-text"><strong>' + escapeHtml(item.title) + '</strong>' + price + '</span></a>';
            }).join('');
            results.classList.add('is-visible');
        }

        function runSearch() {
            var term = input.value.trim();
            if (!term) {
                hideResults();
                return;
            }

            if (controller) {
                controller.abort();
            }
            controller = new AbortController();

            var url = new URL(computechTheme.ajaxUrl);
            url.searchParams.set('action', 'computech_live_product_search');
            url.searchParams.set('nonce', computechTheme.liveSearchNonce);
            url.searchParams.set('term', term);

            results.innerHTML = '<div class="computech-live-search-empty">جاري البحث...</div>';
            results.classList.add('is-visible');

            fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                signal: controller.signal
            }).then(function (response) {
                return response.json();
            }).then(function (payload) {
                if (payload && payload.success && payload.data) {
                    renderResults(payload.data.items || []);
                } else {
                    hideResults();
                }
            }).catch(function (error) {
                if (error.name !== 'AbortError') {
                    hideResults();
                }
            });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(runSearch, 140);
        });

        input.addEventListener('focus', function () {
            if (input.value.trim()) {
                runSearch();
            }
        });

        document.addEventListener('click', function (event) {
            if (!form.contains(event.target)) {
                hideResults();
            }
        });
    }

    document.querySelectorAll('.search-box, .mobile-search').forEach(setupLiveSearch);

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
            var href = this.getAttribute('href');
            var parentItem = this.closest('.menu-item-has-children');
            var hasSubMenu = parentItem && parentItem.querySelector('ul');

            if (href === '#') {
                e.preventDefault();
                if (hasSubMenu) {
                    return;
                }
            }
            mobileNavLinks.forEach(function (l) {
                l.classList.remove('active');
            });
            this.classList.add('active');
            closeMobileMenu();
        });
    });

    /* WordPress dropdown menu support */
    const parentMenuLinks = document.querySelectorAll('.main-header .menu-item-has-children > a');

    function closeSiblingSubmenus(item) {
        if (!item || !item.parentElement) return;
        Array.prototype.forEach.call(item.parentElement.children, function (sibling) {
            if (sibling !== item && sibling.classList && sibling.classList.contains('submenu-open')) {
                sibling.classList.remove('submenu-open');
                sibling.querySelectorAll('.submenu-open').forEach(function (openChild) {
                    openChild.classList.remove('submenu-open');
                });
            }
        });
    }

    parentMenuLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            var href = this.getAttribute('href');
            var item = this.parentElement;
            if (href === '#') {
                e.preventDefault();
                closeSiblingSubmenus(item);
                item.classList.toggle('submenu-open');
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.main-header .menu-item-has-children, .main-header .nav-more')) {
            document.querySelectorAll('.main-header .submenu-open').forEach(function (item) {
                item.classList.remove('submenu-open');
            });
        }
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

    /* Products Page - AJAX Filtering, Search, and Sort */
    var prodFilterForm = document.querySelector('.computech-wc-filters[data-ajax-filter="1"]');
    var prodSearchInput = document.getElementById('prodSearchInput');
    var prodGrid = document.getElementById('prodGrid');
    var prodAjaxTimer = null;
    var prodAjaxController = null;

    if (prodFilterForm && prodGrid && window.fetch && window.URLSearchParams && typeof computechTheme !== 'undefined' && computechTheme.ajaxUrl) {
        var prodPagination = document.querySelector('.prod-grid-section .woocommerce-pagination');

        function setProductsLoading(isLoading) {
            prodFilterForm.classList.toggle('is-loading', isLoading);
            prodGrid.classList.toggle('is-loading', isLoading);
        }

        function getProductsParams() {
            var params = new URLSearchParams();
            var formData = new FormData(prodFilterForm);
            formData.forEach(function (value, key) {
                value = String(value || '').trim();
                if (value === '' || value === 'all') { return; }
                params.set(key, value);
            });
            params.set('action', 'computech_filter_products');
            params.set('nonce', computechTheme.productsFilterNonce || '');
            return params;
        }

        function updateProductsUrl(params) {
            var cleanParams = new URLSearchParams(params.toString());
            cleanParams.delete('action');
            cleanParams.delete('nonce');
            var nextUrl = window.location.pathname + (cleanParams.toString() ? '?' + cleanParams.toString() : '');
            window.history.replaceState({}, '', nextUrl);
        }

        function revealNewProductCards() {
            Array.prototype.slice.call(prodGrid.querySelectorAll('.prod-card')).forEach(function (card, index) {
                card.classList.add('visible');
                card.style.opacity = '0';
                card.style.transform = 'translateY(16px)';
                window.setTimeout(function () {
                    card.style.transition = 'opacity 0.32s ease, transform 0.32s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, Math.min(index * 35, 280));
            });
        }

        function runProductsAjax() {
            var params = getProductsParams();
            if (prodAjaxController) { prodAjaxController.abort(); }
            prodAjaxController = new AbortController();
            setProductsLoading(true);

            fetch(computechTheme.ajaxUrl + '?' + params.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                signal: prodAjaxController.signal
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    if (!payload || !payload.success || !payload.data) { throw new Error('Invalid response'); }
                    prodGrid.innerHTML = payload.data.html || '';
                    if (prodPagination) {
                        prodPagination.outerHTML = payload.data.pagination || '';
                        prodPagination = document.querySelector('.prod-grid-section .woocommerce-pagination');
                    } else if (payload.data.pagination) {
                        prodGrid.insertAdjacentHTML('afterend', payload.data.pagination);
                        prodPagination = document.querySelector('.prod-grid-section .woocommerce-pagination');
                    }
                    revealNewProductCards();
                    updateProductsUrl(params);
                })
                .catch(function (error) {
                    if (error && error.name === 'AbortError') { return; }
                    prodGrid.innerHTML = '<div class="wp-product-empty"><h2>تعذر تحميل النتائج</h2><p>راجع الاتصال أو جرّب مرة أخرى.</p></div>';
                })
                .finally(function () {
                    setProductsLoading(false);
                });
        }

        function scheduleProductsAjax(delay) {
            if (prodAjaxTimer) { window.clearTimeout(prodAjaxTimer); }
            prodAjaxTimer = window.setTimeout(runProductsAjax, delay || 0);
        }

        prodFilterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            scheduleProductsAjax(0);
        });

        prodFilterForm.addEventListener('change', function () {
            scheduleProductsAjax(120);
        });

        if (prodSearchInput) {
            prodSearchInput.addEventListener('input', function () {
                scheduleProductsAjax(280);
            });
        }

        var clearProductsBtn = prodFilterForm.querySelector('.prod-btn-clear');
        if (clearProductsBtn) {
            clearProductsBtn.addEventListener('click', function (event) {
                event.preventDefault();
                prodFilterForm.reset();
                Array.prototype.slice.call(prodFilterForm.querySelectorAll('select')).forEach(function (select) {
                    select.value = 'all';
                });
                scheduleProductsAjax(0);
            });
        }

        document.addEventListener('click', function (event) {
            var link = event.target.closest('.prod-grid-section .woocommerce-pagination a');
            if (!link) { return; }
            event.preventDefault();
            var url = new URL(link.href, window.location.origin);
            var pageMatch = url.pathname.match(/page\/(\d+)/);
            var paged = pageMatch ? pageMatch[1] : (url.searchParams.get('paged') || '1');
            var hiddenPaged = prodFilterForm.querySelector('input[name="paged"]');
            if (!hiddenPaged) {
                hiddenPaged = document.createElement('input');
                hiddenPaged.type = 'hidden';
                hiddenPaged.name = 'paged';
                prodFilterForm.appendChild(hiddenPaged);
            }
            hiddenPaged.value = paged;
            scheduleProductsAjax(0);
        });

        revealNewProductCards();
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


    /* Dynamic top bar slider - fixed slots, stable inner alignment */
    const topbarSlider = document.querySelector('[data-topbar-slider]');
    if (topbarSlider) {
        const track = topbarSlider.querySelector('.benefits-track');
        const slides = track ? Array.prototype.slice.call(track.querySelectorAll('.topbar-slide')) : [];
        let topbarIndex = 0;
        let topbarTimer = null;

        function topbarVisibleCount() {
            return 3;
        }

        function topbarContentWidth() {
            if (!topbarSlider) { return 0; }
            const rectWidth = topbarSlider.getBoundingClientRect().width || topbarSlider.clientWidth || 0;
            const styles = window.getComputedStyle(topbarSlider);
            const padStart = parseFloat(styles.paddingLeft || '0') || 0;
            const padEnd = parseFloat(styles.paddingRight || '0') || 0;
            return Math.max(0, rectWidth - padStart - padEnd);
        }

        function resetTopbarSlotClasses() {
            slides.forEach(function (slide) {
                slide.classList.remove('ct-topbar-slot-left', 'ct-topbar-slot-center', 'ct-topbar-slot-right', 'ct-topbar-slot-single');
            });
        }

        function applyTopbarSlotClasses(visible) {
            resetTopbarSlotClasses();
            for (let slot = 0; slot < visible; slot += 1) {
                const slide = slides[topbarIndex + slot];
                if (!slide) { continue; }
                if (visible === 1) {
                    slide.classList.add('ct-topbar-slot-single');
                } else if (visible === 2) {
                    slide.classList.add(slot === 0 ? 'ct-topbar-slot-right' : 'ct-topbar-slot-left');
                } else if (slot === 0) {
                    slide.classList.add('ct-topbar-slot-right');
                } else if (slot === 1) {
                    slide.classList.add('ct-topbar-slot-center');
                } else {
                    slide.classList.add('ct-topbar-slot-left');
                }
            }
        }

        function updateTopbarSlider() {
            if (!track || slides.length === 0) { return; }
            const visible = Math.min(topbarVisibleCount(), slides.length);
            const maxIndex = Math.max(0, slides.length - visible);
            if (topbarIndex > maxIndex) { topbarIndex = 0; }

            const sliderWidth = topbarContentWidth();
            const slideWidth = sliderWidth > 0 ? (sliderWidth / visible) : (slides[0].getBoundingClientRect().width || 0);

            slides.forEach(function (slide) {
                slide.style.setProperty('flex', '0 0 ' + slideWidth + 'px', 'important');
                slide.style.setProperty('width', slideWidth + 'px', 'important');
                slide.style.setProperty('max-width', slideWidth + 'px', 'important');
                slide.style.setProperty('min-width', slideWidth + 'px', 'important');
            });

            track.style.setProperty('width', (slideWidth * slides.length) + 'px', 'important');
            track.style.setProperty('min-width', (slideWidth * slides.length) + 'px', 'important');
            track.style.transform = 'translate3d(' + (-topbarIndex * slideWidth) + 'px, 0, 0)';
            applyTopbarSlotClasses(visible);
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

/* Normalize WooCommerce AJAX "View cart" link inside custom product cards */
(function () {
    'use strict';

    function normalizeAddedToCartLinks(context) {
        var root = context && context.querySelectorAll ? context : document;
        root.querySelectorAll('.prod-card-actions a.added_to_cart.wc-forward').forEach(function (link) {
            link.classList.add('prod-card-view-cart');
            link.textContent = 'عرض السلة';
            link.setAttribute('aria-label', 'عرض السلة');
            link.setAttribute('title', 'عرض السلة');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            normalizeAddedToCartLinks(document);
        });
    } else {
        normalizeAddedToCartLinks(document);
    }

    if (window.jQuery) {
        window.jQuery(document.body).on('added_to_cart wc_fragments_refreshed wc_fragments_loaded', function () {
            window.setTimeout(function () {
                normalizeAddedToCartLinks(document);
            }, 0);
        });
    }
})();
