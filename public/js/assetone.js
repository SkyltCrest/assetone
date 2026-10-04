/* =====================================================================
   AssetOne — shared UI behaviour for the glass layout.
   Theme (auto / light / dark), eye comfort, top-bar panels, sidebar,
   logout sequence and small motion effects.
   ===================================================================== */
(function () {
    'use strict';

    var $ = function (id) { return document.getElementById(id); };
    var root = document.documentElement;
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

    var lsGet = function (k, d) { try { var v = localStorage.getItem(k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } };
    var lsSet = function (k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };

    /* ---------- Modals ----------
       Glass cards (backdrop-filter) and .main-content create stacking
       contexts that would trap a modal underneath Bootstrap's backdrop,
       so move every page modal up to <body>. */
    document.querySelectorAll('.main-content .modal').forEach(function (m) { document.body.appendChild(m); });

    /* ---------- Toast ---------- */
    function showToast(msg) {
        var el = $('liveToast');
        if (!el || !window.bootstrap) return;
        $('toastMessage').textContent = msg;
        bootstrap.Toast.getOrCreateInstance(el).show();
    }
    window.aoToast = showToast;

    /* ---------- Mobile sidebar ---------- */
    var sidebar = $('sidebar'), overlay = $('sidebarOverlay');
    function openSidebar() { sidebar.classList.add('show'); overlay.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('show'); overlay.classList.remove('show'); }
    if ($('sidebarToggle')) $('sidebarToggle').addEventListener('click', openSidebar);
    if ($('closeSidebarBtn')) $('closeSidebarBtn').addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
    addEventListener('resize', function () { if (innerWidth >= 992 && sidebar) closeSidebar(); });

    // Remember which sidebar groups the user leaves open/closed.
    document.querySelectorAll('.sidebar-toggle').forEach(function (btn) {
        var sel = btn.getAttribute('data-bs-target');
        var target = document.querySelector(sel);
        if (!target) return;
        var key = 'sidebar-group:' + sel;
        var hasActive = !!target.querySelector('.nav-link.active');
        var stored = null;
        try { stored = localStorage.getItem(key); } catch (e) {}
        if (!hasActive && stored === 'closed') {
            target.classList.remove('show');
            btn.setAttribute('aria-expanded', 'false');
        } else if (stored === 'open') {
            target.classList.add('show');
            btn.setAttribute('aria-expanded', 'true');
        }
        target.addEventListener('shown.bs.collapse', function () { try { localStorage.setItem(key, 'open'); } catch (e) {} });
        target.addEventListener('hidden.bs.collapse', function () { try { localStorage.setItem(key, 'closed'); } catch (e) {} });
    });

    /* ---------- Live clock ---------- */
    function tick() {
        var d = new Date();
        if ($('clock')) {
            $('clock').textContent = d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
                + ' • ' + d.toLocaleTimeString('en-GB');
        }
        var g = $('greeting');
        if (g && g.dataset.name) {
            var h = d.getHours();
            g.textContent = (h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening') + ', ' + g.dataset.name + '! 👋';
        }
    }
    tick(); setInterval(tick, 1000);

    /* ---------- Theme (auto by time of day / light / dark) ---------- */
    var THEME_KEY = 'assetone_theme', EYE_KEY = 'assetone_eye';
    var SCHEDULE = { light: 6.5, dusk: 16, dark: 19 };   // 24h clock
    var PHASES = {
        light: { icon: 'bi-sun-fill', label: 'Morning · Light' },
        dusk:  { icon: 'bi-sunset-fill', label: 'Evening · Dusk' },
        dark:  { icon: 'bi-moon-stars-fill', label: 'Night · Dark' }
    };
    var themeMode = lsGet(THEME_KEY, 'auto');
    var curPhase = null;

    function phaseNow() {
        var d = new Date(), h = d.getHours() + d.getMinutes() / 60;
        if (h >= SCHEDULE.light && h < SCHEDULE.dusk) return 'light';
        if (h >= SCHEDULE.dusk && h < SCHEDULE.dark) return 'dusk';
        return 'dark';
    }

    function applyTheme() {
        var phase = themeMode === 'auto' ? phaseNow() : themeMode;
        if (phase !== curPhase) {
            if (curPhase && !reduced) { root.classList.add('theme-anim'); setTimeout(function () { root.classList.remove('theme-anim'); }, 1300); }
            curPhase = phase;
            root.setAttribute('data-theme', phase);
            document.dispatchEvent(new CustomEvent('ao:theme', { detail: { phase: phase } }));
        }
        if ($('themeIcon')) $('themeIcon').className = 'bi fs-6 ' + PHASES[phase].icon;
        if ($('themeAuto')) $('themeAuto').classList.toggle('d-none', themeMode !== 'auto');
        if ($('themeBtn')) $('themeBtn').title = (themeMode === 'auto' ? 'Auto · ' : 'Manual · ') + PHASES[phase].label + ' (click to change)';
        document.querySelectorAll('[data-theme-set]').forEach(function (b) { b.classList.toggle('active', b.dataset.themeSet === themeMode); });
        applyEye();
    }
    window.aoTheme = function () { return curPhase || phaseNow(); };
    window.aoThemeMode = function () { return themeMode; };

    if ($('themeBtn')) $('themeBtn').addEventListener('click', function () {
        themeMode = { auto: 'light', light: 'dark', dark: 'auto' }[themeMode] || 'auto';
        lsSet(THEME_KEY, themeMode); applyTheme();
        showToast(themeMode === 'auto' ? 'Theme: Auto (' + PHASES[phaseNow()].label + ')' : 'Theme: ' + (themeMode === 'light' ? 'Light' : 'Dark'));
    });
    document.querySelectorAll('[data-theme-set]').forEach(function (b) {
        b.addEventListener('click', function () { themeMode = b.dataset.themeSet; lsSet(THEME_KEY, themeMode); applyTheme(); });
    });
    setInterval(applyTheme, 30000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) applyTheme(); });

    /* ---------- Eye comfort ---------- */
    var eye = Object.assign({ on: true, auto: true, level: 50 }, lsGet(EYE_KEY, {}));
    var AUTO_LEVEL = { light: 0, dusk: 35, dark: 60 };
    function eyeLevel() { return !eye.on ? 0 : eye.auto ? AUTO_LEVEL[curPhase || phaseNow()] : eye.level; }
    function eyeEls(kind) { return document.querySelectorAll('[data-eye="' + kind + '"]'); }
    function applyEye() {
        var lv = eyeLevel();
        if ($('eyeShade')) $('eyeShade').style.opacity = (lv / 100 * .5).toFixed(3);
        eyeEls('on').forEach(function (el) { el.checked = eye.on; });
        eyeEls('auto').forEach(function (el) { el.checked = eye.auto; });
        eyeEls('level').forEach(function (el) { el.value = lv; el.disabled = !eye.on || eye.auto; });
        eyeEls('value').forEach(function (el) { el.textContent = lv + '%'; });
        if ($('eyeBtn')) $('eyeBtn').classList.toggle('active-tool', eye.on && lv > 0);
    }
    eyeEls('on').forEach(function (el) {
        el.addEventListener('change', function (e) { eye.on = e.target.checked; lsSet(EYE_KEY, eye); applyEye(); });
    });
    eyeEls('auto').forEach(function (el) {
        el.addEventListener('change', function (e) { eye.auto = e.target.checked; if (!eye.auto) eye.level = eyeLevel() || 50; lsSet(EYE_KEY, eye); applyEye(); });
    });
    eyeEls('level').forEach(function (el) {
        el.addEventListener('input', function (e) { eye.level = +e.target.value; lsSet(EYE_KEY, eye); applyEye(); });
    });

    /* ---------- Top-bar panels ---------- */
    function togglePanel(id) {
        document.querySelectorAll('.nav-panel').forEach(function (p) {
            if (p.id === id) p.classList.toggle('show'); else p.classList.remove('show');
        });
    }
    document.querySelectorAll('[data-panel]').forEach(function (btn) {
        btn.addEventListener('click', function (e) { e.preventDefault(); togglePanel(btn.getAttribute('data-panel')); });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.nav-tool')) document.querySelectorAll('.nav-panel.show').forEach(function (p) { p.classList.remove('show'); });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') document.querySelectorAll('.nav-panel.show').forEach(function (p) { p.classList.remove('show'); });
    });

    applyTheme();

    /* ---------- Spotlight, parallax, cursor glow, stat-card tilt ---------- */
    var SPOT = '.g,.content-card,.form-card,.card';
    document.addEventListener('pointermove', function (e) {
        var c = e.target.closest && e.target.closest(SPOT);
        if (c) { var r = c.getBoundingClientRect(); c.style.setProperty('--mx', (e.clientX - r.left) + 'px'); c.style.setProperty('--my', (e.clientY - r.top) + 'px'); }
        if (reduced) return;
        var glow = $('cursorGlow');
        if (glow) glow.style.transform = 'translate(' + e.clientX + 'px,' + e.clientY + 'px) translate(-50%,-50%)';
        var nx = e.clientX / innerWidth - .5, ny = e.clientY / innerHeight - .5;
        document.querySelectorAll('.blob').forEach(function (b) { b.style.transform = 'translate(' + (nx * b.dataset.d * 3) + 'px,' + (ny * b.dataset.d * 3) + 'px)'; });
    }, { passive: true });
    if (!reduced) document.querySelectorAll('.stat-card').forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var r = card.getBoundingClientRect(), px = (e.clientX - r.left) / r.width - .5, py = (e.clientY - r.top) / r.height - .5;
            card.style.transform = 'perspective(700px) rotateY(' + (px * 8) + 'deg) rotateX(' + (-py * 8) + 'deg) translateY(-6px)';
        });
        card.addEventListener('mouseleave', function () { card.style.transform = ''; });
    });

    /* ---------- Scroll reveal + back-to-top + "/" focuses search ---------- */
    // (Skipped while the window has no height, e.g. a hidden tab, where "below the fold" cannot be judged.)
    if (!reduced && innerHeight > 0 && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } });
        }, { threshold: .08 });
        document.querySelectorAll('.main-content .content-card,.main-content .stat-card,.main-content .card').forEach(function (el, i) {
            if (el.closest('.modal') || el.classList.contains('animate-in')) return;
            if (el.getBoundingClientRect().top < innerHeight) return;
            el.classList.add('ao-reveal'); el.style.transitionDelay = Math.min(i % 6, 5) * 40 + 'ms'; io.observe(el);
        });
    }
    var top = document.createElement('button');
    top.type = 'button'; top.className = 'ao-top'; top.setAttribute('aria-label', 'Back to top');
    top.innerHTML = '<i class="bi bi-arrow-up"></i>';
    document.body.appendChild(top);
    addEventListener('scroll', function () { top.classList.toggle('show', scrollY > 400); }, { passive: true });
    top.onclick = function () { scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' }); };
    document.addEventListener('keydown', function (e) {
        if (e.key !== '/' || e.ctrlKey || e.metaKey) return;
        if (/INPUT|TEXTAREA|SELECT/.test((document.activeElement || {}).tagName || '')) return;
        var s = document.querySelector('.main-content input[type=search],.main-content input[name=search],.main-content input[name=q]');
        if (s) { e.preventDefault(); s.focus(); }
    });

    /* ---------- Logout: confirmation + sign-out sequence ---------- */
    var logoutLink = $('logoutLink'), logoutForm = $('logoutForm'), modalEl = $('logoutConfirmModal'),
        overlayEl = $('logoutOverlay'), content = $('logoutContent'), confirmBtn = $('confirmLogoutBtn');
    var LO_SS = 'assetone_session_start', timers = [], busy = false, modal = null;
    try { if (!sessionStorage.getItem(LO_SS)) sessionStorage.setItem(LO_SS, String(Date.now())); } catch (e) {}

    function loFill() {
        var start = 0; try { start = +sessionStorage.getItem(LO_SS); } catch (e) {}
        var m = Math.max(0, Math.floor((Date.now() - (start || Date.now())) / 60000));
        $('loDur').textContent = m < 1 ? 'Just now' : m < 60 ? m + ' min' : Math.floor(m / 60) + 'h ' + (m % 60) + 'm';
        var nm = { light: 'Light', dusk: 'Dusk', dark: 'Dark' }[curPhase] || curPhase;
        $('loTheme').textContent = nm + (themeMode === 'auto' ? ' (auto)' : '');
        if ($('loClear')) $('loClear').checked = false;
    }
    function loClearData(reset) {
        try { sessionStorage.removeItem(LO_SS); } catch (e) {}
        if (!reset) return;
        try {
            [THEME_KEY, EYE_KEY].forEach(function (k) { localStorage.removeItem(k); });
            Object.keys(localStorage).forEach(function (k) { if (k.indexOf('sidebar-group:') === 0) localStorage.removeItem(k); });
        } catch (e) {}
    }
    function loAbort() {
        timers.forEach(clearTimeout); timers = []; busy = false;
        overlayEl.classList.remove('active'); overlayEl.setAttribute('aria-hidden', 'true');
        setTimeout(function () { if (!busy) content.innerHTML = ''; }, 600);
        showToast("Logout cancelled. You're still signed in.");
    }
    function loGo() { logoutForm.submit(); }

    // Goodbye screen with a short countdown before returning to the login page.
    function loSuccess() {
        var h = new Date().getHours(), bye = h < 12 ? 'Have a great day' : h < 18 ? 'Have a wonderful afternoon' : 'Have a restful evening';
        var nameEl = document.querySelector('#logoutConfirmModal .lo-user b'), first = nameEl ? nameEl.textContent.trim().split(/\s+/)[0] : '';
        content.innerHTML = '<div class="lo-stage"><div class="lo-okwrap"><svg class="lo-ok" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45"/><path d="M30 52 L44 66 L71 36"/></svg></div>'
            + '<h4>You\'re signed out</h4><p id="loBye"></p>'
            + '<div class="lo-cnt"><div class="lo-cring"><svg viewBox="0 0 52 52"><circle class="bg" cx="26" cy="26" r="22"/><circle class="fg" id="loRingC" cx="26" cy="26" r="22"/></svg><b id="loSec">3</b></div><span>Returning to the login page</span></div>'
            + '<button type="button" class="btn lo-confirm w-100 lo-go" id="loNow"><span>Go to login now</span><i class="bi bi-arrow-right"></i></button></div>';
        $('loBye').textContent = bye + (first ? ', ' + first : '') + '. See you again soon.';
        $('loNow').addEventListener('click', loGo); $('loNow').focus();
        var ring = $('loRingC');
        requestAnimationFrame(function () { requestAnimationFrame(function () { ring.style.strokeDashoffset = '138.2'; }); });
        var s = 3, iv = setInterval(function () {
            s--; var el = $('loSec'); if (el) el.textContent = Math.max(s, 0);
            if (s <= 0) { clearInterval(iv); loGo(); }
        }, 1000);
    }

    function loStart() {
        if (busy) return; busy = true;
        var reset = !!($('loClear') && $('loClear').checked), T = reduced ? 120 : 480;
        if (modal) modal.hide();
        content.innerHTML = '<div class="lo-stage"><div class="lo-ring"><i class="bi bi-shield-lock"></i></div><h4>Signing you out...</h4><p>Securing your session</p>'
            + '<ul class="lo-steps"><li><i class="bi bi-circle"></i>Saving your preferences</li><li><i class="bi bi-circle"></i>Clearing session data</li><li><i class="bi bi-circle"></i>Securing your account</li></ul>'
            + '<div class="lo-bar"><i id="loBar"></i></div><button type="button" class="btn lo-cancel w-100 mt-3" id="loAbort"><i class="bi bi-x-lg me-2"></i>Cancel</button></div>';
        overlayEl.classList.add('active'); overlayEl.setAttribute('aria-hidden', 'false');
        var steps = [].slice.call(content.querySelectorAll('.lo-steps li')), bar = $('loBar');
        var set = function (i, st) { var li = steps[i]; li.classList.remove('doing', 'done'); li.classList.add(st); li.querySelector('i').className = 'bi ' + (st === 'done' ? 'bi-check-circle-fill' : 'bi-arrow-repeat'); };
        steps.forEach(function (_, i) {
            timers.push(setTimeout(function () { if (i > 0) set(i - 1, 'done'); set(i, 'doing'); bar.style.width = (12 + i * 28) + '%'; }, 250 + i * T));
        });
        timers.push(setTimeout(function () {
            set(2, 'done'); bar.style.width = '100%';
            var a = $('loAbort'); if (a) a.disabled = true;
            loClearData(reset);
        }, 250 + 3 * T));
        timers.push(setTimeout(loSuccess, 250 + 3 * T + 450));
        $('loAbort').addEventListener('click', loAbort);
    }
    if (logoutLink && modalEl && logoutForm && window.bootstrap) {
        modal = new bootstrap.Modal(modalEl);
        logoutLink.addEventListener('click', function (e) { e.preventDefault(); loFill(); modal.show(); });
        modalEl.addEventListener('shown.bs.modal', function () { if (confirmBtn) confirmBtn.focus(); });
        modalEl.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !busy) { e.preventDefault(); loStart(); } });
        var card = $('loCard');
        card.addEventListener('mousemove', function (e) {
            var r = card.getBoundingClientRect(), px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
            card.style.setProperty('--gx', px * 100 + '%'); card.style.setProperty('--gy', py * 100 + '%');
            if (!reduced) { card.style.setProperty('--ry', (px - .5) * 8 + 'deg'); card.style.setProperty('--rx', (.5 - py) * 8 + 'deg'); }
        });
        card.addEventListener('mouseleave', function () { card.style.setProperty('--rx', '0deg'); card.style.setProperty('--ry', '0deg'); });
        if (confirmBtn) confirmBtn.addEventListener('click', loStart);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && busy) { var a = $('loAbort'); if (a && !a.disabled) loAbort(); }
        });
    }
})();
