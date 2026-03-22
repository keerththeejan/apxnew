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
