<?php $active=(int)($session['logbook_active']??1)===1; $hasRows=count($rows)>0; ?>
<div class="card">
  <div class="card-head">
    <div><h3><?=e($session['logbook_name'])?></h3><div class="muted"><?=e(xray_date_id($session['session_date']))?> • Daily Check satu kali per hari • Dibuka oleh <?=e($session['creator']?:'-')?></div></div>
    <div class="actions">
      <?php if(Auth::isAdmin()):?><a class="btn" href="<?=e(url('entries/session/'.$session['id'].'/edit'))?>">Edit Lembar</a><?php endif;?>
      <?php if($hasRows):?><a class="btn btn-primary" target="_blank" href="<?=e(url('print/session/'.$session['id']))?>">Cetak</a><?php endif;?>
      <?php if(Auth::isPetugas()):?><form method="post" action="<?=e(url('entries/session/'.$session['id'].'/hide'))?>" style="display:inline" onsubmit="return confirm('Sembunyikan data ini dari daftar Anda?')"><?=csrf_field()?><button class="btn">Sembunyikan</button></form><?php endif;?>
      <?php if(Auth::isAdmin()):?><form method="post" action="<?=e(url('entries/session/'.$session['id'].'/delete'))?>" onsubmit="return confirm('Hapus seluruh data Daily Check pada tanggal ini?')" style="display:inline"><?=csrf_field()?><button class="btn btn-danger">Hapus Data</button></form><?php endif;?>
    </div>
  </div>
  <div class="meta-grid"><?php foreach($headerFields as $f):?><div><span><?=e($f['label'])?></span><b><?=format_value($f,$headerValues[$f['id']]??'')?></b></div><?php endforeach;?></div>
</div>

<?php if(!$active && !$hasRows):?>
<div class="card"><div class="alert danger"><b>Daily Check sedang NONAKTIF.</b> Pengisian baru tidak dapat dilakukan. Admin dapat mengaktifkannya kembali dari menu DIALY CHECK HARIAN.</div></div>
<?php elseif(!$hasRows && Auth::canOperate()):?>
<div class="card">
  <div class="card-head"><div><h3>Lengkapi Daily Check Hari Ini</h3><div class="muted small">Data versi lama ini belum memiliki isi checklist. Lengkapi seluruh dropdown, checkbox, personel, catatan, dan tanda tangan pada template yang sama.</div></div></div>
  <form method="post" action="<?=e(url('entries/session/'.$session['id'].'/row'))?>">
    <?=csrf_field()?>
    <?php
      $logbook=['id'=>$session['logbook_id'],'code'=>$session['code'],'name'=>$session['logbook_name']];
      $detailValues=[];
      require APP_ROOT.'/app/views/entries/_xray_daily_full_template.php';
    ?>
    <div class="form-actions"><a class="btn" href="<?=e(url('daily-check'))?>">Batal</a><button class="btn btn-primary">Simpan Daily Check</button></div>
  </form>
</div>
<?php elseif(!$hasRows):?>
<div class="card"><div class="alert success">Mode Supervisor: Daily Check belum diisi. Pengisian dilakukan oleh Petugas atau Admin.</div></div>
<?php else:?>
<div class="card"><div class="alert success"><b>Daily Check tanggal ini sudah terisi.</b> Sistem tidak menyediakan penambahan baris baru. Gunakan Edit pada data tersimpan bila perlu koreksi.</div></div>
<?php endif;?>

<div class="card">
  <div class="card-head"><h3>Daily Check Tersimpan</h3><span class="badge"><?=$hasRows?'TERISI':'BELUM TERISI'?></span></div>
  <?php if(count($rows)>1):?><div class="alert danger">Terdapat <?=count($rows)?> lembar dari data versi lama. Mulai versi ini sistem tidak dapat menambahkan lembar berikutnya. Admin dapat menghapus data duplikat bila diperlukan.</div><?php endif;?>
  <div class="table-wrap"><table><thead><tr><th>No</th><th>Hasil</th><th>Personel 1</th><th>Personel 2</th><th>Penginput</th><th>Aksi</th></tr></thead><tbody>
  <?php $byKey=xray_field_by_key($detailFields); foreach($rows as $r):
    $gv=function(string $key) use($byKey,$r):string { return isset($byKey[$key])?(string)($r['values'][$byKey[$key]['id']]??''):''; };
    $hasil=$gv('result_fail')==='1'?'FAIL':($gv('result_pass')==='1'?'PASS':'-');
  ?>
    <tr><td><?=e($r['sequence_no'])?></td><td><b><?=e($hasil)?></b></td><td><?=e($gv('personel_1')?:'-')?></td><td><?=e($gv('personel_2')?:'-')?></td><td><?=e($r['creator']?:'-')?></td><td class="actions">
      <?php if(Auth::isAdmin() || (Auth::isPetugas() && (int)$r['created_by']===Auth::id())):?><a class="btn btn-sm" href="<?=e(url('entries/row/'.$r['id'].'/edit'))?>">Edit</a><?php endif;?>
      <?php if(Auth::isAdmin()):?><form method="post" action="<?=e(url('entries/row/'.$r['id'].'/delete'))?>" onsubmit="return confirm('Hapus Daily Check ini?')" style="display:inline"><?=csrf_field()?><button class="btn btn-danger btn-sm">Hapus</button></form><?php endif;?>
    </td></tr>
  <?php endforeach;?>
  <?php if(!$rows):?><tr><td colspan="6" class="muted">Belum ada Daily Check tersimpan.</td></tr><?php endif;?>
  </tbody></table></div>
</div>
