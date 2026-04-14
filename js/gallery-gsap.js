/* ============================================================
   BTLDECO — Gallery GSAP Animations
   Parallax tracks only
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

})();
