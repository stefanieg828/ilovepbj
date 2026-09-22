<?php
/**
 * Admin · Catering — inquiries/events, recipe trays, food-cost quotes.
 */
require_once 'config.php';
if (is_file(__DIR__ . '/pbj-permissions.php')) {
    require_once __DIR__ . '/pbj-permissions.php';
}
require_once __DIR__ . '/catering.inc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}

global $pdo;
if (isset($pdo) && $pdo instanceof PDO) {
    pbj_catering_ensure_tables($pdo);
}

$is_sweet = ($_SESSION['theme'] ?? 'sweet') === 'sweet';
$can_edit = function_exists('pbj_can') ? pbj_can('admin.catering.edit') : true;
$can_view = function_exists('pbj_can') ? (pbj_can('admin.catering.view') || $can_edit) : true;
if (!$can_view) {
    header('Location: /admin');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if (function_exists('pbj_render_base_href')) { pbj_render_base_href(); } ?>
<?php if (function_exists('pbj_render_theme_paint')) { pbj_render_theme_paint(); } ?>
<title>Catering · <?php echo pbj_hub_label('admin'); ?> · ilovepbj ops</title>
<?php if (!$is_sweet): ?>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&display=swap" rel="stylesheet">
<?php endif; ?>
<script src="/food-cost-shared.js?v=4" defer></script>
<style>
@font-face { font-family: 'DreamingOutLoudPro'; src: url('/Fonts/dreaming-outloud-pro-regular.otf') format('opentype'); }
@font-face { font-family: 'ModernLoveCaps'; src: url('/Fonts/modern-love-caps.ttf') format('truetype'); }
body{margin:0;padding-bottom:100px;<?php if($is_sweet): ?>font-family:'DreamingOutLoudPro',serif;background:#FCF8EE;color:#3a2f1f;<?php else: ?>font-family:'Lora',serif;background:#F1EBE4;color:#1A2A44;<?php endif; ?>}
.header{<?php if($is_sweet): ?>background:#E55163;<?php else: ?>background:#1A2A44;<?php endif; ?>color:#fff;padding:20px 22px 24px;text-align:center}
.back-link{display:inline-block;color:#fff;text-decoration:none;opacity:.9;margin-bottom:10px}
h1{<?php if($is_sweet): ?>font-family:'ModernLoveCaps',serif;<?php else: ?>font-family:'Lora',serif;<?php endif; ?>font-size:2.4rem;margin:0;line-height:1.15}
.subtitle{margin:8px 0 0;opacity:.9}
.content{padding:18px 14px 40px;max-width:960px;margin:0 auto}
.card{background:#fff;border-radius:18px;padding:16px 18px;margin-bottom:14px;box-shadow:0 5px 15px rgba(0,0,0,.08)}
.card h2{<?php if($is_sweet): ?>font-family:'ModernLoveCaps',serif;color:#E55163;<?php else: ?>font-family:'Lora',serif;color:#1A2A44;<?php endif; ?>font-size:1.25rem;margin:0 0 10px}
.hint{font-size:.92rem;opacity:.75;line-height:1.45;margin:0 0 12px}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px}
@media(max-width:560px){.stats{grid-template-columns:1fr}}
.stat{background:#fff;border-radius:16px;padding:14px 10px;text-align:center;box-shadow:0 5px 15px rgba(0,0,0,.08)}
.stat .num{font-size:1.25rem;<?php if($is_sweet): ?>color:#E55163;<?php else: ?>color:#1A2A44;<?php endif; ?>}
.stat .lbl{font-size:.8rem;opacity:.7;margin-top:4px}
.toolbar{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center}
.chip{border:none;border-radius:999px;padding:8px 14px;font-size:.88rem;cursor:pointer;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.06);<?php if($is_sweet): ?>font-family:'DreamingOutLoudPro',serif;<?php else: ?>font-family:'Lora',serif;<?php endif; ?>}
.chip.active{<?php if($is_sweet): ?>background:#E55163;color:#fff;<?php else: ?>background:#1A2A44;color:#fff;<?php endif; ?>}
.btn{border:none;border-radius:14px;padding:11px 16px;font-size:1rem;cursor:pointer;text-decoration:none;display:inline-block;text-align:center;color:inherit;<?php if($is_sweet): ?>font-family:'DreamingOutLoudPro',serif;<?php else: ?>font-family:'Lora',serif;<?php endif; ?>}
.btn-primary{<?php if($is_sweet): ?>background:#E55163;color:#fff;<?php else: ?>background:#1A2A44;color:#fff;<?php endif; ?>}
.btn-secondary{background:#fff;box-shadow:0 3px 10px rgba(0,0,0,.08)}
.btn-danger{background:#FDECEA;color:#B71C1C}
.btn-small{padding:8px 12px;font-size:.88rem;border-radius:10px}
.btn:disabled{opacity:.45;cursor:not-allowed}
.field{margin-bottom:10px}
.field label{display:block;font-size:.82rem;opacity:.65;margin-bottom:4px}
.field input,.field select,.field textarea{width:100%;box-sizing:border-box;border-radius:12px;border:2px solid <?php echo $is_sweet?'#F3C5CC':'#C5D0DE'; ?>;padding:10px 12px;font-size:1rem;<?php if($is_sweet): ?>font-family:'DreamingOutLoudPro',serif;background:#FFFBF8;<?php else: ?>font-family:'Lora',serif;background:#FAF8F5;<?php endif; ?>}
.field-row{display:flex;flex-wrap:wrap;gap:10px}
.field-row .field{flex:1;min-width:140px}
.event-row{display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid <?php echo $is_sweet?'#F3E8DD':'#E6DFD7'; ?>;cursor:pointer}
.event-row:last-child{border-bottom:none}
.badge{display:inline-block;font-size:.72rem;border-radius:999px;padding:3px 10px;<?php if($is_sweet): ?>background:#FFF0F2;color:#E55163;border:1px solid #F3C5CC;<?php else: ?>background:#EAF1FA;color:#1A2A44;border:1px solid #C5D0DE;<?php endif; ?>}
.meta{font-size:.9rem;opacity:.75;margin-top:4px;line-height:1.4}
.empty{text-align:center;padding:28px 12px;opacity:.8}
.share-box{background:<?php echo $is_sweet?'#FFF5F6':'#EEF2F8'; ?>;border-radius:14px;padding:12px 14px;margin-top:8px;word-break:break-all}
.item-row{display:grid;grid-template-columns:1.5fr .5fr .7fr .7fr auto;gap:8px;align-items:center;padding:10px 0;border-bottom:1px solid <?php echo $is_sweet?'#F3E8DD':'#E6DFD7'; ?>;font-size:.95rem}
@media(max-width:700px){.item-row{grid-template-columns:1fr 1fr}.item-row .span-all{grid-column:1/-1}}
.totals{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}
@media(max-width:560px){.totals{grid-template-columns:1fr}}
.total-box{border-radius:14px;padding:12px;text-align:center;<?php if($is_sweet): ?>background:#FFF5F6;border:1px solid #F3C5CC;<?php else: ?>background:#EEF2F8;border:1px solid #C5D0DE;<?php endif; ?>}
.total-box .v{font-size:1.2rem;font-weight:600}
.toast{position:fixed;bottom:100px;left:50%;transform:translateX(-50%) translateY(12px);background:<?php echo $is_sweet?'#E55163':'#1A2A44'; ?>;color:#fff;padding:10px 18px;border-radius:999px;opacity:0;transition:all .25s;z-index:2100;pointer-events:none}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.recipe-pick{max-height:220px;overflow:auto;border-radius:12px;border:1px solid <?php echo $is_sweet?'#F3C5CC':'#C5D0DE'; ?>}
.recipe-opt{display:block;width:100%;text-align:left;border:none;background:#fff;padding:10px 12px;cursor:pointer;border-bottom:1px solid #eee;<?php if($is_sweet): ?>font-family:'DreamingOutLoudPro',serif;<?php else: ?>font-family:'Lora',serif;<?php endif; ?>}
.recipe-opt:hover{<?php if($is_sweet): ?>background:#FFF5F6;<?php else: ?>background:#EEF2F8;<?php endif; ?>}
.recipe-opt.selected{font-weight:700;<?php if($is_sweet): ?>background:#FFF0F2;<?php else: ?>background:#E8EEF7;<?php endif; ?>}
.view{display:none}.view.active{display:block}
</style>
</head>
<body>
<div class="header">
  <a href="/admin" class="back-link">← <?php echo pbj_back_to_hub('admin'); ?></a>
  <h1>Catering</h1>
  <p class="subtitle"><?php echo $is_sweet ? 'Inquiries → trays → food-cost quotes' : 'Inquiries, trays, and food-cost quotes'; ?></p>
</div>
<div class="content">
  <div id="view-list" class="view active">
    <div class="stats">
      <div class="stat"><div class="num" id="stat-open">0</div><div class="lbl"><?php echo $is_sweet ? 'Open inquiries' : 'Open inquiries'; ?></div></div>
      <div class="stat"><div class="num" id="stat-upcoming">0</div><div class="lbl">Upcoming</div></div>
      <div class="stat"><div class="num" id="stat-pipeline">$0</div><div class="lbl"><?php echo $is_sweet ? 'Quoted pipeline' : 'Quoted pipeline'; ?></div></div>
    </div>
    <div class="card">
      <h2><?php echo $is_sweet ? 'Share your inquire link' : 'Share inquire link'; ?></h2>
      <p class="hint"><?php echo $is_sweet ? 'Guests land in this house's Catering inbox — no login needed for them.' : 'Public form posts into this restaurant's Catering inbox.'; ?></p>
      <div class="share-box" id="share-box">Loading link…</div>
      <div class="toolbar" style="margin-top:10px">
        <button type="button" class="btn btn-secondary btn-small" id="btn-copy-link">Copy link</button>
        <a class="btn btn-secondary btn-small" href="/catering">Public catering page</a>
      </div>
    </div>
    <div class="toolbar">
      <button type="button" class="chip active" data-filter="all">All</button>
      <button type="button" class="chip" data-filter="inquiry">Inquiries</button>
      <button type="button" class="chip" data-filter="quoted">Quoted</button>
      <button type="button" class="chip" data-filter="confirmed">Confirmed</button>
      <button type="button" class="chip" data-filter="completed">Done</button>
      <span style="flex:1"></span>
      <?php if ($can_edit): ?><button type="button" class="btn btn-primary" id="btn-new">+ New event</button><?php endif; ?>
    </div>
    <div class="card"><div id="event-list" class="empty">Loading…</div></div>
  </div>

  <div id="view-detail" class="view">
    <div class="toolbar">
      <button type="button" class="btn btn-secondary" id="btn-back">← All events</button>
      <?php if ($can_edit): ?><button type="button" class="btn btn-danger btn-small" id="btn-delete">Delete</button><?php endif; ?>
    </div>
    <div class="card">
      <h2 id="detail-title">Event</h2>
      <form id="event-form">
        <input type="hidden" id="f-id" value="">
        <div class="field-row">
          <div class="field"><label>Status</label>
            <select id="f-status">
              <option value="inquiry">Inquiry</option>
              <option value="quoted">Quoted</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="field"><label>Target food cost %</label><input type="number" id="f-fc" min="5" max="80" step="0.5" value="30"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Contact name</label><input type="text" id="f-name" required></div>
          <div class="field"><label>Event name</label><input type="text" id="f-event-name"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Email</label><input type="email" id="f-email"></div>
          <div class="field"><label>Phone</label><input type="tel" id="f-phone"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Event date</label><input type="date" id="f-date"></div>
          <div class="field"><label>Time</label><input type="text" id="f-time" placeholder="e.g. 11:30a drop"></div>
          <div class="field"><label>Headcount</label><input type="number" id="f-head" min="0" step="1"></div>
        </div>
        <div class="field"><label>Delivery / setup notes</label><textarea id="f-delivery" rows="2"></textarea></div>
        <div class="field"><label>Kitchen notes</label><textarea id="f-notes" rows="2"></textarea></div>
        <?php if ($can_edit): ?><button type="submit" class="btn btn-primary">Save event</button><?php endif; ?>
      </form>
    </div>
    <div class="card">
      <h2>Trays from recipes</h2>
      <p class="hint">Plate cost is snapshotted from house recipes. Sell defaults to your target FC% (usually 30%).</p>
      <?php if ($can_edit): ?>
      <div class="field"><label>Search recipes</label><input type="search" id="recipe-search" placeholder="PBJ tray, cookie box…"></div>
      <div class="recipe-pick" id="recipe-pick"></div>
      <div class="field-row" style="margin-top:10px">
        <div class="field"><label>Qty</label><input type="number" id="add-qty" value="1" min="0.25" step="0.25"></div>
        <div class="field"><label>Unit</label><input type="text" id="add-unit" value="tray"></div>
        <div class="field"><label>Sell / unit (optional)</label><input type="number" id="add-sell" step="0.01" min="0" placeholder="auto"></div>
      </div>
      <p class="hint" id="add-cost-hint"></p>
      <button type="button" class="btn btn-primary" id="btn-add-item" disabled>Add tray</button>
      <?php endif; ?>
      <div id="items-wrap" style="margin-top:14px"></div>
      <div class="totals">
        <div class="total-box"><div class="lbl">Est. food cost</div><div class="v" id="tot-food">$0.00</div></div>
        <div class="total-box"><div class="lbl">Suggested quote</div><div class="v" id="tot-quote">$0.00</div></div>
        <div class="total-box"><div class="lbl">Implied FC%</div><div class="v" id="tot-fc">—</div></div>
      </div>
    </div>
  </div>
</div>
<div class="toast" id="toast"></div>
<?php include 'bottom-nav.php'; ?>
<script>
(function(){
  var isSweet = <?php echo $is_sweet ? 'true' : 'false'; ?>;
  var canEdit = <?php echo $can_edit ? 'true' : 'false'; ?>;
  var events = [];
  var filter = 'all';
  var current = null;
  var house = null;
  var pickRecipe = null;

  function $(id){ return document.getElementById(id); }
  function money(n){
    if (n == null || isNaN(n)) return '—';
    return '$' + (Math.round(n*100)/100).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
  }
  function toast(msg){
    var el = $('toast'); el.textContent = msg; el.classList.add('show');
    setTimeout(function(){ el.classList.remove('show'); }, 2200);
  }
  function esc(s){
    return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function api(action, body){
    body = body || {}; body.action = action;
    return fetch('/catering-api.php', {
      method:'POST', credentials:'same-origin',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(body)
    }).then(function(r){ return r.json(); });
  }
  function showView(name){
    document.querySelectorAll('.view').forEach(function(v){ v.classList.remove('active'); });
    $('view-' + name).classList.add('active');
  }
  function FC(){ return window.PbjFoodCost || null; }

  function renderShare(){
    if (!house){ $('share-box').textContent = 'No house invite code yet.'; return; }
    var url = house.inquire_url || (location.origin + (house.inquire_path || '/catering/inquire'));
    $('share-box').innerHTML = '<div><strong>' + esc(house.restaurant_name||'') + '</strong></div><code>' + esc(url) + '</code>';
    $('btn-copy-link').onclick = function(){
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function(){ toast(isSweet?'Link copied 💕':'Copied'); });
      } else { toast(url); }
    };
  }

  function upsertLocal(ev){
    var i = events.findIndex(function(x){ return x.id === ev.id; });
    if (i >= 0) events[i] = ev; else events.unshift(ev);
  }

  function renderList(){
    var open=0, upcoming=0, pipeline=0;
    var today = new Date(); today.setHours(0,0,0,0);
    events.forEach(function(e){
      if (e.status === 'inquiry' || e.status === 'quoted') open++;
      if ((e.status === 'quoted' || e.status === 'confirmed') && e.event_date){
        var d = new Date(e.event_date + 'T00:00:00');
        if (d >= today) upcoming++;
      }
      if (e.status === 'quoted' || e.status === 'confirmed'){
        pipeline += (e.rollups && e.rollups.quote_total) ? Number(e.rollups.quote_total) : 0;
      }
    });
    $('stat-open').textContent = String(open);
    $('stat-upcoming').textContent = String(upcoming);
    $('stat-pipeline').textContent = money(pipeline);

    var list = events.filter(function(e){ return filter==='all' || e.status===filter; });
    if (!list.length){
      $('event-list').innerHTML = '<div class="empty">' + (isSweet
        ? 'No catering events yet — tap + New event or share your inquire link 🥂'
        : 'No catering events yet. Create one or share the inquire link.') + '</div>';
      return;
    }
    $('event-list').innerHTML = list.map(function(e){
      var title = e.event_name || e.contact_name || 'Event';
      var bits = [];
      if (e.event_date) bits.push(e.event_date);
      if (e.headcount) bits.push(e.headcount + ' guests');
      if (e.contact_name && e.event_name) bits.push(e.contact_name);
      var q = e.rollups ? e.rollups.quote_total : 0;
      var fc = e.rollups ? e.rollups.food_cost : 0;
      return '<div class="event-row" data-id="'+e.id+'"><div><div><strong>'+esc(title)+'</strong> <span class="badge">'+esc(e.status)+'</span></div><div class="meta">'+esc(bits.join(' · '))+'</div></div><div style="text-align:right;white-space:nowrap"><div>'+money(q)+'</div><div class="meta">food '+money(fc)+'</div></div></div>';
    }).join('');
    $('event-list').querySelectorAll('.event-row').forEach(function(row){
      row.addEventListener('click', function(){ openDetail(parseInt(row.getAttribute('data-id'),10)); });
    });
  }

  function fillForm(e){
    $('f-id').value = e && e.id ? e.id : '';
    $('f-status').value = (e && e.status) || 'inquiry';
    $('f-fc').value = (e && e.target_fc_pct) != null ? e.target_fc_pct : 30;
    $('f-name').value = (e && e.contact_name) || '';
    $('f-event-name').value = (e && e.event_name) || '';
    $('f-email').value = (e && e.contact_email) || '';
    $('f-phone').value = (e && e.contact_phone) || '';
    $('f-date').value = (e && e.event_date) || '';
    $('f-time').value = (e && e.event_time) || '';
    $('f-head').value = (e && e.headcount != null) ? e.headcount : '';
    $('f-delivery').value = (e && e.delivery_notes) || '';
    $('f-notes').value = (e && e.notes) || '';
    $('detail-title').textContent = (e && (e.event_name || e.contact_name)) || (isSweet ? 'New catering event' : 'New event');
  }

  function renderItems(e){
    var items = (e && e.items) || [];
    var roll = (e && e.rollups) || { food_cost:0, quote_total:0, implied_fc:null };
    $('tot-food').textContent = money(roll.food_cost);
    $('tot-quote').textContent = money(roll.quote_total);
    $('tot-fc').textContent = roll.implied_fc != null ? (roll.implied_fc + '%') : '—';
    if (!items.length){
      $('items-wrap').innerHTML = '<div class="empty">' + (isSweet ? 'No trays yet — pick a recipe above 🥪' : 'No line items yet.') + '</div>';
      return;
    }
    $('items-wrap').innerHTML = items.map(function(it){
      return '<div class="item-row" data-item="'+it.id+'"><div class="span-all"><strong>'+esc(it.recipe_title)+'</strong>'+(it.notes?'<div class="meta">'+esc(it.notes)+'</div>':'')+'</div><div>'+esc(it.qty)+' '+esc(it.unit_label||'tray')+'</div><div>cost '+money(it.unit_cost_snapshot)+'</div><div>sell '+money(it.sell_price_snapshot)+'</div>'+(canEdit?'<button type="button" class="btn btn-danger btn-small item-del">Remove</button>':'<span></span>')+'</div>';
    }).join('');
    $('items-wrap').querySelectorAll('.item-del').forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = parseInt(btn.closest('.item-row').getAttribute('data-item'),10);
        api('item_delete', { event_id: current.id, id: id }).then(function(j){
          if (!j.ok){ toast(j.error||'Error'); return; }
          current = j.event; upsertLocal(current); renderItems(current); toast('Removed');
        });
      });
    });
  }

  function costForRecipe(rec){
    var fc = FC(); if (!fc || !rec) return null;
    var plate = fc.plateCostForRecipe(rec);
    if (!plate) return null;
    return plate.batch;
  }

  function renderRecipePick(){
    var box = $('recipe-pick'); if (!box) return;
    var fc = FC();
    var q = (($('recipe-search') && $('recipe-search').value) || '').toLowerCase().trim();
    var list = (fc && fc.flatRecipes) ? (fc.flatRecipes()||[]) : [];
    list = list.filter(function(r){
      var title = (r.title || r.name || '').toLowerCase();
      return !q || title.indexOf(q) >= 0;
    }).slice(0,40);
    if (!list.length){
      box.innerHTML = '<div class="empty" style="padding:14px">' + (isSweet
        ? 'No recipes yet — add some under Menu & Recipes first 📖'
        : 'No recipes found. Add recipes in BOH first.') + '</div>';
      pickRecipe = null; if ($('btn-add-item')) $('btn-add-item').disabled = true;
      return;
    }
    box.innerHTML = list.map(function(r, idx){
      var cost = costForRecipe(r);
      var label = r.title || r.name || 'Recipe';
      return '<button type="button" class="recipe-opt" data-idx="'+idx+'">'+esc(label)+(cost!=null?' <span class="meta">· food '+money(cost)+'</span>':' <span class="meta">· no cost yet</span>')+'</button>';
    }).join('');
    var cached = list;
    box.querySelectorAll('.recipe-opt').forEach(function(btn){
      btn.addEventListener('click', function(){
        pickRecipe = cached[parseInt(btn.getAttribute('data-idx'),10)];
        box.querySelectorAll('.recipe-opt').forEach(function(b){ b.classList.remove('selected'); });
        btn.classList.add('selected');
        var cost = costForRecipe(pickRecipe);
        var targetFc = parseFloat($('f-fc').value) || 30;
        var sug = (cost != null && fc) ? fc.suggestedSellPrices(cost) : null;
        var targetSell = cost != null ? Math.round((cost / (targetFc/100))*100)/100 : null;
        $('add-cost-hint').textContent = cost == null
          ? (isSweet ? 'No plate cost yet — sell stays blank until costing is filled in.' : 'No plate cost available.')
          : ('Food cost/tray ≈ ' + money(cost) + (sug ? (' · suggest ' + money(sug.at30) + ' @30% FC') : '') + (targetSell!=null?(' · at your '+targetFc+'% → '+money(targetSell)):''));
        if ($('add-sell') && targetSell != null) $('add-sell').placeholder = String(targetSell);
        if ($('btn-add-item')) $('btn-add-item').disabled = !canEdit || !current || !current.id;
      });
    });
  }

  function openDetail(id){
    api('get', { id: id }).then(function(j){
      if (!j.ok){ toast(j.error||'Not found'); return; }
      current = j.event;
      if (j.house) { house = j.house; renderShare(); }
      fillForm(current); renderItems(current); showView('detail'); renderRecipePick();
    });
  }

  function loadList(){
    return api('list').then(function(j){
      if (!j.ok){ toast(j.error||'Load failed'); return; }
      events = j.events || [];
      if (j.house) house = j.house;
      renderShare(); renderList();
    });
  }

  document.querySelectorAll('.chip[data-filter]').forEach(function(chip){
    chip.addEventListener('click', function(){
      document.querySelectorAll('.chip[data-filter]').forEach(function(c){ c.classList.remove('active'); });
      chip.classList.add('active'); filter = chip.getAttribute('data-filter'); renderList();
    });
  });
  $('btn-back').addEventListener('click', function(){ showView('list'); loadList(); });

  if ($('btn-new')) {
    $('btn-new').addEventListener('click', function(){
      api('create', { contact_name: 'New guest', status: 'inquiry', target_fc_pct: 30, source: 'admin' }).then(function(j){
        if (!j.ok){ toast(j.error||'Create failed'); return; }
        current = j.event; upsertLocal(current); fillForm(current); renderItems(current); showView('detail'); renderRecipePick();
        toast(isSweet ? 'Event started ✨' : 'Created');
      });
    });
  }

  $('event-form').addEventListener('submit', function(ev){
    ev.preventDefault(); if (!canEdit) return;
    var id = parseInt($('f-id').value || '0', 10);
    var payload = {
      id: id || undefined,
      contact_name: $('f-name').value,
      contact_email: $('f-email').value,
      contact_phone: $('f-phone').value,
      event_name: $('f-event-name').value,
      event_date: $('f-date').value,
      event_time: $('f-time').value,
      headcount: $('f-head').value,
      delivery_notes: $('f-delivery').value,
      notes: $('f-notes').value,
      status: $('f-status').value,
      target_fc_pct: $('f-fc').value
    };
    api(id ? 'update' : 'create', payload).then(function(j){
      if (!j.ok){ toast(j.error||'Save failed'); return; }
      current = j.event; upsertLocal(current); fillForm(current); renderItems(current);
      toast(isSweet ? 'Saved 💕' : 'Saved');
    });
  });

  if ($('btn-delete')) {
    $('btn-delete').addEventListener('click', function(){
      if (!current || !current.id) return;
      if (!confirm('Delete this catering event?')) return;
      api('delete', { id: current.id }).then(function(j){
        if (!j.ok){ toast(j.error||'Delete failed'); return; }
        events = events.filter(function(e){ return e.id !== current.id; });
        current = null; showView('list'); renderList(); toast('Deleted');
      });
    });
  }

  if ($('recipe-search')) $('recipe-search').addEventListener('input', renderRecipePick);
  if ($('btn-add-item')) {
    $('btn-add-item').addEventListener('click', function(){
      if (!canEdit || !current || !current.id || !pickRecipe) return;
      var cost = costForRecipe(pickRecipe);
      var sellRaw = $('add-sell').value;
      api('item_add', {
        event_id: current.id,
        recipe_id: String(pickRecipe.id || pickRecipe.recipeId || ''),
        recipe_title: pickRecipe.title || pickRecipe.name || 'Recipe',
        qty: parseFloat($('add-qty').value) || 1,
        unit_label: $('add-unit').value || 'tray',
        unit_cost_snapshot: cost,
        sell_price_snapshot: sellRaw !== '' ? parseFloat(sellRaw) : null
      }).then(function(j){
        if (!j.ok){ toast(j.error||'Add failed'); return; }
        current = j.event; upsertLocal(current); renderItems(current);
        $('add-sell').value = ''; toast(isSweet ? 'Tray added 🥪' : 'Item added');
      });
    });
  }

  setTimeout(function(){ loadList().then(function(){ renderRecipePick(); }); }, 60);
})();
</script>
</body>
</html>
