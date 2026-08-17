<?php $edit=(bool)$session; $isXray=is_xray_special_code((string)($logbook['code']??''));?>
<div class="card <?=$isXray?'':'narrow'?>">
  <h3><?=e($logbook['name'])?></h3>
  <p class="muted"><?=e($logbook['description'])?></p>
  <?php if($isXray):?>
    <div class="alert success"><b>Pengisian Daily Check langsung pada satu lembar template.</b> Dropdown header, checkbox pengujian, Catatan, Personel Pengamanan Penerbangan, dan tanda tangan diisi pada lembar yang sama.<?php if(Auth::isAdmin()):?> <a href="<?=e(url('xray-master'))?>"><b>Kelola Master Data X-Ray</b></a>.<?php endif;?></div>
    <?php if(!$edit):?><div class="alert success"><b>Satu kali per hari.</b> Satu jenis Daily Check X-Ray hanya memiliki satu lembar pada tanggal yang sama. Setelah disimpan, gunakan Edit untuk koreksi.</div><?php endif;?>
  <?php elseif(!$edit):?>
    <div class="alert success">Jika jenis logbook, tanggal, dan Dinas/Regu/Shift yang dipilih sudah ada, sistem otomatis membuka sesi yang sama. Data baru akan ditambahkan sebagai baris berikutnya.</div>
  <?php endif;?>

  <form method="post" action="<?=e($edit?url('entries/session/'.$session['id'].'/update'):url('entries/create/'.$logbook['id']))?>">
    <?=csrf_field()?>
    <div class="grid2">
      <div>
        <label class="form-label">Tanggal *</label>
        <input class="form-control" type="date" name="session_date" required value="<?=e($session['session_date']??date('Y-m-d'))?>">
      </div>
      <?php if(!$isXray):?>
      <div>
        <label class="form-label">Dinas / Regu / Shift</label>
        <input class="form-control" name="shift" value="<?=e($session['shift']??'')?>" placeholder="Contoh: Pagi / Regu 2">
        <div class="muted small">Gunakan penulisan yang konsisten agar satu tanggal dan satu regu/shift masuk ke lembar yang sama.</div>
      </div>
      <?php else:?><input type="hidden" name="shift" value=""><?php endif;?>
    </div>
    <hr>
    <?php if($isXray):
      $headerFields=$fields;
      $headerValues=$values;
      $detailFields=$detailFields??[];
      $detailValues=$detailValues??[];
      require APP_ROOT.'/app/views/entries/_xray_daily_full_template.php';
    else:
      foreach($fields as $f) echo field_input($f,$values[$f['id']]??'');
    endif;?>
    <div class="form-actions">
      <a class="btn" href="<?=e($edit?url('entries/session/'.$session['id']):url($isXray?'daily-check':'logbooks'))?>">Batal</a>
      <button class="btn btn-primary"><?=$isXray?($edit?'Simpan Perubahan':'Simpan Daily Check'):($edit?'Simpan Header':'Buka / Mulai Sesi')?></button>
    </div>
  </form>
</div>
