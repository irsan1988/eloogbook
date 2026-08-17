<?php $locked=is_xray_special_code((string)($item['code']??'')); ?>
<div class="card">
  <div class="card-head">
    <div>
      <h3><?=e($item['name'])?></h3>
      <p class="muted"><?=$locked?'Daily Check X-Ray menggunakan satu header dan satu lembar pengujian per tanggal.':'Header diisi sekali per sesi. Detail dapat diisi berulang sebagai baris pemeriksaan.'?></p>
    </div>
    <a class="btn" href="<?=e(url($locked?'daily-check':'logbooks'))?>">Kembali</a>
  </div>
  <?php if($locked):?>
    <div class="alert success"><b>Template Tetap Daily Check X-Ray.</b> Nama, kode, struktur field, urutan field, jenis input, posisi checkbox, layout cetak dan orientasi tetap dikunci agar hasil cetak tidak berubah. <b>Status Aktif/Nonaktif dapat diubah Admin dari menu DIALY CHECK HARIAN.</b> Data hasil pengujian tetap dapat diedit sesuai hak akses.</div>
  <?php endif;?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Urut</th><th>Bagian</th><th>Nama Kolom</th><th>Tipe</th><th>Wajib</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach($fields as $f):?>
        <tr>
          <td><?=e($f['sort_order'])?></td>
          <td><span class="badge"><?=e($f['section'])?></span></td>
          <td><b><?=e($f['label'])?></b><div class="muted small"><?=e($f['field_key'])?></div></td>
          <td><?=e($f['field_type'])?></td>
          <td><?=$f['required']?'Ya':'Tidak'?></td>
          <td>
            <?php if($locked):?>
              <span class="muted small">Dikunci</span>
            <?php else:?>
              <details><summary class="btn btn-sm">Edit</summary><form class="inline-editor" method="post" action="<?=e(url('fields/'.$f['id'].'/update'))?>"><?=csrf_field()?><?php $ff=$f; include APP_ROOT.'/app/views/logbooks/field_inputs.php';?><button class="btn btn-primary btn-sm">Simpan</button></form></details>
              <form method="post" action="<?=e(url('fields/'.$f['id'].'/delete'))?>" onsubmit="return confirm('Hapus kolom ini?')" style="display:inline"><?=csrf_field()?><button class="btn btn-danger btn-sm">Hapus</button></form>
            <?php endif;?>
          </td>
        </tr>
      <?php endforeach;?>
      <?php if(!$fields):?><tr><td colspan="6" class="muted">Belum ada rincian kolom.</td></tr><?php endif;?>
      </tbody>
    </table>
  </div>
</div>
<?php if(!$locked):?>
<div class="card narrow">
  <h3>Tambah Rincian Kolom</h3>
  <form method="post" action="<?=e(url('logbooks/'.$item['id'].'/fields'))?>">
    <?=csrf_field()?>
    <?php $ff=['section'=>'detail','label'=>'','field_key'=>'','field_type'=>'text','options'=>'','required'=>0,'help_text'=>'','sort_order'=>count($fields)+1]; include APP_ROOT.'/app/views/logbooks/field_inputs.php';?>
    <button class="btn btn-primary" type="submit">+ Tambahkan Kolom</button>
  </form>
</div>
<?php endif;?>
