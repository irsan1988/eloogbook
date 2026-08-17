<div class="card">
  <div class="card-head"><div><h3>Logbook Belum Terisi</h3><p class="muted">Sistem menandai hari ketika minimal satu logbook aktif belum memiliki baris isian. Maksimal tampilan 93 hari per pemeriksaan.</p></div></div>
  <form class="filter" method="get" action="<?=e(url('notifications'))?>">
    <div></div><input class="form-control" type="date" name="date_from" value="<?=e($dateFrom)?>"><input class="form-control" type="date" name="date_to" value="<?=e($dateTo)?>"><button class="btn" type="submit">Periksa</button>
  </form>
</div>
<div class="card">
  <?php if(!$report):?><div class="alert success">Tidak ada kekurangan pengisian pada rentang tanggal tersebut.</div><?php endif;?>
  <div class="notification-list">
  <?php foreach($report as $day):?>
    <div class="notification-item">
      <div class="notification-date"><b><?=e(date('d-m-Y',strtotime($day['date'])))?></b><span class="badge danger-badge"><?=e($day['count'])?> belum terisi</span></div>
      <div class="notification-missing">
        <?php foreach($day['missing'] as $l):?><span class="missing-chip"><b><?=e($l['code'])?></b> <?=e($l['name'])?><?php if(!empty($l['assigned_petugas'])):?><br><small>Petugas: <?=e($l['assigned_petugas'])?></small><?php else:?><br><small>Petugas: belum ditugaskan</small><?php endif;?></span><?php endforeach;?>
      </div>
    </div>
  <?php endforeach;?>
  </div>
</div>
