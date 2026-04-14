/* ============================================================
   BTLDECO — Gallery GSAP ScrollTrigger Animations
   ============================================================ */
(function () {
    'use strict';

    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);

    // ── Section header: fade in + slide up ──
    var galHeader = document.querySelector('.gallery .section__header');
    if (galHeader) {
        gsap.from(galHeader, {
            y: 60,
            opacity: 0,
            duration: 1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: galHeader,
                start: 'top 85%',
                toggleActions: 'play none none none'
            }
        });
    }

    // ── Track 1 (left): parallax speed boost on scroll ──
    var track1 = document.getElementById('galleryTrack1');
    if (track1) {
        // Speed up the CSS animation via translateX on scroll
        gsap.to(track1, {
            x: '-=200',
            ease: 'none',
            scrollTrigger: {
                trigger: track1.closest('.gallery__track-wrapper'),
                start: 'top bottom',
                end: 'bottom top',
                scrub: 1.5
            }
        });
    }

    // ── Track 2 (right): parallax opposite direction ──
    var track2 = document.getElementById('galleryTrack2');
    if (track2) {
        gsap.to(track2, {
            x: '+=200',
            ease: 'none',
            scrollTrigger: {
                trigger: track2.closest('.gallery__track-wrapper'),
                start: 'top bottom',
                end: 'bottom top',
                scrub: 1.5
            }
        });
    }

    // ── Gallery items: scale + fade in staggered on scroll ──
    var items = document.querySelectorAll('.gallery__item');
    if (items.length > 0) {
        // Only animate unique items (first half, not duplicates)
        var uniqueCount = Math.ceil(items.length / 2);
        var uniqueItems = Array.prototype.slice.call(items, 0, uniqueCount);

        ScrollTrigger.batch(uniqueItems, {
            onEnter: function (batch) {
                gsap.fromTo(batch,
                    { scale: 0.85, opacity: 0.3 },
                    { scale: 1, opacity: 1, duration: 0.8, stagger: 0.08, ease: 'power2.out', overwrite: true }
                );
            },
            start: 'top 90%'
        });
    }

    // ── Gallery section: subtle background color shift on scroll ──
    var gallerySection = document.querySelector('.gallery');
    if (gallerySection) {
        gsap.fromTo(gallerySection,
            { '--gallery-glow': 0 },
            {
                '--gallery-glow': 1,
                ease: 'none',
                scrollTrigger: {
                    trigger: gallerySection,
                    start: 'top 60%',
                    end: 'bottom 40%',
                    scrub: 2
                }
            }
        );
    }

    // ── Expand overlay: items scale up more on hover with GSAP ──
    items.forEach(function (item) {
        item.addEventListener('mouseenter', function () {
            gsap.to(item, { scale: 1.12, zIndex: 5, duration: 0.4, ease: 'power2.out' });
            gsap.to(item.querySelector('.gallery__item-overlay'), { opacity: 1, duration: 0.3 });
        });
        item.addEventListener('mouseleave', function () {
            gsap.to(item, { scale: 1, zIndex: 1, duration: 0.35, ease: 'power2.inOut' });
            gsap.to(item.querySelector('.gallery__item-overlay'), { opacity: 0, duration: 0.25 });
        });
    });

})();
