function formatEuro(value) {
  const num = Number(value);
  if (Number.isFinite(num)) {
    return num.toLocaleString('nl-NL', { style: 'currency', currency: 'EUR' });
  }
  return `€${String(value ?? '')}`;
}

function escapeHtml(str) {
  return String(str ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

async function loadProducts() {
  const res = await fetch('products.json', { cache: 'no-store' });
  if (!res.ok) throw new Error(`Fetch failed (${res.status})`);
  const data = await res.json();
  const map = new Map();
  for (const p of data) {
    map.set(String(p.id), p);
  }
  return map;
}

function buildOrderText({ cart, productsById }) {
  const name = document.getElementById('pickup-name')?.value?.trim() || '';
  const email = document.getElementById('pickup-email')?.value?.trim() || '';
  const phone = document.getElementById('pickup-phone')?.value?.trim() || '';
  const when = document.getElementById('pickup-when')?.value?.trim() || '';
  const note = document.getElementById('pickup-note')?.value?.trim() || '';

  let total = 0;
  const lines = [];
  for (const [id, qtyRaw] of Object.entries(cart)) {
    const qty = Number(qtyRaw);
    if (!Number.isFinite(qty) || qty <= 0) continue;
    const p = productsById.get(String(id));
    const pname = p?.name || `Product ${id}`;
    const price = Number(p?.price || 0);
    const lineTotal = Number.isFinite(price) ? price * qty : 0;
    total += lineTotal;
    lines.push(`- ${qty}x ${pname} (${formatEuro(price)}) = ${formatEuro(lineTotal)}`);
  }

  const header = [
    'Bestelling voor afhalen (geen bezorging)',
    '',
    `Naam: ${name || '-'}`,
    `E-mail: ${email || '-'}`,
    `Telefoon: ${phone || '-'}`,
    `Afhalen: ${when || '-'}`,
    note ? `Opmerking: ${note}` : null,
    '',
    'Items:',
    ...lines,
    '',
    `Totaal: ${formatEuro(total)}`,
  ].filter(Boolean);

  return header.join('\n');
}

function render({ cart, productsById }) {
  const container = document.getElementById('cart-items');
  const empty = document.getElementById('cart-empty');
  const totalEl = document.getElementById('cart-total');
  if (!container || !empty || !totalEl) return;

  const entries = Object.entries(cart).filter(([, q]) => Number(q) > 0);
  if (entries.length === 0) {
    container.innerHTML = '';
    empty.classList.remove('hidden');
    totalEl.textContent = formatEuro(0);
    return;
  }

  empty.classList.add('hidden');

  let total = 0;
  container.innerHTML = entries
    .map(([id, qtyRaw]) => {
      const qty = Number(qtyRaw);
      const p = productsById.get(String(id));
      const name = escapeHtml(p?.name || `Product ${id}`);
      const cat = escapeHtml(p?.category || 'Product');
      const img = p?.image_url ? String(p.image_url) : '';
      const price = Number(p?.price || 0);
      const lineTotal = Number.isFinite(price) ? price * qty : 0;
      total += lineTotal;

      return `
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 md:p-5 flex gap-4 items-center">
        <div class="w-20 h-16 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden flex items-center justify-center">
          ${img ? `<img src="${img}" alt="${name}" class="object-contain w-full h-full">` : `<span class="text-slate-400 text-xs">Geen foto</span>`}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-xs text-slate-500 dark:text-slate-400">${cat}</div>
              <div class="font-bold text-slate-900 dark:text-white truncate">${name}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-slate-500 dark:text-slate-400">${formatEuro(price)}</div>
              <div class="font-black text-slate-900 dark:text-white">${formatEuro(lineTotal)}</div>
            </div>
          </div>
          <div class="mt-3 flex items-center gap-2">
            <button class="qty-btn px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700" data-id="${escapeHtml(id)}" data-delta="-1" type="button">-</button>
            <input class="qty-input w-16 text-center rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white" data-id="${escapeHtml(id)}" type="number" min="1" value="${qty}">
            <button class="qty-btn px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700" data-id="${escapeHtml(id)}" data-delta="1" type="button">+</button>
            <button class="remove-btn ml-auto text-sm font-semibold text-slate-500 hover:text-red-600" data-id="${escapeHtml(id)}" type="button">Verwijder</button>
          </div>
        </div>
      </div>`;
    })
    .join('');

  totalEl.textContent = formatEuro(total);
}

async function init() {
  if (!window.Cart) return;

  let productsById;
  try {
    productsById = await loadProducts();
  } catch {
    const empty = document.getElementById('cart-empty');
    if (empty) {
      empty.classList.remove('hidden');
      empty.innerHTML = 'Kon <code>products.json</code> niet laden.';
    }
    return;
  }

  function rerender() {
    render({ cart: window.Cart.read(), productsById });
  }

  rerender();
  window.addEventListener('cart:updated', rerender);

  const items = document.getElementById('cart-items');
  if (items) {
    items.addEventListener('click', (e) => {
      const qtyBtn = e.target.closest?.('.qty-btn');
      if (qtyBtn) {
        const id = qtyBtn.getAttribute('data-id');
        const delta = Number(qtyBtn.getAttribute('data-delta') || 0);
        const cart = window.Cart.read();
        const current = Number(cart[id] || 0);
        window.Cart.setItem(id, (Number.isFinite(current) ? current : 0) + delta);
        return;
      }

      const removeBtn = e.target.closest?.('.remove-btn');
      if (removeBtn) {
        const id = removeBtn.getAttribute('data-id');
        window.Cart.setItem(id, 0);
      }
    });

    items.addEventListener('change', (e) => {
      const input = e.target.closest?.('.qty-input');
      if (!input) return;
      const id = input.getAttribute('data-id');
      const qty = Number(input.value);
      window.Cart.setItem(id, qty);
    });
  }

  // order form (Web3Forms)
  const form = document.getElementById('pickupOrderForm');
  const feedback = document.getElementById('order-feedback');
  const errorEl = document.getElementById('order-error');
  const clearBtn = document.getElementById('clear-cart');
  const messageField = document.getElementById('order-message');
  const totalField = document.getElementById('order-total');
  const submitBtn = document.getElementById('submit-order');

  function calcTotal(cart) {
    let total = 0;
    for (const [id, qtyRaw] of Object.entries(cart)) {
      const qty = Number(qtyRaw);
      if (!Number.isFinite(qty) || qty <= 0) continue;
      const p = productsById.get(String(id));
      const price = Number(p?.price || 0);
      if (Number.isFinite(price)) total += price * qty;
    }
    return total;
  }

  function updateOrderMessage() {
    const cart = window.Cart.read();
    const text = buildOrderText({ cart, productsById });
    const total = calcTotal(cart);
    if (messageField) messageField.value = text;
    if (totalField) totalField.value = formatEuro(total);
  }

  updateOrderMessage();
  window.addEventListener('cart:updated', updateOrderMessage);
  ['pickup-name', 'pickup-email', 'pickup-phone', 'pickup-when', 'pickup-note'].forEach((id) => {
    document.getElementById(id)?.addEventListener('input', updateOrderMessage);
  });

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (errorEl) errorEl.classList.add('hidden');
      if (feedback) feedback.classList.add('hidden');

      const cart = window.Cart.read();
      const hasItems = Object.values(cart).some((q) => Number(q) > 0);
      if (!hasItems) {
        if (errorEl) {
          errorEl.textContent = 'Je winkelmand is leeg.';
          errorEl.classList.remove('hidden');
        }
        return;
      }

      updateOrderMessage();

      const formData = new FormData(form);
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Versturen...';
      }

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: { Accept: 'application/json' },
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok && data && data.success) {
          if (feedback) {
            feedback.textContent = 'Bestelling verstuurd. We nemen contact op voor afhalen.';
            feedback.classList.remove('hidden');
          }
          window.Cart.clear();
          form.reset();
          updateOrderMessage();
        } else {
          const msg = data?.message || 'Versturen mislukt. Probeer het later opnieuw.';
          if (errorEl) {
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
          }
        }
      } catch {
        if (errorEl) {
          errorEl.textContent = 'Netwerkfout. Controleer je internet en probeer opnieuw.';
          errorEl.classList.remove('hidden');
        }
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Bestellen voor afhalen';
        }
      }
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      window.Cart.clear();
    });
  }
}

document.addEventListener('DOMContentLoaded', init);

