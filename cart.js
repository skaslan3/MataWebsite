(() => {
  const CART_KEY = 'mata_cart_v1';

  function readCart() {
    try {
      const raw = localStorage.getItem(CART_KEY);
      if (!raw) return {};
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') return {};
      return parsed;
    } catch {
      return {};
    }
  }

  function writeCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart || {}));
    window.dispatchEvent(new CustomEvent('cart:updated'));
  }

  function getCartQty(cart = readCart()) {
    let total = 0;
    for (const k of Object.keys(cart)) {
      const qty = Number(cart[k]);
      if (Number.isFinite(qty) && qty > 0) total += qty;
    }
    return total;
  }

  function addToCart(productId, qty = 1) {
    const id = String(productId);
    const amount = Number(qty);
    if (!id || !Number.isFinite(amount) || amount <= 0) return;
    const cart = readCart();
    const current = Number(cart[id] || 0);
    cart[id] = (Number.isFinite(current) ? current : 0) + amount;
    writeCart(cart);
  }

  function setCartItem(productId, qty) {
    const id = String(productId);
    const amount = Number(qty);
    if (!id || !Number.isFinite(amount)) return;
    const cart = readCart();
    if (amount <= 0) {
      delete cart[id];
    } else {
      cart[id] = amount;
    }
    writeCart(cart);
  }

  function clearCart() {
    writeCart({});
  }

  window.Cart = {
    read: readCart,
    write: writeCart,
    qty: getCartQty,
    add: addToCart,
    setItem: setCartItem,
    clear: clearCart,
  };
})();

