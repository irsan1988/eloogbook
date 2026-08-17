<div class="card">
  <div class="card-head">
    <div><h3><?=e($session['logbook_name'])?></h3><div class="muted"><?=e($session['session_date'])?><?=$session['shift']?' • '.e($session['shift']):''?> • Sesi dibuka oleh <?=e($session['creator']?:'-')?></div></div>
    <div class="actions">
      <?php if(Auth::isAdmin()):?><a class="btn" href="<?=e(url('entries/session/'.$session['id'].'/edit'))?>">Edit Header</a><?php endif;?>
      <a class="btn btn-primary" target="_blank" href="<?=e(url('print/session/'.$session['id']))?>">Cetak</a>
      <?php if(Auth::isPetugas()):?><form method="post" action="<?=e(url('entries/session/'.$session['id'].'/hide'))?>" style="display:inline" onsubmit="return confirm('Sembunyikan data ini dari daftar Anda?')"><?=csrf_field()?><button class="btn">Sembunyikan</button></form><?php endif;?>
      <?php if(Auth::isAdmin()):?><form method="post" action="<?=e(url('entries/session/'.$session['id'].'/delete'))?>" onsubmit="return confirm('Hapus seluruh data logbook terisi dan semua baris secara permanen?')" style="display:inline"><?=csrf_field()?><button class="btn btn-danger">Hapus Data</button></form><?php endif;?>
    </div>
  </div>
  <?php if($headerFields):?><div class="meta-grid"><?php foreach($headerFields as $f):?><div><span><?=e($f['label'])?></span><b><?=format_value($f,$headerValues[$f['id']]??'')?></b></div><?php endforeach;?></div><?php endif;?>
</div>

<?php if(Auth::canOperate()):?>
<div class="card"><div class="card-head"><div><h3>Tambah Pemeriksaan</h3><div class="muted small">Nomor berikutnya: <?=count($rows)?((int)end($rows)['sequence_no']+1):1?></div></div></div>
  <?php if(!$detailFields):?><div class="alert danger">Belum ada kolom detail. Admin harus menambahkan rincian kolom terlebih dahulu.</div><?php else:?><form method="post" action="<?=e(url('entries/session/'.$session['id'].'/row'))?>"><?=csrf_field()?><div class="dynamic-grid"><?php foreach($detailFields as $f) echo field_input($f,''); ?></div><button class="btn btn-primary">+ Simpan Sebagai Baris Berikutnya</button></form><?php endif;?>
</div>
<?php else:?><div class="card"><div class="alert success">Mode Supervisor: data hanya dapat dilihat dan dicetak. Pengisian dilakukan oleh Petugas atau Admin.</div></div><?php endif;?>

<div class="card"><div class="card-head"><h3>Isi Logbook</h3><span class="badge"><?=count($rows)?> baris</span></div><div class="table-wrap"><table><thead><tr><th>No</th><?php foreach($detailFields as $f):?><th><?=e($f['label'])?></th><?php endforeach;?><th>Petugas</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?=e($r['sequence_no'])?></td><?php foreach($detailFields as $f):?><td><?=format_value($f,$r['values'][$f['id']]??'')?></td><?php endforeach;?><td><?=e($r['creator']?:'-')?></td><td class="actions"><?php if(Auth::isAdmin() || (Auth::isPetugas() && (int)$r['created_by']===Auth::id())):?><a class="btn btn-sm" href="<?=e(url('entries/row/'.$r['id'].'/edit'))?>">Edit</a><?php endif;?><?php if(Auth::isAdmin()):?><form method="post" action="<?=e(url('entries/row/'.$r['id'].'/delete'))?>" onsubmit="return confirm('Hapus baris ini?')" style="display:inline"><?=csrf_field()?><button class="btn btn-danger btn-sm">Hapus</button></form><?php endif;?></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="<?=count($detailFields)+3?>" class="muted">Belum ada baris.</td></tr><?php endif;?></tbody></table></div></div>
