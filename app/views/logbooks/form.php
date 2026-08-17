<?php $edit=(bool)$item; $special=$edit && is_xray_special_code((string)($item['code']??'')); ?>
<div class="card narrow">
  <?php if($special):?><div class="alert success"><b>Template Tetap Daily Check X-Ray.</b> Struktur template tidak dapat diubah. Status Aktif/Nonaktif diatur dari menu <b>DIALY CHECK HARIAN</b>. Edit lainnya dilakukan pada data pengujian.</div><?php endif;?>
  <form method="post" action="<?=e($edit?url('logbooks/'.$item['id'].'/update'):url('logbooks'))?>">
    <?=csrf_field()?>
    <?php if($special):?><fieldset disabled style="border:0;padding:0;margin:0"><?php endif;?>
    <div class="grid2">
      <div><label class="form-label">Kode Logbook *</label><input class="form-control" name="code" required value="<?=e($item['code']??'')?>" placeholder="HBSC01" <?=$special?'readonly':''?>></div>
      <div><label class="form-label">Status</label><select class="form-select" name="active"><option value="1" <?=($item['active']??1)?'selected':''?>>Aktif</option><option value="0" <?=isset($item)&&!$item['active']?'selected':''?>>Nonaktif</option></select></div>
    </div>
    <label class="form-label mt">Nama Logbook *</label><input class="form-control" name="name" required value="<?=e($item['name']??'')?>">
    <label class="form-label mt">Deskripsi</label><textarea class="form-control" name="description" rows="3"><?=e($item['description']??'')?></textarea>
    <div class="grid2 mt">
      <div><label class="form-label">Layout Cetak</label><select class="form-select" name="print_layout" <?=$special?'disabled':''?>><option value="table" <?=($item['print_layout']??'table')==='table'?'selected':''?>>Tabel</option><option value="form" <?=($item['print_layout']??'')==='form'?'selected':''?>>Form / Checklist</option></select><?php if($special):?><input type="hidden" name="print_layout" value="form"><?php endif;?></div>
      <div><label class="form-label">Orientasi</label><select class="form-select" name="orientation" <?=$special?'disabled':''?>><option value="landscape" <?=($item['orientation']??'landscape')==='landscape'?'selected':''?>>Landscape</option><option value="portrait" <?=($item['orientation']??'')==='portrait'?'selected':''?>>Portrait</option></select><?php if($special):?><input type="hidden" name="orientation" value="portrait"><?php endif;?></div>
    </div>
    <?php if($special):?></fieldset><?php endif;?>
    <div class="form-actions"><a class="btn" href="<?=e(url('logbooks'))?>"><?=$special?'Kembali':'Batal'?></a><?php if(!$special):?><button class="btn btn-primary" type="submit">Simpan</button><?php endif;?></div>
  </form>
</div>
