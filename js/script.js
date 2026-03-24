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

  function initQuoteWidget(){
    var root = document.getElementById('apx-quote-widget');
    if (!root) return;

    var route = document.getElementById('apx-quote-route');
    var qty = document.getElementById('apx-quote-qty');
    var dealer = document.getElementById('apx-quote-dealer');
    var btnCalc = document.getElementById('apx-quote-calc');
    var result = document.getElementById('apx-quote-result');
    var extras = document.getElementById('apx-quote-extras');
    var outCountry = document.getElementById('apx-quote-out-country');
    var outService = document.getElementById('apx-quote-out-service');
    var outWeight = document.getElementById('apx-quote-out-weight');
    var outPpkg = document.getElementById('apx-quote-out-ppkg');
    var outTotal = document.getElementById('apx-quote-out-total');
    var btnPdf = document.getElementById('apx-quote-dl-pdf');
    var btnPng = document.getElementById('apx-quote-dl-png');
    var btnTxt = document.getElementById('apx-quote-dl-txt');
    var linkWa = document.getElementById('apx-quote-wa');
    var waDigits = (root.getAttribute('data-whatsapp') || '').replace(/\D/g, '') || '94770000000';

    var last = null;

    function fmtMoney(n){
      try {
        return new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(Math.round(n));
      } catch (e) {
        return String(Math.round(n));
      }
    }

    function selectedOption(){
      if (!route || !route.options) return null;
      var i = route.selectedIndex;
      if (i < 0) return null;
      return route.options[i];
    }

    function parseQty(){
      var q = qty ? parseFloat(String(qty.value).replace(',', '.')) : NaN;
      if (!isFinite(q) || q <= 0) return NaN;
      return q;
    }

    function buildTextSummary(){
      if (!last) return '';
      var lines = [
        'APX — Price quote',
        'Country: ' + last.country,
        'Service: ' + last.service,
        'Total weight: ' + last.weightStr,
        'Price per kg: LKR ' + fmtMoney(last.pricePerKg),
        'Total price: LKR ' + fmtMoney(last.total)
      ];
      if (last.dealerNote) lines.push(last.dealerNote);
      return lines.join('\n');
    }

    function setWaHref(){
      if (!linkWa) return;
      var text = encodeURIComponent(buildTextSummary() + '\n');
      linkWa.href = 'https://wa.me/' + waDigits + '?text=' + text;
    }

    function triggerDownload(filename, mime, body){
      var a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob([body], { type: mime }));
      a.download = filename;
      a.rel = 'noopener';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      setTimeout(function(){ URL.revokeObjectURL(a.href); }, 0);
    }

    function downloadPng(){
      if (!last) return;
      var w = 880;
      var h = 520;
      var canvas = document.createElement('canvas');
      canvas.width = w;
      canvas.height = h;
      var ctx = canvas.getContext('2d');
      if (!ctx) return;
      var g = ctx.createLinearGradient(0, 0, w, h);
      g.addColorStop(0, '#0b1120');
      g.addColorStop(1, '#0f172a');
      ctx.fillStyle = g;
      ctx.fillRect(0, 0, w, h);
      ctx.strokeStyle = 'rgba(99,102,241,.4)';
      ctx.lineWidth = 2;
      ctx.strokeRect(12, 12, w - 24, h - 24);
      ctx.fillStyle = '#f8fafc';
      ctx.font = '600 22px Inter, system-ui, sans-serif';
      ctx.fillText('APX — Price quote', 40, 56);
      ctx.fillStyle = '#94a3b8';
      ctx.font = '500 15px Inter, system-ui, sans-serif';
      var y = 100;
      var lineH = 28;
      var rows = [
        ['Country', last.country],
        ['Service', last.service],
        ['Total weight', last.weightStr],
        ['Price per kg', 'LKR ' + fmtMoney(last.pricePerKg)],
        ['Total price', 'LKR ' + fmtMoney(last.total)]
      ];
      rows.forEach(function(row){
        ctx.fillStyle = '#cbd5e1';
        ctx.fillText(row[0] + ':', 40, y);
        ctx.fillStyle = '#f1f5f9';
        ctx.font = '600 15px Inter, system-ui, sans-serif';
        ctx.fillText(row[1], 220, y);
        ctx.font = '500 15px Inter, system-ui, sans-serif';
        y += lineH;
      });
      canvas.toBlob(function(blob){
        if (!blob) return;
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'apx-quote.png';
        a.rel = 'noopener';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(function(){ URL.revokeObjectURL(a.href); }, 0);
      }, 'image/png');
    }

    function downloadPdf(){
      if (!last) return;
      var html = '<!doctype html><html><head><meta charset="utf-8"/><title>Quote</title>'
        + '<style>body{font-family:Inter,system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:32px}'
        + 'h1{font-size:20px;margin:0 0 20px}dl{margin:0}dt{color:#94a3b8;font-weight:600}dd{margin:0 0 10px;font-weight:700}'
        + '.total{font-size:22px;margin-top:16px;font-weight:800}</style></head><body>'
        + '<h1>APX — Price quote</h1><dl>'
        + '<dt>Country</dt><dd>' + escapeHtml(last.country) + '</dd>'
        + '<dt>Service</dt><dd>' + escapeHtml(last.service) + '</dd>'
        + '<dt>Total weight</dt><dd>' + escapeHtml(last.weightStr) + '</dd>'
        + '<dt>Price per kg</dt><dd>LKR ' + escapeHtml(fmtMoney(last.pricePerKg)) + '</dd>'
        + '</dl><p class="total">Total: LKR ' + escapeHtml(fmtMoney(last.total)) + '</p>'
        + '</body></html>';
      var w = window.open('', '_blank');
      if (!w) return;
      w.document.open();
      w.document.write(html);
      w.document.close();
      w.focus();
      w.print();
    }

    function escapeHtml(s){
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function calculate(){
      var opt = selectedOption();
      if (!opt || !opt.value) {
        route.focus();
        route.setCustomValidity('Please select a route.');
        route.reportValidity();
        return;
      }
      route.setCustomValidity('');
      var w = parseQty();
      if (!isFinite(w)) {
        qty.focus();
        return;
      }
      var pricePerKg = parseFloat(opt.getAttribute('data-price') || '0');
      if (!isFinite(pricePerKg)) pricePerKg = 0;
      var country = opt.getAttribute('data-country') || '—';
      var service = opt.getAttribute('data-service') || '—';
      var dealerCode = dealer && dealer.value ? String(dealer.value).trim() : '';
      var dealerDiscount = dealerCode !== '' ? 0.05 : 0;
      var subtotal = w * pricePerKg;
      var total = subtotal * (1 - dealerDiscount);
      var weightStr = (Math.round(w * 10) / 10) + ' kg';

      last = {
        country: country,
        service: service,
        weightKg: w,
        weightStr: weightStr,
        pricePerKg: pricePerKg,
        total: total,
        dealerNote: dealerDiscount ? 'Dealer adjustment: -5% applied.' : ''
      };

      outCountry.textContent = country;
      outService.textContent = service;
      outWeight.textContent = weightStr;
      outPpkg.textContent = 'LKR ' + fmtMoney(pricePerKg);
      outTotal.textContent = 'LKR ' + fmtMoney(total);

      result.removeAttribute('hidden');
      result.setAttribute('aria-hidden', 'false');
      extras.removeAttribute('hidden');
      extras.setAttribute('aria-hidden', 'false');

      requestAnimationFrame(function(){
        result.classList.add('is-visible');
        extras.classList.add('is-visible');
      });

      setWaHref();
    }

    if (btnCalc) btnCalc.addEventListener('click', calculate);

    if (btnTxt) btnTxt.addEventListener('click', function(){
      if (!last) return;
      triggerDownload('apx-quote.txt', 'text/plain;charset=utf-8', buildTextSummary());
    });
    if (btnPng) btnPng.addEventListener('click', downloadPng);
    if (btnPdf) btnPdf.addEventListener('click', downloadPdf);

    if (route) {
      route.addEventListener('change', function(){
        route.setCustomValidity('');
      });
    }
  }

  function initVehicleBooking(){
    var root = document.getElementById('vehicleBookingRoot');
    if (!root) return;

    var form = document.getElementById('vehicleBookingForm');
    var tripType = document.getElementById('vb_trip_type');
    var rentalWrap = document.getElementById('vb_rental_unit_wrap');
    var returnWrap = document.getElementById('vb_return_wrap');
    var pickupDt = document.getElementById('vb_pickup_dt');
    var returnDt = document.getElementById('vb_return_dt');
    var dtError = document.getElementById('vb_datetime_error');
    var dtNowBtn = document.getElementById('vb_dt_now');
    var dtPlus1Btn = document.getElementById('vb_dt_plus1');
    var dtTomorrowBtn = document.getElementById('vb_dt_tomorrow');
    var vehicleType = document.getElementById('vb_vehicle_type');
    var couponCode = document.getElementById('vb_coupon_code');
    var previewBtn = document.getElementById('vb_preview_btn');
    var submitBtn = document.getElementById('vb_submit_btn');
    var preview = document.getElementById('vb_preview');
    var breakdownEl = document.getElementById('vb_breakdown');
    var availableList = document.getElementById('vb_available_list');
    var distanceInput = document.getElementById('vb_distance_km');
    var durationInput = document.getElementById('vb_duration_minutes');
    var totalInput = document.getElementById('vb_estimated_total');
    var pricingJsonInput = document.getElementById('vb_pricing_json');
    var mapApiKey = (root.getAttribute('data-map-api-key') || '').trim();
    var pickupPicker = null;
    var returnPicker = null;
    var flatpickrLoader = null;

    function ensureFlatpickr(){
      if (window.flatpickr) return Promise.resolve(true);
      if (flatpickrLoader) return flatpickrLoader;
      flatpickrLoader = new Promise(function(resolve){
        var cssId = 'vb-flatpickr-css';
        if (!document.getElementById(cssId)) {
          var css = document.createElement('link');
          css.id = cssId;
          css.rel = 'stylesheet';
          css.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
          document.head.appendChild(css);
        }
        var jsId = 'vb-flatpickr-js';
        if (document.getElementById(jsId)) {
          var wait = function(){
            if (window.flatpickr) resolve(true);
            else setTimeout(wait, 60);
          };
          wait();
          return;
        }
        var js = document.createElement('script');
        js.id = jsId;
        js.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
        js.async = true;
        js.onload = function(){ resolve(!!window.flatpickr); };
        js.onerror = function(){ resolve(false); };
        document.head.appendChild(js);
      });
      return flatpickrLoader;
    }

    function parseMachineDate(v){
      var s = String(v || '').trim();
      if (!s) return null;
      var d = new Date(s.replace(' ', 'T'));
      if (!isFinite(d.getTime())) return null;
      return d;
    }

    function setDateValue(el, picker, dateObj){
      if (!el || !dateObj) return;
      if (picker) {
        picker.setDate(dateObj, true);
      } else {
        var y = dateObj.getFullYear();
        var m = String(dateObj.getMonth() + 1).padStart(2, '0');
        var d = String(dateObj.getDate()).padStart(2, '0');
        var h = String(dateObj.getHours()).padStart(2, '0');
        var i = String(dateObj.getMinutes()).padStart(2, '0');
        el.value = y + '-' + m + '-' + d + ' ' + h + ':' + i;
      }
    }

    function addHours(baseDate, hrs){
      return new Date(baseDate.getTime() + (hrs * 60 * 60 * 1000));
    }

    function validateDateRange(){
      if (!pickupDt || !returnDt) return true;
      var p = parseMachineDate(pickupDt.value);
      var r = parseMachineDate(returnDt.value);
      if (!r || !p) {
        if (dtError) dtError.textContent = '';
        return true;
      }
      if (r.getTime() <= p.getTime()) {
        if (dtError) dtError.textContent = 'End time must be after pickup time';
        return false;
      }
      if (dtError) dtError.textContent = '';
      return true;
    }

    function syncReturnMinAndDefault(autoFill){
      if (!pickupDt || !returnDt) return;
      var p = parseMachineDate(pickupDt.value);
      if (!p) return;
      var minR = addHours(p, 0.01);
      if (returnPicker) returnPicker.set('minDate', minR);
      var r = parseMachineDate(returnDt.value);
      if (autoFill && (!r || r.getTime() <= p.getTime())) {
        setDateValue(returnDt, returnPicker, addHours(p, 1));
      }
      validateDateRange();
    }

    function initDatePickers(){
      ensureFlatpickr().then(function(ok){
        if (!ok || !window.flatpickr || !pickupDt || !returnDt) return;
        var now = new Date();
        pickupPicker = window.flatpickr(pickupDt, {
          enableTime: true,
          time_24hr: false,
          minuteIncrement: 5,
          allowInput: false,
          disableMobile: true,
          dateFormat: 'Y-m-d H:i',
          altInput: true,
          altFormat: 'd/m/Y h:i K',
          minDate: now,
          onReady: function(selectedDates, dateStr, inst){
            if (inst && inst.calendarContainer) inst.calendarContainer.classList.add('vb-flatpickr');
          },
          onChange: function(){
            syncReturnMinAndDefault(true);
            previewQuote();
          }
        });
        returnPicker = window.flatpickr(returnDt, {
          enableTime: true,
          time_24hr: false,
          minuteIncrement: 5,
          allowInput: false,
          disableMobile: true,
          dateFormat: 'Y-m-d H:i',
          altInput: true,
          altFormat: 'd/m/Y h:i K',
          minDate: now,
          onReady: function(selectedDates, dateStr, inst){
            if (inst && inst.calendarContainer) inst.calendarContainer.classList.add('vb-flatpickr');
          },
          onChange: function(){
            validateDateRange();
            previewQuote();
          }
        });
        syncReturnMinAndDefault(true);
      });
    }

    var staticLocations = [
      { name: 'Colombo Fort, Sri Lanka', lat: 6.9344, lng: 79.8428 },
      { name: 'Bandaranaike International Airport (CMB)', lat: 7.1808, lng: 79.8841 },
      { name: 'Kandy City Center, Kandy', lat: 7.2906, lng: 80.6337 },
      { name: 'Galle Fort, Galle', lat: 6.0269, lng: 80.2168 },
      { name: 'Negombo Beach, Negombo', lat: 7.2083, lng: 79.8380 },
      { name: 'Matara Bus Stand, Matara', lat: 5.9495, lng: 80.5490 },
      { name: 'Jaffna Town, Jaffna', lat: 9.6615, lng: 80.0255 },
      { name: 'Ella Railway Station, Ella', lat: 6.8753, lng: 81.0464 }
    ];
    var placesReady = false;
    var placesSvc = null;
    var placesSessionToken = null;
    var googleLoadPromise = null;

    function ensureGooglePlaces(){
      if (!mapApiKey) return Promise.resolve(false);
      if (placesReady && placesSvc) return Promise.resolve(true);
      if (googleLoadPromise) return googleLoadPromise;
      googleLoadPromise = new Promise(function(resolve){
        if (window.google && window.google.maps && window.google.maps.places) {
          placesSvc = new window.google.maps.places.AutocompleteService();
          placesSessionToken = new window.google.maps.places.AutocompleteSessionToken();
          placesReady = true;
          resolve(true);
          return;
        }
        var cb = 'vbGooglePlacesCb_' + Math.random().toString(36).slice(2);
        window[cb] = function(){
          try {
            placesSvc = new window.google.maps.places.AutocompleteService();
            placesSessionToken = new window.google.maps.places.AutocompleteSessionToken();
            placesReady = true;
            resolve(true);
          } catch (e) {
            resolve(false);
          } finally {
            try { delete window[cb]; } catch (e) {}
          }
        };
        var s = document.createElement('script');
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(mapApiKey) + '&libraries=places&callback=' + cb;
        s.async = true;
        s.defer = true;
        s.onerror = function(){ resolve(false); };
        document.head.appendChild(s);
      });
      return googleLoadPromise;
    }

    function debounce(fn, wait){
      var t = null;
      return function(){
        var args = arguments;
        clearTimeout(t);
        t = setTimeout(function(){ fn.apply(null, args); }, wait);
      };
    }

    function escapeHtml(v){
      return String(v || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function highlight(text, query){
      var t = String(text || '');
      var q = String(query || '').trim();
      if (!q) return escapeHtml(t);
      var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
      return escapeHtml(t).replace(re, '<strong>$1</strong>');
    }

    function readRecent(kind){
      try {
        var k = 'vbRecent_' + kind;
        var raw = localStorage.getItem(k) || '[]';
        var arr = JSON.parse(raw);
        return Array.isArray(arr) ? arr : [];
      } catch (e) {
        return [];
      }
    }

    function saveRecent(kind, item){
      if (!item || !item.name) return;
      try {
        var k = 'vbRecent_' + kind;
        var arr = readRecent(kind).filter(function(x){ return String(x.name || '').toLowerCase() !== String(item.name || '').toLowerCase(); });
        arr.unshift({ name: item.name, lat: item.lat || '', lng: item.lng || '' });
        localStorage.setItem(k, JSON.stringify(arr.slice(0, 6)));
      } catch (e) {}
    }

    function setupAutocomplete(cfg){
      var input = document.getElementById(cfg.inputId);
      var wrap = document.getElementById(cfg.wrapId);
      var clearBtn = document.getElementById(cfg.clearId);
      var menu = document.getElementById(cfg.menuId);
      var loading = document.getElementById(cfg.loadingId);
      var lat = document.getElementById(cfg.latId);
      var lng = document.getElementById(cfg.lngId);
      if (!input || !wrap || !menu || !lat || !lng) return null;

      var items = [];
      var active = -1;
      var open = false;
      var selected = false;

      function setSelected(item){
        selected = !!item;
        input.dataset.selected = selected ? '1' : '0';
        if (item) {
          input.value = item.name || '';
          lat.value = (item.lat != null && item.lat !== '') ? String(item.lat) : '';
          lng.value = (item.lng != null && item.lng !== '') ? String(item.lng) : '';
          saveRecent(cfg.kind, item);
          wrap.classList.add('has-value');
        } else {
          lat.value = '';
          lng.value = '';
          if (!input.value) wrap.classList.remove('has-value');
        }
      }

      function closeMenu(){
        open = false;
        wrap.classList.remove('open');
        menu.innerHTML = '';
        active = -1;
      }

      function render(q){
        if (!open) return;
        if (!items.length) {
          menu.innerHTML = '<div class="vb-autocomplete-empty">No results found</div>';
          return;
        }
        menu.innerHTML = items.map(function(it, idx){
          var cls = 'vb-autocomplete-item' + (idx === active ? ' is-active' : '');
          var meta = it.meta ? '<small>' + escapeHtml(it.meta) + '</small>' : '';
          return '<div class="' + cls + '" role="option" data-idx="' + idx + '">' + highlight(it.name, q) + (meta ? '<div>' + meta + '</div>' : '') + '</div>';
        }).join('');
      }

      function setLoading(on){
        if (on) wrap.classList.add('loading');
        else wrap.classList.remove('loading');
        if (loading) loading.setAttribute('aria-hidden', on ? 'false' : 'true');
      }

      function normalizeStatic(query){
        var q = String(query || '').trim().toLowerCase();
        var all = staticLocations.slice();
        if (!q) return all.slice(0, 6);
        return all.filter(function(it){
          return String(it.name || '').toLowerCase().indexOf(q) !== -1;
        }).slice(0, 8);
      }

      function recentAsItems(){
        return readRecent(cfg.kind).map(function(r){
          return { name: r.name, lat: r.lat, lng: r.lng, meta: 'Recent location' };
        });
      }

      function useCurrentLocationItem(){
        return { name: 'Use current location', action: 'gps', meta: 'GPS' };
      }

      function fetchGoogle(query){
        return ensureGooglePlaces().then(function(ok){
          if (!ok || !placesSvc) return [];
          return new Promise(function(resolve){
            placesSvc.getPlacePredictions({
              input: query,
              sessionToken: placesSessionToken || undefined
            }, function(predictions, status){
              if (!predictions || status !== 'OK') {
                resolve([]);
                return;
              }
              resolve(predictions.slice(0, 6).map(function(p){
                return {
                  name: p.description || '',
                  placeId: p.place_id || '',
                  meta: 'Google Places'
                };
              }));
            });
          });
        });
      }

      function geocodePlace(placeId, fallbackName){
        return new Promise(function(resolve){
          if (!window.google || !window.google.maps || !window.google.maps.Geocoder) {
            resolve({ name: fallbackName || '', lat: '', lng: '' });
            return;
          }
          var geocoder = new window.google.maps.Geocoder();
          geocoder.geocode({ placeId: placeId }, function(results, status){
            if (status === 'OK' && results && results[0] && results[0].geometry && results[0].geometry.location) {
              var loc = results[0].geometry.location;
              resolve({
                name: results[0].formatted_address || fallbackName || '',
                lat: loc.lat(),
                lng: loc.lng()
              });
              return;
            }
            resolve({ name: fallbackName || '', lat: '', lng: '' });
          });
        });
      }

      var doSearch = debounce(function(){
        var q = input.value.trim();
        wrap.classList.toggle('has-value', q !== '');
        setSelected(null);
        open = true;
        wrap.classList.add('open');
        active = -1;
        setLoading(true);
        var merged = [];
        if (!q) {
          merged = [useCurrentLocationItem()].concat(recentAsItems(), normalizeStatic(''));
          items = merged.slice(0, 10);
          setLoading(false);
          render(q);
          return;
        }
        var staticItems = normalizeStatic(q).map(function(s){
          return { name: s.name, lat: s.lat, lng: s.lng, meta: 'Saved location' };
        });
        fetchGoogle(q).then(function(gItems){
          var seen = {};
          merged = staticItems.concat(gItems).filter(function(it){
            var k = String(it.name || '').toLowerCase();
            if (seen[k]) return false;
            seen[k] = true;
            return true;
          });
          items = merged.slice(0, 12);
          setLoading(false);
          render(q);
        }).catch(function(){
          items = staticItems.slice(0, 10);
          setLoading(false);
          render(q);
        });
      }, 300);

      function pickIndex(idx){
        if (idx < 0 || idx >= items.length) return;
        var it = items[idx];
        if (!it) return;
        if (it.action === 'gps') {
          if (!navigator.geolocation) return;
          setLoading(true);
          navigator.geolocation.getCurrentPosition(function(pos){
            setLoading(false);
            setSelected({
              name: 'Current location',
              lat: pos.coords.latitude,
              lng: pos.coords.longitude
            });
            closeMenu();
            previewQuote();
          }, function(){
            setLoading(false);
          }, { enableHighAccuracy: true, timeout: 10000 });
          return;
        }
        if (it.placeId) {
          setLoading(true);
          geocodePlace(it.placeId, it.name).then(function(res){
            setLoading(false);
            setSelected(res);
            closeMenu();
            previewQuote();
          });
          return;
        }
        setSelected(it);
        closeMenu();
        previewQuote();
      }

      input.addEventListener('focus', function(){
        open = true;
        wrap.classList.add('open');
        doSearch();
      });
      input.addEventListener('input', function(){
        input.setCustomValidity('');
        doSearch();
      });
      input.addEventListener('keydown', function(e){
        if (!open) return;
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          active = Math.min(items.length - 1, active + 1);
          render(input.value.trim());
          return;
        }
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          active = Math.max(0, active - 1);
          render(input.value.trim());
          return;
        }
        if (e.key === 'Enter') {
          if (active >= 0 && items[active]) {
            e.preventDefault();
            pickIndex(active);
          }
        }
        if (e.key === 'Escape') {
          closeMenu();
        }
      });
      menu.addEventListener('mousedown', function(e){
        var row = e.target.closest('.vb-autocomplete-item');
        if (!row) return;
        e.preventDefault();
        pickIndex(parseInt(row.getAttribute('data-idx') || '-1', 10));
      });
      if (clearBtn) {
        clearBtn.addEventListener('click', function(){
          input.value = '';
          setSelected(null);
          closeMenu();
          input.focus();
          previewQuote();
        });
      }
      document.addEventListener('click', function(e){
        if (!wrap.contains(e.target)) closeMenu();
      });

      if (input.value.trim() !== '') {
        wrap.classList.add('has-value');
      }

      return {
        isValidSelection: function(){
          return input.value.trim() === '' ? false : input.dataset.selected === '1';
        },
        hasValue: function(){ return input.value.trim() !== ''; },
        clear: function(){ input.value = ''; setSelected(null); closeMenu(); }
      };
    }

    var pickupAuto = setupAutocomplete({
      kind: 'pickup',
      wrapId: 'vb_pickup_wrap',
      inputId: 'vb_pickup',
      menuId: 'vb_pickup_menu',
      clearId: 'vb_pickup_clear',
      loadingId: 'vb_pickup_loading',
      latId: 'vb_pickup_lat',
      lngId: 'vb_pickup_lng'
    });
    var dropAuto = setupAutocomplete({
      kind: 'drop',
      wrapId: 'vb_drop_wrap',
      inputId: 'vb_drop',
      menuId: 'vb_drop_menu',
      clearId: 'vb_drop_clear',
      loadingId: 'vb_drop_loading',
      latId: 'vb_drop_lat',
      lngId: 'vb_drop_lng'
    });

    function setMode(){
      var t = tripType ? tripType.value : 'one_way';
      var isRental = t === 'rental';
      if (rentalWrap) rentalWrap.style.display = isRental ? '' : 'none';
      // Keep End/Return visible for all trip types so users can always pick.
      // For one-way it remains optional, but date options stay accessible.
      if (returnWrap) returnWrap.style.display = '';
    }

    function fmt(n){
      var x = Number(n || 0);
      try {
        return new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(x);
      } catch (e) {
        return String(Math.round(x * 100) / 100);
      }
    }

    function postJson(url, data){
      return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data || {})
      }).then(function(r){ return r.json(); });
    }

    function durationFromDates(start, end){
      var st = Date.parse(start || '');
      var en = Date.parse(end || '');
      if (!isFinite(st) || !isFinite(en) || en <= st) return 0;
      return Math.max(0, Math.round((en - st) / 60000));
    }

    function roughDistanceKm(){
      var pickup = (document.getElementById('vb_pickup') || {}).value || '';
      var drop = (document.getElementById('vb_drop') || {}).value || '';
      if (!pickup || !drop) return 8;
      if (pickup.toLowerCase() === drop.toLowerCase()) return 3;
      return 12;
    }

    function renderAvailable(items){
      if (!availableList) return;
      if (!items || !items.length) {
        availableList.innerHTML = '<div class="vehicle-booking-vehicle-card">No vehicles available for the selected window.</div>';
        return;
      }
      availableList.innerHTML = items.map(function(v){
        return '<div class="vehicle-booking-vehicle-card">'
          + '<strong>' + (v.name || 'Vehicle') + '</strong>'
          + '<div class="small text-secondary">' + (v.type || '') + ' • Seats: ' + (v.seats || 0) + ' • Luggage: ' + (v.luggage || 0) + '</div>'
          + '</div>';
      }).join('');
    }

    function renderBreakdown(b){
      if (!breakdownEl) return;
      breakdownEl.innerHTML = ''
        + '<div><span>Distance</span><strong>' + fmt(b.distance_km || 0) + ' km</strong></div>'
        + '<div><span>Duration</span><strong>' + fmt((b.duration_minutes || 0) / 60) + ' hr</strong></div>'
        + '<div><span>Base fare</span><strong>' + fmt(b.base_fare || 0) + '</strong></div>'
        + '<div><span>Distance fare</span><strong>' + fmt(b.distance_fare || 0) + '</strong></div>'
        + '<div><span>Rental fare</span><strong>' + fmt(b.rental_fare || 0) + '</strong></div>'
        + '<div><span>Waiting</span><strong>' + fmt(b.waiting_fare || 0) + '</strong></div>'
        + '<div><span>Extra km</span><strong>' + fmt(b.extra_km_fare || 0) + '</strong></div>'
        + '<div><span>Night</span><strong>' + fmt(b.night_charge || 0) + '</strong></div>'
        + '<div><span>Peak</span><strong>' + fmt(b.peak_charge || 0) + '</strong></div>'
        + '<div><span>Discount</span><strong>- ' + fmt(b.discount || 0) + '</strong></div>'
        + '<div class="vehicle-booking-total"><span>Total</span><strong>' + (b.currency || 'LKR') + ' ' + fmt(b.total || 0) + '</strong></div>';
    }

    function previewQuote(){
      if (!form) return;
      var data = {
        vehicle_type: vehicleType ? vehicleType.value : 'car',
        trip_type: tripType ? tripType.value : 'one_way',
        rental_unit: (document.getElementById('vb_rental_unit') || {}).value || 'hourly',
        pickup_datetime: pickupDt ? pickupDt.value : '',
        return_datetime: returnDt ? returnDt.value : '',
        coupon_code: couponCode ? couponCode.value : '',
        distance_km: parseFloat((distanceInput && distanceInput.value) || '0') || roughDistanceKm(),
        duration_minutes: parseInt((durationInput && durationInput.value) || '0', 10) || durationFromDates(pickupDt ? pickupDt.value : '', returnDt ? returnDt.value : '') || 45
      };

      if (previewBtn) {
        previewBtn.disabled = true;
        previewBtn.textContent = 'Calculating...';
      }
      return postJson('/vehicle-booking/quote', data).then(function(res){
        if (!res || !res.ok) return;
        var b = res.breakdown || {};
        if (distanceInput) distanceInput.value = String(b.distance_km || data.distance_km || 0);
        if (durationInput) durationInput.value = String(b.duration_minutes || data.duration_minutes || 0);
        if (totalInput) totalInput.value = String(b.total || 0);
        if (pricingJsonInput) pricingJsonInput.value = JSON.stringify(b || {});
        renderBreakdown(b);
        renderAvailable(((res.available || {}).vehicles) || []);
        if (preview) preview.removeAttribute('hidden');
      }).catch(function(){
      }).finally(function(){
        if (previewBtn) {
          previewBtn.disabled = false;
          previewBtn.textContent = 'Preview Price';
        }
      });
    }

    if (tripType) tripType.addEventListener('change', setMode);
    if (previewBtn) previewBtn.addEventListener('click', previewQuote);
    if (pickupDt) pickupDt.addEventListener('change', previewQuote);
    if (returnDt) returnDt.addEventListener('change', previewQuote);
    if (vehicleType) vehicleType.addEventListener('change', previewQuote);
    if (pickupDt) pickupDt.addEventListener('change', function(){ syncReturnMinAndDefault(false); validateDateRange(); });
    if (returnDt) returnDt.addEventListener('change', validateDateRange);

    if (dtNowBtn && pickupDt) {
      dtNowBtn.addEventListener('click', function(){
        var now = new Date();
        setDateValue(pickupDt, pickupPicker, now);
        syncReturnMinAndDefault(true);
        previewQuote();
      });
    }
    if (dtPlus1Btn && pickupDt) {
      dtPlus1Btn.addEventListener('click', function(){
        var base = parseMachineDate(pickupDt.value) || new Date();
        setDateValue(pickupDt, pickupPicker, addHours(base, 1));
        syncReturnMinAndDefault(true);
        previewQuote();
      });
    }
    if (dtTomorrowBtn && pickupDt) {
      dtTomorrowBtn.addEventListener('click', function(){
        var n = new Date();
        n.setDate(n.getDate() + 1);
        setDateValue(pickupDt, pickupPicker, n);
        syncReturnMinAndDefault(true);
        previewQuote();
      });
    }

    if (form) {
      form.addEventListener('submit', function(e){
        if (pickupAuto && !pickupAuto.isValidSelection()) {
          var p = document.getElementById('vb_pickup');
          if (p) {
            p.setCustomValidity('Please select pickup location from suggestions.');
            p.reportValidity();
            p.focus();
          }
          e.preventDefault();
          return false;
        }
        var dropEl = document.getElementById('vb_drop');
        if (dropAuto && dropEl && dropEl.value.trim() !== '' && !dropAuto.isValidSelection()) {
          dropEl.setCustomValidity('Please select drop location from suggestions.');
          dropEl.reportValidity();
          dropEl.focus();
          e.preventDefault();
          return false;
        }
        if (dropEl) dropEl.setCustomValidity('');
        var pickupEl = document.getElementById('vb_pickup');
        if (pickupEl) pickupEl.setCustomValidity('');
        if (!validateDateRange()) {
          if (returnDt) {
            returnDt.setCustomValidity('End time must be after pickup time');
            returnDt.reportValidity();
            returnDt.focus();
          }
          e.preventDefault();
          return false;
        }
        if (returnDt) returnDt.setCustomValidity('');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Booking...';
        }
      });
    }

    initDatePickers();
    setMode();
  }

  function initTripTracker(){
    var root = document.getElementById('tripTrackerRoot');
    if (!root) return;
    var ref = root.getAttribute('data-ref') || '';
    var statusEl = document.getElementById('tripTrackerStatus');
    var bar = document.getElementById('tripTrackerProgress');
    var coords = document.getElementById('tripTrackerCoords');
    if (!ref) return;

    function tick(){
      fetch('/vehicle-booking/tracking/' + encodeURIComponent(ref), {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).then(function(r){ return r.json(); }).then(function(data){
        if (!data || !data.ok) return;
        var p = Math.max(0, Math.min(100, Math.round((Number(data.progress || 0)) * 100)));
        if (statusEl) statusEl.textContent = 'Status: ' + (data.status || 'pending');
        if (bar) {
          bar.style.width = p + '%';
          bar.textContent = p + '%';
        }
        if (coords && data.position) {
          coords.textContent = 'Coordinates: ' + data.position.lat + ', ' + data.position.lng;
        }
      }).catch(function(){});
    }

    tick();
    setInterval(tick, 8000);
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
    initQuoteWidget();
    initVehicleBooking();
    initTripTracker();
  });
})();
