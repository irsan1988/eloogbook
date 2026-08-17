<div class="card">
  <form class="filter" method="get" action="<?=e(url('entries'))?>">
    <select class="form-select" name="logbook_id"><option value="">Semua logbook</option><?php foreach($logbooks as $l):?><option value="<?=$l['id']?>" <?=$filter['logbook_id']==$l['id']?'selected':''?>><?=e($l['name'])?></option><?php endforeach;?></select>
    <input class="form-control" type="date" name="date_from" value="<?=e($filter['date_from'])?>">
    <input class="form-control" type="date" name="date_to" value="<?=e($filter['date_to'])?>">
    <button class="btn" type="submit">Filter</button>
  </form>
  <?php if(Auth::isPetugas()):?>
    <div class="mt"><a class="btn btn-sm <?=$filter['show_hidden']?'':'btn-secondary'?>" href="<?=e(url('entries'))?>">Data Ditampilkan</a> <a class="btn btn-sm <?=$filter['show_hidden']?'btn-secondary':''?>" href="<?=e(url('entries?show_hidden=1'))?>">Data Disembunyikan</a></div>
  <?php endif;?>
</div>
<div class="card">
  <div class="card-head"><div><h3><?=Auth::isPetugas()&&$filter['show_hidden']?'Data yang Disembunyikan':'Daftar Sesi'?></h3><p class="muted">Logbook umum tetap dapat memiliki banyak baris. Khusus Daily Check X-Ray hanya satu lembar per jenis X-Ray per tanggal.</p></div><?php if(Auth::isAdmin()):?><a class="btn btn-primary" href="<?=e(url('reports/print-range'))?>">Cetak Semua per Rentang</a><?php endif;?></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Logbook</th><th>Dinas/Regu/Shift</th><th>Pembuka Sesi</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach($sessions as $s):?>
        <tr>
          <td><?=e($s['session_date'])?></td>
          <td><b><?=e($s['code'])?></b><br><?=e($s['logbook_name'])?></td>
          <td><?=e($s['shift']?:'-')?></td>
          <td><?=e($s['creator']?:'-')?></td>
          <td class="actions">
            <a class="btn btn-sm btn-primary" href="<?=e(url('entries/session/'.$s['id']))?>"><?php if(Auth::isSupervisor()):?>Lihat<?php elseif(is_xray_special_code((string)$s['code'])):?>Buka Daily Check<?php else:?>Buka / Tambah<?php endif;?></a>
            <a class="btn btn-sm" target="_blank" href="<?=e(url('print/session/'.$s['id']))?>">Cetak</a>
            <?php if(Auth::isPetugas()):?>
              <?php if($filter['show_hidden']):?><form method="post" action="<?=e(url('entries/session/'.$s['id'].'/unhide'))?>" style="display:inline"><?=csrf_field()?><button class="btn btn-sm">Tampilkan</button></form>
              <?php else:?><form method="post" action="<?=e(url('entries/session/'.$s['id'].'/hide'))?>" style="display:inline" onsubmit="return confirm('Sembunyikan data ini hanya dari daftar Anda? Data tidak akan dihapus.')"><?=csrf_field()?><button class="btn btn-sm">Sembunyikan</button></form><?php endif;?>
            <?php endif;?>
            <?php if(Auth::isAdmin()):?><form method="post" action="<?=e(url('entries/session/'.$s['id'].'/delete'))?>" style="display:inline" onsubmit="return confirm('Hapus data logbook terisi ini beserta seluruh barisnya secara permanen?')"><?=csrf_field()?><button class="btn btn-danger btn-sm">Hapus Data</button></form><?php endif;?>
          </td>
        </tr>
      <?php endforeach;?>
      <?php if(!$sessions):?><tr><td colspan="5" class="muted">Belum ada data sesuai filter.</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>
