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
        (function f(t) {
            var p = Math.min((t - t0) / 700, 1);
            el.textContent = fmt(to * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(f); else el.textContent = raw;
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
    document.querySelectorAll('.main-content table tbody').forEach(function (tb) {
        [].slice.call(tb.rows).forEach(function (r, i) {
            if (r.classList.contains('row-in')) return;
            r.classList.add('row-stagger'); r.style.setProperty('--i', Math.min(i, 12));
        });
    });

    /* ---------- Click-to-sort headers (sorts the rows shown on this page) ---------- */
    document.querySelectorAll('table[data-sortable]').forEach(function (table) {
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
                    location.href = url.toString();
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
    });

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
    if (window.bootstrap) document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) { bootstrap.Tooltip.getOrCreateInstance(el); });
})();
