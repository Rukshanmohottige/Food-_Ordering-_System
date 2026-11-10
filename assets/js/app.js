document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss flash banners after 5 seconds
  document.querySelectorAll('.alert[data-auto-dismiss="true"]').forEach((el) => {
    setTimeout(() => {
      el.style.transition = 'opacity 0.3s ease';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 300);
    }, 5000);
  });

  // Quantity stepper buttons (- / +)
  document.querySelectorAll('[data-qty-stepper]').forEach((stepper) => {
    const input = stepper.querySelector('input[type="number"]');
    const minus = stepper.querySelector('[data-qty-minus]');
    const plus = stepper.querySelector('[data-qty-plus]');
    if (!input) return;

    const clamp = (val) => {
      const min = parseInt(input.min || '1', 10);
      const max = parseInt(input.max || '50', 10);
      return Math.max(min, Math.min(max, val));
    };

    if (minus) {
      minus.addEventListener('click', () => {
        input.value = String(clamp((parseInt(input.value, 10) || 1) - 1));
      });
    }
    if (plus) {
      plus.addEventListener('click', () => {
        input.value = String(clamp((parseInt(input.value, 10) || 1) + 1));
      });
    }
  });

  // Menu search & category live filter
  const searchInput = document.getElementById('menuSearch');
  const categoryButtons = document.querySelectorAll('[data-filter-category]');
  const dishCards = document.querySelectorAll('[data-dish-card]');
  const emptyNotice = document.getElementById('menuEmptyFilter');

  if (dishCards.length > 0 && (searchInput || categoryButtons.length > 0)) {
    let activeCategory = 'all';

    const applyFilters = () => {
      const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
      let visible = 0;

      dishCards.forEach((card) => {
        const cat = (card.getAttribute('data-category') || '').toLowerCase();
        const text = (card.getAttribute('data-search') || '').toLowerCase();
        const catMatch = activeCategory === 'all' || cat === activeCategory;
        const textMatch = query === '' || text.includes(query);
        const show = catMatch && textMatch;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      if (emptyNotice) {
        emptyNotice.style.display = visible === 0 ? '' : 'none';
      }
    };

    if (searchInput) {
      searchInput.addEventListener('input', applyFilters);
    }

    categoryButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        categoryButtons.forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        activeCategory = (btn.getAttribute('data-filter-category') || 'all').toLowerCase();
        applyFilters();
      });
    });
  }

  // Show / Hide password toggle
  document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-toggle-password');
      const input = targetId ? document.getElementById(targetId) : null;
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.textContent = show ? 'Hide' : 'Show';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
});
