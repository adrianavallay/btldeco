/* ============================================================
   BTLDECO — TIENDA: Filter & Sort
   ============================================================ */

(function () {
    'use strict';

    const grid = document.getElementById('tiendaGrid');
    const emptyState = document.getElementById('tiendaEmpty');
    const filterPills = document.querySelectorAll('.filter-pill');
    const sortSelect = document.getElementById('sortSelect');

    if (!grid) return;

    // --- Click to product detail ---
    grid.querySelectorAll('.tienda-card').forEach((card) => {
        card.addEventListener('click', () => {
            const name = card.querySelector('.tienda-card__name').textContent;
            const id = name.toLowerCase().replace(/\s+/g, '-').replace(/&/g, '').replace(/--/g, '-');
            window.location.href = 'producto.html?id=' + id;
        });
    });

    // --- Category Filter ---
    filterPills.forEach((pill) => {
        pill.addEventListener('click', () => {
            filterPills.forEach((p) => p.classList.remove('active'));
            pill.classList.add('active');

            const filter = pill.dataset.filter;
            const cards = grid.querySelectorAll('.tienda-card');
            let visibleCount = 0;

            cards.forEach((card) => {
                if (filter === 'todos' || card.dataset.category === filter) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    });

    // --- Sort ---
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            const value = sortSelect.value;
            const cards = Array.from(grid.querySelectorAll('.tienda-card'));

            cards.sort((a, b) => {
                if (value === 'price-asc') {
                    return parseInt(a.dataset.price) - parseInt(b.dataset.price);
                }
                if (value === 'price-desc') {
                    return parseInt(b.dataset.price) - parseInt(a.dataset.price);
                }
                if (value === 'name-asc') {
                    const nameA = a.querySelector('.tienda-card__name').textContent;
                    const nameB = b.querySelector('.tienda-card__name').textContent;
                    return nameA.localeCompare(nameB);
                }
                return 0;
            });

            cards.forEach((card) => grid.appendChild(card));
        });
    }

})();
