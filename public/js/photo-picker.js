/* =====================================================================
   AssetOne — photo picker.
   Enhances every [data-photo-picker] block (see partials/photo-picker):
   upload from files or take a picture with the camera, shrink it in the
   browser, preview it, and hand the result to the form's file input.
   ===================================================================== */
(function () {
    'use strict';

    var MAX_SIDE = 1280, QUALITY = 0.82;

    /* Shrink an image file to a JPEG no larger than MAX_SIDE on its long edge. */
    function shrink(file, crop) {
        return new Promise(function (resolve) {
            if (!/^image\//.test(file.type)) { resolve(null); return; }
            var img = new Image(), url = URL.createObjectURL(file);
            img.onload = function () {
                URL.revokeObjectURL(url);
                var canvas = document.createElement('canvas'), ctx = canvas.getContext('2d');
                if (crop) {
                    // Fixed frame (e.g. 800 x 600 for 4:3): scale to cover it, then centre-crop.
                    canvas.width = crop[0]; canvas.height = crop[1];
                    ctx.fillStyle = '#fff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    var cover = Math.max(crop[0] / img.width, crop[1] / img.height), w = img.width * cover, h = img.height * cover;
                    ctx.drawImage(img, (crop[0] - w) / 2, (crop[1] - h) / 2, w, h);
                } else {
                    var scale = Math.min(1, MAX_SIDE / Math.max(img.width, img.height));
                    canvas.width = Math.round(img.width * scale);
                    canvas.height = Math.round(img.height * scale);
                    ctx.fillStyle = '#fff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                }
                canvas.toBlob(function (blob) {
                    if (!blob) { resolve(file); return; }
                    var name = (file.name || 'photo').replace(/\.[^.]+$/, '') + '.jpg';
                    resolve(new File([blob], name, { type: 'image/jpeg' }));
                }, 'image/jpeg', QUALITY);
            };
            img.onerror = function () { URL.revokeObjectURL(url); resolve(null); };
            img.src = url;
        });
    }

    /* ---------- Shared camera dialog (built on first use) ---------- */
    var cam = null;
    function camera() {
        if (cam) return cam;
        var el = document.createElement('div');
        el.className = 'modal fade';
        el.tabIndex = -1;
        el.innerHTML =
            '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
            '<div class="modal-header"><h5 class="modal-title"><i class="bi bi-camera me-2"></i>Take Photo</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>' +
            '<div class="modal-body text-center">' +
            '<video class="w-100 rounded" autoplay muted playsinline style="background:#000;min-height:220px"></video>' +
            '<p class="small text-muted mt-2 mb-0" data-cam-msg>Point the camera and press Capture.</p></div>' +
            '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
            '<button type="button" class="btn btn-primary" data-cam-shot><i class="bi bi-camera me-2"></i>Capture</button></div>' +
            '</div></div>';
        document.body.appendChild(el);

        var video = el.querySelector('video'), msg = el.querySelector('[data-cam-msg]'),
            shot = el.querySelector('[data-cam-shot]'), modal = new bootstrap.Modal(el), onPhoto = null;

        function stop() {
            if (video.srcObject) { video.srcObject.getTracks().forEach(function (t) { t.stop(); }); video.srcObject = null; }
        }
        el.addEventListener('shown.bs.modal', function () {
            msg.textContent = 'Point the camera and press Capture.';
            shot.disabled = true;
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                msg.textContent = 'This browser cannot open the camera. Use Upload instead.';
                return;
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (stream) {
                video.srcObject = stream; shot.disabled = false;
            }).catch(function () {
                msg.textContent = 'Camera access was denied or no camera is available. Use Upload instead.';
            });
        });
        el.addEventListener('hidden.bs.modal', stop);
        shot.addEventListener('click', function () {
            if (!video.videoWidth) return;
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(function (blob) {
                if (blob && onPhoto) onPhoto(new File([blob], 'camera-' + Date.now() + '.jpg', { type: 'image/jpeg' }));
                modal.hide();
            }, 'image/jpeg', 0.92);
        });

        cam = { open: function (cb) { onPhoto = cb; modal.show(); } };
        return cam;
    }

    /* ---------- One picker ---------- */
    function setup(box) {
        var input = box.querySelector('[data-photo-input]'),
            preview = box.querySelector('[data-photo-preview]'),
            error = box.querySelector('[data-photo-error]'),
            max = parseInt(box.dataset.max || '1', 10),
            maxMb = parseFloat(box.dataset.maxMb || '0'),
            crop = box.dataset.crop === '4:3' ? [800, 600] : null,
            files = [],
            existing = preview.innerHTML;   // server-rendered current picture(s)

        function fail(text) {
            if (!error) return;
            error.textContent = text || '';
            error.classList.toggle('d-none', !text);
        }

        function sync() {
            var dt = new DataTransfer();
            files.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
        }

        function announce() {
            box.dispatchEvent(new CustomEvent('photo:change', { bubbles: true, detail: { count: files.length, url: files.length ? URL.createObjectURL(files[0]) : null } }));
        }

        function render() {
            preview.classList.toggle('has-photo', files.length > 0);
            announce();
            if (!files.length) { preview.innerHTML = existing; return; }
            preview.innerHTML = '';
            files.forEach(function (file, i) {
                var item = document.createElement('div');
                item.className = 'photo-thumb';
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = 'Selected photo ' + (i + 1);
                var remove = document.createElement('button');
                remove.type = 'button'; remove.className = 'photo-remove'; remove.setAttribute('aria-label', 'Remove photo');
                remove.innerHTML = '<i class="bi bi-x-lg"></i>';
                remove.addEventListener('click', function () { files.splice(i, 1); sync(); render(); });
                item.appendChild(img); item.appendChild(remove);
                preview.appendChild(item);
            });
        }

        function add(list) {
            fail('');
            var incoming = [].slice.call(list), tooBig = false;
            if (maxMb) incoming = incoming.filter(function (f) { var ok = f.size <= maxMb * 1024 * 1024; if (!ok) tooBig = true; return ok; });
            Promise.all(incoming.map(function (f) { return shrink(f, crop); })).then(function (done) {
                var good = done.filter(Boolean);
                if (good.length < incoming.length) fail('Only image files (JPG, PNG, WebP) can be used.');
                if (tooBig) fail('Photo size must not exceed ' + maxMb + 'MB.');
                if (max === 1) {
                    if (good.length) files = [good[0]];
                } else {
                    files = files.concat(good);
                    if (files.length > max) { files = files.slice(0, max); fail('You can attach up to ' + max + ' photos.'); }
                }
                sync(); render();
            });
        }

        // The hidden picker input feeds add(); the real input carries the result.
        var chooser = document.createElement('input');
        chooser.type = 'file'; chooser.accept = 'image/*'; chooser.multiple = max > 1; chooser.className = 'd-none';
        box.appendChild(chooser);
        chooser.addEventListener('change', function () { add(chooser.files); chooser.value = ''; });

        box.querySelector('[data-photo-upload]').addEventListener('click', function () { chooser.click(); });

        // Drop a picture onto the preview, or click the empty preview to browse.
        ['dragenter', 'dragover'].forEach(function (t) { preview.addEventListener(t, function (e) { e.preventDefault(); preview.classList.add('drag'); }); });
        ['dragleave', 'drop'].forEach(function (t) { preview.addEventListener(t, function (e) { e.preventDefault(); preview.classList.remove('drag'); }); });
        preview.addEventListener('drop', function (e) { if (e.dataTransfer && e.dataTransfer.files.length) add(e.dataTransfer.files); });
        preview.addEventListener('click', function (e) {
            if (e.target.closest('button') || e.target.closest('[data-lightbox]')) return;
            if (!files.length || max > 1) chooser.click();
        });
        preview.style.cursor = 'pointer';
        var camBtn = box.querySelector('[data-photo-camera]');
        if (camBtn) camBtn.addEventListener('click', function () { camera().open(function (file) { add([file]); }); });

        // Required pictures: stop the submit with a clear message instead of a server round trip.
        var form = box.closest('form');
        if (form && box.dataset.required === '1') {
            form.addEventListener('submit', function (e) {
                if (files.length) return;
                e.preventDefault();
                fail(max > 1 ? 'Please add at least one photo.' : 'Please add a photo.');
                box.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }
        if (form) form.addEventListener('reset', function () { files = []; sync(); render(); fail(''); });
    }

    document.querySelectorAll('[data-photo-picker]').forEach(setup);
})();
