/* ============================================================
   BTLDECO — CARRITO DRAWER
   ============================================================ */
(function () {
    'use strict';

    const drawer = document.getElementById('cartDrawer');
    const overlay = document.getElementById('cartOverlay');
    const cartBtn = document.getElementById('cartBtn');
    const cartClose = document.getElementById('cartClose');
    const cartBadge = document.getElementById('cartBadge');
    const cartItems = document.getElementById('cartItems');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTotal = document.getElementById('cartTotal');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartFooter = document.getElementById('cartFooter');

    if (!drawer) return;

    // Toggle drawer
    function openCart() {
        drawer.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        loadCart();
    }
    function closeCart() {
        drawer.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    cartBtn.addEventListener('click', openCart);
    cartClose.addEventListener('click', closeCart);
    overlay.addEventListener('click', closeCart);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && drawer.classList.contains('active')) closeCart();
    });

    // API helper
    function cartFetch(action, params) {
        const url = 'carrito_api.php?action=' + action;
        const body = new URLSearchParams(params || {});
        return fetch(url, { method: 'POST', body: body })
            .then(r => r.json());
    }

    // Load cart
    function loadCart() {
        cartFetch('get').then(function(data) {
            renderCart(data.cart || data);
        });
    }

    // Update badge
    function updateBadge() {
        cartFetch('count').then(function(data) {
            const count = data.count || 0;
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'flex' : 'none';
        });
    }

    // Render cart items
    function renderCart(cart) {
        var rawItems = cart.items || cart;
        var subtotal = 0;
        var html = '';

        // Normalize items to array
        var itemsList = [];
        if (Array.isArray(rawItems)) {
            itemsList = rawItems;
        } else if (rawItems && typeof rawItems === 'object') {
            Object.keys(rawItems).forEach(function(k) {
                var it = rawItems[k];
                if (it && it.nombre) {
                    if (!it.key) it.key = k;
                    itemsList.push(it);
                }
            });
        }

        if (itemsList.length === 0) {
            cartEmpty.style.display = 'block';
            cartFooter.style.display = 'none';
            cartItems.innerHTML = '';
            updateBadge();
            return;
        }

        cartEmpty.style.display = 'none';
        cartFooter.style.display = 'block';

        itemsList.forEach(function(item) {
            var itemKey = String(item.key || (item.producto_id + (item.variante ? '-' + item.variante : '')));
            var itemTotal = item.precio * item.qty;
            subtotal += itemTotal;

            var imgSrc = item.imagen || '';
            if (imgSrc && !imgSrc.startsWith('http')) {
                imgSrc = 'uploads/productos/' + imgSrc;
            }

            var safeKey = itemKey.replace(/'/g, "\\'");

            html += '<div class="cart-item">';
            html += '  <div class="cart-item__img">';
            html += '    <img src="' + imgSrc + '" alt="' + (item.nombre || '') + '">';
            html += '  </div>';
            html += '  <div class="cart-item__info">';
            html += '    <span class="cart-item__name">' + (item.nombre || '') + '</span>';
            if (item.variante) html += '<span class="cart-item__variant">' + item.variante + '</span>';
            html += '    <span class="cart-item__price">$' + formatPrice(item.precio) + '</span>';
            html += '    <div class="cart-item__qty">';
            html += '      <button type="button" class="qty-btn" onclick="window.btlCart.update(\'' + safeKey + '\', ' + (item.qty - 1) + ')">−</button>';
            html += '      <span>' + item.qty + '</span>';
            html += '      <button type="button" class="qty-btn" onclick="window.btlCart.update(\'' + safeKey + '\', ' + (item.qty + 1) + ')">+</button>';
            html += '    </div>';
            html += '  </div>';
            html += '  <button type="button" class="cart-item__remove" onclick="window.btlCart.remove(\'' + safeKey + '\')">';
            html += '    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            html += '  </button>';
            html += '</div>';
        });

        cartItems.innerHTML = html;
        cartSubtotal.textContent = '$' + formatPrice(subtotal);
        cartTotal.textContent = '$' + formatPrice(cart.total || subtotal);
        updateBadge();
    }

    function formatPrice(n) {
        return Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Public API
    window.btlCart = {
        add: function(productoId, qty, variante) {
            qty = qty || 1;
            cartFetch('add', { producto_id: productoId, qty: qty, variante: variante || '' }).then(function(data) {
                if (data.ok) {
                    openCart();
                } else {
                    alert(data.mensaje || 'Error al agregar');
                }
            });
        },
        update: function(key, qty) {
            if (qty < 1) {
                this.remove(key);
                return;
            }
            cartFetch('update', { key: key, qty: qty }).then(function(data) {
                if (data.ok) renderCart(data.cart || {});
            });
        },
        remove: function(key) {
            cartFetch('remove', { key: key }).then(function(data) {
                if (data.ok) renderCart(data.cart || {});
            });
        },
        open: openCart,
        close: closeCart
    };

    // Init badge on page load
    updateBadge();

})();
