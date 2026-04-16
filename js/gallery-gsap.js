/* ============================================================
   BTLDECO — Gallery GSAP Animations
   Parallax + reveal all items at once per row
   ============================================================ */
(function () {
    'use strict';

    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    // ── Header reveal ──
    var galHeader = document.querySelector('.gallery .section__header');
    if (galHeader) {
        gsap.from(galHeader, {
            y: 40,
            opacity: 0,
            duration: 0.8,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: '.gallery',
                start: 'top 80%',
                once: true
            }
        });
    }

    // ── Parallax: tracks move faster on scroll ──
    var track1 = document.getElementById('galleryTrack1');
    var track2 = document.getElementById('galleryTrack2');

    if (track1) {
        gsap.to(track1, {
            x: '-=300',
            ease: 'none',
            scrollTrigger: {
                trigger: '.gallery',
                start: 'top bottom',
                end: 'bottom top',
                scrub: 0.8
            }
        });
    }

    if (track2) {
        gsap.to(track2, {
            x: '+=300',
            ease: 'none',
            scrollTrigger: {
                trigger: '.gallery',
                start: 'top bottom',
                end: 'bottom top',
                scrub: 0.8
            }
        });
    }

    // ── Reveal: all items in each row at once with clip-path ──
    var wrappers = document.querySelectorAll('.gallery__track-wrapper');
    wrappers.forEach(function (wrapper) {
        var items = wrapper.querySelectorAll('.gallery__item');

        gsap.fromTo(items,
            { clipPath: 'inset(0 100% 0 0)', opacity: 0 },
            {
                clipPath: 'inset(0 0% 0 0)',
                opacity: 1,
                duration: 1.2,
                ease: 'power3.inOut',
                scrollTrigger: {
                    trigger: wrapper,
                    start: 'top 85%',
                    once: true
                }
            }
        );
    });

    // ══════════════════════════════════════════
    // ABOUT V2 — SCROLL ANIMATIONS
    // ══════════════════════════════════════════

    // ── Pin gallery: stays fixed, About slides over it ──
    var gallerySection = document.querySelector('.gallery');
    var aboutSection = document.querySelector('.about-v2');
    if (gallerySection && aboutSection) {
        ScrollTrigger.create({
            trigger: gallerySection,
            start: 'top -20%',
            end: function () {
                return '+=' + aboutSection.offsetHeight;
            },
            pin: true,
            pinSpacing: false
        });
    }

    // ── Pin About: stays fixed, Contact slides over it ──
    if (aboutSection) {
        var contactSection = document.querySelector('.contact');
        if (contactSection) {
            ScrollTrigger.create({
                trigger: aboutSection,
                start: 'top 20%',
                end: function () {
                    return '+=' + contactSection.offsetHeight;
                },
                pin: true,
                pinSpacing: false
            });
        }
    }

    // About header
    var aboutHeader = document.getElementById('aboutHeader');
    if (aboutHeader) {
        gsap.from(aboutHeader.children, {
            y: 40, opacity: 0, duration: 0.8, stagger: 0.12, ease: 'power3.out',
            scrollTrigger: { trigger: aboutHeader, start: 'top 80%', once: true }
        });
    }

    // About content: story slides from left, values from right
    var aboutGrid = document.getElementById('aboutGrid');
    if (aboutGrid) {
        var story = aboutGrid.querySelector('.about-v2__story');
        var values = aboutGrid.querySelectorAll('.about-v2__value');
        if (story) {
            gsap.from(story, {
                x: -60, opacity: 0, duration: 0.9, ease: 'power3.out',
                scrollTrigger: { trigger: aboutGrid, start: 'top 80%', once: true }
            });
        }
        if (values.length) {
            gsap.from(values, {
                x: 40, opacity: 0, duration: 0.7, stagger: 0.1, ease: 'power2.out',
                scrollTrigger: { trigger: aboutGrid, start: 'top 80%', once: true }
            });
        }
    }

})();
