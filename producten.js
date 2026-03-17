function formatEuro(value) {
  const num = Number(value);
  if (Number.isFinite(num)) {
    return num.toLocaleString('nl-NL', { style: 'currency', currency: 'EUR' });
  }
  return `€${String(value ?? '')}`;
}

function getParams() {
  const params = new URLSearchParams(window.location.search);
  const cat = (params.get('cat') || '').toLowerCase().trim();
  const sort = (params.get('sort') || 'default').trim();
  return { params, cat, sort };
}

function setParam(key, value) {
  const url = new URL(window.location.href);
  if (!value || value === 'default') {
    url.searchParams.delete(key);
  } else {
    url.searchParams.set(key, value);
  }
  // reset pagination later if you add it
  window.location.href = url.toString();
}

function mapCatKeyToLabel(catKey) {
  const map = {
    telefoons: 'Telefoons',
    tablets: 'Tablets',
    laptops: 'Laptops',
    accessoires: 'Accessoires',
  };
  return map[catKey] || null;
}

function safeDateValue(value) {
  const t = Date.parse(value);
  return Number.isFinite(t) ? t : null;
}

function sortProducts(items, sort) {
  const arr = [...items];
  switch (sort) {
    case 'price_asc':
      arr.sort((a, b) => Number(a.price) - Number(b.price));
      return arr;
    case 'price_desc':
      arr.sort((a, b) => Number(b.price) - Number(a.price));
      return arr;
    case 'newest':
      arr.sort((a, b) => {
        const da = safeDateValue(a.created_at) ?? 0;
        const db = safeDateValue(b.created_at) ?? 0;
        return db - da;
      });
      return arr;
    default:
      arr.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), 'nl'));
      return arr;
  }
}

function renderChips({ cat, sort }) {
  const chips = document.getElementById('active-chips');
  const clear = document.getElementById('clear-filters');
  if (!chips || !clear) return;

  chips.innerHTML = '';
  let hasAny = false;

  const catLabel = mapCatKeyToLabel(cat);
  if (catLabel) {
    hasAny = true;
    chips.innerHTML += `
      <span class="inline-flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-3 py-1 text-sm text-slate-700 dark:text-slate-300">
        ${catLabel}
      </span>
    `;
  }

  if (sort && sort !== 'default') {
    hasAny = true;
    const labels = {
      price_asc: 'Prijs ↑',
      price_desc: 'Prijs ↓',
      newest: 'Nieuwste eerst',
    };
    chips.innerHTML += `
      <span class="inline-flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-3 py-1 text-sm text-slate-700 dark:text-slate-300">
        ${labels[sort] || 'Sortering'}
      </span>
    `;
  }

  clear.classList.toggle('hidden', !hasAny);
}

function renderProducts(items) {
  const grid = document.getElementById('products-grid');
  const count = document.getElementById('result-count');
  const empty = document.getElementById('empty-state');
  if (!grid || !count || !empty) return;

  count.textContent = `${items.length} resultaten`;

  if (items.length === 0) {
    grid.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }

  empty.classList.add('hidden');
  grid.innerHTML = items
    .map((p) => {
      const name = String(p.name || 'Naam onbekend');
      const desc = String(p.description || '');
      const price = formatEuro(p.price);
      const img = p.image_url ? String(p.image_url) : '';
      const cat = String(p.category || 'Product');
      const id = String(p.id ?? '');
      return `
      <div class="group relative bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
        <div class="relative aspect-[4/3] bg-slate-100 dark:bg-slate-900 overflow-hidden p-6 flex items-center justify-center">
          <div class="absolute top-3 left-3 z-10">
            <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">${cat}</span>
          </div>
          ${
            img
              ? `<img class="object-contain h-full w-full group-hover:scale-105 transition-transform duration-500" src="${img}" alt="${name}">`
              : `<span class="text-slate-400 text-sm">Geen afbeelding</span>`
          }
        </div>
        <div class="p-5 flex-1 flex flex-col">
          <div class="flex items-start justify-between mb-2">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-primary transition-colors">${name}</h3>
          </div>
          ${
            desc
              ? `<p class="text-sm text-slate-500 dark:text-slate-400 mb-3 line-clamp-2">${desc}</p>`
              : ''
          }
          <div class="mt-auto flex items-end justify-between">
            <div class="flex flex-col">
              <span class="text-xs text-slate-500 dark:text-slate-400">Prijs</span>
              <span class="text-xl font-bold text-slate-900 dark:text-white">${price}</span>
            </div>
            <button class="add-to-cart bg-primary/10 hover:bg-primary text-primary hover:text-white p-2.5 rounded-lg transition-colors" type="button" data-product-id="${id}">
              <span class="material-symbols-outlined text-[20px] block">add_shopping_cart</span>
            </button>
          </div>
        </div>
      </div>`;
    })
    .join('');
}

async function initProductsPage() {
  const { cat, sort } = getParams();

  const title = document.getElementById('page-title');
  const catLabel = mapCatKeyToLabel(cat);
  if (title) title.textContent = catLabel || 'Ons Assortiment';

  const sortSelect = document.getElementById('sort-select');
  if (sortSelect) {
    sortSelect.value = ['default', 'price_asc', 'price_desc', 'newest'].includes(sort) ? sort : 'default';
    sortSelect.addEventListener('change', () => setParam('sort', sortSelect.value));
  }

  const clear = document.getElementById('clear-filters');
  if (clear) {
    clear.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = 'Producten.html';
    });
  }

  renderChips({ cat, sort });

  let products = [];
  try {
    const res = await fetch('products.json', { cache: 'no-store' });
    if (!res.ok) throw new Error(`Fetch failed (${res.status})`);
    products = await res.json();
  } catch (e) {
    const empty = document.getElementById('empty-state');
    if (empty) {
      empty.classList.remove('hidden');
      empty.innerHTML =
        'Kon <code>products.json</code> niet laden. Check of het bestand bestaat en dat je de site via GitHub Pages/localhost opent (niet via <code>file://</code>).';
    }
    return;
  }

  // filter
  const catDbLabel = catLabel;
  if (catDbLabel) {
    products = products.filter((p) => String(p.category || '').toLowerCase() === catDbLabel.toLowerCase());
  }

  // sort
  products = sortProducts(products, sort);

  renderProducts(products);

  // Add-to-cart handlers (event delegation)
  const grid = document.getElementById('products-grid');
  if (grid) {
    grid.addEventListener('click', (e) => {
      const btn = e.target.closest?.('.add-to-cart');
      if (!btn) return;
      const id = btn.getAttribute('data-product-id');
      if (!id) return;
      if (!window.Cart) return;
      window.Cart.add(id, 1);

      // kleine feedback
      btn.classList.add('bg-primary', 'text-white');
      setTimeout(() => {
        btn.classList.remove('bg-primary', 'text-white');
      }, 300);
    });
  }
}

document.addEventListener('DOMContentLoaded', initProductsPage);

