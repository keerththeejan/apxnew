(function(){
  function el(html){
    var t=document.createElement('template');
    t.innerHTML=html.trim();
    return t.content.firstElementChild;
  }

  function getPageMeta(){
    var page = document.body.getAttribute('data-page') || '';
    var title = document.body.getAttribute('data-title') || 'Dashboard';
    var crumb = document.body.getAttribute('data-crumb') || title;
    return { page: page, title: title, crumb: crumb };
  }

  function adminUrl(path){
    var base = (typeof window.__APX_BASE__ === 'string') ? window.__APX_BASE__ : '';
    base = base.replace(/\/$/, '');
    path = (path.charAt(0) === '/') ? path : '/' + path;
    return base + path;
  }

  function escapeAttr(s){
    return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
  }

  function getMenu(){
    var role = (typeof window.__ADMIN_ROLE__ === 'string') ? String(window.__ADMIN_ROLE__).toLowerCase().trim() : '';
    var menu = [
      { key:'dashboard', label:'Dashboard', icon:'bi-speedometer2', href: adminUrl('/admin') },
      { key:'site', label:'Public homepage', linkTitle:'View site (public homepage). Opens in a new tab.', icon:'bi-house-door', href: adminUrl('/'), external: true },
      { key:'pages', label:'Manage Pages', icon:'bi-layout-text-window-reverse', href: adminUrl('/admin/pages') },
      { key:'banners', label:'Home banners', icon:'bi-image', href: adminUrl('/admin/banners') },
      { key:'footer_gallery', label:'Footer gallery', icon:'bi-grid-3x3-gap', href: adminUrl('/admin/footer-gallery') },
      { key:'navigation', label:'Menu management', icon:'bi-list-nested', href: adminUrl('/admin/navigation') },
      { key:'services', label:'Services', icon:'bi-grid-1x2', href: adminUrl('/admin/services') },
      { key:'flights', label:'Flight Tickets', icon:'bi-airplane', href: adminUrl('/admin/flights') },
      { key:'hotels', label:'Hotels', icon:'bi-building', href: adminUrl('/admin/hotels') },
      { key:'visa', label:'Visa Services', icon:'bi-passport', href: adminUrl('/admin/visa') },
      { key:'finance', label:'Finance Services', icon:'bi-cash-coin', href: adminUrl('/admin/finance') },
      { key:'insurance', label:'Insurance Plans', icon:'bi-shield-check', href: adminUrl('/admin/insurance') },
      { key:'blog', label:'Blog / News', icon:'bi-newspaper', href: adminUrl('/admin/blog') },
      { key:'enquiries', label:'Enquiries', icon:'bi-chat-dots', href: adminUrl('/admin/enquiries') },
      { key:'applications', label:'Applications', icon:'bi-inboxes', href: adminUrl('/admin/applications') },
      { key:'users', label:'Users', icon:'bi-people', href: adminUrl('/admin/users') },
      { key:'settings', label:'Settings', icon:'bi-gear', href: adminUrl('/admin/settings') },
      { key:'logout', label:'Logout', icon:'bi-box-arrow-right', href: adminUrl('/admin/logout') }
    ];
    if (role === 'staff') {
      menu = menu.filter(function(m){
        return m.key !== 'settings' && m.key !== 'users';
      });
    }
    return menu;
  }

  function buildSidebar(activeKey){
    var menu = getMenu();
    var links = menu.map(function(m){
      var active = m.key === activeKey ? ' active' : '';
      var ext = m.external ? ' target="_blank" rel="noopener noreferrer"' : '';
      var extIcon = m.external ? ' <i class="bi bi-box-arrow-up-right small opacity-75" aria-hidden="true"></i>' : '';
      var tip = m.linkTitle ? ' title="'+escapeAttr(m.linkTitle)+'"' : '';
      return '<a class="nav-link'+active+'" href="'+m.href+'"'+ext+tip+'><i class="bi '+m.icon+'"></i><span>'+m.label+'</span>'+extIcon+'</a>';
    }).join('');

    return (
      '<aside class="sf-sidebar" id="sfSidebar" aria-label="Sidebar navigation">'
        + '<div class="sf-brand">'
          + '<div>'
            + '<div class="sf-brand-title">APX</div>'
            + '<div class="sf-brand-sub">Admin Console</div>'
          + '</div>'
          + '<button class="btn btn-sm btn-outline-light d-none d-lg-inline-flex" type="button" id="sfCollapseBtn" aria-label="Collapse sidebar" disabled>Menu</button>'
        + '</div>'
        + '<nav class="sf-nav nav flex-column" role="navigation">'
          + links
        + '</nav>'
      + '</aside>'
    );
  }

  function buildTopbar(meta){
    var role = (typeof window.__ADMIN_ROLE__ === 'string') ? String(window.__ADMIN_ROLE__).toLowerCase().trim() : '';
    var settingsBlock = '';
    if (role !== 'staff') {
      settingsBlock = '<li><a class="dropdown-item" href="'+adminUrl('/admin/settings')+'"><i class="bi bi-gear me-2"></i>Settings</a></li><li><hr class="dropdown-divider"></li>';
    }
    return (
      '<header class="sf-topbar">'
        + '<div class="sf-topbar-inner">'
          + '<div class="d-flex align-items-center gap-2">'
            + '<button class="btn btn-outline-light btn-sm sf-mobile-toggle" type="button" id="sfSidebarToggle" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>'
            + '<div>'
              + '<h1 class="sf-page-title">'+meta.title+'</h1>'
              + '<div class="sf-breadcrumb">'+meta.crumb+'</div>'
            + '</div>'
          + '</div>'

          + '<div class="d-flex align-items-center gap-2">'
            + '<button class="btn btn-outline-light btn-sm" type="button" id="sfToastBtn"><i class="bi bi-bell"></i></button>'
            + '<div class="dropdown">'
              + '<button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'
                + '<i class="bi bi-person-circle me-1"></i> Admin'
              + '</button>'
              + '<ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">'
                + settingsBlock
                + '<li><a class="dropdown-item" href="'+adminUrl('/admin/logout')+'"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>'
              + '</ul>'
            + '</div>'
          + '</div>'
        + '</div>'
      + '</header>'
    );
  }

  function mountLayout(){
    var meta = getPageMeta();
    var host = document.getElementById('sfApp');
    if (!host) return;

    host.className = 'sf-app';
    host.innerHTML = buildSidebar(meta.page) + '<div class="sf-content" id="sfContent"></div>';

    var content = document.getElementById('sfContent');
    content.insertAdjacentHTML('beforeend', buildTopbar(meta));
    content.insertAdjacentHTML('beforeend', '<main class="sf-main" id="sfMain" role="main"></main>');

    var tpl = document.getElementById('page-content');
    if (tpl) {
      document.getElementById('sfMain').appendChild(tpl.content.cloneNode(true));
    }

    content.insertAdjacentHTML('beforeend', '<div class="toast-container position-fixed bottom-0 end-0 p-3" id="sfToasts" aria-live="polite" aria-atomic="true"></div>');

    wireInteractions();
    initPage(meta.page);
  }

  function showToast(title, body){
    var wrap = document.getElementById('sfToasts');
    if (!wrap) return;
    var id = 't' + Math.random().toString(16).slice(2);
    var node = el(
      '<div class="toast sf-toast" role="status" aria-live="polite" aria-atomic="true" id="'+id+'">'
        + '<div class="toast-header bg-transparent text-white border-0">'
          + '<i class="bi bi-info-circle me-2"></i>'
          + '<strong class="me-auto">'+title+'</strong>'
          + '<small class="text-secondary">now</small>'
          + '<button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>'
        + '</div>'
        + '<div class="toast-body text-white-50">'+body+'</div>'
      + '</div>'
    );
    wrap.appendChild(node);
    var t = bootstrap.Toast.getOrCreateInstance(node, { delay: 2200 });
    t.show();
    node.addEventListener('hidden.bs.toast', function(){ node.remove(); });
  }

  function wireInteractions(){
    var toggle = document.getElementById('sfSidebarToggle');
    var sidebar = document.getElementById('sfSidebar');

    function close(){ if (sidebar) sidebar.classList.remove('open'); }
    function open(){ if (sidebar) sidebar.classList.add('open'); }

    if (toggle && sidebar) {
      toggle.addEventListener('click', function(){
        sidebar.classList.toggle('open');
      });

      document.addEventListener('click', function(e){
        if (window.innerWidth > 992) return;
        if (!sidebar.classList.contains('open')) return;
        if (sidebar.contains(e.target) || toggle.contains(e.target)) return;
        close();
      });

      window.addEventListener('resize', function(){
        if (window.innerWidth > 992) close();
      });
    }

    var toastBtn = document.getElementById('sfToastBtn');
    if (toastBtn) {
      toastBtn.addEventListener('click', function(){
        showToast('Notification', 'This is a demo toast. Wire it to your backend events.');
      });
    }

    document.addEventListener('submit', function(e){
      var form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (form.hasAttribute('data-toast')) {
        e.preventDefault();
        showToast('Saved', 'Your changes have been saved (demo).');
        var m = form.closest('.modal');
        if (m) {
          var inst = bootstrap.Modal.getInstance(m);
          if (inst) inst.hide();
        }
      }
    });

    document.addEventListener('click', function(e){
      var btn = e.target.closest('[data-action="demo"]');
      if (!btn) return;
      e.preventDefault();
      showToast('Action', btn.getAttribute('data-message') || 'Action executed (demo).');
    });
  }

  function initPage(key){
    if (key === 'dashboard') {
      initDashboardChart();
    }
    if (key === 'navigation') {
      setTimeout(initNavigationOrdering, 0);
    }
    if (key === 'services') {
      setTimeout(initServicesPage, 0);
    }
    if (key === 'applications') {
      setTimeout(initApplicationsPage, 0);
    }
    if (key === 'services' || key === 'applications') {
      setTimeout(initWhatsAppActions, 0);
    }
  }

  function initWhatsAppActions(){
    if (document.body.getAttribute('data-wa-wired') === '1') return;
    document.body.setAttribute('data-wa-wired', '1');

    document.addEventListener('click', function(e){
      var btn = e.target.closest('.js-wa-send');
      if (!btn) return;
      e.preventDefault();
      var tokenEl = document.getElementById('waCsrfToken');
      var token = tokenEl ? tokenEl.value : '';
      var phone = btn.getAttribute('data-phone') || '';
      var message = btn.getAttribute('data-message') || '';
      var context = btn.getAttribute('data-context') || 'admin.manual';
      var entityId = btn.getAttribute('data-entity-id') || '';
      if (!token || !phone || !message) {
        showToast('WhatsApp', 'Missing data to send message.');
        return;
      }
      var fd = new FormData();
      fd.append('_token', token);
      fd.append('phone', phone);
      fd.append('message', message);
      fd.append('context', context);
      fd.append('entity_id', entityId);
      fetch(adminUrl('/admin/settings/whatsapp/send'), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      }).then(function(r){ return r.json(); })
      .then(function(data){
        if (data && data.ok) {
          if (data.provider === 'click_to_chat' && data.click_url) {
            window.open(data.click_url, '_blank', 'noopener');
          }
          showToast('WhatsApp', data.message || 'Message sent.');
        } else {
          showToast('WhatsApp', (data && data.message) ? data.message : 'Failed to send.');
        }
      }).catch(function(){
        showToast('WhatsApp', 'Failed to send.');
      });
    });
  }

  function initApplicationsPage(){
    var checkAll = document.querySelector('.js-wa-check-all');
    if (checkAll) {
      checkAll.addEventListener('change', function(){
        var rows = document.querySelectorAll('.js-wa-row');
        rows.forEach(function(n){ n.checked = !!checkAll.checked; });
      });
    }

    var bulk = document.querySelector('.js-wa-bulk');
    if (bulk) {
      bulk.addEventListener('click', function(){
        var tokenEl = document.getElementById('waCsrfToken');
        var token = tokenEl ? tokenEl.value : '';
        var checked = Array.prototype.slice.call(document.querySelectorAll('.js-wa-row:checked')).map(function(n){ return n.value; });
        if (!token || checked.length === 0) {
          showToast('WhatsApp', 'Select at least one application.');
          return;
        }
        var custom = window.prompt('Optional custom message for selected rows (leave empty for template):', '') || '';
        var fd = new FormData();
        fd.append('_token', token);
        checked.forEach(function(id){ fd.append('ids[]', id); });
        fd.append('message', custom);
        fetch(adminUrl('/admin/applications/bulk-whatsapp'), {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        }).then(function(r){ return r.json(); })
        .then(function(data){
          if (data && data.ok) {
            showToast('WhatsApp', 'Sent ' + (data.sent || 0) + ' of ' + (data.selected || checked.length));
          } else {
            showToast('WhatsApp', (data && data.message) ? data.message : 'Bulk send failed.');
          }
        }).catch(function(){ showToast('WhatsApp', 'Bulk send failed.'); });
      });
    }

    document.querySelectorAll('.js-app-status-form').forEach(function(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        var fd = new FormData(form);
        fd.append('ajax', '1');
        fetch(form.getAttribute('action') || adminUrl('/admin/applications/status'), {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        }).then(function(r){ return r.json(); })
        .then(function(data){
          if (data && data.ok) {
            showToast('Status', data.message || 'Updated.');
          } else {
            showToast('Status', (data && data.message) ? data.message : 'Update failed.');
          }
        }).catch(function(){ showToast('Status', 'Update failed.'); });
      });
    });
  }

  function loadSortableScript(cb){
    if (typeof window.Sortable !== 'undefined') {
      cb();
      return;
    }
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
    s.crossOrigin = 'anonymous';
    s.onload = function(){ cb(); };
    s.onerror = function(){ console.error('SortableJS failed to load'); };
    document.head.appendChild(s);
  }

  function loadTomSelectScript(cb){
    if (typeof window.TomSelect !== 'undefined') {
      cb();
      return;
    }
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js';
    s.crossOrigin = 'anonymous';
    s.onload = function(){ cb(); };
    s.onerror = function(){ console.error('Tom Select failed to load'); };
    document.head.appendChild(s);
  }

  function initServicesPage(){
    var modal = document.getElementById('svcModal');
    var form = document.getElementById('svcForm');
    if (!modal || !form) return;

    var countrySel = document.getElementById('svc_country');
    var fileInput = document.getElementById('svc_image');
    var previewWrap = document.getElementById('svcImgPreviewWrap');
    var previewImg = document.getElementById('svcImgPreview');
    var clearWrap = document.getElementById('svcClearImgWrap');
    var countryTs = null;

    function hidePreview(){
      if (previewImg) previewImg.src = '';
      if (previewWrap) previewWrap.classList.add('d-none');
      if (clearWrap) clearWrap.classList.add('d-none');
      if (fileInput) fileInput.value = '';
    }

    if (fileInput) {
      fileInput.addEventListener('change', function(){
        var f = fileInput.files && fileInput.files[0];
        if (!f) { hidePreview(); return; }
        if (previewImg) previewImg.src = URL.createObjectURL(f);
        if (previewWrap) previewWrap.classList.remove('d-none');
        if (clearWrap) clearWrap.classList.add('d-none');
        var c = document.getElementById('svc_clear_image');
        if (c) c.checked = false;
      });
    }

    function flagUrlForCode(code){
      var c = String(code || '').toLowerCase();
      if (!c) return '';
      return 'https://flagcdn.com/w40/' + c + '.png';
    }

    if (countrySel) {
      loadTomSelectScript(function(){
        if (typeof window.TomSelect === 'undefined') return;
        countryTs = new window.TomSelect(countrySel, {
          allowEmptyOption: true,
          create: false,
          plugins: ['clear_button'],
          placeholder: 'Select Country',
          render: {
            option: function(data, escape){
              var code = (data.value || '').toLowerCase();
              if (!code) {
                return '<div class="px-2 py-1 text-white-50">Select Country</div>';
              }
              var u = flagUrlForCode(code);
              return (
                '<div class="d-flex align-items-center gap-2 py-1 px-1">'
                  + '<img src="'+u+'" alt="" width="28" height="18" class="rounded border border-secondary flex-shrink-0" style="object-fit:cover"/>'
                  + '<span class="flex-grow-1 text-truncate">'+escape(data.text)+'</span>'
                  + '<span class="text-white-50 small flex-shrink-0">'+escape(data.value)+'</span>'
                + '</div>'
              );
            },
            item: function(data, escape){
              var code = (data.value || '').toLowerCase();
              if (!code) {
                return '<div class="text-white-50">Select Country</div>';
              }
              var u = flagUrlForCode(code);
              return (
                '<div class="d-flex align-items-center gap-2">'
                  + '<img src="'+u+'" alt="" width="22" height="15" class="rounded border border-secondary flex-shrink-0" style="object-fit:cover"/>'
                  + '<span class="text-truncate">'+escape(data.text)+'</span>'
                + '</div>'
              );
            }
          }
        });
      });
    }

    function applyCountry(val){
      var v = String(val || '').trim();
      function sync(){
        if (countryTs) {
          if (!v) countryTs.clear(true);
          else countryTs.setValue(v, true);
        } else if (countrySel) {
          countrySel.value = v;
        }
      }
      sync();
      if (countrySel && !countryTs) {
        var tries = 0;
        var id = setInterval(function(){
          tries++;
          if (countryTs) {
            clearInterval(id);
            if (!v) countryTs.clear(true);
            else countryTs.setValue(v, true);
          } else if (tries > 80) clearInterval(id);
        }, 25);
      }
    }

    modal.addEventListener('show.bs.modal', function(ev){
      var btn = ev.relatedTarget;
      var mode = btn && btn.getAttribute('data-mode') === 'edit' ? 'edit' : 'create';
      form.reset();
      document.getElementById('svc_id').value = '';
      hidePreview();

      var urlCreate = form.getAttribute('data-url-create') || '';
      var urlUpdate = form.getAttribute('data-url-update') || '';
      form.setAttribute('action', mode === 'edit' ? urlUpdate : urlCreate);

      var cc = '';
      if (mode === 'edit' && btn) {
        document.getElementById('svc_id').value = btn.getAttribute('data-id') || '';
        document.getElementById('svc_icon').value = btn.getAttribute('data-icon') || '';
        document.getElementById('svc_title').value = btn.getAttribute('data-title') || '';
        document.getElementById('svc_desc').value = btn.getAttribute('data-description') || '';
        document.getElementById('svc_link').value = btn.getAttribute('data-link') || '';
        document.getElementById('svc_sort').value = btn.getAttribute('data-sort') || '0';
        document.getElementById('svc_active').value = btn.getAttribute('data-active') || '1';
        var iu = btn.getAttribute('data-image_url') || '';
        if (iu && previewImg && previewWrap) {
          previewImg.src = iu;
          previewWrap.classList.remove('d-none');
          if (clearWrap) clearWrap.classList.remove('d-none');
        }
        var clr = document.getElementById('svc_clear_image');
        if (clr) clr.checked = false;
        cc = btn.getAttribute('data-country-code') || '';
      }
      applyCountry(cc);
    });
  }

  function initNavigationOrdering(){
    var root = document.getElementById('navTreeRoot');
    if (!root) return;
    var csrf = root.getAttribute('data-csrf') || '';
    var url = root.getAttribute('data-reorder-url') || '';
    if (!url) return;

    var saveTimer = null;
    var statusEl = document.getElementById('navOrderStatus');

    function setStatus(t, cls){
      if (!statusEl) return;
      statusEl.textContent = t;
      statusEl.className = 'small ' + (cls || 'text-white-50');
    }

    function serializeTree(ul){
      var out = [];
      if (!ul) return out;
      var ch = ul.children;
      for (var i = 0; i < ch.length; i++) {
        var li = ch[i];
        if (!li.matches || !li.matches('li.nav-sortable-li')) continue;
        var id = parseInt(li.getAttribute('data-id'), 10);
        if (!id) continue;
        var sub = li.querySelector(':scope > ul.nav-nested-sortable');
        var node = { id: id, children: [] };
        if (sub) node.children = serializeTree(sub);
        out.push(node);
      }
      return out;
    }

    function saveNow(){
      saveTimer = null;
      var tree = serializeTree(root);
      setStatus('Saving…', 'text-warning');
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ _token: csrf, tree: tree }),
        credentials: 'same-origin'
      })
        .then(function(r){ return r.json().then(function(j){ return { ok: r.ok, status: r.status, j: j }; }); })
        .then(function(res){
          if (res.ok && res.j && res.j.ok) {
            setStatus('Saved', 'text-success');
            setTimeout(function(){ setStatus('', 'text-white-50'); }, 2200);
          } else {
            var msg = (res.j && res.j.error) ? res.j.error : ('HTTP ' + res.status);
            setStatus('Error', 'text-danger');
            showToast('Menu order', String(msg));
          }
        })
        .catch(function(){
          setStatus('Network error', 'text-danger');
          showToast('Menu order', 'Network error while saving order.');
        });
    }

    function scheduleSave(){
      if (saveTimer) clearTimeout(saveTimer);
      saveTimer = setTimeout(saveNow, 450);
    }

    function destroyNavSortables(){
      document.querySelectorAll('.nav-nested-root, .nav-nested-sortable').forEach(function(el){
        if (el._sortable) {
          try { el._sortable.destroy(); } catch (e) {}
          el._sortable = null;
        }
      });
    }

    function mountNavSortables(){
      document.querySelectorAll('.nav-nested-root, .nav-nested-sortable').forEach(function(el){
        if (el._sortable) return;
        el._sortable = Sortable.create(el, {
          group: 'nav',
          animation: 180,
          handle: '.nav-drag-handle',
          ghostClass: 'nav-sortable-ghost',
          chosenClass: 'nav-sortable-chosen',
          dragClass: 'nav-sortable-drag',
          fallbackOnBody: true,
          swapThreshold: 0.65,
          invertSwap: true,
          onEnd: function(){ scheduleSave(); }
        });
      });
    }

    loadSortableScript(function(){
      destroyNavSortables();
      mountNavSortables();
    });
  }

  function initDashboardChart(){
    var canvas = document.getElementById('sfMonthlyChart');
    if (!canvas || typeof Chart === 'undefined') return;

    var ctx = canvas.getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [
          {
            label: 'Applications',
            data: [18,22,26,24,31,35,34,38,41,44,46,52],
            borderColor: 'rgba(79,140,255,1)',
            backgroundColor: 'rgba(79,140,255,.18)',
            tension: .35,
            fill: true,
            pointRadius: 2
          },
          {
            label: 'Revenue (k)',
            data: [4,5,6,7,8,9,11,12,13,14,16,18],
            borderColor: 'rgba(255,122,24,1)',
            backgroundColor: 'rgba(255,122,24,.12)',
            tension: .35,
            fill: true,
            pointRadius: 2
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { labels: { color: '#e5e7eb', font: { weight: '700' } } },
          tooltip: { enabled: true }
        },
        scales: {
          x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(148,163,184,.10)' } },
          y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(148,163,184,.10)' } }
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', mountLayout);
})();
