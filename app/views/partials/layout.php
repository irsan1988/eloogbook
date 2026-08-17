<?php $user=Auth::user(); ?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title><?=e($title??'AVSEC Logbook')?></title><link rel="stylesheet" href="<?=e(url('public/assets/app.css'))?>"></head><body>
<div class="app-shell"><aside class="sidebar" id="appSidebar" aria-label="Navigasi utama"><div class="brand"><div class="brand-mark">A</div><div><b>AVSEC LOGBOOK</b><small>Security Operations</small></div></div><nav>
<a href="<?=e(url(''))?>">Dashboard</a>
<a href="<?=e(url('entries'))?>">Rekapan</a>
<a href="<?=e(url('logbooks'))?>"> Logbook Pemeriksaan</a>
<a href="<?=e(url('daily-check'))?>">Dialy Check Peralatan</a>
<?php if(Auth::isAdmin()):?><a href="<?=e(url('xray-master'))?>">Master Data X-Ray</a><?php endif;?>
<?php if(Auth::canMonitor()):?><a href="<?=e(url('notifications'))?>">Notifikasi Pengisian</a><?php endif;?>
<?php if(Auth::isAdmin()):?><a href="<?=e(url('assignments'))?>">Penugasan Petugas</a><a href="<?=e(url('reports/print-range'))?>">Cetak Rentang Tanggal</a><a href="<?=e(url('users'))?>">Pengguna</a><a href="<?=e(url('audit'))?>">Audit Trail</a><?php endif;?>
</nav><div class="sidebar-foot"><small><?=e($user['name']??'')?><br><?=e(strtoupper($user['role']??''))?></small><form method="post" action="<?=e(url('logout'))?>"><?=csrf_field()?><button class="btn btn-ghost btn-sm" type="submit">Keluar</button></form></div></aside>
<button type="button" class="sidebar-backdrop" aria-label="Tutup menu" data-menu-close></button>
<main class="main"><header class="topbar"><button class="menu-btn" type="button" aria-label="Buka menu" aria-controls="appSidebar" aria-expanded="false" data-menu-toggle>☰</button><div><h1><?=e($title??'')?></h1></div></header><section class="content"><?php if($m=flash('success')):?><div class="alert success"><?=e($m)?></div><?php endif;?><?php if($m=flash('error')):?><div class="alert danger"><?=e($m)?></div><?php endif;?><?=$content?></section></main></div>
<script>
(function(){
  const body=document.body;
  const toggle=document.querySelector('[data-menu-toggle]');
  const closeBtn=document.querySelector('[data-menu-close]');
  const sidebar=document.getElementById('appSidebar');
  function setMenu(open){
    body.classList.toggle('menu-open',open);
    if(toggle) toggle.setAttribute('aria-expanded',open?'true':'false');
  }
  if(toggle) toggle.addEventListener('click',()=>setMenu(!body.classList.contains('menu-open')));
  if(closeBtn) closeBtn.addEventListener('click',()=>setMenu(false));
  if(sidebar) sidebar.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>setMenu(false)));
  document.addEventListener('keydown',e=>{if(e.key==='Escape')setMenu(false)});
  window.addEventListener('resize',()=>{if(window.innerWidth>1000)setMenu(false)});
})();
</script>
</body></html>
