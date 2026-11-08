/* FastSouq theme — small vanilla script: menus, hero slider, favorites, modals, filters, post form. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var T = (window.QS && QS.txt) || {};

  /* Toasts */
  function toast(msg) {
    var box = $('#toasts'); if (!box || !msg) return;
    var el = document.createElement('div'); el.className = 'toast'; el.textContent = msg;
    box.appendChild(el); requestAnimationFrame(function () { el.classList.add('show'); });
    setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 300); }, 2800);
  }

  /* Modals from <template id="tpl-x"> */
  function openModal(id) {
    var tpl = document.getElementById('tpl-' + id), m = $('#modal'); if (!tpl || !m) return;
    m.innerHTML = '<div class="m-bg" data-act="mclose"></div><div class="m-box ' + (tpl.dataset.cls || '') + '" role="dialog" aria-modal="true"><button class="m-x" data-act="mclose" aria-label="close">×</button></div>';
    m.querySelector('.m-box').appendChild(tpl.content.cloneNode(true));
    m.hidden = false; document.body.classList.add('noscroll');
    requestAnimationFrame(function () { m.classList.add('show'); });
  }
  function closeModal() { var m = $('#modal'); if (!m) return; m.classList.remove('show'); m.hidden = true; m.innerHTML = ''; document.body.classList.remove('noscroll'); }
  function toggleDrawer(force) {
    var d = $('#drawer'); if (!d) return;
    d.hidden = force !== undefined ? !force : !d.hidden; document.body.classList.toggle('noscroll', !d.hidden);
  }

  /* Favorites in a cookie (ids), so they also work for visitors */
  function favs() { var m = document.cookie.match(/(?:^|; )qs_favs=([^;]*)/); return m ? decodeURIComponent(m[1]).split(',').filter(Boolean) : []; }
  function setFavs(a) { document.cookie = 'qs_favs=' + encodeURIComponent(a.join(',')) + ';path=/;max-age=31536000;samesite=lax'; }
  function toggleFav(id) {
    var a = favs(), i = a.indexOf(id), on = i < 0;
    if (on) a.unshift(id); else a.splice(i, 1);
    setFavs(a.slice(0, 200));
    $$('[data-fav="' + id + '"]').forEach(function (b) {
      b.classList.toggle('on', on);
      var sp = b.querySelector('span'); if (sp) sp.textContent = on ? T.in_fav : T.add_fav;
    });
    toast(on ? T.fav_added : T.fav_rm);
  }

  /* Hero slider */
  var slideI = 0, timer = null;
  function slide(d, to) {
    var s = $$('.hero .slide'); if (!s.length) return;
    slideI = to !== undefined ? to : (slideI + d + s.length) % s.length;
    s.forEach(function (x, i) { x.classList.toggle('on', i === slideI); });
    $$('.hero .dots button').forEach(function (x, i) { x.classList.toggle('on', i === slideI); });
  }


  document.addEventListener('click', function (e) {
    var th = e.target.closest('.gal-thumbs .th');
    if (th) { var img = $('#galMain img.ph'); if (img) img.src = th.dataset.img; $$('.gal-thumbs .th').forEach(function (x) { x.classList.toggle('on', x === th); }); return; }
    var fav = e.target.closest('[data-fav]');
    if (fav) { e.preventDefault(); e.stopPropagation(); toggleFav(fav.dataset.fav); return; }
    var mod = e.target.closest('[data-modal]');
    if (mod) { e.preventDefault(); openModal(mod.dataset.modal); return; }
    var a = e.target.closest('[data-act]');
    if (!a) {
      var pop = $('#userPop'); if (pop && !pop.hidden && !e.target.closest('.pop-wrap')) pop.hidden = true;
      return;
    }
    switch (a.dataset.act) {
      case 'drawer': e.preventDefault(); toggleDrawer(); break;
      case 'umenu': { var p = $('#userPop'); if (p) p.hidden = !p.hidden; break; }
      case 'mclose': closeModal(); break;
      case 'back': e.preventDefault(); if (history.length > 1 && document.referrer.indexOf(location.host) > -1) history.back(); else location.href = '/'; break;
      case 'slide': clearInterval(timer); slide(+a.dataset.d); break;
      case 'slide-to': clearInterval(timer); slide(0, +a.dataset.i); break;
      case 'toggle-filters': { var f = $('#filters'); if (f) f.classList.toggle('open'); break; }
      case 'locate':
        if (navigator.geolocation && pickMap) navigator.geolocation.getCurrentPosition(function (p) { var ll = L.latLng(p.coords.latitude, p.coords.longitude); pickMap.setView(ll, 15); setPin(pickMap, ll, a.closest('form')); });
        break;
      case 'reveal': {
        var sp = a.querySelector('span');
        if (sp && !a.dataset.shown) { e.preventDefault(); sp.textContent = a.dataset.phone; sp.classList.add('ltr'); a.dataset.shown = 1; }
        break;
      }
      case 'share': {
        e.preventDefault();
        if (navigator.share) navigator.share({ title: document.title, url: location.href }).catch(function () {});
        else if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(function () { toast(T.copied); });
        break;
      }
    }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeModal(); toggleDrawer(false); } });

  /* Chips that wrap radio / checkbox inputs */
  function syncChips(root) {
    $$('label.chip, label.kind-card, label.type-card', root).forEach(function (l) {
      var i = l.querySelector('input'); if (i) l.classList.toggle('on', i.checked);
    });
  }
  document.addEventListener('change', function (e) {
    var t = e.target;
    if (t.closest('label.chip, label.kind-card, label.type-card')) syncChips(t.form || document);
    var f = t.form;
    if (f && f.classList.contains('filters') && t.tagName === 'SELECT' && f.querySelector('select[data-dep="' + t.name + '"]')) { var d = f.querySelector('select[data-dep="' + t.name + '"]'); if (d) d.value = ''; }
    if (f && f.hasAttribute('data-autosubmit') && t.type !== 'number') { f.submit(); }
    if (f && f.hasAttribute('data-postform')) { if (t.name === 'section') postForm(f); else deps(f); }
    if (t.name === 'kind' && t.form) {
      var lbl = $('[data-name-label]', t.form); if (lbl) lbl.textContent = t.value === 'company' ? lbl.dataset.company : lbl.dataset.person;
    }
    if (t.matches('[data-photos]')) previews(t);
    if (t.matches('.up-btn input[type=file]') && t.files[0]) { var s = t.parentNode.querySelector('span'); if (s) s.textContent = t.files[0].name; }
  });
  /* Sort select lives outside the filter form (form= attribute) */
  $$('select[form="filters"]').forEach(function (s) { s.addEventListener('change', function () { var f = $('#filters'); if (f) f.submit(); }); });
  $$('form[data-confirm]').forEach(function (f) { f.addEventListener('submit', function (e) { if (!confirm(T.del)) e.preventDefault(); }); });

  /* Dependent selects: <select data-dep="parent-field"> keeps only options whose data-p matches the parent value. */
  function liveField(form, name) { return $$('[name="' + name + '"]', form).filter(function (i) { return !i.disabled && (i.type !== 'radio' || i.checked); })[0]; }
  function deps(form) {
    $$('select[data-dep]', form).forEach(function (sel) {
      var gw = sel.closest('[data-g],[data-sec]'); if (gw && gw.dataset.gHidden === '1') return;
      var par = liveField(form, sel.dataset.dep), pv = par ? par.value : '', n = 0;
      $$('option[data-p]', sel).forEach(function (o) { var ok = pv !== '' && o.dataset.p === pv; o.hidden = !ok; o.disabled = !ok; if (ok) n++; });
      if (sel.selectedOptions[0] && sel.selectedOptions[0].disabled) sel.value = '';
      var w = sel.closest('[data-hide-empty]'); if (w) { w.hidden = !n; sel.disabled = !n; }
    });
    var sub = liveField(form, 'sub'), sv = sub ? sub.value : '';
    $$('[data-subs]', form).forEach(function (el) {
      if (el.dataset.gHidden) return;
      var show = (' ' + el.dataset.subs + ' ').indexOf(' ' + sv + ' ') > -1; el.hidden = !show; $$('input', el).forEach(function (i) { i.disabled = !show; });
    });
    var mk = liveField(form, 'make'), dl = $('#qs-models'), data = $('#qs-models-data');
    if (dl && data) { var m = JSON.parse(data.textContent || '{}')[mk ? mk.value : ''] || []; dl.innerHTML = m.map(function (x) { return '<option value="' + String(x).replace(/"/g, '&quot;') + '">'; }).join(''); }
  }
  /* Post form: show the fields of the chosen section / group */
  function postForm(f) {
    var secIn = $('input[name=section]:checked', f), sec = secIn ? secIn.value : '', grp = secIn ? secIn.dataset.group : '';
    $$('[data-after-type]', f).forEach(function (c) { c.hidden = !sec; });
    $$('[data-sec],[data-g]', f).forEach(function (el) {
      var show = el.dataset.sec ? el.dataset.sec === sec : el.dataset.g === grp;
      el.hidden = !show; el.dataset.gHidden = show ? '' : '1';
      $$('input,select,textarea', el).forEach(function (i) { i.disabled = !show; });
    });
    deps(f);
  }
  $$('form[data-postform]').forEach(function (f) {
    postForm(f); syncChips(f);
    f.addEventListener('submit', function (e) {
      var bad = null;
      $$('[data-required]', f).forEach(function (el) {
        if (el.hidden) return;
        var ins = $$('input:not([type=hidden]),select,textarea', el).filter(function (i) { return !i.disabled; }), ok;
        if (!ins.length) return;
        if (ins[0].type === 'radio' || ins[0].type === 'checkbox') ok = ins.some(function (i) { return i.checked; });
        else ok = ins.every(function (i) { return String(i.value).trim() !== ''; });
        el.classList.toggle('err', !ok); if (!ok && !bad) bad = el;
      });
      var agree = $('input[name=agree]', f);
      if (agree && !agree.checked) { agree.closest('.check').classList.add('err'); bad = bad || agree; }
      var msg = $('[data-form-err]', f); if (msg) msg.hidden = !bad;
      if (bad) { e.preventDefault(); bad.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    });
  });
  /* Filter forms use dependent selects too (make → model, emirate → city, sub → item) */
  $$('form.filters').forEach(deps);

  /* Maps (OpenStreetMap via Leaflet): show a pin, or let the advertiser drop one */
  var pickMap = null, pickMarker = null;
  function setPin(map, latlng, form) {
    if (pickMarker) pickMarker.setLatLng(latlng); else pickMarker = L.marker(latlng, { draggable: true }).addTo(map).on('dragend', function (ev) { setPin(map, ev.target.getLatLng(), form); });
    if (form) { form.querySelector('input[name=lat]').value = latlng.lat.toFixed(6); form.querySelector('input[name=lng]').value = latlng.lng.toFixed(6); }
  }
  if (window.L) $$('[data-map]').forEach(function (el) {
    var lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng), has = !isNaN(lat) && !isNaN(lng);
    var map = L.map(el, { scrollWheelZoom: false }).setView(has ? [lat, lng] : [24.9, 55.2], has ? 14 : 7);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    if (el.hasAttribute('data-edit')) {
      var form = el.closest('form'); pickMap = map;
      if (has) setPin(map, L.latLng(lat, lng), form);
      map.on('click', function (ev) { setPin(map, ev.latlng, form); });
    } else if (has) L.marker([lat, lng]).addTo(map);
    setTimeout(function () { map.invalidateSize(); }, 300);
  });
  document.addEventListener('change', function (e) { if (e.target.name === 'section' && pickMap) setTimeout(function () { pickMap.invalidateSize(); }, 50); });

  function previews(input) {
    var box = $('#pthumbs'); if (!box) return; box.innerHTML = '';
    Array.prototype.slice.call(input.files, 0, 10).forEach(function (file, i) {
      var d = document.createElement('div'); d.className = 'pthumb';
      var img = document.createElement('img'); img.src = URL.createObjectURL(file); d.appendChild(img);
      if (i === 0) { var c = document.createElement('span'); c.className = 'cover'; c.textContent = '★'; d.appendChild(c); }
      box.appendChild(d);
    });
  }

  /* Show a toast for ?qs_msg notices, then tidy the address bar */
  var n = $('.qs-notice.ok'); if (n) toast(n.textContent.trim());
  if (/[?&](qs_msg|qs_err|old_[a-z]+)=/.test(location.search) && history.replaceState) {
    var u = new URL(location.href); ['qs_msg'].forEach(function (k) { u.searchParams.delete(k); }); history.replaceState(null, '', u);
  }
})();
