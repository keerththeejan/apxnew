(function(){
  function include(selector, url){
    var host = document.querySelector(selector);
    if (!host) return Promise.resolve();
    return fetch(url, { cache: 'no-cache' })
      .then(function(r){ return r.text(); })
      .then(function(html){ host.innerHTML = html; });
  }

  function fallbackHeaderHtml(){
    return ''
      + '<header class="topbar">'
      +   '<div class="top-contact-bar">'
      +     '<div class="top-contact-inner">'
      +       '<div class="top-contact-left">'
      +         '<a class="top-contact-link" href="mailto:info@apx.com" aria-label="Email">✉️ info@apx.com</a>'
      +         '<a class="top-contact-link" href="tel:+94770000000" aria-label="Phone">📞 +94 77 000 0000</a>'
      +       '</div>'
      +       '<div class="top-contact-right" aria-label="Social links">'
      +         '<div class="top-social-links">'
      +           '<a class="top-social" href="#" aria-label="Facebook" title="Facebook">f</a>'
      +           '<a class="top-social" href="#" aria-label="Instagram" title="Instagram">i</a>'
      +           '<a class="top-social" href="#" aria-label="YouTube" title="YouTube">y</a>'
      +           '<a class="top-social" href="#" aria-label="TikTok" title="TikTok">t</a>'
      +         '</div>'
      +       '</div>'
      +     '</div>'
      +   '</div>'
      +   '<div class="navbar-shell">'
      +     '<a class="brand" href="./index.html" aria-label="Home">'
      +       '<img src="./images/logo.png" alt="APX logo" width="180" height="60" />'
      +     '</a>'
      +     '<button class="hamb" type="button" aria-expanded="false" aria-controls="navbar-menu" aria-label="Open menu">Menu</button>'
      +     '<div id="navbar-menu" class="navbar-menu navbar-collapse">'
      +     '<nav class="primary-links" aria-label="Primary">'
      +       '<a data-nav href="index.html">Home</a>'
      +       '<a data-nav href="about.html">About</a>'
      +       '<a data-nav href="contact.html">Contact</a>'
      +       '<a data-nav href="flights.html">Flight Ticket</a>'
      +       '<a data-nav href="visa.html">Visa</a>'
      +       '<a data-nav href="flights.html#finance">Finance</a>'
      +       '<a data-nav href="insurance.html">Insurance</a>'
      +       '<a data-nav href="hotels.html">Hotel</a>'
      +       '<a data-nav href="index.html#news">News</a>'
      +     '</nav>'
      +     '</div>'
      +   '</div>'
      + '</header>';
  }

  function fallbackFooterHtml(){
    return ''
      + '<footer class="site-footer">'
      +   '<div class="footer-inner">'
      +     '<div class="footer-top-contact" aria-label="Footer contact">'
      +       '<div class="footer-contact-pill">'
      +         '<div class="footer-contact-item">📍 <span>Colombo, Sri Lanka</span></div>'
      +         '<div class="footer-contact-item">✉️ <span>info@apx.com</span></div>'
      +         '<div class="footer-contact-item">📞 <span>+94 77 000 0000</span></div>'
      +       '</div>'
      +     '</div>'
      +     '<div class="footer-bottom">'
      +       '<div>© 2024 Adda Ads. All Rights Reserved</div>'
      +       '<div><a href="#">Terms &amp; Conditions</a><span style="opacity:.65;margin:0 8px">|</span><a href="#">Privacy Policy</a></div>'
      +     '</div>'
      +   '</div>'
      + '</footer>';
  }

  function initHeader(){
    var topbar = document.querySelector('.topbar');
    if (!topbar) return;

    function syncScrolled(){
      var y = window.scrollY || window.pageYOffset || 0;
      topbar.classList.toggle('is-scrolled', y > 12);
    }

    syncScrolled();
    window.addEventListener('scroll', syncScrolled, { passive: true });

    var hamb = document.querySelector('.hamb');
    var links = document.getElementById('navbar-menu');
    if (!hamb || !links) return;

    var prevFocus = null;
    function closeMenu(){
      links.classList.remove('open');
      hamb.setAttribute('aria-expanded','false');
      document.body.style.overflow = '';
      if (prevFocus && typeof prevFocus.focus === 'function') prevFocus.focus();
      prevFocus = null;
    }
    function openMenu(){
      prevFocus = document.activeElement;
      links.classList.add('open');
      hamb.setAttribute('aria-expanded','true');
      if (window.innerWidth <= 991) document.body.style.overflow = 'hidden';
      var first = links.querySelector('a');
      if (first) first.focus();
    }

    hamb.addEventListener('click', function(){
      var isOpen = links.classList.contains('open');
      if (isOpen) closeMenu();
      else openMenu();
    });

    window.addEventListener('resize', function(){
      if (window.innerWidth > 991) closeMenu();
    });

    document.addEventListener('click', function(e){
      if (window.innerWidth <= 991 && links.classList.contains('open') && !links.contains(e.target) && !hamb.contains(e.target)) {
        closeMenu();
      }
    });

    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && links.classList.contains('open')) {
        e.preventDefault();
        closeMenu();
      }
    });

    links.addEventListener('click', function(e){
      var a = e.target.closest('a[data-nav]');
      if (!a || window.innerWidth > 991) return;
      if (a.classList.contains('dropdown-toggle')) return;
      if (a.getAttribute('href') === '#' || a.getAttribute('href') === '') return;
      closeMenu();
    });
  }

  function initReveal(){
    var items = document.querySelectorAll('.js-reveal');
    if (!items || !items.length) return;

    if (!('IntersectionObserver' in window)) {
      items.forEach(function(el){ el.classList.add('is-visible'); });
      return;
    }

    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          io.unobserve(e.target);
        }
      });
    }, { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.12 });

    items.forEach(function(el){ io.observe(el); });
  }

  function initLightbox(){
    var thumbs = document.querySelectorAll('a.js-lightbox');
    if (!thumbs || !thumbs.length) return;

    var lastFocus = null;
    var overlay = document.createElement('div');
    overlay.className = 'lightbox';
    overlay.setAttribute('role','dialog');
    overlay.setAttribute('aria-modal','true');
    overlay.setAttribute('aria-label','Image preview');
    overlay.innerHTML = '<div class="lightbox-inner" role="document">'
      + '<button type="button" class="lightbox-close" aria-label="Close">×</button>'
      + '<img class="lightbox-img" alt="" />'
      + '</div>';
    document.body.appendChild(overlay);

    var img = overlay.querySelector('.lightbox-img');
    var btn = overlay.querySelector('.lightbox-close');

    function openLightbox(src, alt){
      lastFocus = document.activeElement;
      img.src = src;
      img.alt = alt || '';
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
      btn.focus();
    }
    function closeLightbox(){
      overlay.classList.remove('open');
      document.body.style.overflow = '';
      img.src = '';
      if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
      lastFocus = null;
    }

    thumbs.forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        var image = a.querySelector('img');
        openLightbox(a.getAttribute('href'), image ? image.getAttribute('alt') : '');
      });
    });

    overlay.addEventListener('click', function(e){
      if (e.target === overlay) closeLightbox();
    });

    if (btn) btn.addEventListener('click', closeLightbox);

    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && overlay.classList.contains('open')) {
        e.preventDefault();
        closeLightbox();
      }
    });
  }

  function setActiveNav(){
    var links = document.querySelectorAll('.primary-links a[data-nav], .primary-links .dropdown-menu a[data-nav]');
    if (!links || !links.length) return;

    function normalize(p){
      if (!p) return '/';
      p = ('' + p).split('#')[0].split('?')[0];
      if (!p.startsWith('/')) p = '/' + p;
      if (p.length > 1 && p.endsWith('/')) p = p.slice(0, -1);
      return p.toLowerCase();
    }

    var current = normalize(location.pathname);
    links.forEach(function(a){
      var href = a.getAttribute('href') || '';
      var path = '/';
      try {
        path = normalize(new URL(href, location.origin).pathname);
      } catch(e) {
        path = normalize(href);
      }
      var isActive = path === current;
      a.classList.toggle('is-active', isActive);
      if (isActive) a.setAttribute('aria-current','page');
      else a.removeAttribute('aria-current');
    });
  }

  Promise.all([
    include('#site-header', './components/header.html').catch(function(){
      var host = document.querySelector('#site-header');
      if (host) host.innerHTML = fallbackHeaderHtml();
    }),
    include('#site-footer', './components/footer.html').catch(function(){
      var host = document.querySelector('#site-footer');
      if (host) host.innerHTML = fallbackFooterHtml();
    })
  ]).then(function(){
    setActiveNav();
    initHeader();
    initReveal();
    initLightbox();
  });
})();
