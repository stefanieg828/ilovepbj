/**
 * Phone-friendly task photo helpers for FOH/BOH checklists & prep lists.
 * Compresses on-device, uploads to checklist-photo-api.php, stores photoUrl on the item.
 */
(function (global) {
    'use strict';

    function todayStr() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function compressFile(file, maxPx, quality) {
        maxPx = maxPx || 1200;
        quality = quality || 0.72;
        return new Promise(function (resolve, reject) {
            if (!file || !file.type || file.type.indexOf('image/') !== 0) {
                reject(new Error('not_image'));
                return;
            }
            var reader = new FileReader();
            reader.onerror = function () { reject(new Error('read')); };
            reader.onload = function () {
                var dataUrl = reader.result;
                var img = new Image();
                img.onerror = function () { reject(new Error('img')); };
                img.onload = function () {
                    var w = img.width, h = img.height;
                    if (w > maxPx || h > maxPx) {
                        if (w > h) {
                            h = Math.round(h * maxPx / w);
                            w = maxPx;
                        } else {
                            w = Math.round(w * maxPx / h);
                            h = maxPx;
                        }
                    }
                    var canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    var out = canvas.toDataURL('image/jpeg', quality);
                    // dataURL → Blob for upload
                    var bin = atob(out.split(',')[1] || '');
                    var arr = new Uint8Array(bin.length);
                    for (var i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
                    var blob = new Blob([arr], { type: 'image/jpeg' });
                    resolve({ blob: blob, dataUrl: out, name: (file.name || 'task').replace(/\.[^.]+$/, '') + '.jpg' });
                };
                img.src = dataUrl;
            };
            reader.readAsDataURL(file);
        });
    }

    function uploadBlob(blob, name) {
        var fd = new FormData();
        fd.append('action', 'upload');
        fd.append('date', todayStr());
        fd.append('file', blob, name || 'task.jpg');
        return fetch('checklist-photo-api.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        }).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok || !data || !data.ok) {
                    throw new Error((data && data.error) || 'upload_failed');
                }
                return data;
            });
        });
    }

    function deleteRemote(url) {
        if (!url || url.indexOf('/uploads/checklist-photos/') !== 0) {
            return Promise.resolve({ ok: true, deleted: false });
        }
        var fd = new FormData();
        fd.append('action', 'delete');
        fd.append('url', url);
        fd.append('date', todayStr());
        return fetch('checklist-photo-api.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        }).then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
            .catch(function () { return { ok: false }; });
    }

    /**
     * Pick image from camera/library, compress, upload, return { url, photoAt }.
     * Falls back to data URL storage if upload fails (offline / no house).
     */
    function captureAndAttach(file) {
        return compressFile(file).then(function (packed) {
            return uploadBlob(packed.blob, packed.name).then(function (res) {
                return {
                    photoUrl: res.url,
                    photoAt: Date.now(),
                    photoDataUrl: null
                };
            }).catch(function () {
                // Offline / no house: keep compressed data URL (capped)
                var url = packed.dataUrl;
                if (url && url.length > 450000) {
                    // re-compress smaller
                    return compressFile(file, 800, 0.6).then(function (p2) {
                        return { photoUrl: null, photoAt: Date.now(), photoDataUrl: p2.dataUrl };
                    });
                }
                return { photoUrl: null, photoAt: Date.now(), photoDataUrl: url };
            });
        });
    }

    function itemPhotoSrc(item) {
        if (!item) return '';
        return item.photoUrl || item.photoDataUrl || item.photo || '';
    }

    function clearItemPhoto(item) {
        if (!item) return Promise.resolve();
        var url = item.photoUrl;
        delete item.photoUrl;
        delete item.photoDataUrl;
        delete item.photo;
        item.photoAt = Date.now();
        return deleteRemote(url);
    }

    /**
     * Hidden file input wired once per page.
     * onPick(file) called with selected File.
     */
    function ensureFileInput(onPick) {
        var id = 'pbj-task-photo-input';
        var input = document.getElementById(id);
        if (!input) {
            input = document.createElement('input');
            input.type = 'file';
            input.id = id;
            input.accept = 'image/*';
            input.setAttribute('capture', 'environment');
            input.style.display = 'none';
            document.body.appendChild(input);
        }
        input.onchange = function () {
            var f = (input.files && input.files[0]) || null;
            input.value = '';
            if (f && typeof onPick === 'function') onPick(f);
        };
        return input;
    }

    function openPicker(onPick) {
        var input = ensureFileInput(onPick);
        input.click();
    }

    /**
     * HTML snippet for photo thumb + actions inside a check item.
     * canAttach: show Add/Change/Remove
     */
    function renderPhotoUi(item, opts) {
        opts = opts || {};
        var canAttach = !!opts.canAttach;
        var isSweet = !!opts.isSweet;
        var id = item && item.id ? String(item.id) : '';
        var src = itemPhotoSrc(item);
        var html = '<div class="task-photo-row">';
        if (src) {
            html += '<a class="task-photo-thumb" href="' + src.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener">' +
                '<img src="' + src.replace(/"/g, '&quot;') + '" alt="' + (isSweet ? 'Task photo' : 'Task photo') + '"></a>';
        }
        if (canAttach) {
            if (src) {
                html += '<button type="button" class="btn btn-small btn-ghost" data-act="photo" data-id="' + id.replace(/"/g, '&quot;') + '">' +
                    (isSweet ? '📷 Change' : '📷 Change') + '</button>';
                html += '<button type="button" class="btn btn-small btn-danger" data-act="photo-del" data-id="' + id.replace(/"/g, '&quot;') + '">' +
                    (isSweet ? 'Remove photo' : 'Remove photo') + '</button>';
            } else {
                html += '<button type="button" class="btn btn-small btn-ghost" data-act="photo" data-id="' + id.replace(/"/g, '&quot;') + '">' +
                    (isSweet ? '📷 Photo' : '📷 Photo') + '</button>';
            }
        } else if (src) {
            // view only
        }
        html += '</div>';
        return html;
    }

    var cssInjected = false;
    function injectCss() {
        if (cssInjected) return;
        cssInjected = true;
        var style = document.createElement('style');
        style.textContent =
            '.task-photo-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:8px;margin-left:38px;}' +
            '.task-photo-thumb{display:inline-block;border-radius:12px;overflow:hidden;border:2px solid rgba(0,0,0,.08);line-height:0;}' +
            '.task-photo-thumb img{width:72px;height:72px;object-fit:cover;display:block;}' +
            '.task-photo-busy{opacity:.55;pointer-events:none;}' +
            '@media print{.task-photo-row .btn{display:none!important;}.task-photo-thumb img{width:56px;height:56px;}}';
        document.head.appendChild(style);
    }

    injectCss();

    global.PbjTaskPhotos = {
        compressFile: compressFile,
        captureAndAttach: captureAndAttach,
        clearItemPhoto: clearItemPhoto,
        itemPhotoSrc: itemPhotoSrc,
        openPicker: openPicker,
        renderPhotoUi: renderPhotoUi,
        injectCss: injectCss
    };
})(window);
