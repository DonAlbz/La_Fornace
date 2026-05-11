document.addEventListener('DOMContentLoaded', () => {
    // Unico setup per animazioni all'apparizione
    const animabili = ['.impasto-card', '.pizza-card', '.about-text', '.order-form-wrapper'];
    
    animabili.forEach(selector => {
        gsap.from(selector, {
            scrollTrigger: { trigger: selector, start: 'top 85%' },
            y: 30, opacity: 0, duration: 0.6
        });
    });

    // Funzione carrello snellita

  // ── Intersection Observer fade-in ────────────────────────
  const fadeEls = document.querySelectorAll('.fade-in');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('visible'), i * 80);
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  fadeEls.forEach(el => observer.observe(el));

  // ── Contatore stats ───────────────────────────────────────
  const statEls = document.querySelectorAll('.stat strong[data-target]');
  const statsObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el     = entry.target;
        const target = parseInt(el.getAttribute('data-target'));
        const suffix = el.getAttribute('data-suffix') || '';
        let current  = 0;
        const step   = target / 60;
        const timer  = setInterval(() => {
          current += step;
          if (current >= target) { current = target; clearInterval(timer); }
          el.textContent = Math.floor(current) + suffix;
        }, 20);
        statsObs.unobserve(el);
      }
    });
  }, { threshold: 0.5 });
  statEls.forEach(el => statsObs.observe(el));

  // ── FILTRO MENU ───────────────────────────────────────────
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const filter = this.getAttribute('data-filter');
      document.querySelectorAll('.pizza-card').forEach(card => {
        const show = filter === 'all' || card.getAttribute('data-cat') === filter;
        card.style.display = show ? 'block' : 'none';
        if (show && typeof gsap !== 'undefined') {
          gsap.from(card, { opacity: 0, y: 20, duration: 0.35 });
        }
      });
    });
  });

  // ── SMOOTH SCROLL ─────────────────────────────────────────
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) target.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // ════════════════════════════════════════════════════════════
  //  CARRELLO — impasto per ogni singola pizza
  // ════════════════════════════════════════════════════════════
  let cart = JSON.parse(localStorage.getItem('fornace_cart') || '[]');

  function saveCart() {
    localStorage.setItem('fornace_cart', JSON.stringify(cart));
  }

  function cartKey(name, impasto) {
    // Una voce carrello è unica per combinazione pizza + impasto
    return `${name}||${impasto}`;
  }

  function renderCart() {
    const container = document.getElementById('cart-items');
    const countEl   = document.getElementById('cart-count');
    if (!container) return;

    const totalItems = cart.reduce((s, i) => s + i.qty, 0);
    if (countEl) countEl.textContent = totalItems;

    if (cart.length === 0) {
      container.innerHTML = '<div class="cart-empty">🛒 Il carrello è vuoto</div>';
      updateTotals(0);
      return;
    }

    container.innerHTML = cart.map((item, idx) => `
      <div class="cart-item">
        <div class="cart-item-info">
          <strong>${item.name}</strong>
          <span class="cart-item-impasto">🌾 ${item.impasto}</span>
          <div class="cart-item-qty">x${item.qty}</div>
        </div>
        <span class="cart-item-price">€${(item.price * item.qty).toFixed(2)}</span>
        <button class="remove-item" onclick="removeFromCart(${idx})" title="Rimuovi">×</button>
      </div>
    `).join('');

    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    updateTotals(subtotal);
  }

  function updateTotals(subtotal) {
    const delivery = subtotal > 0 ? 1.50 : 0;
    const total    = subtotal + delivery;
    const subEl  = document.getElementById('cart-subtotal');
    const delEl  = document.getElementById('cart-delivery');
    const totEl  = document.getElementById('cart-total');
    if (subEl) subEl.textContent = '€' + subtotal.toFixed(2);
    if (delEl) delEl.textContent = delivery > 0 ? '€1.50' : 'Gratis';
    if (totEl) totEl.textContent = '€' + total.toFixed(2);
  }

  // ── Aggiungi al carrello ───────────────────────────────────
  window.addToCart = function (name, price, cardEl) {
    // Legge la select impasto della card specifica
    const selectEl = cardEl.querySelector('.impasto-select');
    const impasto  = selectEl ? selectEl.value : 'Classica Napoletana';

    const key      = cartKey(name, impasto);
    const existing = cart.find(i => cartKey(i.name, i.impasto) === key);

    if (existing) {
      existing.qty++;
    } else {
      cart.push({ name, price, impasto, qty: 1 });
    }

    saveCart();
    renderCart();
    showToast(`🍕 ${name} (${impasto}) aggiunta!`);

    // Feedback visivo sul pulsante
    const btn = cardEl.querySelector('.add-btn');
    if (btn) {
      btn.textContent = '✓ Aggiunta!';
      btn.classList.add('added');
      setTimeout(() => {
        btn.textContent = '+ Aggiungi';
        btn.classList.remove('added');
      }, 1600);
    }
  };

  window.removeFromCart = function (idx) {
    const item = cart[idx];
    cart.splice(idx, 1);
    saveCart();
    renderCart();
    if (item) showToast(`Rimossa: ${item.name}`);
  };

  // ── TOAST ─────────────────────────────────────────────────
  window.showToast = function (msg) {
    let toast = document.getElementById('toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'toast';
      toast.className = 'toast';
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), 2800);
  };

  // ── FORM ORDINE ───────────────────────────────────────────
  const orderForm = document.getElementById('order-form');
  if (orderForm) {
    orderForm.addEventListener('submit', function (e) {
      e.preventDefault();

      if (cart.length === 0) {
        showToast('⚠️ Aggiungi almeno una pizza al carrello!');
        return;
      }

      const formData = new FormData(this);
      const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
      formData.append('cart',  JSON.stringify(cart));
      formData.append('total', (subtotal + 1.50).toFixed(2));

      const btn = this.querySelector('.submit-btn');
      btn.disabled    = true;
      btn.textContent = 'Invio in corso...';

     // Dentro l'evento submit di orderForm in main.js
fetch('php/ordine.php', { method: 'POST', body: formData })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // 1. Mostra il container dell'animazione
      const animWrap = document.getElementById('boxing-anim');
      animWrap.style.display = 'flex';

      // 2. Timeline GSAP per l'incartonamento
      const tl = gsap.timeline({
        onComplete: () => {
          // Al termine dell'animazione, resetta e mostra il modal finale
          animWrap.style.display = 'none';
          cart = []; saveCart(); renderCart(); orderForm.reset();
          document.getElementById('success-modal').classList.add('active');
          if (data.ordine_id) document.getElementById('ordine-id').textContent = '#' + data.ordine_id;
        }
      });

      tl.from(".box-lid", { rotationX: -110, duration: 0.5, ease: "power2.out" }) // Apre il coperchio
        .to(".anim-pizza", { top: "40px", duration: 0.7, ease: "bounce.out" })   // La pizza cade dentro
        .to(".box-lid", { rotationX: 0, duration: 0.4, ease: "power4.in" }, "+=0.3") // Chiude il coperchio
        .to(".box-container", { scale: 0, opacity: 0, duration: 0.5, ease: "back.in(1.7)" }, "+=0.5"); // Sparisce

    } else {
      showToast('❌ ' + (data.message || 'Errore. Riprova.'));
    }
  })
        .catch(() => showToast('❌ Errore di connessione. Riprova.'))
        .finally(() => {
          btn.disabled    = false;
          btn.textContent = '🍕 Conferma Ordine';
        });
    });
  }

  // ── MODAL ─────────────────────────────────────────────────
  window.closeModal = function () {
    document.getElementById('success-modal').classList.remove('active');
  };

  // ── DELIVERY TOGGLE ───────────────────────────────────────
  window.toggleDelivery = function (val) {
    const f = document.getElementById('delivery-fields');
    if (f) f.style.display = val === 'consegna' ? 'block' : 'none';
  };

  // ── INIT ──────────────────────────────────────────────────
  renderCart();
});
