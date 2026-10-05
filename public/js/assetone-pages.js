/* =====================================================================
   AssetOne — shared page behaviour: count-up, ripple, photo lightbox,
   sortable tables, CSV export, row stagger and keyboard shortcuts.
   ===================================================================== */
(function () {
    'use strict';
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    var $ = function (id) { return document.getElementById(id); };

    /* ---------- Count-up on stat cards ---------- */
    if (!reduced) document.querySelectorAll('.main-content .stat-card h2, .main-content .stat-card h3').forEach(function (el) {
        if (el.dataset.count !== undefined) return;            // the dashboard animates its own
        var raw = el.textContent.trim(), to = parseFloat(raw.replace(/,/g, ''));
        if (!/^[\d,]+(\.\d+)?$/.test(raw) || !to) return;
        var dec = (raw.split('.')[1] || '').length, t0 = performance.now();
        var fmt = function (v) { return v.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec }); };
        var shown = raw;
        (function f(t) {
            if (el.textContent !== shown) return;              // a live refresh has put a new figure in
            var p = Math.min((t - t0) / 700, 1);
            el.textContent = shown = p < 1 ? fmt(to * (1 - Math.pow(1 - p, 3))) : raw;
            if (p < 1) requestAnimationFrame(f);
        })(t0);
    });

    /* ---------- Button ripple ---------- */
    if (!reduced) document.addEventListener('click', function (e) {
        var b = e.target.closest && e.target.closest('.btn:not(.btn-close):not(.btn-link)');
        // Top-bar tool buttons are skipped: their badges overhang, so they cannot clip a ripple.
        if (!b || b.parentElement.classList.contains('nav-tool')) return;
        var r = b.getBoundingClientRect(), z = Math.max(r.width, r.height), sp = document.createElement('span');
        sp.className = 'ripple';
        sp.style.cssText = 'width:' + z + 'px;height:' + z + 'px;left:' + (e.clientX - r.left - z / 2) + 'px;top:' + (e.clientY - r.top - z / 2) + 'px';
        b.appendChild(sp); setTimeout(function () { sp.remove(); }, 650);
    });

    /* ---------- Photo lightbox ---------- */
    var lb = $('lb');
    function closeLb() { if (lb) lb.classList.remove('show'); }
    if (lb) {
        document.addEventListener('click', function (e) {
            var im = e.target.closest && e.target.closest('[data-lightbox]');
            if (im) {
                e.preventDefault(); e.stopPropagation();
                $('lbImg').src = im.dataset.lightbox || im.currentSrc || im.src;
                $('lbName').textContent = im.dataset.lbName || im.alt || '';
                $('lbInfo').textContent = im.dataset.lbInfo || '';
                lb.classList.add('show');
                return;
            }
            if (e.target === lb || (e.target.closest && e.target.closest('#lbClose'))) closeLb();
        }, true);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLb(); });
    }

    /* ---------- Row stagger ---------- */
    function staggerRows(root) {
        root.querySelectorAll('table tbody').forEach(function (tb) {
            [].slice.call(tb.rows).forEach(function (r, i) {
                if (r.classList.contains('row-in')) return;
                r.classList.add('row-stagger'); r.style.setProperty('--i', Math.min(i, 12));
            });
        });
    }
    var mainEl = document.querySelector('.main-content');
    if (mainEl) staggerRows(mainEl);

    /* ---------- Click-to-sort headers (sorts the rows shown on this page) ---------- */
    function sortableTables(root) { root.querySelectorAll('table[data-sortable]').forEach(function (table) {
        var tb = table.tBodies[0], head = table.tHead && table.tHead.rows[0];
        if (!tb || !head) return;

        // Lists sorted by the server: a header click reloads the page in that order,
        // so the sort covers every record rather than the rows on this page.
        if (table.dataset.serverSort !== undefined) {
            [].slice.call(head.cells).forEach(function (th) {
                var k = th.dataset.sortKey; if (!k) return;
                th.classList.add('sortable'); th.title = 'Click to sort';
                if (table.dataset.sort === k) th.classList.add(table.dataset.dir === 'desc' ? 'desc' : 'asc');
                th.addEventListener('click', function () {
                    var url = new URL(location.href);
                    url.searchParams.set('sort', k);
                    url.searchParams.set('dir', table.dataset.sort === k && table.dataset.dir !== 'desc' ? 'desc' : 'asc');
                    url.searchParams.delete('page');
                    liveGo(url.toString(), true);
                });
            });
            return;
        }
        var state = { i: null, dir: 1 };
        var key = function (cell) {
            if (!cell) return '';
            if (cell.dataset.sort !== undefined) return cell.dataset.sort;
            return cell.textContent.replace(/\s+/g, ' ').trim();
        };
        [].slice.call(head.cells).forEach(function (th, i) {
            var name = th.textContent.trim();
            if (/^(No\.|Action|Picture|Photo|)$/.test(name) || th.dataset.nosort !== undefined) return;
            th.classList.add('sortable'); th.title = 'Click to sort';
            th.addEventListener('click', function () {
                state.dir = state.i === i && state.dir === 1 ? -1 : 1; state.i = i;
                var rows = [].slice.call(tb.rows).filter(function (r) { return r.cells.length > 1; });
                rows.sort(function (a, b) { return state.dir * key(a.cells[i]).localeCompare(key(b.cells[i]), undefined, { numeric: true, sensitivity: 'base' }); });
                rows.forEach(function (r) { tb.appendChild(r); });
                [].slice.call(head.cells).forEach(function (h, j) {
                    h.classList.toggle('asc', j === i && state.dir === 1);
                    h.classList.toggle('desc', j === i && state.dir === -1);
                });
            });
        });
    }); }
    sortableTables(document);

    /* ---------- Export the rows on screen as CSV ---------- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('[data-export]');
        if (!btn) return;
        var pane = btn.closest('.tab-pane');
        var table = btn.dataset.exportTable ? document.querySelector(btn.dataset.exportTable)
            : (pane || document.querySelector('.main-content')).querySelector('table');
        if (!table || !table.tHead) return;
        var heads = [].slice.call(table.tHead.rows[0].cells);
        var keep = heads.map(function (h) { return !/^(Action|Picture|Photo|)$/.test(h.textContent.trim()); });
        var clean = function (c) { return c.textContent.replace(/\s+/g, ' ').trim(); };
        var rows = [].slice.call(table.tBodies[0].rows).filter(function (r) { return r.cells.length === heads.length && !r.classList.contains('row-hidden'); });
        var data = [heads.filter(function (h, i) { return keep[i]; }).map(clean)].concat(rows.map(function (r) {
            return [].slice.call(r.cells).filter(function (c, i) { return keep[i]; }).map(clean);
        }));
        var csv = data.map(function (r) { return r.map(function (v) { return '"' + v.replace(/"/g, '""') + '"'; }).join(','); }).join('\r\n');
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv' }));
        a.download = btn.dataset.export + '.csv'; a.click();
        if (window.aoToast) window.aoToast(rows.length + ' row(s) exported to ' + btn.dataset.export + '.csv');
    });

    /* ---------- Shortcuts: any element with data-key="n" etc. is clicked by that key ---------- */
    document.addEventListener('keydown', function (e) {
        if (/INPUT|TEXTAREA|SELECT/.test(e.target.tagName) || e.ctrlKey || e.metaKey || e.altKey || document.querySelector('.modal.show')) return;
        var k = e.key.toLowerCase();
        if (k.length !== 1) return;
        var pane = document.querySelector('.main-content .tab-pane.active');
        var el = (pane && pane.querySelector('[data-key="' + k + '"]')) || document.querySelector('.main-content [data-key="' + k + '"]');
        if (el) { e.preventDefault(); el.click(); }
    });

    /* ---------- Styled confirmation for forms marked data-confirm ---------- */
    var confirmEl = $('confirmModal'), pending = null;
    if (confirmEl && window.bootstrap) {
        var confirmModal = new bootstrap.Modal(confirmEl);
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form.matches || !form.matches('form[data-confirm]') || form.dataset.confirmed) return;
            e.preventDefault();
            pending = form;
            $('confirmModalText').textContent = form.dataset.confirm;
            var danger = /^(delete|remove|clear|reject)/i.test(form.dataset.confirm), yes = $('confirmModalYes');
            $('confirmModalTitle').innerHTML = danger ? '<i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm ' + (/^reject/i.test(form.dataset.confirm) ? 'Reject' : 'Delete') : '<i class="bi bi-question-circle me-2"></i>Please Confirm';
            yes.className = 'btn ' + (danger ? 'btn-danger' : 'btn-primary');
            yes.textContent = /^reject/i.test(form.dataset.confirm) ? 'Reject' : danger ? 'Delete' : 'Confirm';
            // Close any pop-up the form sits in first, so the two do not stack.
            var open = form.closest('.modal.show');
            if (open) bootstrap.Modal.getOrCreateInstance(open).hide();
            confirmModal.show();
        }, true);
        $('confirmModalYes').addEventListener('click', function () {
            if (!pending) return;
            pending.dataset.confirmed = '1';
            confirmModal.hide();
            pending.submit();
        });
        confirmEl.addEventListener('hidden.bs.modal', function () { if (pending && !pending.dataset.confirmed) pending = null; });
    }

    /* ---------- QR code: download as a labelled PNG, or print ---------- */
    function loadQr(src) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = reject;
            img.src = src;
        });
    }
    function fitText(ctx, text, maxWidth) {
        var t = String(text);
        while (t.length > 1 && ctx.measureText(t).width > maxWidth) t = t.slice(0, -1);
        return t.length < String(text).length ? t.slice(0, -1) + '…' : t;
    }
    document.addEventListener('click', function (e) {
        var dl = e.target.closest && e.target.closest('[data-qr-download]');
        if (dl) {
            loadQr(dl.dataset.qrSrc).then(function (img) {
                var W = 520, H = 660, canvas = document.createElement('canvas'), ctx = canvas.getContext('2d');
                canvas.width = W; canvas.height = H;
                ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, W, H);
                ctx.drawImage(img, 60, 40, 400, 400);
                ctx.textAlign = 'center';
                ctx.fillStyle = '#0d47a1'; ctx.font = 'bold 30px Arial, sans-serif'; ctx.fillText(dl.dataset.code, W / 2, 495);
                ctx.fillStyle = '#222222'; ctx.font = '22px Arial, sans-serif'; ctx.fillText(fitText(ctx, dl.dataset.name, W - 60), W / 2, 540);
                ctx.fillStyle = '#555555'; ctx.font = '20px Arial, sans-serif'; ctx.fillText(fitText(ctx, 'S/N: ' + dl.dataset.serial, W - 60), W / 2, 578);
                ctx.fillStyle = '#999999'; ctx.font = '15px Arial, sans-serif'; ctx.fillText('AssetOne · MDPT Asset Management', W / 2, 625);
                canvas.toBlob(function (blob) {
                    if (!blob) { if (window.aoToast) window.aoToast('Unable to create the QR image.'); return; }
                    var a = document.createElement('a');
                    a.href = URL.createObjectURL(blob); a.download = dl.dataset.code + '-QR.png'; a.click();
                    if (window.aoToast) window.aoToast('QR code downloaded.');
                }, 'image/png');
            }).catch(function () { if (window.aoToast) window.aoToast('Unable to create the QR image.'); });
            return;
        }
        var pr = e.target.closest && e.target.closest('[data-qr-print]');
        if (pr) {
            var w = window.open('', '_blank');
            if (!w) { if (window.aoToast) window.aoToast('Pop-up blocked. Please allow pop-ups to print.'); return; }
            var d = w.document, esc = function (s) { var x = d.createElement('div'); x.textContent = s; return x.innerHTML; };
            d.write('<html><head><title>' + esc(pr.dataset.code) + '</title><style>body{font-family:Arial,sans-serif;text-align:center;padding:40px}h2{margin-bottom:5px;color:#1565C0}img{margin:25px;width:240px;height:240px}.code{font-size:20px;font-weight:bold;margin-top:10px}.name{color:#495057;margin-top:5px}.serial{color:#6c757d;margin-top:2px;font-size:14px}</style></head>'
                + '<body><h2>AssetOne</h2><p>MDPT Asset Management</p><img src="' + new URL(pr.dataset.qrSrc, location.href).href + '" alt="QR Code" onload="window.print()">'
                + '<div class="code">' + esc(pr.dataset.code) + '</div><p class="name">' + esc(pr.dataset.name) + '</p><p class="serial">S/N: ' + esc(pr.dataset.serial) + '</p></body></html>');
            d.close(); w.focus();
        }
    });

    /* ---------- Tooltips ---------- */
    function tooltips(root) {
        if (window.bootstrap) root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) { bootstrap.Tooltip.getOrCreateInstance(el); });
    }
    tooltips(document);

    /* ---------- Live lists ----------
       Filters, searches, sort, status chips and page links fetch the page in the
       background and swap only the parts marked data-live (the list, its counts
       and its pager), so the rest of the page stays put. Anything that does not
       fit (no marked parts, a redirect elsewhere, a failed request) falls back
       to an ordinary page load. After a swap, "ao:live" fires on document with
       detail.regions (the refreshed parts) and detail.modals (new row pop-ups). */
    var liveSeq = 0;
    function liveParts(doc) { return [].slice.call(doc.querySelectorAll('.main-content [data-live]')); }
    function getForms(doc) {
        return [].slice.call(doc.querySelectorAll('.main-content form')).filter(function (f) { return (f.getAttribute('method') || 'get').toLowerCase() === 'get'; });
    }
    function fields(form) { return [].slice.call(form.elements).filter(function (x) { return x.type !== 'hidden' && x.tagName !== 'BUTTON'; }); }
    // Bring a filter form in line with the page just loaded (chips and Reset change filters too).
    function syncForm(mine, theirs) {
        mine.querySelectorAll('input[type=hidden]').forEach(function (x) { x.remove(); });
        [].slice.call(theirs.querySelectorAll('input[type=hidden]')).reverse().forEach(function (x) { mine.prepend(document.importNode(x, true)); });
        var a = fields(mine), b = fields(theirs);
        if (a.length !== b.length) return;
        a.forEach(function (x, i) {
            if (x === document.activeElement) return;
            if (x.type === 'checkbox' || x.type === 'radio') x.checked = b[i].checked; else x.value = b[i].value;
        });
    }
    function liveApply(doc) {
        var mine = liveParts(document), theirs = liveParts(doc), modals = [];
        if (!theirs.length || theirs.length !== mine.length) return false;

        // Row pop-ups: add the ones for the new rows, drop the ones whose rows have gone.
        [].slice.call(doc.querySelectorAll('.main-content .modal[id]')).forEach(function (m) {
            if (document.getElementById(m.id)) return;
            var n = document.importNode(m, true);
            n.setAttribute('data-page-modal', ''); document.body.appendChild(n); modals.push(n);
        });
        document.querySelectorAll('.modal[data-page-modal][id]').forEach(function (m) {
            if (doc.getElementById(m.id)) return;
            var inst = window.bootstrap && bootstrap.Modal.getInstance(m);
            if (inst) inst.dispose();
            m.remove();
        });

        mine.forEach(function (el, i) {
            if (window.bootstrap) el.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (t) { var tip = bootstrap.Tooltip.getInstance(t); if (tip) tip.dispose(); });
            var n = document.importNode(theirs[i], true);
            while (el.firstChild) el.removeChild(el.firstChild);
            while (n.firstChild) el.appendChild(n.firstChild);
            staggerRows(el); sortableTables(el); tooltips(el);
        });

        var mf = getForms(document), tf = getForms(doc);
        if (mf.length === tf.length) mf.forEach(function (f, i) { syncForm(f, tf[i]); });

        // Export links carry the current filters.
        var mx = mainEl.querySelectorAll('a[href*="export"]'), tx = doc.querySelectorAll('.main-content a[href*="export"]');
        if (mx.length === tx.length) [].forEach.call(mx, function (a, i) { a.setAttribute('href', tx[i].getAttribute('href')); });

        document.dispatchEvent(new CustomEvent('ao:live', { detail: { regions: mine, modals: modals } }));
        return true;
    }
    function liveGo(url, push, from) {
        if (!mainEl || !window.fetch || !window.DOMParser || !liveParts(document).length) { location.href = url; return; }
        var seq = ++liveSeq, path = new URL(url, location.href).pathname;
        mainEl.classList.add('live-busy');
        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'text/html' } })
            .then(function (r) {
                if (!r.ok || new URL(r.url).pathname !== path) throw new Error('live');
                return r.text().then(function (html) { return { html: html, url: r.url }; });
            })
            .then(function (res) {
                if (seq !== liveSeq) return;
                if (!liveApply(new DOMParser().parseFromString(res.html, 'text/html'))) throw new Error('live');
                mainEl.classList.remove('live-busy');
                if (push && res.url !== location.href) history.pushState({ aoLive: 1 }, '', res.url);
                // A page link low in a long list: bring the top of the new page back into view.
                var card = from && from.closest && from.closest('.pagination') && from.closest('.content-card');
                if (card && card.getBoundingClientRect().top < 0) card.scrollIntoView({ block: 'start' });
            })
            .catch(function () { if (seq === liveSeq) location.href = url; });
    }
    window.aoLive = { go: function (url) { liveGo(url, true); } };

    if (mainEl) {
        document.addEventListener('submit', function (e) {
            var f = e.target, s = e.submitter;
            // (Attributes are read by name: a field called "action" or "target" would shadow the form's own property.)
            if (e.defaultPrevented || !f.matches || !mainEl.contains(f) || f.getAttribute('target')) return;
            if ((f.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
            if (s && (s.hasAttribute('formaction') || s.hasAttribute('formtarget'))) return;   // export / print buttons
            var url = new URL(f.getAttribute('action') || location.href, location.href);
            if (url.pathname !== location.pathname || !liveParts(document).length) return;
            e.preventDefault();
            url.search = new URLSearchParams(new FormData(f)).toString();
            liveGo(url.toString(), true, f);
        });
        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var a = e.target.closest && e.target.closest('a[href]');
            if (!a || !mainEl.contains(a) || a.target || a.hasAttribute('download') || a.getAttribute('href').charAt(0) === '#') return;
            var url = new URL(a.href, location.href);
            if (url.origin !== location.origin || url.pathname !== location.pathname || url.searchParams.has('export')) return;
            if (!liveParts(document).length) return;
            e.preventDefault();
            liveGo(url.toString(), true, a);
        });
        addEventListener('popstate', function () { if (liveParts(document).length) liveGo(location.href, false); });
    }
})();
