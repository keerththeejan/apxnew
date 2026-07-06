/**
 * APX Premium UX Layer — presentation only; no business logic changes.
 */
(function () {
  "use strict";

  var prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* —— Ripple on primary buttons —— */
  function initRipple() {
    if (prefersReducedMotion) return;
    var selectors = ".btn-cta, .btn-primary, .service-card, .home-whatsapp-fab";
    document.addEventListener("click", function (e) {
      var el = e.target.closest(selectors);
      if (!el || el.disabled) return;
      var rect = el.getBoundingClientRect();
      var size = Math.max(rect.width, rect.height);
      var ripple = document.createElement("span");
      ripple.className = "apx-ripple";
      ripple.style.width = ripple.style.height = size + "px";
      ripple.style.left = e.clientX - rect.left - size / 2 + "px";
      ripple.style.top = e.clientY - rect.top - size / 2 + "px";
      if (!el.classList.contains("apx-ripple-host")) {
        el.classList.add("apx-ripple-host");
      }
      el.appendChild(ripple);
      ripple.addEventListener("animationend", function () {
        ripple.remove();
      });
    });
  }

  /* —— Back to top —— */
  function initBackToTop() {
    var btn = document.getElementById("apxBackToTop");
    if (!btn) return;
    var toggle = function () {
      if (window.scrollY > 400) {
        btn.classList.add("is-visible");
      } else {
        btn.classList.remove("is-visible");
      }
    };
    window.addEventListener("scroll", toggle, { passive: true });
    toggle();
    btn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: prefersReducedMotion ? "auto" : "smooth" });
    });
  }

  /* —— Scroll reveal handled by script.js (.js-reveal) —— */

  /* —— Counter animation —— */
  function initCounters() {
    if (prefersReducedMotion) return;
    var counters = document.querySelectorAll("[data-apx-counter]");
    if (!counters.length || !("IntersectionObserver" in window)) return;
    var animate = function (el) {
      var target = parseInt(el.getAttribute("data-apx-counter") || "0", 10);
      var suffix = el.getAttribute("data-apx-suffix") || "";
      var duration = 1200;
      var start = 0;
      var startTime = null;
      function step(ts) {
        if (!startTime) startTime = ts;
        var progress = Math.min((ts - startTime) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(start + (target - start) * eased) + suffix;
        if (progress < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    };
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animate(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(function (c) { io.observe(c); });
  }

  /* —— Form: double-submit prevention + loading state —— */
  function initForms() {
    document.querySelectorAll("form[method='post'], form[method='POST']").forEach(function (form) {
      if (form.id === "apx-quote-widget-form") return;
      form.addEventListener("submit", function (e) {
        if (form.dataset.apxLocked === "1") {
          e.preventDefault();
          return;
        }
        if (form.checkValidity && !form.checkValidity()) {
          return;
        }
        form.dataset.apxLocked = "1";
        var btn = form.querySelector("[type='submit']");
        if (!btn) return;
        setTimeout(function () {
          if (!btn.querySelector(".btn-label")) {
            var span = document.createElement("span");
            span.className = "btn-label";
            span.textContent = btn.textContent;
            btn.textContent = "";
            btn.appendChild(span);
          }
          btn.classList.add("is-loading");
          btn.disabled = true;
        }, 0);
      });
    });
  }

  /* —— Floating labels —— */
  function initFloatingLabels() {
    document.querySelectorAll(".needs-validation, .form-card form, #tms-application-form, form[action*='contact-messages']").forEach(function (form) {
      form.querySelectorAll(".col-12, .col-md-6, .mb-3").forEach(function (wrap) {
        var input = wrap.querySelector(".form-control, .form-select");
        var label = wrap.querySelector(".form-label");
        if (!input || !label || wrap.querySelector(".apx-float-group")) return;
        if (input.tagName === "SELECT" && input.querySelector('option[value=""]')) {
          input.removeAttribute("required");
        }
        var group = document.createElement("div");
        group.className = "apx-float-group";
        input.parentNode.insertBefore(group, input);
        group.appendChild(input);
        group.appendChild(label);
        if (input.value) group.classList.add("is-filled");
        input.addEventListener("input", function () {
          group.classList.toggle("is-filled", input.value.trim() !== "");
        });
        input.addEventListener("change", function () {
          group.classList.toggle("is-filled", input.value.trim() !== "");
        });
        if (!input.getAttribute("placeholder")) {
          input.setAttribute("placeholder", " ");
        }
      });
    });
  }

  /* —— Responsive table wrappers —— */
  function wrapTables() {
    document.querySelectorAll("main table.table, main .table").forEach(function (table) {
      if (table.closest(".apx-table-wrap")) return;
      var wrap = document.createElement("div");
      wrap.className = "apx-table-wrap";
      table.parentNode.insertBefore(wrap, table);
      wrap.appendChild(table);
    });
  }

  /* —— Lazy image fade-in —— */
  function initLazyImages() {
    document.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
      if (img.complete) {
        img.classList.add("apx-loaded");
      } else {
        img.addEventListener("load", function () { img.classList.add("apx-loaded"); }, { once: true });
      }
    });
  }

  /* —— Smooth anchor scroll —— */
  function initSmoothAnchors() {
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      var id = a.getAttribute("href");
      if (!id || id === "#") return;
      a.addEventListener("click", function (e) {
        var target = document.querySelector(id);
        if (!target) return;
        e.preventDefault();
        target.scrollIntoView({ behavior: prefersReducedMotion ? "auto" : "smooth", block: "start" });
        target.setAttribute("tabindex", "-1");
        target.focus({ preventScroll: true });
      });
    });
  }

  /* —— Mobile menu: close on link click —— */
  function initMobileNav() {
    var menu = document.getElementById("navbar-menu");
    var hamb = document.querySelector(".hamb");
    if (!menu || !hamb) return;
    menu.querySelectorAll("a[data-nav], .nav-cta a").forEach(function (link) {
      link.addEventListener("click", function () {
        if (window.innerWidth <= 991 && menu.classList.contains("open")) {
          menu.classList.remove("open");
          hamb.setAttribute("aria-expanded", "false");
        }
      });
    });
  }

  function initPageLoad() {
    document.body.classList.add("apx-page-loaded");
  }

  function boot() {
    initPageLoad();
    initRipple();
    initBackToTop();
    initCounters();
    initForms();
    initFloatingLabels();
    wrapTables();
    initLazyImages();
    initSmoothAnchors();
    initMobileNav();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
