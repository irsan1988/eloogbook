<?php
$labels=[
  'XRAY-SINGLE-BAGASI'=>'Bagasi Single View',
  'XRAY-SINGLE-CABIN'=>'Cabin Single View',
  'XRAY-SINGLE-SSCP'=>'SSCP Single View',
  'XRAY-SINGLE-CARGO'=>'Cargo Single View',
  'XRAY-MULTI-BAGASI'=>'Bagasi Multi View',
  'XRAY-MULTI-CABIN'=>'Cabin Multi View',
  'XRAY-MULTI-SSCP'=>'SSCP Multi View',
  'XRAY-MULTI-CARGO'=>'Cargo Multi View',
];
?>
<div class="card">
  <div class="card-head">
    <div>
      <h3>DIALY CHECK HARIAN X-RAY</h3>
      <p class="muted">Setiap jenis Daily Check X-Ray hanya dapat diisi <b>satu kali per tanggal</b>. Setelah tersimpan, sistem tidak menambahkan baris baru. Gunakan Edit untuk koreksi data yang sudah ada.</p>
      <?php if(Auth::isAdmin()):?><p class="muted small">Admin dapat mengaktifkan, menonaktifkan, atau menghapus permanen masing-masing Daily Check. Daily Check nonaktif tetap menyimpan riwayat dan hasil cetak. Hapus permanen akan menghapus template beserta seluruh data pengisian dan penugasannya.</p><?php endif;?>
    </div>
  </div>
  <div class="daily-grid">
    <?php foreach($items as $idx=>$x): $code=(string)$x['code']; $active=(int)($x['active']??0)===1; $assigned=$dailyAssignments[(int)$x['id']]??null; ?>
      <section class="daily-item <?=$active?'':'daily-item-inactive'?>">
        <div class="daily-number"><?=e((string)($idx+1))?></div>
        <div class="daily-body">
          <div class="daily-title-line">
            <h3><?=e($labels[$code]??$x['name'])?></h3>
            <span class="badge <?=$active?'badge-active':'badge-inactive'?>"><?=$active?'AKTIF':'NONAKTIF'?></span>
          </div>
          <div class="daily-code"><?=e($code)?></div>
          <p class="muted small"><?=e($x['name'])?></p>
          <p class="daily-assigned"><b>Petugas:</b> <?=e($assigned['name']??'Belum ditugaskan')?><?php if(!empty($assigned['username'])):?><span class="muted"> (@<?=e($assigned['username'])?>)</span><?php endif;?></p>
          <div class="daily-actions">
            <?php if($active && Auth::canOperate()):?>
              <a class="btn btn-primary" href="<?=e(url('entries/create/'.$x['id']))?>">Isi Daily Check</a>
            <?php elseif($active):?>
              <a class="btn btn-primary" href="<?=e(url('entries?logbook_id='.$x['id']))?>">Lihat Data</a>
            <?php else:?>
              <span class="btn btn-disabled" aria-disabled="true">Pengisian Nonaktif</span>
            <?php endif;?>

            <a class="btn" href="<?=e(url('entries?logbook_id='.$x['id']))?>">Riwayat</a>
            <?php if(Auth::isAdmin()):?>
              <a class="btn" href="<?=e(url('logbooks/'.$x['id'].'/fields'))?>">Lihat Template</a>
              <form method="post" action="<?=e(url('daily-check/'.$x['id'].'/toggle'))?>" style="display:inline" onsubmit="return confirm('<?=$active?'Nonaktifkan':'Aktifkan'?> Daily Check ini?')">
                <?=csrf_field()?>
                <input type="hidden" name="active" value="<?=$active?'0':'1'?>">
                <button class="btn <?=$active?'btn-danger':''?>" type="submit"><?=$active?'Nonaktifkan':'Aktifkan'?></button>
              </form>
              <form method="post" action="<?=e(url('logbooks/'.$x['id'].'/delete'))?>" style="display:inline" onsubmit="return confirm('HAPUS PERMANEN Daily Check <?=e($labels[$code]??$x['name'])?>?\n\nSeluruh data pengisian, sesi, checkbox, tanda tangan, riwayat, dan penugasan petugas pada Daily Check ini akan ikut terhapus dan tidak dapat dikembalikan.')">
                <?=csrf_field()?>
                <button class="btn btn-danger" type="submit">Hapus Daily Check</button>
              </form>
            <?php endif;?>
          </div>
        </div>
      </section>
    <?php endforeach;?>
    <?php if(!$items):?><div class="alert danger">Tidak ada Daily Check X-Ray yang tersedia.<?php if(Auth::isAdmin()):?> Template yang telah dihapus permanen tidak dibuat ulang otomatis.<?php endif;?></div><?php endif;?>
  </div>
</div>
