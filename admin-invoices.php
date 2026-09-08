<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
    <?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
    <title><?php echo $is_sweet ? 'Invoices' : 'Invoices'; ?> • <?php echo pbj_hub_label('admin'); ?> • ilovepbj ops</title>
    <?php if (!$is_sweet): ?><link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Gabriola&display=swap" rel="stylesheet"><?php endif; ?>
    <style>
        @font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
        @font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
        body { margin: 0; padding-bottom: 100px; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FCF8EE; color: #3a2f1f;<?php else: ?>font-family: 'Lora', serif; background: #F1EBE4; color: #1A2A44;<?php endif; ?> }
        .header { <?php if ($is_sweet): ?>background: #E55163;<?php else: ?>background: #1A2A44;<?php endif; ?> color: white; padding: 20px 25px 25px; text-align: center; }
        .back-link { display: inline-block; color: white; text-decoration: none; opacity: 0.9; margin-bottom: 12px; }
        .back-link:hover { opacity: 1; text-decoration: underline; }
        h1 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> font-size: 2.5rem; margin: 0; }
        .subtitle { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        .content { padding: 24px 16px; max-width: 820px; margin: 0 auto; }
        .intro { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); line-height: 1.45; }
        .card { background: white; border-radius: 18px; padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;<?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?> font-size: 1.25rem; margin: 0 0 12px; }
        .card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .card-head h2 { margin: 0; }
        .btn { border: none; border-radius: 14px; padding: 11px 14px; font-size: 0.95rem; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif;<?php else: ?>font-family: 'Lora', serif;<?php endif; ?> color: inherit; }
        .btn:disabled { opacity: 0.45; cursor: default; }
        .btn-primary { <?php if ($is_sweet): ?>background: #E55163; color: white;<?php else: ?>background: #1A2A44; color: white;<?php endif; ?> }
        .btn-secondary { background: white; color: inherit; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .btn-ghost { background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>; border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>; }
        .btn-danger { background: #FDECEA; color: #B71C1C; }
        .btn-small { padding: 7px 10px; font-size: 0.82rem; border-radius: 10px; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 0.82rem; opacity: 0.65; margin-bottom: 4px; }
        .field input, .field select, .field textarea {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 10px 12px; font-size: 1rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;
            <?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?>
        }
        .field-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .field-row .field { flex: 1; min-width: 120px; }
        .hint { font-size: 0.92rem; opacity: 0.72; margin: 0 0 12px; line-height: 1.4; }
        .folders { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        @media (max-width: 520px) { .folders { grid-template-columns: 1fr; } }
        .folder {
            border-radius: 14px; padding: 14px 12px; text-align: left; cursor: pointer;
            border: 2px solid transparent; transition: transform 0.15s, box-shadow 0.15s;
            <?php if ($is_sweet): ?>background: #FFFBF8; border-color: #F3E8DD;
            <?php else: ?>background: #FAF8F5; border-color: #E6DFD7;<?php endif; ?>
        }
        .folder:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,0,0,0.08); }
        .folder.active {
            <?php if ($is_sweet): ?>border-color: #E55163; background: #FFF5F6;
            <?php else: ?>border-color: #1A2A44; background: #EEF2F8;<?php endif; ?>
        }
        .folder .f-name { font-weight: 600; font-size: 1.02rem; margin: 0 0 4px; line-height: 1.25; }
        .folder .f-count { font-size: 0.82rem; opacity: 0.7; margin: 0; }
        .folder .f-actions { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 10px; }
        .folder .f-actions button { pointer-events: auto; }
        .upload-bar {
            display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
            margin-bottom: 12px;
        }
        .upload-bar .btn { flex: 1; min-width: 140px; }
        .invoice-list { display: flex; flex-direction: column; gap: 10px; }
        .invoice {
            display: flex; gap: 12px; align-items: flex-start;
            border-radius: 14px; padding: 12px;
            <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;
            <?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?>
        }
        .inv-thumb {
            width: 72px; height: 72px; border-radius: 12px; flex-shrink: 0;
            overflow: hidden; display: flex; align-items: center; justify-content: center;
            background: <?php echo $is_sweet ? '#FFF5F6' : '#EEF2F8'; ?>;
            border: 1px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            text-decoration: none; color: inherit;
        }
        .inv-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .inv-thumb .pdf-ico { font-size: 1.6rem; }
        .inv-meta { flex: 1; min-width: 0; }
        .inv-title { margin: 0 0 2px; font-weight: 600; font-size: 1rem; word-break: break-word; }
        .inv-sub { margin: 0; font-size: 0.8rem; opacity: 0.7; line-height: 1.35; }
        .inv-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .empty { text-align: center; padding: 28px 12px; opacity: 0.75; }
        .actions-bar { display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; }
        .actions-bar .btn { flex: 1; min-width: 120px; }
        .toast {
            position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%) translateY(12px);
            background: <?php echo $is_sweet ? '#E55163' : '#1A2A44'; ?>; color: white;
            padding: 10px 18px; border-radius: 999px; opacity: 0; transition: all 0.25s;
            z-index: 2100; pointer-events: none; max-width: 90vw; text-align: center;
        }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .status-line { font-size: 0.85rem; opacity: 0.7; min-height: 1.2em; margin: 0 0 8px; }
        .status-line.err { color: #B71C1C; opacity: 1; }
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 3000;
            display: flex; align-items: flex-end; justify-content: center; padding: 16px;
        }
        @media (min-width: 560px) {
            .modal-backdrop { align-items: center; }
        }
        .modal {
            background: white; border-radius: 18px; padding: 18px; width: 100%; max-width: 420px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2); max-height: 90vh; overflow: auto;
            color: inherit;
        }
        .modal h3 { margin: 0 0 12px; font-size: 1.2rem;
            <?php if ($is_sweet): ?>font-family: 'ModernLoveCaps', serif; color: #E55163;
            <?php else: ?>font-family: 'Lora', serif; color: #1A2A44;<?php endif; ?>
        }
        .modal-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        .modal-actions .btn { flex: 1; min-width: 100px; }
        /* Visually hidden but still “clickable” via <label for> on mobile browsers */
        .hidden-file {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }
        label.btn { box-sizing: border-box; cursor: pointer; }
        label.btn.disabled, label.btn[aria-disabled="true"] { opacity: 0.45; pointer-events: none; cursor: default; }
        .pill {
            display: inline-block; font-size: 0.72rem; border-radius: 999px; padding: 3px 8px;
            <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?>
        }
        .pill.applied { <?php if ($is_sweet): ?>background: #E8F8F1; color: #1F6B4A;<?php else: ?>background: #EAF1FA; color: #1A2A44;<?php endif; ?> }
        .pill.needs-cost { background: #FFF8E6; color: #8A6D1F; }
        .invoice.needs-cost-row {
            outline: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            border-radius: 14px;
        }
        .cost-line {
            display: grid; grid-template-columns: 36px 1.4fr 0.65fr 0.5fr auto; gap: 6px; align-items: end;
            margin-bottom: 8px; padding-bottom: 8px;
            border-bottom: 1px dashed <?php echo $is_sweet ? '#F3E8DD' : '#E6DFD7'; ?>;
        }
        @media (max-width: 520px) {
            .cost-line { grid-template-columns: 28px 1fr 1fr; }
            .cost-line .cl-name { grid-column: 1 / -1; }
        }
        .cost-line input, .cost-line select {
            width: 100%; box-sizing: border-box; border-radius: 10px;
            border: 2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;
            padding: 8px 10px; font-size: 0.95rem;
            <?php if ($is_sweet): ?>font-family: 'DreamingOutLoudPro', serif; background: #FFFBF8; color: #3a2f1f;
            <?php else: ?>font-family: 'Lora', serif; background: #FAF8F5; color: #1A2A44;<?php endif; ?>
        }
        .cost-line label { display: block; font-size: 0.72rem; opacity: 0.65; margin-bottom: 2px; }
        .match-ok { color: #1F6B4A; font-size: 0.78rem; }
        .match-new { color: #8A6D1F; font-size: 0.78rem; }
        .ocr-status { font-size: 0.88rem; opacity: 0.8; margin: 8px 0; line-height: 1.35; min-height: 1.2em; }
        .ocr-preview {
            max-height: 120px; overflow: auto; font-size: 0.78rem; opacity: 0.7;
            white-space: pre-wrap; word-break: break-word; margin: 0 0 10px;
            padding: 8px 10px; border-radius: 10px;
            <?php if ($is_sweet): ?>background: #FFFBF8; border: 1px solid #F3E8DD;
            <?php else: ?>background: #FAF8F5; border: 1px solid #E6DFD7;<?php endif; ?>
        }
        .modal.wide { max-width: 560px; }
    </style>
</head>
<body>
    <div class="header">
        <a href="/admin/inventory" class="back-link">← <?php echo $is_sweet ? 'Back to Inventory & Vendors' : 'Back to Inventory & Vendors'; ?></a>
        <h1><?php echo $is_sweet ? 'Invoices' : 'Invoices'; ?></h1>
        <p class="subtitle"><?php echo $is_sweet ? 'Scan, upload & file by category' : 'Scan, upload, and file by category'; ?></p>
    </div>

    <div class="content">
        <div class="intro">
            <?php echo $is_sweet
                ? 'Snap a delivery ticket or upload a PDF — file it by folder, then <strong>Apply costs</strong> so case prices update on Product Setup &amp; every recipe that uses those ingredients 🧾💰'
                : 'Upload invoices, organize by folder, then Apply costs to push case prices into inventory/recipe costing.'; ?>
        </div>

        <div class="card" id="unapplied-nudge" style="display:none;border:2px solid <?php echo $is_sweet ? '#F3C5CC' : '#C5D0DE'; ?>;">
            <h2 style="margin:0 0 6px;font-size:1.15rem;"><?php echo $is_sweet ? 'Costs not applied yet' : 'Costs not applied yet'; ?></h2>
            <p class="hint" id="unapplied-nudge-text" style="margin:0 0 10px;"></p>
            <button type="button" class="btn btn-primary btn-small" id="btn-show-unapplied"><?php echo $is_sweet ? 'Show invoices needing costs' : 'Show unapplied'; ?></button>
        </div>

        <p class="status-line" id="status" aria-live="polite"></p>

        <div class="card">
            <div class="card-head">
                <h2><?php echo $is_sweet ? 'Folders' : 'Folders'; ?></h2>
                <button type="button" class="btn btn-primary btn-small" id="btn-add-folder"><?php echo $is_sweet ? '+ New folder' : '+ New folder'; ?></button>
            </div>
            <p class="hint"><?php echo $is_sweet
                ? 'Tap a folder to open it. Use ↑ ↓ to reorder, rename or delete as you like.'
                : 'Tap a folder to open it. Reorder, rename, or delete anytime.'; ?></p>
            <div class="folders" id="folders"></div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2 id="folder-title"><?php echo $is_sweet ? 'Invoices' : 'Invoices'; ?></h2>
                <span class="pill" id="folder-count">0</span>
            </div>
            <div class="upload-bar">
                <label class="btn btn-primary" id="btn-camera" for="file-camera"><?php echo $is_sweet ? '📷 Take photo' : '📷 Camera'; ?></label>
                <label class="btn btn-ghost" id="btn-upload" for="file-upload"><?php echo $is_sweet ? '📁 Upload file' : '📁 Upload'; ?></label>
            </div>
            <p class="hint"><?php echo $is_sweet
                ? 'Photos & PDFs up to 10MB. Use camera for delivery slips on the dock 📦'
                : 'Images or PDF up to 10MB.'; ?></p>
            <!-- File inputs nested in the bar so mobile browsers treat the tap as a real file-picker gesture -->
            <input type="file" id="file-camera" class="hidden-file" accept="image/*" capture="environment">
            <input type="file" id="file-upload" class="hidden-file" accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.heic,.heif,.pdf,application/pdf" multiple>
            <div class="invoice-list" id="invoice-list"></div>
        </div>

        <div class="card">
            <h2><?php echo $is_sweet ? 'Costing loop' : 'Costing loop'; ?></h2>
            <p class="hint" style="margin:0 0 10px;"><?php echo $is_sweet
                ? 'After you apply costs from an invoice, open Costing Sheet to see live plate costs. Recipes re-price automatically from case price + conversions.'
                : 'Applied invoice prices feed Product Setup case prices → Costing Sheet plate costs.'; ?></p>
            <div class="btn-row">
                <a href="/admin/costing" class="btn btn-primary"><?php echo $is_sweet ? 'Costing Sheet' : 'Costing Sheet'; ?></a>
                <a href="/admin/product-setup" class="btn btn-ghost"><?php echo $is_sweet ? 'Product Setup' : 'Product Setup'; ?></a>
            </div>
        </div>

        <div class="actions-bar">
            <a href="/admin/inventory" class="btn btn-secondary"><?php echo $is_sweet ? 'Inventory hub' : 'Inventory hub'; ?></a>
            <a href="/admin/vendors" class="btn btn-primary"><?php echo $is_sweet ? 'Vendors' : 'Vendors'; ?></a>
        </div>
    </div>

    <div class="toast" id="toast"></div>
    <div id="modal-root"></div>

    <?php include 'bottom-nav.php'; ?>
    <script src="/ops-nudges.js?v=1"></script>
    <script>
    (function () {
        var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
        var API = '/invoices-api.php';
        var folders = [];
        var invoices = [];
        var activeFolderId = null;
        var canEdit = true;
        var busy = false;
        var filterUnappliedOnly = false;

        var els = {
            folders: document.getElementById('folders'),
            list: document.getElementById('invoice-list'),
            title: document.getElementById('folder-title'),
            count: document.getElementById('folder-count'),
            status: document.getElementById('status'),
            toast: document.getElementById('toast'),
            modalRoot: document.getElementById('modal-root'),
            fileCamera: document.getElementById('file-camera'),
            fileUpload: document.getElementById('file-upload'),
            btnCamera: document.getElementById('btn-camera'),
            btnUpload: document.getElementById('btn-upload'),
            btnAddFolder: document.getElementById('btn-add-folder')
        };

        function canP(key) {
            if (window.PbjPerms && window.PbjPerms.loaded) return window.PbjPerms.can(key);
            return true;
        }

        function applyPerms() {
            if (window.PbjPerms) window.PbjPerms.applyDom();
            var view = canP('admin.inventory.view') || canP('admin.inventory.edit');
            canEdit = canP('admin.inventory.edit');
            if (!view) {
                var c = document.querySelector('.content');
                if (c && !document.getElementById('inv-denied')) {
                    c.insertAdjacentHTML('afterbegin',
                        '<div class="intro" id="inv-denied">' +
                        (isSweet ? 'No permission to view inventory invoices.' : 'No permission to view invoices.') +
                        '</div>');
                }
            }
            if (!canEdit) {
                [els.btnCamera, els.btnUpload].forEach(function (b) {
                    if (!b) return;
                    b.style.display = 'none';
                    b.setAttribute('aria-disabled', 'true');
                });
                if (els.btnAddFolder) els.btnAddFolder.style.display = 'none';
                if (els.fileCamera) els.fileCamera.disabled = true;
                if (els.fileUpload) els.fileUpload.disabled = true;
            }
        }

        function esc(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function toast(msg) {
            if (!els.toast) return;
            els.toast.textContent = msg;
            els.toast.classList.add('show');
            clearTimeout(toast._t);
            toast._t = setTimeout(function () { els.toast.classList.remove('show'); }, 2200);
        }

        function setStatus(msg, isErr) {
            if (!els.status) return;
            els.status.textContent = msg || '';
            els.status.classList.toggle('err', !!isErr);
        }

        function folderById(id) {
            for (var i = 0; i < folders.length; i++) {
                if (folders[i].id === id) return folders[i];
            }
            return null;
        }

        function countInFolder(fid) {
            var n = 0;
            invoices.forEach(function (inv) { if (inv.folderId === fid) n++; });
            return n;
        }

        function formatDate(ms) {
            if (!ms) return '';
            try {
                var d = new Date(ms);
                return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            } catch (e) { return ''; }
        }

        function isPdf(inv) {
            var m = (inv.mime || '').toLowerCase();
            var u = (inv.url || '').toLowerCase();
            return m.indexOf('pdf') >= 0 || /\.pdf($|\?)/.test(u);
        }

        function api(action, fields, file) {
            var fd = new FormData();
            if (action) fd.append('action', action);
            if (fields) {
                Object.keys(fields).forEach(function (k) {
                    if (fields[k] !== undefined && fields[k] !== null) {
                        fd.append(k, fields[k]);
                    }
                });
            }
            if (file) {
                var fname = (file && file.name) ? file.name : 'invoice.jpg';
                fd.append('file', file, fname);
            }
            return fetch(API, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(function (r) {
                    return r.text().then(function (text) {
                        var data = null;
                        try { data = text ? JSON.parse(text) : null; } catch (e) { data = null; }
                        if (!r.ok || !data || !data.ok) {
                            var err = (data && data.error) || (r.status === 413 ? 'size' : 'failed');
                            throw new Error(err);
                        }
                        return data;
                    });
                });
        }

        /** Compress large phone photos so they fit upload limits. PDFs pass through. */
        function prepareFile(file) {
            return new Promise(function (resolve) {
                if (!file) {
                    resolve(null);
                    return;
                }
                var type = (file.type || '').toLowerCase();
                var name = file.name || '';
                // Android gallery often leaves type empty — sniff from extension
                var isPdf = type === 'application/pdf' || /\.pdf$/i.test(name);
                var isImage = type.indexOf('image/') === 0 ||
                    /\.(jpe?g|png|webp|gif|heic|heif|bmp)$/i.test(name) ||
                    (!type && !isPdf && file.size > 0); // unknown binary from photos app → treat as image
                // HEIC/PDF / non-canvas-friendly / already-small: upload as-is
                var isHeic = /heic|heif/i.test(type) || /\.(heic|heif)$/i.test(name);
                if (!isImage || isPdf || isHeic || file.size < 900 * 1024) {
                    resolve(file);
                    return;
                }
                var reader = new FileReader();
                reader.onerror = function () { resolve(file); };
                reader.onload = function () {
                    var img = new Image();
                    img.onerror = function () { resolve(file); };
                    img.onload = function () {
                        try {
                            var maxPx = 1800;
                            var w = img.width || 0;
                            var h = img.height || 0;
                            if (!w || !h) { resolve(file); return; }
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
                            canvas.toBlob(function (blob) {
                                if (!blob) { resolve(file); return; }
                                var name = (file.name || 'invoice').replace(/\.[^.]+$/, '') + '.jpg';
                                try {
                                    resolve(new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() }));
                                } catch (e2) {
                                    blob.name = name;
                                    resolve(blob);
                                }
                            }, 'image/jpeg', 0.82);
                        } catch (e3) {
                            resolve(file);
                        }
                    };
                    img.src = reader.result;
                };
                reader.readAsDataURL(file);
            });
        }

        function load() {
            setStatus(isSweet ? 'Loading…' : 'Loading…');
            return fetch(API, { credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !data.ok) throw new Error((data && data.error) || 'load');
                    folders = Array.isArray(data.folders) ? data.folders : [];
                    invoices = Array.isArray(data.invoices) ? data.invoices : [];
                    if (!activeFolderId || !folderById(activeFolderId)) {
                        activeFolderId = folders[0] ? folders[0].id : null;
                    }
                    if (data.localOnly) {
                        setStatus(isSweet
                            ? 'Join a house to save invoices for the whole team.'
                            : 'No restaurant membership — invoices won’t save to the house.', true);
                    } else {
                        setStatus('');
                    }
                    render();
                })
                .catch(function () {
                    setStatus(isSweet ? 'Couldn’t load invoices — try again.' : 'Could not load invoices.', true);
                });
        }

        function paintUnappliedNudge() {
            var box = document.getElementById('unapplied-nudge');
            var txt = document.getElementById('unapplied-nudge-text');
            if (!box || !txt || !window.PbjOpsNudges) return;
            var info = window.PbjOpsNudges.unappliedFromInvoices(invoices);
            if (!info.count) {
                box.style.display = 'none';
                filterUnappliedOnly = false;
                return;
            }
            box.style.display = 'block';
            txt.innerHTML = isSweet
                ? ('<strong>' + info.count + '</strong> invoice' + (info.count === 1 ? '' : 's') +
                    ' still need cost apply — case prices on Product Setup / recipes stay stale until you do 💕' +
                    (info.total ? ' <span style="opacity:0.75;">(' + info.applied + '/' + info.total + ' applied)</span>' : ''))
                : (info.count + ' invoice(s) without cost apply · ' + info.applied + '/' + info.total + ' applied.');
            var btn = document.getElementById('btn-show-unapplied');
            if (btn) {
                btn.textContent = filterUnappliedOnly
                    ? (isSweet ? 'Show all invoices' : 'Show all')
                    : (isSweet ? 'Show invoices needing costs' : 'Show unapplied');
            }
        }

        function render() {
            paintUnappliedNudge();
            renderFolders();
            renderInvoices();
        }

        function renderFolders() {
            if (!els.folders) return;
            if (!folders.length) {
                els.folders.innerHTML = '<div class="empty">' +
                    (isSweet ? 'No folders yet — add one 💕' : 'No folders yet.') + '</div>';
                return;
            }
            els.folders.innerHTML = folders.map(function (f, i) {
                var n = countInFolder(f.id);
                var active = f.id === activeFolderId ? ' active' : '';
                var actions = '';
                if (canEdit) {
                    actions =
                        '<div class="f-actions">' +
                        '<button type="button" class="btn btn-ghost btn-small" data-f-act="up" data-id="' + esc(f.id) + '" title="Move up"' + (i === 0 ? ' disabled' : '') + '>↑</button>' +
                        '<button type="button" class="btn btn-ghost btn-small" data-f-act="down" data-id="' + esc(f.id) + '" title="Move down"' + (i === folders.length - 1 ? ' disabled' : '') + '>↓</button>' +
                        '<button type="button" class="btn btn-ghost btn-small" data-f-act="rename" data-id="' + esc(f.id) + '">' + (isSweet ? 'Rename' : 'Rename') + '</button>' +
                        '<button type="button" class="btn btn-danger btn-small" data-f-act="delete" data-id="' + esc(f.id) + '">' + (isSweet ? 'Delete' : 'Delete') + '</button>' +
                        '</div>';
                }
                return '<div class="folder' + active + '" data-folder="' + esc(f.id) + '" role="button" tabindex="0">' +
                    '<p class="f-name">' + esc(f.name) + '</p>' +
                    '<p class="f-count">' + n + ' ' + (n === 1 ? (isSweet ? 'invoice' : 'invoice') : (isSweet ? 'invoices' : 'invoices')) + '</p>' +
                    actions +
                    '</div>';
            }).join('');
        }

        function renderInvoices() {
            var f = folderById(activeFolderId);
            if (els.title) {
                els.title.textContent = f ? f.name : (isSweet ? 'Invoices' : 'Invoices');
            }
            var list = invoices.filter(function (inv) { return inv.folderId === activeFolderId; });
            if (filterUnappliedOnly && window.PbjOpsNudges) {
                list = list.filter(function (inv) {
                    return !window.PbjOpsNudges.isInvoiceApplied(inv.id);
                });
            }
            list.sort(function (a, b) {
                // unapplied first, then newest
                var aa = window.PbjOpsNudges && window.PbjOpsNudges.isInvoiceApplied(a.id) ? 1 : 0;
                var bb = window.PbjOpsNudges && window.PbjOpsNudges.isInvoiceApplied(b.id) ? 1 : 0;
                if (aa !== bb) return aa - bb;
                return (b.createdAt || 0) - (a.createdAt || 0);
            });
            if (els.count) els.count.textContent = String(list.length);

            if (!els.list) return;
            if (!activeFolderId) {
                els.list.innerHTML = '<div class="empty">' + (isSweet ? 'Create a folder first 💕' : 'Create a folder first.') + '</div>';
                return;
            }
            if (!list.length) {
                els.list.innerHTML = '<div class="empty">' +
                    (isSweet ? 'This folder is empty — snap a photo or upload a file ✨' : 'No invoices in this folder yet.') +
                    '</div>';
                return;
            }

            els.list.innerHTML = list.map(function (inv) {
                var thumb;
                if (isPdf(inv)) {
                    thumb = '<a class="inv-thumb" href="' + esc(inv.url) + '" target="_blank" rel="noopener"><span class="pdf-ico">📄</span></a>';
                } else {
                    thumb = '<a class="inv-thumb" href="' + esc(inv.url) + '" target="_blank" rel="noopener"><img src="' + esc(inv.url) + '" alt=""></a>';
                }
                var sub = [];
                if (inv.originalName) sub.push(inv.originalName);
                if (inv.createdAt) sub.push(formatDate(inv.createdAt));
                if (inv.note) sub.push(inv.note);
                var actions = '';
                if (canEdit) {
                    actions =
                        '<div class="inv-actions">' +
                        '<button type="button" class="btn btn-primary btn-small" data-i-act="costs" data-id="' + esc(inv.id) + '">' + (isSweet ? '💰 Apply costs' : 'Apply costs') + '</button>' +
                        '<button type="button" class="btn btn-ghost btn-small" data-i-act="edit" data-id="' + esc(inv.id) + '">' + (isSweet ? 'Edit' : 'Edit') + '</button>' +
                        '<button type="button" class="btn btn-ghost btn-small" data-i-act="move" data-id="' + esc(inv.id) + '">' + (isSweet ? 'Move' : 'Move') + '</button>' +
                        '<a class="btn btn-ghost btn-small" href="' + esc(inv.url) + '" target="_blank" rel="noopener">' + (isSweet ? 'Open' : 'Open') + '</a>' +
                        '<button type="button" class="btn btn-danger btn-small" data-i-act="delete" data-id="' + esc(inv.id) + '">' + (isSweet ? 'Delete' : 'Delete') + '</button>' +
                        '</div>';
                } else {
                    actions = '<div class="inv-actions"><a class="btn btn-ghost btn-small" href="' + esc(inv.url) + '" target="_blank" rel="noopener">' +
                        (isSweet ? 'Open' : 'Open') + '</a></div>';
                }
                var applied = loadAppliedMeta(inv.id);
                var needsCost = !(applied && applied.at);
                var appliedPill = applied && applied.at
                    ? ' <span class="pill applied">' + (isSweet ? 'Costs applied ' : 'Applied ') + formatDate(applied.at) +
                      (applied.count ? ' · ' + applied.count + (isSweet ? ' items' : ' items') : '') + '</span>'
                    : ' <span class="pill needs-cost">' + (isSweet ? 'Needs cost apply' : 'Needs cost apply') + '</span>';
                return '<div class="invoice' + (needsCost ? ' needs-cost-row' : '') + '" data-id="' + esc(inv.id) + '">' +
                    thumb +
                    '<div class="inv-meta">' +
                    '<p class="inv-title">' + esc(inv.title || inv.originalName || 'Invoice') + appliedPill + '</p>' +
                    '<p class="inv-sub">' + esc(sub.join(' · ')) + '</p>' +
                    actions +
                    '</div></div>';
            }).join('');
        }

        function closeModal() {
            if (els.modalRoot) els.modalRoot.innerHTML = '';
        }

        function openModal(html) {
            if (!els.modalRoot) return;
            els.modalRoot.innerHTML = '<div class="modal-backdrop" id="modal-backdrop"><div class="modal" role="dialog" aria-modal="true">' + html + '</div></div>';
            var backdrop = document.getElementById('modal-backdrop');
            if (backdrop) {
                backdrop.addEventListener('click', function (e) {
                    if (e.target === backdrop) closeModal();
                });
            }
        }

        function promptFolderName(title, initial, onOk) {
            openModal(
                '<h3>' + esc(title) + '</h3>' +
                '<div class="field"><label>' + (isSweet ? 'Folder name' : 'Folder name') + '</label>' +
                '<input type="text" id="modal-name" maxlength="80" value="' + esc(initial || '') + '"></div>' +
                '<div class="modal-actions">' +
                '<button type="button" class="btn btn-secondary" id="modal-cancel">' + (isSweet ? 'Cancel' : 'Cancel') + '</button>' +
                '<button type="button" class="btn btn-primary" id="modal-ok">' + (isSweet ? 'Save' : 'Save') + '</button>' +
                '</div>'
            );
            var input = document.getElementById('modal-name');
            var ok = document.getElementById('modal-ok');
            var cancel = document.getElementById('modal-cancel');
            if (input) {
                setTimeout(function () { input.focus(); input.select(); }, 50);
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') { e.preventDefault(); if (ok) ok.click(); }
                });
            }
            if (cancel) cancel.addEventListener('click', closeModal);
            if (ok) ok.addEventListener('click', function () {
                var name = (input && input.value || '').trim();
                if (!name) {
                    toast(isSweet ? 'Name can’t be empty' : 'Name required');
                    return;
                }
                closeModal();
                onOk(name);
            });
        }

        function editInvoice(inv) {
            var opts = folders.map(function (f) {
                return '<option value="' + esc(f.id) + '"' + (f.id === inv.folderId ? ' selected' : '') + '>' + esc(f.name) + '</option>';
            }).join('');
            openModal(
                '<h3>' + (isSweet ? 'Edit invoice' : 'Edit invoice') + '</h3>' +
                '<div class="field"><label>' + (isSweet ? 'Title' : 'Title') + '</label>' +
                '<input type="text" id="modal-title" maxlength="120" value="' + esc(inv.title || '') + '"></div>' +
                '<div class="field"><label>' + (isSweet ? 'Note' : 'Note') + '</label>' +
                '<textarea id="modal-note" rows="3" maxlength="500">' + esc(inv.note || '') + '</textarea></div>' +
                '<div class="field"><label>' + (isSweet ? 'Folder' : 'Folder') + '</label>' +
                '<select id="modal-folder">' + opts + '</select></div>' +
                '<div class="modal-actions">' +
                '<button type="button" class="btn btn-secondary" id="modal-cancel">' + (isSweet ? 'Cancel' : 'Cancel') + '</button>' +
                '<button type="button" class="btn btn-primary" id="modal-ok">' + (isSweet ? 'Save' : 'Save') + '</button>' +
                '</div>'
            );
            document.getElementById('modal-cancel').addEventListener('click', closeModal);
            document.getElementById('modal-ok').addEventListener('click', function () {
                var title = (document.getElementById('modal-title').value || '').trim();
                var note = (document.getElementById('modal-note').value || '').trim();
                var folderId = document.getElementById('modal-folder').value;
                closeModal();
                if (busy) return;
                busy = true;
                setStatus(isSweet ? 'Saving…' : 'Saving…');
                api('update', { id: inv.id, title: title, note: note, folderId: folderId })
                    .then(function (data) {
                        if (data.invoices) invoices = data.invoices;
                        else {
                            inv.title = title;
                            inv.note = note;
                            inv.folderId = folderId;
                        }
                        render();
                        toast(isSweet ? 'Saved ✨' : 'Saved');
                        setStatus('');
                    })
                    .catch(function (e) {
                        setStatus(errMsg(e), true);
                    })
                    .finally(function () { busy = false; });
            });
        }

        function moveInvoice(inv) {
            var opts = folders.map(function (f) {
                return '<option value="' + esc(f.id) + '"' + (f.id === inv.folderId ? ' selected' : '') + '>' + esc(f.name) + '</option>';
            }).join('');
            openModal(
                '<h3>' + (isSweet ? 'Move invoice' : 'Move invoice') + '</h3>' +
                '<div class="field"><label>' + (isSweet ? 'Move to folder' : 'Folder') + '</label>' +
                '<select id="modal-folder">' + opts + '</select></div>' +
                '<div class="modal-actions">' +
                '<button type="button" class="btn btn-secondary" id="modal-cancel">' + (isSweet ? 'Cancel' : 'Cancel') + '</button>' +
                '<button type="button" class="btn btn-primary" id="modal-ok">' + (isSweet ? 'Move' : 'Move') + '</button>' +
                '</div>'
            );
            document.getElementById('modal-cancel').addEventListener('click', closeModal);
            document.getElementById('modal-ok').addEventListener('click', function () {
                var folderId = document.getElementById('modal-folder').value;
                closeModal();
                if (folderId === inv.folderId) return;
                if (busy) return;
                busy = true;
                api('move', { id: inv.id, folderId: folderId })
                    .then(function (data) {
                        if (data.invoices) invoices = data.invoices;
                        else inv.folderId = folderId;
                        render();
                        toast(isSweet ? 'Moved 📁' : 'Moved');
                    })
                    .catch(function (e) { setStatus(errMsg(e), true); })
                    .finally(function () { busy = false; });
            });
        }

        function errMsg(e) {
            var code = e && e.message ? e.message : 'error';
            var map = {
                no_house: isSweet ? 'Join a restaurant house first to save invoices.' : 'No restaurant membership.',
                size: isSweet ? 'File is too big (max 10MB).' : 'File too large (10MB max).',
                type: isSweet ? 'Use a photo or PDF, please.' : 'Images or PDF only.',
                last_folder: isSweet ? 'Keep at least one folder.' : 'Cannot delete the last folder.',
                name: isSweet ? 'Name required.' : 'Name required.',
                auth: isSweet ? 'Please log in again.' : 'Please log in again.'
            };
            return map[code] || (isSweet ? 'Something went wrong — try again.' : 'Something went wrong.');
        }

        function uploadFiles(fileList) {
            if (!canEdit || busy) return;
            var files = Array.prototype.slice.call(fileList || []).filter(Boolean);
            if (!files.length) return;
            if (!activeFolderId) {
                toast(isSweet ? 'Pick a folder first' : 'Select a folder first');
                return;
            }
            busy = true;
            setStatus(isSweet ? 'Preparing…' : 'Preparing…');
            var i = 0;
            var okCount = 0;
            function next() {
                if (i >= files.length) {
                    busy = false;
                    setStatus('');
                    render();
                    if (okCount > 0) {
                        toast(isSweet
                            ? (okCount === 1 ? 'Uploaded ✨' : okCount + ' uploaded ✨')
                            : (okCount === 1 ? 'Uploaded' : okCount + ' uploaded'));
                    }
                    return;
                }
                var raw = files[i++];
                setStatus((isSweet ? 'Uploading ' : 'Uploading ') + i + '/' + files.length + '…');
                prepareFile(raw).then(function (file) {
                    if (!file) {
                        next();
                        return;
                    }
                    return api('upload', { folderId: activeFolderId }, file)
                        .then(function (data) {
                            if (data.invoices) invoices = data.invoices;
                            else if (data.invoice) invoices.push(data.invoice);
                            okCount++;
                            render();
                            next();
                        });
                }).catch(function (e) {
                    busy = false;
                    setStatus(errMsg(e), true);
                    render();
                    if (okCount > 0) {
                        toast(isSweet ? okCount + ' uploaded before an error' : okCount + ' uploaded, then failed');
                    }
                });
            }
            next();
        }

        // Folder list clicks
        if (els.folders) {
            els.folders.addEventListener('click', function (e) {
                var actBtn = e.target.closest('[data-f-act]');
                if (actBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!canEdit || busy) return;
                    var id = actBtn.getAttribute('data-id');
                    var act = actBtn.getAttribute('data-f-act');
                    var f = folderById(id);
                    if (!f) return;
                    if (act === 'up' || act === 'down') {
                        busy = true;
                        api('folder_move', { id: id, direction: act })
                            .then(function (data) {
                                if (data.folders) folders = data.folders;
                                render();
                            })
                            .catch(function (err) { setStatus(errMsg(err), true); })
                            .finally(function () { busy = false; });
                        return;
                    }
                    if (act === 'rename') {
                        promptFolderName(isSweet ? 'Rename folder' : 'Rename folder', f.name, function (name) {
                            busy = true;
                            api('folder_rename', { id: id, name: name })
                                .then(function (data) {
                                    if (data.folders) folders = data.folders;
                                    render();
                                    toast(isSweet ? 'Renamed ✨' : 'Renamed');
                                })
                                .catch(function (err) { setStatus(errMsg(err), true); })
                                .finally(function () { busy = false; });
                        });
                        return;
                    }
                    if (act === 'delete') {
                        var n = countInFolder(id);
                        var msg = n
                            ? (isSweet
                                ? 'Delete “' + f.name + '”? Its ' + n + ' invoice(s) will move to another folder.'
                                : 'Delete “' + f.name + '”? Invoices will move to another folder.')
                            : (isSweet ? 'Delete folder “' + f.name + '”?' : 'Delete folder “' + f.name + '”?');
                        if (!confirm(msg)) return;
                        busy = true;
                        api('folder_delete', { id: id })
                            .then(function (data) {
                                if (data.folders) folders = data.folders;
                                if (data.invoices) invoices = data.invoices;
                                if (activeFolderId === id) {
                                    activeFolderId = folders[0] ? folders[0].id : null;
                                }
                                render();
                                toast(isSweet ? 'Folder removed' : 'Folder deleted');
                            })
                            .catch(function (err) { setStatus(errMsg(err), true); })
                            .finally(function () { busy = false; });
                        return;
                    }
                }
                var card = e.target.closest('[data-folder]');
                if (card) {
                    activeFolderId = card.getAttribute('data-folder');
                    render();
                }
            });
            els.folders.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                var card = e.target.closest('[data-folder]');
                if (!card) return;
                e.preventDefault();
                activeFolderId = card.getAttribute('data-folder');
                render();
            });
        }

        // Invoice actions
        if (els.list) {
            els.list.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-i-act]');
                if (!btn || !canEdit) return;
                var id = btn.getAttribute('data-id');
                var act = btn.getAttribute('data-i-act');
                var inv = null;
                invoices.forEach(function (x) { if (x.id === id) inv = x; });
                if (!inv) return;
                if (act === 'costs') openCostApply(inv);
                if (act === 'edit') editInvoice(inv);
                if (act === 'move') moveInvoice(inv);
                if (act === 'delete') {
                    if (!confirm(isSweet ? 'Delete this invoice?' : 'Delete this invoice?')) return;
                    if (busy) return;
                    busy = true;
                    api('delete', { id: id })
                        .then(function (data) {
                            if (data.invoices) invoices = data.invoices;
                            else invoices = invoices.filter(function (x) { return x.id !== id; });
                            render();
                            toast(isSweet ? 'Deleted' : 'Deleted');
                        })
                        .catch(function (err) { setStatus(errMsg(err), true); })
                        .finally(function () { busy = false; });
                }
            });
        }

        if (els.btnAddFolder) {
            els.btnAddFolder.addEventListener('click', function () {
                if (!canEdit) return;
                promptFolderName(isSweet ? 'New folder' : 'New folder', '', function (name) {
                    busy = true;
                    api('folder_create', { name: name })
                        .then(function (data) {
                            if (data.folders) folders = data.folders;
                            if (data.folder) activeFolderId = data.folder.id;
                            render();
                            toast(isSweet ? 'Folder added ✨' : 'Folder added');
                        })
                        .catch(function (err) { setStatus(errMsg(err), true); })
                        .finally(function () { busy = false; });
                });
            });
        }

        // Snapshot FileList BEFORE clearing the input — live FileList becomes empty on clear (Android).
        function filesFromInput(input) {
            var out = [];
            if (!input || !input.files) return out;
            for (var i = 0; i < input.files.length; i++) {
                out.push(input.files[i]);
            }
            return out;
        }

        if (els.fileCamera) {
            els.fileCamera.addEventListener('change', function () {
                if (!canEdit) {
                    els.fileCamera.value = '';
                    return;
                }
                var files = filesFromInput(els.fileCamera);
                els.fileCamera.value = '';
                if (files.length) uploadFiles(files);
            });
        }
        if (els.fileUpload) {
            els.fileUpload.addEventListener('change', function () {
                if (!canEdit) {
                    els.fileUpload.value = '';
                    return;
                }
                var files = filesFromInput(els.fileUpload);
                els.fileUpload.value = '';
                if (files.length) {
                    uploadFiles(files);
                } else {
                    setStatus(isSweet ? 'No file selected — try again?' : 'No file selected.', true);
                }
            });
        }

        // Labels open the picker natively; keep button-like click fallback if needed
        if (els.btnCamera && els.fileCamera) {
            els.btnCamera.addEventListener('click', function (e) {
                if (!canEdit) {
                    e.preventDefault();
                    return;
                }
                // label[for] handles open; no extra .click() needed
            });
        }
        if (els.btnUpload && els.fileUpload) {
            els.btnUpload.addEventListener('click', function (e) {
                if (!canEdit) {
                    e.preventDefault();
                    return;
                }
            });
        }

        /* ── Invoice → ingredient cost apply (OCR + manual) ── */
        var ING_KEY = 'pbj_heat_ingredients_v1';
        var APPLIED_KEY = 'pbj_invoice_cost_applied_v1';
        var tesseractLoading = null;

        function loadMaster() {
            try {
                var r = JSON.parse(localStorage.getItem(ING_KEY) || 'null');
                if (!r || typeof r.items !== 'object') return { items: {} };
                return r;
            } catch (e) { return { items: {} }; }
        }
        function saveMaster(m) {
            localStorage.setItem(ING_KEY, JSON.stringify(m));
        }
        function loadAppliedAll() {
            try {
                var r = JSON.parse(localStorage.getItem(APPLIED_KEY) || 'null');
                return r && typeof r === 'object' ? r : {};
            } catch (e) { return {}; }
        }
        function loadAppliedMeta(invId) {
            var all = loadAppliedAll();
            return all[invId] || null;
        }
        function saveAppliedMeta(invId, meta) {
            var all = loadAppliedAll();
            all[invId] = meta;
            localStorage.setItem(APPLIED_KEY, JSON.stringify(all));
        }
        function normName(s) {
            return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim().replace(/\s+/g, ' ');
        }
        function ingredientKeys() {
            var m = loadMaster();
            return Object.keys(m.items || {}).map(function (k) {
                return { key: k, name: (m.items[k] && m.items[k].name) || k, sku: (m.items[k] && m.items[k].sku) || '' };
            });
        }
        function matchIngredient(rawName) {
            var n = normName(rawName);
            if (!n) return null;
            var list = ingredientKeys();
            var i, it, nn, score, best = null, bestScore = 0;
            for (i = 0; i < list.length; i++) {
                it = list[i];
                nn = normName(it.name);
                if (!nn) continue;
                if (nn === n) return { key: it.key, name: it.name, score: 100 };
                if (it.sku && String(it.sku).toLowerCase() === String(rawName).toLowerCase().trim()) {
                    return { key: it.key, name: it.name, score: 95 };
                }
                if (nn.indexOf(n) >= 0 || n.indexOf(nn) >= 0) {
                    score = Math.min(nn.length, n.length) / Math.max(nn.length, n.length) * 80;
                    if (score > bestScore) { bestScore = score; best = { key: it.key, name: it.name, score: score }; }
                } else {
                    var ta = n.split(' '), tb = nn.split(' '), hit = 0, j, k;
                    for (j = 0; j < ta.length; j++) {
                        if (ta[j].length < 3) continue;
                        for (k = 0; k < tb.length; k++) {
                            if (ta[j] === tb[k]) hit++;
                        }
                    }
                    if (hit) {
                        score = (hit / Math.max(ta.length, tb.length)) * 70;
                        if (score > bestScore) { bestScore = score; best = { key: it.key, name: it.name, score: score }; }
                    }
                }
            }
            return bestScore >= 40 ? best : null;
        }
        function parseMoney(s) {
            if (s == null || s === '') return null;
            var t = String(s).replace(/[$,\s]/g, '');
            var n = parseFloat(t);
            return isNaN(n) ? null : Math.round(n * 100) / 100;
        }
        function parseOcrLines(text) {
            var lines = String(text || '').split(/\r?\n/);
            var out = [];
            var skipRe = /^(invoice|subtotal|total|tax|amount due|balance|page|thank|sold to|ship to|date|po#|acct|account|qty|description|unit price|extended)/i;
            lines.forEach(function (line) {
                var raw = line.replace(/\s+/g, ' ').trim();
                if (raw.length < 4 || skipRe.test(raw)) return;
                var m = raw.match(/^(.*?)[\s$]+(\d{1,5}(?:[.,]\d{2})?)\s*$/);
                if (!m) m = raw.match(/^(.*?)[\s]+(\d+[.,]\d{2})\s*$/);
                if (!m) return;
                var name = m[1].replace(/[\s.·•\-–—]+$/g, '').replace(/^\d+\s+/, '').trim();
                var price = parseMoney(m[2].replace(',', '.'));
                if (!name || name.length < 2 || price == null || price <= 0 || price > 50000) return;
                name = name.replace(/^\d+(\.\d+)?\s*(cs|case|ea|ct|pk|pack|bag|lb|lbs)?\s+/i, '').trim();
                if (name.length < 2) return;
                out.push({ name: name, casePrice: price, qty: '' });
            });
            var seen = {};
            return out.filter(function (r) {
                var k = normName(r.name);
                if (seen[k]) return false;
                seen[k] = 1;
                return true;
            }).slice(0, 40);
        }
        function loadTesseract() {
            if (window.Tesseract) return Promise.resolve(window.Tesseract);
            if (tesseractLoading) return tesseractLoading;
            tesseractLoading = new Promise(function (resolve, reject) {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
                s.async = true;
                s.onload = function () {
                    if (window.Tesseract) resolve(window.Tesseract);
                    else reject(new Error('tesseract'));
                };
                s.onerror = function () { reject(new Error('tesseract')); };
                document.head.appendChild(s);
            });
            return tesseractLoading;
        }
        function runOcrOnUrl(url, statusEl) {
            if (!url || isPdf({ url: url, mime: '' })) {
                return Promise.reject(new Error('pdf'));
            }
            if (statusEl) statusEl.textContent = isSweet ? 'Loading OCR engine…' : 'Loading OCR…';
            return loadTesseract().then(function (T) {
                if (statusEl) statusEl.textContent = isSweet ? 'Reading invoice… (phone photos work best)' : 'Reading invoice…';
                return T.recognize(url, 'eng', {
                    logger: function (m) {
                        if (!statusEl || !m || m.status !== 'recognizing text') return;
                        var p = m.progress != null ? Math.round(m.progress * 100) : 0;
                        statusEl.textContent = (isSweet ? 'Reading invoice… ' : 'Reading… ') + p + '%';
                    }
                }).then(function (res) {
                    return (res && res.data && res.data.text) ? res.data.text : '';
                });
            });
        }
        function openCostApply(inv) {
            var prior = loadAppliedMeta(inv.id);
            var seedLines = (prior && Array.isArray(prior.lines) && prior.lines.length)
                ? prior.lines.map(function (l) {
                    return { name: l.name || '', casePrice: l.casePrice != null ? l.casePrice : '', qty: l.qty || '', key: l.key || '' };
                })
                : [{ name: '', casePrice: '', qty: '' }, { name: '', casePrice: '', qty: '' }];

            openModal(
                '<h3>' + (isSweet ? 'Apply costs from invoice' : 'Apply invoice costs') + '</h3>' +
                '<p class="hint">' + (isSweet
                    ? 'OCR scans the photo for product + $ lines, or type them. Matching names update <strong>case price</strong> on Product Setup — recipes re-cost live 💕'
                    : 'OCR or enter lines. Matching products get a new case price; recipe costing updates automatically.') + '</p>' +
                (inv.url && !isPdf(inv)
                    ? '<div class="btn-row" style="margin-bottom:8px;">' +
                      '<button type="button" class="btn btn-primary btn-small" id="ocr-run">' + (isSweet ? '📷 Scan with OCR' : 'Scan with OCR') + '</button>' +
                      '<a class="btn btn-ghost btn-small" href="' + esc(inv.url) + '" target="_blank" rel="noopener">' + (isSweet ? 'Open photo' : 'Open image') + '</a>' +
                      '</div>'
                    : '<p class="hint">' + (isSweet
                        ? 'PDFs: enter lines manually (or open the PDF and copy prices). Photos get one-tap OCR.'
                        : 'PDF: enter lines manually. Image invoices support OCR.') + '</p>') +
                '<div class="ocr-status" id="ocr-status"></div>' +
                '<pre class="ocr-preview" id="ocr-preview" style="display:none;"></pre>' +
                '<div id="cost-lines"></div>' +
                '<div class="btn-row" style="margin:8px 0 12px;">' +
                '<button type="button" class="btn btn-ghost btn-small" id="cost-add-line">' + (isSweet ? '+ Line' : '+ Line') + '</button>' +
                '</div>' +
                '<div class="modal-actions">' +
                '<button type="button" class="btn btn-secondary" id="modal-cancel">' + (isSweet ? 'Cancel' : 'Cancel') + '</button>' +
                '<button type="button" class="btn btn-primary" id="modal-ok">' + (isSweet ? 'Apply to inventory ✨' : 'Apply to inventory') + '</button>' +
                '</div>'
            );
            var modalEl = els.modalRoot && els.modalRoot.querySelector('.modal');
            if (modalEl) modalEl.classList.add('wide');

            var lines = seedLines.slice();
            var linesRoot = document.getElementById('cost-lines');
            var statusEl = document.getElementById('ocr-status');
            var previewEl = document.getElementById('ocr-preview');

            function paintLines() {
                if (!linesRoot) return;
                var opts = ingredientKeys().map(function (it) {
                    return '<option value="' + esc(it.name) + '"></option>';
                }).join('');
                linesRoot.innerHTML = '<datalist id="cost-ing-list">' + opts + '</datalist>' +
                    '<p class="hint" style="margin:0 0 8px;">' + (isSweet
                        ? 'Check the lines you want to apply — uncheck anything that looks wrong 💕'
                        : 'Only checked lines are applied to inventory.') + '</p>' +
                    lines.map(function (ln, idx) {
                        var match = matchIngredient(ln.name);
                        var matchHtml = '';
                        if (ln.name && match) {
                            matchHtml = '<span class="match-ok">' + (isSweet ? '→ ' : '→ ') + esc(match.name) + '</span>';
                        } else if (ln.name) {
                            matchHtml = '<span class="match-new">' + (isSweet ? 'New / no match — will create if priced' : 'No match — will create') + '</span>';
                        }
                        var checked = ln.apply === false ? '' : ' checked';
                        return '<div class="cost-line" data-idx="' + idx + '">' +
                            '<div class="field" style="margin:0;min-width:28px;"><label>' + (isSweet ? 'Apply' : 'On') + '</label>' +
                            '<input type="checkbox" class="cl-apply-inp" style="width:auto;transform:scale(1.2);margin:8px 0;"' + checked + '></div>' +
                            '<div class="cl-name field" style="margin:0;"><label>' + (isSweet ? 'Product' : 'Product') + '</label>' +
                            '<input type="text" class="cl-name-inp" list="cost-ing-list" value="' + esc(ln.name) + '" placeholder="' + (isSweet ? 'e.g. Heavy cream' : 'Product name') + '">' +
                            matchHtml + '</div>' +
                            '<div class="field" style="margin:0;"><label>' + (isSweet ? 'Case $' : 'Case $') + '</label>' +
                            '<input type="number" class="cl-price-inp" min="0" step="0.01" value="' + esc(ln.casePrice !== '' && ln.casePrice != null ? ln.casePrice : '') + '" placeholder="0.00"></div>' +
                            '<div class="field" style="margin:0;"><label>' + (isSweet ? 'Qty' : 'Qty') + '</label>' +
                            '<input type="number" class="cl-qty-inp" min="0" step="any" value="' + esc(ln.qty !== '' && ln.qty != null ? ln.qty : '') + '" placeholder="—"></div>' +
                            '<button type="button" class="btn btn-danger btn-small cl-rm" data-rm="' + idx + '">×</button>' +
                            '</div>';
                    }).join('');
            }
            function collectLines() {
                if (!linesRoot) return;
                linesRoot.querySelectorAll('.cost-line').forEach(function (row) {
                    var idx = parseInt(row.getAttribute('data-idx'), 10);
                    if (isNaN(idx) || !lines[idx]) return;
                    var n = row.querySelector('.cl-name-inp');
                    var p = row.querySelector('.cl-price-inp');
                    var q = row.querySelector('.cl-qty-inp');
                    var a = row.querySelector('.cl-apply-inp');
                    lines[idx].name = n ? n.value : '';
                    lines[idx].casePrice = p ? p.value : '';
                    lines[idx].qty = q ? q.value : '';
                    lines[idx].apply = a ? !!a.checked : true;
                });
            }
            paintLines();

            if (linesRoot) {
                linesRoot.addEventListener('input', function (e) {
                    if (!e.target.classList.contains('cl-name-inp')) return;
                    collectLines();
                    var row = e.target.closest('.cost-line');
                    if (!row) return;
                    var match = matchIngredient(e.target.value);
                    var span = row.querySelector('.match-ok, .match-new');
                    if (!span) {
                        span = document.createElement('span');
                        e.target.parentNode.appendChild(span);
                    }
                    if (e.target.value && match) {
                        span.className = 'match-ok';
                        span.textContent = '→ ' + match.name;
                    } else if (e.target.value) {
                        span.className = 'match-new';
                        span.textContent = isSweet ? 'New / no match — will create if priced' : 'No match — will create';
                    } else {
                        span.textContent = '';
                    }
                });
                linesRoot.addEventListener('click', function (e) {
                    var rm = e.target.closest('[data-rm]');
                    if (!rm) return;
                    collectLines();
                    var idx = parseInt(rm.getAttribute('data-rm'), 10);
                    lines.splice(idx, 1);
                    if (!lines.length) lines.push({ name: '', casePrice: '', qty: '' });
                    paintLines();
                });
            }
            var addBtn = document.getElementById('cost-add-line');
            if (addBtn) addBtn.addEventListener('click', function () {
                collectLines();
                lines.push({ name: '', casePrice: '', qty: '' });
                paintLines();
            });
            var ocrBtn = document.getElementById('ocr-run');
            if (ocrBtn) ocrBtn.addEventListener('click', function () {
                ocrBtn.disabled = true;
                runOcrOnUrl(inv.url, statusEl).then(function (text) {
                    if (previewEl) {
                        previewEl.style.display = 'block';
                        previewEl.textContent = text.slice(0, 1200) || (isSweet ? '(no text found)' : '(no text)');
                    }
                    var parsed = parseOcrLines(text);
                    if (!parsed.length) {
                        if (statusEl) statusEl.textContent = isSweet
                            ? 'Couldn’t find price lines — type them below 💕'
                            : 'No price lines found — enter manually.';
                        return;
                    }
                    collectLines();
                    var hasData = lines.some(function (l) { return String(l.name || '').trim() && parseMoney(l.casePrice) != null; });
                    if (!hasData) lines = parsed;
                    else {
                        parsed.forEach(function (p) {
                            var exists = lines.some(function (l) { return normName(l.name) === normName(p.name); });
                            if (!exists) lines.push(p);
                        });
                    }
                    paintLines();
                    if (statusEl) statusEl.textContent = isSweet
                        ? ('Found ' + parsed.length + ' line(s) — check matches, then Apply ✨')
                        : ('Found ' + parsed.length + ' line(s). Review, then apply.');
                }).catch(function (err) {
                    var code = err && err.message;
                    if (statusEl) {
                        statusEl.textContent = code === 'pdf'
                            ? (isSweet ? 'OCR works on photos — enter PDF lines manually.' : 'OCR is for images; enter PDF lines manually.')
                            : (isSweet ? 'OCR failed to load — enter lines manually (or check network).' : 'OCR failed — enter lines manually.');
                    }
                }).finally(function () { ocrBtn.disabled = false; });
            });

            document.getElementById('modal-cancel').addEventListener('click', closeModal);
            document.getElementById('modal-ok').addEventListener('click', function () {
                collectLines();
                var master = loadMaster();
                var updated = 0, created = 0;
                var appliedLines = [];
                lines.forEach(function (ln) {
                    if (ln.apply === false) return;
                    var name = String(ln.name || '').trim();
                    var price = parseMoney(ln.casePrice);
                    if (!name || price == null || price < 0) return;
                    var match = matchIngredient(name);
                    var key = match ? match.key : normName(name);
                    if (!key) return;
                    if (!master.items[key]) {
                        master.items[key] = {
                            name: name,
                            unit: '',
                            costPerUnit: '',
                            casePrice: '',
                            pack: '',
                            packSize: '',
                            par: '',
                            parBy: 'each',
                            onHand: '',
                            vendor: '',
                            category: 'canned_dry',
                            sku: '',
                            source: 'invoice',
                            location: ''
                        };
                        created++;
                    } else {
                        updated++;
                        if (!master.items[key].name) master.items[key].name = name;
                    }
                    var item = master.items[key];
                    var prev = item.casePrice;
                    item.casePrice = price;
                    item.priceUpdatedAt = Date.now();
                    item.priceFromInvoiceId = inv.id;
                    item.priceFromInvoiceTitle = inv.title || inv.originalName || '';
                    var packNum = parseFloat(item.pack != null && item.pack !== '' ? item.pack : item.packSize);
                    if (!isNaN(packNum) && packNum > 0) {
                        item.costPerUnit = Math.round((price / packNum) * 10000) / 10000;
                    } else {
                        item.costPerUnit = price;
                    }
                    appliedLines.push({
                        name: item.name || name,
                        key: key,
                        casePrice: price,
                        prevCasePrice: prev,
                        qty: ln.qty || ''
                    });
                });
                if (!appliedLines.length) {
                    toast(isSweet ? 'Add at least one product + case $' : 'Enter at least one product and price');
                    return;
                }
                saveMaster(master);
                saveAppliedMeta(inv.id, {
                    at: Date.now(),
                    count: appliedLines.length,
                    lines: appliedLines,
                    invoiceTitle: inv.title || inv.originalName || ''
                });
                closeModal();
                render();
                toast(isSweet
                    ? ('Updated ' + updated + (created ? ', added ' + created : '') + ' · recipes re-cost live 💰')
                    : ('Updated ' + updated + (created ? ', created ' + created : '') + ' products'));
                setStatus(isSweet
                    ? 'Costs applied. Open Costing Sheet to see plate costs with new prices.'
                    : 'Costs applied to inventory. Check Costing Sheet for plate costs.');
            });
        }

        var btnUnapplied = document.getElementById('btn-show-unapplied');
        if (btnUnapplied) {
            btnUnapplied.addEventListener('click', function () {
                filterUnappliedOnly = !filterUnappliedOnly;
                if (filterUnappliedOnly && window.PbjOpsNudges) {
                    // Jump to folder with most unapplied
                    var info = window.PbjOpsNudges.unappliedFromInvoices(invoices);
                    if (info.unapplied[0] && info.unapplied[0].folderId) {
                        activeFolderId = info.unapplied[0].folderId;
                    }
                }
                render();
            });
        }

        applyPerms();
        if (window.PbjPerms && typeof window.PbjPerms.ready === 'function') {
            window.PbjPerms.ready(function () { applyPerms(); render(); });
        }
        load();
    })();
    </script>
</body>
</html>
