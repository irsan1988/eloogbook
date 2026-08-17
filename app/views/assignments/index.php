<div class="card">
  <div class="card-head"><div><h3>Penugasan Petugas per Logbook</h3><p class="muted">Admin menentukan logbook yang boleh diisi setiap Petugas. Logbook umum dapat memiliki beberapa petugas. Khusus DIALY CHECK HARIAN X-RAY hanya satu petugas untuk setiap jenis Daily Check.</p></div></div>
  <?php if(!$petugas):?><div class="alert danger">Belum ada akun role Petugas yang aktif. Tambahkan Petugas terlebih dahulu dari menu Pengguna.</div><?php endif;?>
</div>
<div class="card">
  <h3>Logbook Umum</h3>
  <p class="muted small">Centang satu atau beberapa Petugas. Petugas yang tidak ditugaskan tidak dapat melihat, membuka, atau mengisi logbook tersebut.</p>
  <div class="assignment-list">
  <?php foreach($general as $l): $selected=array_map(fn($x)=>(int)$x['user_id'],$generalMap[(int)$l['id']]??[]); ?>
    <form class="assignment-card" method="post" action="<?=e(url('assignments/general/'.$l['id']))?>">
      <?=csrf_field()?>
      <div><b><?=e($l['name'])?></b><div class="daily-code"><?=e($l['code'])?></div></div>
      <div class="assignment-checks">
        <?php foreach($petugas as $u):?><label class="assignment-check"><input type="checkbox" name="user_ids[]" value="<?=$u['id']?>" <?=in_array((int)$u['id'],$selected,true)?'checked':''?>> <span><?=e($u['name'])?><small>@<?=e($u['username'])?></small></span></label><?php endforeach;?>
        <?php if(!$petugas):?><span class="muted">Belum ada petugas.</span><?php endif;?>
      </div>
      <button class="btn btn-primary" type="submit">Simpan Penugasan</button>
    </form>
  <?php endforeach;?>
  <?php if(!$general):?><div class="muted">Belum ada logbook umum.</div><?php endif;?>
  </div>
</div>
<div class="card">
  <h3>DIALY CHECK HARIAN X-RAY</h3>
  <p class="muted small">Setiap Daily Check hanya boleh mempunyai <b>1 Petugas</b>. Pilih petugas yang bertanggung jawab. Pilih “Belum ditugaskan” untuk mengosongkan penugasan.</p>
  <div class="table-wrap"><table><thead><tr><th>Daily Check</th><th>Petugas Tunggal</th></tr></thead><tbody>
  <?php foreach($daily as $l): $d=$dailyMap[(int)$l['id']]??null; ?>
    <tr><td><b><?=e($l['name'])?></b><div class="daily-code"><?=e($l['code'])?></div></td><td>
      <form class="daily-assignment-form" method="post" action="<?=e(url('assignments/daily/'.$l['id']))?>"><?=csrf_field()?>
        <select class="form-select" name="user_id"><option value="0">-- Belum ditugaskan --</option><?php foreach($petugas as $u):?><option value="<?=$u['id']?>" <?=((int)($d['user_id']??0)===(int)$u['id'])?'selected':''?>><?=e($u['name'])?> (@<?=e($u['username'])?>)</option><?php endforeach;?></select>
        <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
      </form>
    </td></tr>
  <?php endforeach;?>
  </tbody></table></div>
</div>
