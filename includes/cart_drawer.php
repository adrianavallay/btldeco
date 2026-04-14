<!-- CART DRAWER -->
<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer__header">
        <h3>Tu Carrito</h3>
        <button class="cart-drawer__close" id="cartClose" aria-label="Cerrar carrito">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <div class="cart-drawer__body">
        <div class="cart-empty" id="cartEmpty">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
            <p>Tu carrito esta vacio</p>
            <a href="tienda.php" class="btn btn--primary btn--sm">EXPLORAR PRODUCTOS</a>
        </div>
        <div class="cart-items" id="cartItems"></div>
    </div>

    <div class="cart-drawer__footer" id="cartFooter" style="display:none;">
        <div class="cart-totals">
            <div class="cart-totals__row">
                <span>Subtotal</span>
                <span id="cartSubtotal">$0,00</span>
            </div>
            <div class="cart-totals__row cart-totals__row--total">
                <span>Total</span>
                <span id="cartTotal">$0,00</span>
            </div>
        </div>
        <a href="checkout.php" class="btn btn--primary btn--lg btn--full">
            FINALIZAR COMPRA
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="tienda.php" class="cart-continue">Seguir comprando</a>
    </div>
</div>
<script src="js/carrito.js?v=7"></script>
