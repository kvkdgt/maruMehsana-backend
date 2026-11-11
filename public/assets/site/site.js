(function () {
  'use strict';

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var toggle = document.querySelector('.nav-toggle');
  var panel = document.getElementById('nav-panel');
  var langMenu = document.getElementById('lang-menu');

  function safeJSON(id) {
    var el = document.getElementById(id);
    if (!el) return null;
    try { return JSON.parse(el.textContent); } catch (e) { return null; }
  }

  /* ---------------- Mobile menu ---------------- */
  function setMenu(open) {
    if (!toggle || !panel) return;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', toggle.getAttribute(open ? 'data-close' : 'data-open') || '');
    panel.classList.toggle('is-open', open);
  }
  if (toggle && panel) {
    toggle.addEventListener('click', function () {
      setMenu(toggle.getAttribute('aria-expanded') !== 'true');
    });
    panel.addEventListener('click', function (e) {
      if (e.target.closest('a')) setMenu(false);
    });
  }

  /* ---------------- Language menu: close on outside click / Escape ---------------- */
  document.addEventListener('click', function (e) {
    if (langMenu && langMenu.open && !langMenu.contains(e.target)) langMenu.open = false;
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (langMenu && langMenu.open) { langMenu.open = false; var s = langMenu.querySelector('summary'); if (s) s.focus(); }
    setMenu(false);
  });

  /* ---------------- "Add your business" enquiry popup ---------------- */
  var enq = document.getElementById('enquiry');
  var enqForm = document.getElementById('enq-form');
  if (enq && enqForm && typeof enq.showModal === 'function') {
    var enqMsg = document.getElementById('enq-msg');
    var enqBtn = document.getElementById('enq-submit');
    var enqBtnText = enqBtn.textContent;
    var mobile = enqForm.elements.mobile_no;
    var whatsapp = enqForm.elements.whatsapp_no;
    var same = document.getElementById('enq-same');

    function syncWhatsapp() {
      if (same.checked) whatsapp.value = mobile.value;
      whatsapp.readOnly = same.checked;
    }
    function digitsOnly(input) { input.value = input.value.replace(/\D/g, '').slice(0, 10); }
    function setMsg(text, ok) {
      enqMsg.textContent = text || '';
      enqMsg.className = 'enq-msg' + (text ? (ok ? ' is-ok' : ' is-bad') : '');
    }
    function clearErrors() {
      [].forEach.call(enqForm.querySelectorAll('[data-err]'), function (p) { p.textContent = ''; });
      [].forEach.call(enqForm.querySelectorAll('[aria-invalid]'), function (i) { i.removeAttribute('aria-invalid'); });
    }
    function showErrors(errors) {
      var first = null;
      Object.keys(errors).forEach(function (name) {
        var p = enqForm.querySelector('[data-err="' + name + '"]');
        var input = enqForm.elements[name];
        if (p) p.textContent = [].concat(errors[name])[0];
        if (input) { input.setAttribute('aria-invalid', 'true'); if (!first) first = input; }
      });
      if (first) first.focus();
    }

    mobile.addEventListener('input', function () { digitsOnly(mobile); syncWhatsapp(); });
    whatsapp.addEventListener('input', function () { digitsOnly(whatsapp); });
    same.addEventListener('change', syncWhatsapp);
    syncWhatsapp();

    var enqFields = document.getElementById('enq-fields');
    var enqDone = document.getElementById('enq-done');
    function showDone(text) {
      document.getElementById('enq-done-text').textContent = text;
      enqFields.hidden = true;
      enqDone.hidden = false;
      enqDone.querySelector('button').focus();
    }

    [].forEach.call(document.querySelectorAll('[data-open-enquiry]'), function (b) {
      b.addEventListener('click', function () {
        // Start fresh each time the popup opens (after a previous success)
        enqFields.hidden = false;
        enqDone.hidden = true;
        setMsg('');
        if (!enq.open) enq.showModal();
        enqForm.elements.business_name.focus();
      });
    });
    [].forEach.call(enq.querySelectorAll('[data-close-enquiry]'), function (b) {
      b.addEventListener('click', function () { enq.close(); });
    });
    // Click on the dark backdrop closes the popup
    enq.addEventListener('click', function (e) { if (e.target === enq) enq.close(); });

    // Other pages link to /#add-business to open this popup directly
    if (window.location.hash === '#add-business') {
      var opener = document.querySelector('[data-open-enquiry]');
      if (opener) opener.click();
      if (window.history.replaceState) window.history.replaceState(null, '', window.location.pathname + window.location.search);
    }

    enqForm.addEventListener('submit', function (e) {
      e.preventDefault();
      clearErrors();
      setMsg('');
      syncWhatsapp();
      enqBtn.disabled = true;
      enqBtn.textContent = enqForm.getAttribute('data-sending');

      window.fetch(enqForm.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(enqForm),
        credentials: 'same-origin'
      }).then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (body) { return { status: res.status, body: body }; });
      }).then(function (r) {
        if (r.status === 201) {
          enqForm.reset();
          syncWhatsapp();
          showDone(r.body.message);
        } else if (r.status === 422 && r.body.errors) {
          showErrors(r.body.errors);
        } else if (r.status === 429) {
          setMsg(enqForm.getAttribute('data-err-many'), false);
        } else {
          setMsg(enqForm.getAttribute('data-err-generic'), false);
        }
      }).catch(function () {
        setMsg(enqForm.getAttribute('data-err-generic'), false);
      }).then(function () {
        enqBtn.disabled = false;
        enqBtn.textContent = enqBtnText;
      });
    });
  }

  /* ---------------- Facts rotator ---------------- */
  var factEl = document.getElementById('fact-text');
  var factBtn = document.getElementById('fact-next');
  var facts = safeJSON('fact-data') || [];
  if (factEl && factBtn && facts.length > 1) {
    var fi = 0;
    factBtn.addEventListener('click', function () {
      fi = (fi + 1) % facts.length;
      factEl.classList.add('is-swapping');
      window.setTimeout(function () {
        factEl.textContent = facts[fi];
        factEl.classList.remove('is-swapping');
      }, reduce ? 0 : 200);
    });
  }

  /* ---------------- QR code (library loaded lazily; hidden if it fails) ---------------- */
  var qrBox = document.getElementById('qr');
  var qrHost = document.getElementById('qr-code');
  function buildQR() {
    if (!qrBox || !qrHost || qrBox.getAttribute('data-started')) return;
    qrBox.setAttribute('data-started', '1');
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    s.integrity = 'sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==';
    s.crossOrigin = 'anonymous';
    s.referrerPolicy = 'no-referrer';
    s.async = true;
    s.onload = function () {
      try {
        new window.QRCode(qrHost, {
          text: qrBox.getAttribute('data-url'),
          width: 180, height: 180,
          colorDark: '#0f2447', colorLight: '#ffffff',
          correctLevel: window.QRCode.CorrectLevel.M
        });
        qrHost.removeAttribute('title');
        qrBox.hidden = false;
      } catch (e) { qrBox.hidden = true; }
    };
    s.onerror = function () { qrBox.hidden = true; };
    document.head.appendChild(s);
  }
  if (qrBox) {
    if ('IntersectionObserver' in window) {
      var qio = new IntersectionObserver(function (entries) {
        if (entries.some(function (e) { return e.isIntersecting; })) { qio.disconnect(); buildQR(); }
      }, { rootMargin: '400px 0px' });
      qio.observe(qrBox.parentNode);
    } else {
      window.addEventListener('load', buildQR);
    }
  }

  /* ---------------- Live search (existing public JSON API, no new endpoints) ---------------- */
  var form = document.getElementById('search');
  var cfg = safeJSON('search-i18n');
  if (!form || !cfg) return;

  var input = document.getElementById('search-input');
  var box = document.getElementById('search-panel');
  var list = document.getElementById('search-results');
  var msg = document.getElementById('search-msg');
  var status = document.getElementById('search-status');
  var clearBtn = document.getElementById('search-clear');
  var goBtn = document.getElementById('search-go');

  var SVGNS = 'http://www.w3.org/2000/svg';
  var ICON = { category: 'i-store', business: 'i-store', place: 'i-pin', news: 'i-news', job: 'i-briefcase' };
  var timer = 0, seq = 0, ctl = null, uid = 0;
  var options = [];        // [{el, href, onPick}]
  var active = -1;
  var lastGroups = null;   // remembered so a category view can go back

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function icon(name) {
    var s = document.createElementNS(SVGNS, 'svg');
    s.setAttribute('class', 'icon');
    s.setAttribute('aria-hidden', 'true');
    var u = document.createElementNS(SVGNS, 'use');
    u.setAttribute('href', '#' + name);
    s.appendChild(u);
    return s;
  }
  function open(isOpen) {
    box.hidden = !isOpen;
    input.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    if (!isOpen) { active = -1; input.removeAttribute('aria-activedescendant'); }
  }
  function say(text) { status.textContent = text || ''; }
  function showMessage(text) {
    list.textContent = '';
    options = []; active = -1;
    msg.textContent = text;
    msg.hidden = !text;
    open(true);
  }
  function setActive(i, scroll) {
    if (options[active]) { options[active].el.setAttribute('aria-selected', 'false'); }
    active = i;
    if (options[active]) {
      var o = options[active].el;
      o.setAttribute('aria-selected', 'true');
      input.setAttribute('aria-activedescendant', o.id);
      if (scroll) o.scrollIntoView({ block: 'nearest' });
    } else {
      input.removeAttribute('aria-activedescendant');
    }
  }
  function addOption(parent, item) {
    var node = item.href ? el('a', 'search-opt') : el('button', 'search-opt');
    if (item.href) node.href = item.href; else node.type = 'button';
    node.id = 'so-' + (++uid);
    node.setAttribute('role', 'option');
    node.setAttribute('aria-selected', 'false');
    node.tabIndex = -1;

    var oi = el('span', 'oi' + (item.type === 'news' || item.type === 'job' ? ' is-saffron' : ''));
    oi.appendChild(icon(item.icon || ICON[item.type] || 'i-search'));
    var text = el('span', 'ot', item.title);
    if (item.sub) text.appendChild(el('span', 'os', item.sub));
    node.appendChild(oi);
    node.appendChild(text);
    if (item.pick) {
      var chev = icon('i-chev-r');
      chev.setAttribute('class', 'icon oc');
      node.appendChild(chev);
    }

    var rec = { el: node, onPick: item.pick || null, href: item.href || null };
    var idx = options.length;
    options.push(rec);
    node.addEventListener('mouseenter', function () { setActive(idx, false); });
    if (item.pick) node.addEventListener('click', function (e) { e.preventDefault(); item.pick(); });
    parent.appendChild(node);
  }
  function renderGroups(groups, header) {
    list.textContent = '';
    options = []; active = -1;
    input.removeAttribute('aria-activedescendant');
    msg.hidden = true;

    if (header) {
      var back = el('li', null);
      back.setAttribute('role', 'presentation');
      var b = el('button', 'search-back');
      b.type = 'button';
      b.id = 'so-' + (++uid);
      b.setAttribute('role', 'option');
      b.setAttribute('aria-selected', 'false');
      b.tabIndex = -1;
      b.appendChild(icon('i-back'));
      b.appendChild(el('span', null, header.label));
      var rec = { el: b, onPick: header.pick };
      options.push(rec);
      b.addEventListener('mouseenter', function () { setActive(0, false); });
      b.addEventListener('click', function (e) { e.preventDefault(); header.pick(); });
      back.appendChild(b);
      list.appendChild(back);
    }

    groups.forEach(function (g) {
      if (!g.items.length) return;
      var li = el('li', 'search-group');
      li.setAttribute('role', 'group');
      var tid = 'sg-' + (++uid);
      li.setAttribute('aria-labelledby', tid);
      var t = el('div', 'search-group-title', g.title);
      t.id = tid;
      t.setAttribute('role', 'presentation');
      li.appendChild(t);
      g.items.forEach(function (it) { addOption(li, it); });
      list.appendChild(li);
    });
    open(true);
  }

  function getJSON(url, signal) {
    return fetch(url, { headers: { 'Accept': 'application/json' }, signal: signal, credentials: 'omit' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
  }
  function cap(arr, n) { return (arr || []).slice(0, n); }
  function fill(str, vars) {
    return String(str).replace(/:(\w+)/g, function (m, k) { return vars[k] != null ? vars[k] : m; });
  }

  function search(q) {
    q = q.trim();
    if (q.length < 2) { showMessage(cfg.minHint); say(''); return; }
    if (ctl) ctl.abort();
    ctl = ('AbortController' in window) ? new AbortController() : null;
    var my = ++seq;
    var sig = ctl ? ctl.signal : undefined;
    showMessage(cfg.loading);
    say(cfg.loading);
    input.setAttribute('aria-busy', 'true');

    var enc = encodeURIComponent(q);
    var newsQ = encodeURIComponent(q.replace(/[\/\\?#%]+/g, ' ').trim());
    var calls = [
      getJSON(cfg.api + '/search?query=' + enc, sig),
      getJSON(cfg.api + '/news/search/' + newsQ + '?per_page=3', sig),
      getJSON(cfg.api + '/jobs?search=' + enc + '&per_page=3', sig)
    ];
    Promise.allSettled(calls).then(function (res) {
      if (my !== seq) return;               // a newer search superseded this one
      input.removeAttribute('aria-busy');
      var ok = res.filter(function (r) { return r.status === 'fulfilled'; });
      if (!ok.length) {
        if (res.some(function (r) { return r.reason && r.reason.name === 'AbortError'; })) return;
        showMessage(cfg.error); say(cfg.error); return;
      }

      var mixed = res[0].status === 'fulfilled' && Array.isArray(res[0].value) ? res[0].value : [];
      var newsRows = res[1].status === 'fulfilled' && res[1].value && Array.isArray(res[1].value.data) ? res[1].value.data : [];
      var jobRows = res[2].status === 'fulfilled' && res[2].value && Array.isArray(res[2].value.data) ? res[2].value.data : [];

      var cats = [], biz = [], places = [];
      mixed.forEach(function (r) {
        if (!r || r.id == null || !r.name) return;
        if (r.type === 'Category') {
          cats.push({ type: 'category', title: String(r.name), sub: cfg.catLabel, pick: function () { openCategory(r.id, String(r.name)); } });
        } else if (r.type === 'Business') {
          biz.push({ type: 'business', title: String(r.name), href: cfg.base + '/business/' + encodeURIComponent(r.id) });
        } else if (r.type === 'Tourist Place') {
          places.push({ type: 'place', title: String(r.name), href: cfg.base + '/place/' + encodeURIComponent(r.id) });
        }
      });
      var news = newsRows.filter(function (r) { return r && r.id != null && r.title; }).map(function (r) {
        return { type: 'news', title: String(r.title), sub: r.agency && r.agency.name ? String(r.agency.name) : '', href: cfg.base + '/news/' + encodeURIComponent(r.id) };
      });
      var jobs = jobRows.filter(function (r) { return r && r.id != null && r.title; }).map(function (r) {
        var sub = [r.location, r.company_name].filter(Boolean).join(' · ');
        return { type: 'job', title: String(r.title), sub: sub, href: cfg.base + '/job/' + encodeURIComponent(r.id) };
      });

      var groups = [
        { title: cfg.groups.category, items: cap(cats, 3) },
        { title: cfg.groups.business, items: cap(biz, 5) },
        { title: cfg.groups.place, items: cap(places, 4) },
        { title: cfg.groups.news, items: cap(news, 3) },
        { title: cfg.groups.job, items: cap(jobs, 3) }
      ];
      var total = groups.reduce(function (n, g) { return n + g.items.length; }, 0);
      if (!total) { lastGroups = null; showMessage(cfg.none); say(cfg.none); return; }
      lastGroups = groups;
      renderGroups(groups);
      say(fill(cfg.found, { count: total }));
    });
  }

  function openCategory(id, name) {
    if (ctl) ctl.abort();
    ctl = ('AbortController' in window) ? new AbortController() : null;
    var my = ++seq;
    input.value = name;
    syncClear();
    showMessage(cfg.loading);
    say(cfg.loading);
    // isFromTrendingCategory=true makes the API skip its category-visit counter.
    getJSON(cfg.api + '/businesses/get?category_id=' + encodeURIComponent(id) + '&limit=8&isFromTrendingCategory=true', ctl ? ctl.signal : undefined)
      .then(function (r) {
        if (my !== seq) return;
        var rows = (r && Array.isArray(r.data)) ? r.data : [];
        var items = rows.filter(function (b) { return b && b.id != null && b.name; }).map(function (b) {
          return { type: 'business', title: String(b.name), href: cfg.base + '/business/' + encodeURIComponent(b.id) };
        });
        var head = lastGroups ? { label: cfg.catBack, pick: function () { input.value = ''; syncClear(); input.focus(); showPrev(); } } : null;
        if (!items.length) {
          renderGroups([], head);
          msg.textContent = cfg.catEmpty; msg.hidden = false;
          say(cfg.catEmpty);
          return;
        }
        renderGroups([{ title: fill(cfg.catTitle, { name: name }), items: items }], head);
        say(fill(cfg.found, { count: items.length }));
      })
      .catch(function (err) {
        if (my !== seq || (err && err.name === 'AbortError')) return;
        showMessage(cfg.error); say(cfg.error);
      });
  }
  function showPrev() { if (lastGroups) { renderGroups(lastGroups); } else { open(false); } }

  function syncClear() { clearBtn.hidden = !input.value; }

  input.addEventListener('input', function () {
    syncClear();
    window.clearTimeout(timer);
    var v = input.value.trim();
    if (v.length < 2) {
      seq++; if (ctl) ctl.abort();
      open(false); say('');
      return;
    }
    timer = window.setTimeout(function () { search(v); }, 260);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    window.clearTimeout(timer);
    if (options[active] && !box.hidden) { pick(options[active]); return; }
    search(input.value);
  });
  goBtn.addEventListener('click', function () { /* submit handler does the work */ });

  function pick(o) {
    if (o.onPick) o.onPick();
    else if (o.href) window.location.href = o.href;
  }

  input.addEventListener('keydown', function (e) {
    var n = options.length;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (box.hidden) { if (input.value.trim().length >= 2) search(input.value); return; }
      if (n) setActive(active + 1 >= n ? 0 : active + 1, true);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (n && !box.hidden) setActive(active - 1 < 0 ? n - 1 : active - 1, true);
    } else if (e.key === 'Escape') {
      if (!box.hidden) { e.preventDefault(); e.stopPropagation(); open(false); }
      else if (input.value) { input.value = ''; syncClear(); }
    } else if (e.key === 'Enter') {
      if (options[active] && !box.hidden) { e.preventDefault(); pick(options[active]); }
    }
  });
  input.addEventListener('focus', function () {
    if (options.length && input.value.trim().length >= 2) open(true);
  });

  clearBtn.addEventListener('click', function () {
    input.value = ''; syncClear(); seq++; if (ctl) ctl.abort();
    open(false); say(''); input.focus();
  });

  // Close when focus or a click leaves the search form.
  document.addEventListener('click', function (e) { if (!form.contains(e.target) && !e.target.closest('.chip')) open(false); });
  form.addEventListener('focusout', function () {
    window.setTimeout(function () { if (!form.contains(document.activeElement)) open(false); }, 0);
  });

  // Popular-search chips: fill the box and show that category's businesses.
  [].forEach.call(document.querySelectorAll('.chip'), function (chip) {
    chip.addEventListener('click', function () {
      window.clearTimeout(timer);
      var cat = chip.getAttribute('data-category');
      var name = chip.getAttribute('data-q') || '';
      input.value = name; syncClear();
      lastGroups = null;
      if (cat) openCategory(cat, name); else search(name);
    });
  });
})();

/* ---------------- Businesses page: infinite scroll ----------------
   Page 1 of /businesses carries data-next on the grid. We fetch the same URL as JSON
   ({ html, next, shown, total }) and append the cards. Without JS the numeric pager stays. */
(function () {
  'use strict';

  var grid = document.querySelector('.dir-grid[data-next]');
  var box = document.querySelector('[data-dir-more]');
  if (!grid || !box || !window.fetch || !('content' in document.createElement('template'))) return;

  var next = grid.getAttribute('data-next');
  var pager = document.querySelector('.pager');
  var sentinel = box.querySelector('.dir-sentinel');
  var loading = box.querySelector('.dir-loading');
  var btn = box.querySelector('.dir-more-btn');
  var end = box.querySelector('.dir-end');
  var status = box.querySelector('[data-dir-status]');
  var busy = false;
  var io = null;
  var seen = {};

  // Cards are keyed by their business link so a card never shows twice (e.g. if the list changed meanwhile).
  function key(li) {
    var a = li.querySelector && li.querySelector('a.stretched');
    return a ? a.getAttribute('href') : null;
  }
  [].forEach.call(grid.children, function (li) { var k = key(li); if (k) seen[k] = true; });

  function showButton(label) {
    btn.textContent = label;
    btn.hidden = false;
  }

  function finish() {
    next = null;
    if (io) io.disconnect();
    loading.hidden = true;
    btn.hidden = true;
    end.hidden = false;
    status.textContent = end.textContent;
  }

  function load() {
    if (busy || !next) return;
    busy = true;
    loading.hidden = false;
    btn.hidden = true;
    status.textContent = loading.textContent;

    fetch(next, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) {
        var tpl = document.createElement('template');
        tpl.innerHTML = (data && data.html) || '';
        [].slice.call(tpl.content.children).forEach(function (li) {
          var k = key(li);
          if (k && seen[k]) return;
          if (k) seen[k] = true;
          grid.appendChild(li);
        });
        next = (data && data.next) || null;
        busy = false;
        loading.hidden = true;
        if (!next) { finish(); return; }
        status.textContent = (box.getAttribute('data-showing') || '').replace('__SHOWN__', grid.children.length);
        if (io) { io.unobserve(sentinel); io.observe(sentinel); } // re-check: still near the end on tall screens
        else showButton(box.getAttribute('data-load'));
      })
      .catch(function () {
        busy = false;
        loading.hidden = true;
        showButton(box.getAttribute('data-retry'));
        status.textContent = btn.textContent;
      });
  }

  btn.addEventListener('click', load);
  if (pager) pager.hidden = true;
  box.hidden = false;

  if ('IntersectionObserver' in window) {
    io = new IntersectionObserver(function (entries) {
      if (entries.some(function (e) { return e.isIntersecting; })) load();
    }, { rootMargin: '0px 0px 600px 0px' });
    io.observe(sentinel);
  } else {
    showButton(box.getAttribute('data-load'));
  }
})();

/* Business detail photo lightbox (native <dialog>) */
(function () {
  'use strict';
  var dlg = document.getElementById('lb');
  if (!dlg || typeof dlg.showModal !== 'function') return;
  var photos;
  try { photos = JSON.parse(dlg.getAttribute('data-photos') || '[]'); } catch (e) { photos = []; }
  if (!photos.length) return;
  var img = dlg.querySelector('.lb-img');
  var count = dlg.querySelector('.lb-count');
  var idx = 0;
  if (photos.length < 2) dlg.classList.add('single');

  function show(i) {
    idx = (i + photos.length) % photos.length;
    img.src = photos[idx];
    count.textContent = (idx + 1) + ' / ' + photos.length;
    dlg.classList.toggle('single', photos.length < 2);
  }
  function open(i) {
    show(i);
    if (!dlg.open) dlg.showModal();
    document.body.classList.add('lb-open');
  }
  function close() { if (dlg.open) dlg.close(); }

  document.addEventListener('click', function (e) {
    var t = e.target.closest && e.target.closest('[data-lb]');
    if (t) { e.preventDefault(); open(parseInt(t.getAttribute('data-lb'), 10) || 0); }
  });
  dlg.addEventListener('close', function () { document.body.classList.remove('lb-open'); img.removeAttribute('src'); });
  dlg.addEventListener('click', function (e) {
    if (e.target.closest('[data-lb-close]')) return close();
    if (e.target.closest('[data-lb-prev]')) return show(idx - 1);
    if (e.target.closest('[data-lb-next]')) return show(idx + 1);
    if (e.target === dlg) close();
  });
  dlg.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft') show(idx - 1);
    else if (e.key === 'ArrowRight') show(idx + 1);
  });
  img.addEventListener('error', function () {
    if (!img.getAttribute('src')) return;
    if (photos.length > 1) { photos.splice(idx, 1); show(idx); }
    else close();
  });
  var x0 = null;
  dlg.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  dlg.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0;
    x0 = null;
    if (Math.abs(dx) > 50) show(idx + (dx < 0 ? 1 : -1));
  });
})();
