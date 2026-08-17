<div class="card">
  <div class="card-head">
    <div>
      <h3>Daftar Logbook Pemeriksaan</h3>
      <p class="muted"><?php if(Auth::isAdmin()):?>Admin dapat membuat dan mengelola logbook Pemeriksaan. Daily Check X-Ray dipisahkan ke menu DIALY CHECK HARIAN.<?php else:?><?=Auth::isSupervisor()?'Supervisor dapat melihat data melalui menu Data Logbook dan memantau notifikasi pengisian.':'Pilih logbook aktif untuk membuka sesi yang sudah ada atau memulai sesi baru.'?><?php endif;?></p>
    </div>
    <?php if(Auth::isAdmin()):?><a class="btn btn-primary" href="<?=e(url('logbooks/create'))?>">+ Logbook Baru</a><?php endif;?>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Nama</th><th>Layout Cetak</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach($items as $x):?>
        <tr>
          <td><b><?=e($x['code'])?></b></td>
          <td><?=e($x['name'])?><div class="muted small"><?=e($x['description'])?></div></td>
          <td><?=e(ucfirst($x['print_layout']))?> / <?=e($x['orientation'])?></td>
          <td><span class="badge <?=$x['active']?'ok':'off'?>"><?=$x['active']?'Aktif':'Nonaktif'?></span></td>
          <td class="actions">
            <?php if($x['active'] && Auth::canOperate()):?><a class="btn btn-sm btn-primary" href="<?=e(url('entries/create/'.$x['id']))?>">Isi</a><?php endif;?>
            <?php if(Auth::isAdmin()):?>
              <?php if(is_xray_special_code((string)($x['code']??''))):?>
                <a class="btn btn-sm" href="<?=e(url('logbooks/'.$x['id'].'/fields'))?>">Lihat Template</a>
                <span class="badge ok" title="Struktur dan layout template tidak dapat diubah">Template Tetap</span>
              <?php else:?>
                <a class="btn btn-sm" href="<?=e(url('logbooks/'.$x['id'].'/fields'))?>">Rincian</a>
                <a class="btn btn-sm" href="<?=e(url('logbooks/'.$x['id'].'/edit'))?>">Edit</a>
                <form method="post" action="<?=e(url('logbooks/'.$x['id'].'/delete'))?>" style="display:inline" onsubmit="return confirm('HAPUS jenis logbook ini? Semua sesi, rincian pemeriksaan, dan data yang pernah diinput akan ikut terhapus permanen.');">
                  <?=csrf_field()?>
                  <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                </form>
              <?php endif;?>
            <?php endif;?>
          </td>
        </tr>
      <?php endforeach;?>
      <?php if(!$items):?><tr><td colspan="5" class="muted">Belum ada jenis logbook.</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>
