/* ============================================================
   BTLDECO — Gallery GSAP Animations
   Reveal + 3D tilt + parallax tracks
   ============================================================ */
(function () {
    'use strict';

    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);

    // Check reduced motion
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

    // ── Items: clip-path reveal + slight Y offset ──
    var wrappers = document.querySelectorAll('.gallery__track-wrapper');
    wrappers.forEach(function (wrapper, wIndex) {
        var items = wrapper.querySelectorAll('.gallery__item');
        // Only animate first set (not duplicates)
        var half = Math.ceil(items.length / 2);
        var unique = Array.prototype.slice.call(items, 0, half);

        unique.forEach(function (item, i) {
            gsap.fromTo(item,
                {
                    clipPath: 'inset(0 100% 0 0)',
                    opacity: 0
                },
                {
                    clipPath: 'inset(0 0% 0 0)',
                    opacity: 1,
                    duration: 1.2,
                    delay: i * 0.1,
                    ease: 'power3.inOut',
                    scrollTrigger: {
                        trigger: wrapper,
                        start: 'top 85%',
                        once: true
                    }
                }
            );
        });
    });

    // ── Hover: 3D tilt effect ──
    var allItems = document.querySelectorAll('.gallery__item');
    allItems.forEach(function (item) {
        item.addEventListener('mousemove', function (e) {
            var rect = item.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;

            gsap.to(item, {
                rotateY: x * 12,
                rotateX: -y * 8,
                scale: 1.06,
                zIndex: 10,
                boxShadow: '0 20px 50px rgba(0,0,0,0.2)',
                duration: 0.4,
                ease: 'power2.out'
            });
        });

        item.addEventListener('mouseleave', function () {
            gsap.to(item, {
                rotateY: 0,
                rotateX: 0,
                scale: 1,
                zIndex: 1,
                boxShadow: 'none',
                duration: 0.5,
                ease: 'power2.inOut'
            });
        });
    });

})();
