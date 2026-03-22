/**
 * Public site: theme (light/dark/auto + localStorage) and live clock from #apx-public-config.
 */
(function () {
  function readConfig() {
    var el = document.getElementById('apx-public-config');
    if (!el) return null;
    try {
      return JSON.parse(el.textContent || '{}');
    } catch (e) {
      return null;
    }
  }

  function prefersDark() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  }

  function applyDomTheme(mode) {
    var html = document.documentElement;
    var body = document.body;
    var m = mode === 'dark' ? 'dark' : 'light';
    html.setAttribute('data-bs-theme', m);
    html.setAttribute('data-theme', m);
    if (body) {
      body.classList.remove('theme-light', 'theme-dark');
      body.classList.add(m === 'dark' ? 'theme-dark' : 'theme-light');
    }
    var btn = document.getElementById('themeToggle');
    if (btn) {
      btn.setAttribute('data-apx-theme', m);
      var sun = '<i class="bi bi-sun-fill" aria-hidden="true"></i>';
      var moon = '<i class="bi bi-moon-stars-fill" aria-hidden="true"></i>';
      btn.innerHTML = m === 'dark' ? sun : moon;
      btn.setAttribute('aria-label', m === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
      btn.setAttribute('title', m === 'dark' ? 'Light mode' : 'Dark mode');
    }
  }

  function withTransition(fn) {
    var html = document.documentElement;
    html.classList.add('apx-theme-transitioning');
    window.setTimeout(function () {
      fn();
      window.setTimeout(function () {
        html.classList.remove('apx-theme-transitioning');
      }, 280);
    }, 0);
  }

  function resolveTheme(cfg, storageKey, mediaDark) {
    if (!cfg || !cfg.themeEnabled) {
      return 'light';
    }
    var saved = null;
    try {
      saved = localStorage.getItem(storageKey);
    } catch (e) {}
    if (saved === 'dark' || saved === 'light') {
      return saved;
    }
    var tm = cfg.themeMode || 'light';
    if (tm === 'dark') return 'dark';
    if (tm === 'light') return 'light';
    return mediaDark ? 'dark' : 'light';
  }

  function initTheme(cfg) {
    var storageKey = (cfg && cfg.storageKey) || 'tms_theme';
    var mq = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var mediaDark = mq ? mq.matches : false;

    function currentResolved() {
      return resolveTheme(cfg, storageKey, mediaDark);
    }

    function apply() {
      applyDomTheme(currentResolved());
    }

    if (!cfg || !cfg.themeEnabled) {
      applyDomTheme('light');
      var btnOff = document.getElementById('themeToggle');
      if (btnOff) btnOff.style.display = 'none';
      return;
    }

    apply();

    if (!cfg.themeSwitcher) {
      var btnHide = document.getElementById('themeToggle');
      if (btnHide) btnHide.style.display = 'none';
    }

    var btn = document.getElementById('themeToggle');
    if (btn && cfg.themeSwitcher) {
      btn.addEventListener('click', function () {
        var cur = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        var next = cur === 'dark' ? 'light' : 'dark';
        withTransition(function () {
          try {
            localStorage.setItem(storageKey, next);
          } catch (e) {}
          applyDomTheme(next);
        });
      });
    }

    if (mq && cfg.themeMode === 'auto') {
      mq.addEventListener('change', function (e) {
        mediaDark = e.matches;
        var saved = null;
        try {
          saved = localStorage.getItem(storageKey);
        } catch (err) {}
        if (saved === 'dark' || saved === 'light') return;
        withTransition(apply);
      });
    }
  }

  function initClock(cfg) {
    var el = document.getElementById('liveClock');
    if (!el || !cfg || !cfg.clockEnabled) return;

    var tz = (cfg.timezone && String(cfg.timezone).trim()) || 'UTC';
    var hour12 = cfg.clockFormat === '12';
    var locale = hour12 ? 'en-US' : 'en-GB';

    function tick() {
      var now = new Date();
      try {
        el.textContent = new Intl.DateTimeFormat(locale, {
          timeZone: tz,
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: hour12,
        }).format(now);
      } catch (e) {
        try {
          el.textContent = new Intl.DateTimeFormat(locale, {
            timeZone: 'UTC',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: hour12,
          }).format(now);
        } catch (e2) {
          el.textContent = now.toLocaleTimeString();
        }
      }
      try {
        el.setAttribute('datetime', now.toISOString());
      } catch (e) {}
    }

    tick();
    window.setInterval(tick, 1000);
  }

  function boot() {
    var cfg = readConfig();
    initTheme(cfg);
    initClock(cfg);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
