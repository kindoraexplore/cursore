(function() {
  document.body.classList.remove('no-js');

  const select = (q, el = document) => el.querySelector(q);
  const selectAll = (q, el = document) => Array.from(el.querySelectorAll(q));

  // Smooth scroll
  selectAll('[data-scroll]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = select(btn.getAttribute('data-scroll'));
      if (target) target.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // Page scroll progress bar
  (function initScrollProgress(){
    const bar = select('#scrollProgress .bar');
    if (!bar) return;
    function update() {
      const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
      const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const progress = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
      bar.style.width = progress + '%';
    }
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  })();

  // Sidebar toggle
  const sidebar = select('#sidebar');
  const menuToggle = select('#menuToggle');
  const menuClose = select('#menuClose');
  function setSidebar(open) {
    sidebar.classList.toggle('open', open);
    sidebar.setAttribute('aria-hidden', String(!open));
    menuToggle.setAttribute('aria-expanded', String(open));
    document.documentElement.style.overflow = open ? 'hidden' : '';
  }
  menuToggle?.addEventListener('click', () => setSidebar(true));
  menuClose?.addEventListener('click', () => setSidebar(false));
  sidebar?.addEventListener('click', (e) => {
    if (e.target === sidebar) setSidebar(false);
  });

  // Be Inspired dropdown
  const inspireBtn = select('#inspireBtn');
  const inspireScroll = select('#inspireScroll');
  function toggleInspire(force) {
    const show = typeof force === 'boolean' ? force : inspireScroll.hasAttribute('hidden');
    inspireScroll.toggleAttribute('hidden', !show);
    inspireBtn.setAttribute('aria-expanded', String(show));
  }
  inspireBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    toggleInspire();
  });
  document.addEventListener('click', (e) => {
    if (!inspireScroll?.contains(e.target) && e.target !== inspireBtn) {
      toggleInspire(false);
    }
  });

  // Enable left-click drag scroll
  function enableDragScroll(container) {
    let isDragging = false;
    let startX = 0;
    let scrollLeft = 0;
    let moved = false;

    container.addEventListener('mousedown', (e) => {
      if (e.button !== 0) return;
      isDragging = true; moved = false;
      startX = e.pageX; scrollLeft = container.scrollLeft;
      container.classList.add('dragging');
    });
    container.addEventListener('mousemove', (e) => {
      if (!isDragging) return;
      const walk = e.pageX - startX;
      if (Math.abs(walk) > 4) moved = true;
      container.scrollLeft = scrollLeft - walk;
    });
    const end = () => { isDragging = false; container.classList.remove('dragging'); setTimeout(() => moved = false, 0); };
    container.addEventListener('mouseup', end);
    container.addEventListener('mouseleave', end);

    selectAll('a', container).forEach(link => {
      link.addEventListener('click', (e) => { if (moved) { e.preventDefault(); e.stopImmediatePropagation(); } });
      // previous behavior: double click to navigate using data-href
      const href = link.getAttribute('data-href');
      if (href) {
        link.addEventListener('dblclick', () => { window.location.href = href; });
        link.addEventListener('click', (e) => { e.preventDefault(); });
      }
    });
  }
  selectAll('[data-drag-scroll]').forEach(enableDragScroll);
  if (inspireScroll) enableDragScroll(inspireScroll);

  // Search filter for popular places
  const searchInput = select('#placeSearch');
  const placeCards = selectAll('#popular .card-link');
  searchInput?.addEventListener('input', () => {
    const q = searchInput.value.trim().toLowerCase();
    placeCards.forEach(card => {
      const t = (card.getAttribute('data-title') || '').toLowerCase();
      card.style.display = t.includes(q) ? '' : 'none';
    });
  });

  // Slideshow
  (function initSlideshow(){
    const container = select('[data-slideshow]');
    if (!container) return;
    const slides = selectAll('.mySlides', container);
    const dots = selectAll('.dot');
    const intervalMs = Number(container.getAttribute('data-interval')) || 3000;
    let index = 0;
    let timer = null;
    let paused = false;

    function show(n) {
      index = (n + slides.length) % slides.length;
      slides.forEach((s, i) => {
        s.style.display = i === index ? 'block' : 'none';
        s.setAttribute('aria-hidden', String(i !== index));
      });
      dots.forEach((d, i) => {
        const active = i === index;
        d.classList.toggle('active', active);
        d.setAttribute('aria-selected', String(active));
      });
    }

    function next() { show(index + 1); }
    function prev() { show(index - 1); }

    function start() { stop(); timer = setInterval(next, intervalMs); }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }

    select('[data-next]', container)?.addEventListener('click', () => { next(); if (!paused) start(); });
    select('[data-prev]', container)?.addEventListener('click', () => { prev(); if (!paused) start(); });
    const pauseBtn = select('[data-pause]', container);
    pauseBtn?.addEventListener('click', () => {
      paused = !paused;
      pauseBtn.setAttribute('aria-pressed', String(paused));
      pauseBtn.textContent = paused ? '▶' : '❚❚';
      paused ? stop() : start();
    });

    dots.forEach((dot, i) => { dot.addEventListener('click', () => { show(i); if (!paused) start(); }); });
    container.addEventListener('mouseenter', stop);
    container.addEventListener('mouseleave', () => { if (!paused) start(); });

    show(0); start();
  })();

  // Counters with IntersectionObserver and rAF
  (function initCounters(){
    const counters = selectAll('.counter');
    if (!counters.length) return;
    const duration = 1200; // ms

    function animate(el) {
      const target = Number(el.getAttribute('data-target') || '0');
      const startVal = Number(el.textContent || '0');
      const startTime = performance.now();
      function step(now) {
        const t = Math.min(1, (now - startTime) / duration);
        const eased = 1 - Math.pow(1 - t, 3);
        const value = Math.round(startVal + (target - startVal) * eased);
        el.textContent = String(value);
        if (t < 1) requestAnimationFrame(step); else el.textContent = String(target);
      }
      requestAnimationFrame(step);
    }

    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animate(entry.target);
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });

    counters.forEach(c => observer.observe(c));
  })();

  // Reviews: star rating + localStorage
  (function initReviews(){
    const form = select('#reviewForm');
    const reviewsList = select('#reviews-list');
    const stars = selectAll('#starRating button');
    let selectedRating = 0;

    function setRating(n) {
      selectedRating = n;
      stars.forEach((s, i) => {
        const active = i < n;
        s.classList.toggle('active', active);
        s.setAttribute('aria-checked', String(active && i === n - 1));
      });
    }

    stars.forEach(btn => {
      btn.addEventListener('click', () => setRating(Number(btn.getAttribute('data-value'))));
      btn.addEventListener('keydown', (e) => {
        const val = Number(btn.getAttribute('data-value'));
        if (e.key === 'ArrowRight' || e.key === 'ArrowUp') { e.preventDefault(); setRating(Math.min(5, val + 1)); }
        if (e.key === 'ArrowLeft'  || e.key === 'ArrowDown') { e.preventDefault(); setRating(Math.max(1, val - 1)); }
      });
    });

    function renderReview({ name, rating, comment }) {
      const div = document.createElement('div');
      div.className = 'review-card';
      const starsText = '★★★★★'.slice(0, rating) + '☆☆☆☆☆'.slice(0, 5 - rating);
      div.innerHTML = `<h4>${escapeHtml(name)}</h4><p class="stars" aria-label="${rating} out of 5">${starsText}</p><p>${escapeHtml(comment)}</p>`;
      reviewsList.prepend(div);
    }

    function escapeHtml(s) { return s.replace(/[&<>"']/g, (c) => ({ '&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;','\'':'&#39;' }[c])); }

    function loadReviews() {
      try {
        const raw = localStorage.getItem('kindora_reviews') || '[]';
        const arr = JSON.parse(raw);
        arr.forEach(renderReview);
      } catch {}
    }

    function saveReview(r) {
      try {
        const raw = localStorage.getItem('kindora_reviews') || '[]';
        const arr = JSON.parse(raw);
        arr.unshift(r);
        localStorage.setItem('kindora_reviews', JSON.stringify(arr.slice(0, 100)));
      } catch {}
    }

    form?.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = (select('#name').value || '').trim();
      const comment = (select('#comment').value || '').trim();
      if (!name || !comment) return;
      if (!selectedRating) { alert('Please select a star rating!'); return; }
      const review = { name, rating: selectedRating, comment };
      renderReview(review);
      saveReview(review);
      form.reset(); setRating(0);
    });

    loadReviews();
  })();

  // FAQ accordion (one open at a time)
  (function initFaq(){
    const qs = selectAll('.faq-question');
    qs.forEach(btn => {
      btn.addEventListener('click', () => {
        qs.forEach(q => { if (q !== btn) { q.setAttribute('aria-expanded', 'false'); q.nextElementSibling?.setAttribute('hidden', ''); } });
        const open = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!open));
        const ans = btn.nextElementSibling; if (ans) ans.toggleAttribute('hidden', open);
      });
    });
  })();

  // Footer: subscribe mock
  (function initSubscribe(){
    const form = document.getElementById('subscribeForm');
    const input = document.getElementById('subscribeEmail');
    const note = document.getElementById('subscribeNote');
    form?.addEventListener('submit', (e) => {
      e.preventDefault();
      try {
        const listRaw = localStorage.getItem('kindora_subscribers') || '[]';
        const list = JSON.parse(listRaw);
        const email = String(input.value || '').trim();
        if (!email) return;
        list.push({ email, ts: Date.now() });
        localStorage.setItem('kindora_subscribers', JSON.stringify(list));
        input.value = '';
        note?.removeAttribute('hidden');
        setTimeout(() => note?.setAttribute('hidden', ''), 2500);
      } catch {}
    });
  })();

  // Back to top
  (function initBackToTop(){
    const btn = document.getElementById('backToTop');
    const onScroll = () => { btn?.toggleAttribute('hidden', window.scrollY < 600); };
    btn?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();

  // Reveal fade-ins
  (function initReveal(){
    const els = selectAll('.fade-in');
    const obs = new IntersectionObserver((entries, o) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); o.unobserve(e.target); } });
    }, { threshold: 0.2 });
    els.forEach(el => obs.observe(el));
  })();
})();
