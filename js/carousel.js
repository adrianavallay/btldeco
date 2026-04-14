/* ============================================================
   BTLDECO — Product Carousel (Premium)
   Auto-scroll, drag, arrows, dots, hover pause
   ============================================================ */
(function () {
    'use strict';

    var track = document.getElementById('pcTrack');
    var viewport = document.getElementById('pcViewport');
    var prevBtn = document.getElementById('pcPrev');
    var nextBtn = document.getElementById('pcNext');
    var dotsContainer = document.getElementById('pcDots');

    if (!track || !viewport) return;

    var slides = track.querySelectorAll('.pc-slide');
    var dots = dotsContainer ? dotsContainer.querySelectorAll('.pc-dot') : [];
    var slideCount = slides.length;
    if (slideCount === 0) return;

    var currentIndex = 0;
    var slideWidth = 0;
    var gap = 24;
    var autoplayInterval = null;
    var autoplayDelay = 4000;
    var isDragging = false;
    var startX = 0;
    var currentTranslate = 0;
    var prevTranslate = 0;

    function getSlideWidth() {
        slideWidth = slides[0].offsetWidth + gap;
    }

    function getMaxIndex() {
        var viewportWidth = viewport.offsetWidth;
        var totalWidth = slideCount * slideWidth - gap;
        var max = Math.ceil((totalWidth - viewportWidth) / slideWidth);
        return Math.max(0, max);
    }

    function goTo(index) {
        var maxIndex = getMaxIndex();
        if (index < 0) index = 0;
        if (index > maxIndex) index = maxIndex;
        currentIndex = index;
        currentTranslate = -currentIndex * slideWidth;
        prevTranslate = currentTranslate;
        track.style.transform = 'translateX(' + currentTranslate + 'px)';
        updateDots();
    }

    function updateDots() {
        dots.forEach(function (dot, i) {
            dot.classList.toggle('active', i === currentIndex);
        });
        // Disable arrows at limits
        var maxIndex = getMaxIndex();
        if (prevBtn) {
            prevBtn.style.opacity = currentIndex === 0 ? '0.3' : '1';
            prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
        }
        if (nextBtn) {
            nextBtn.style.opacity = currentIndex >= maxIndex ? '0.3' : '1';
            nextBtn.style.pointerEvents = currentIndex >= maxIndex ? 'none' : 'auto';
        }
    }

    function startAutoplay() {
        stopAutoplay();
        autoplayInterval = setInterval(function () {
            var maxIndex = getMaxIndex();
            if (currentIndex >= maxIndex) {
                stopAutoplay();
                return;
            }
            goTo(currentIndex + 1);
        }, autoplayDelay);
    }

    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
    }

    // Arrows
    if (prevBtn) prevBtn.addEventListener('click', function () { goTo(currentIndex - 1); startAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { goTo(currentIndex + 1); startAutoplay(); });

    // Dots
    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(dot.dataset.index));
            startAutoplay();
        });
    });

    // Hover pause
    viewport.addEventListener('mouseenter', stopAutoplay);
    viewport.addEventListener('mouseleave', startAutoplay);

    // Drag support
    function dragStart(e) {
        isDragging = true;
        startX = getPositionX(e);
        track.classList.add('grabbing');
        stopAutoplay();
    }

    function dragMove(e) {
        if (!isDragging) return;
        var currentX = getPositionX(e);
        var diff = currentX - startX;
        currentTranslate = prevTranslate + diff;
        track.style.transform = 'translateX(' + currentTranslate + 'px)';
    }

    function dragEnd() {
        if (!isDragging) return;
        isDragging = false;
        track.classList.remove('grabbing');

        var moved = currentTranslate - prevTranslate;
        if (Math.abs(moved) > slideWidth * 0.2) {
            if (moved < 0) {
                goTo(currentIndex + 1);
            } else {
                goTo(currentIndex - 1);
            }
        } else {
            goTo(currentIndex);
        }
        startAutoplay();
    }

    function getPositionX(e) {
        return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
    }

    // Mouse events
    viewport.addEventListener('mousedown', dragStart);
    viewport.addEventListener('mousemove', dragMove);
    viewport.addEventListener('mouseup', dragEnd);
    viewport.addEventListener('mouseleave', function () { if (isDragging) dragEnd(); });

    // Touch events
    viewport.addEventListener('touchstart', dragStart, { passive: true });
    viewport.addEventListener('touchmove', dragMove, { passive: true });
    viewport.addEventListener('touchend', dragEnd);

    // Prevent image drag
    track.querySelectorAll('img').forEach(function (img) {
        img.addEventListener('dragstart', function (e) { e.preventDefault(); });
    });

    // Keyboard
    document.addEventListener('keydown', function (e) {
        var rect = viewport.getBoundingClientRect();
        if (rect.top > window.innerHeight || rect.bottom < 0) return;
        if (e.key === 'ArrowLeft') { goTo(currentIndex - 1); startAutoplay(); }
        if (e.key === 'ArrowRight') { goTo(currentIndex + 1); startAutoplay(); }
    });

    // Init
    function init() {
        getSlideWidth();
        goTo(0);
        startAutoplay();
    }

    // Resize handler
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            getSlideWidth();
            goTo(currentIndex);
        }, 150);
    });

    // Wait for images to load then init
    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }

})();
