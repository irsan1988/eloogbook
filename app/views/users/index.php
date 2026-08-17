<div class="grid-users">
  <div class="card">
    <h3>Tambah Pengguna</h3>
    <form method="post" action="<?=e(url('users'))?>">
      <?=csrf_field()?>
      <label class="form-label">Nama</label><input class="form-control" name="name" required>
      <label class="form-label mt">Username</label><input class="form-control" name="username" required>
      <label class="form-label mt">Password (min. 8 karakter)</label><input class="form-control" type="password" name="password" required minlength="8">
      <label class="form-label mt">Role</label>
      <select class="form-select" name="role">
        <option value="petugas">Petugas</option>
        <option value="supervisor">Supervisor</option>
        <option value="admin">Admin</option>
      </select>
      <div class="form-text">Petugas baru otomatis dibuatkan QR Code login. Setelah itu Admin menentukan logbook yang menjadi tugasnya melalui menu Penugasan Petugas.</div>
      <input type="hidden" name="active" value="1">
      <button class="btn btn-primary mt-lg">Tambah</button>
    </form>
  </div>
  <div class="card">
    <div class="card-head"><div><h3>Daftar Pengguna</h3><p class="muted small">QR Code hanya berlaku untuk role Petugas. Reset QR Code langsung membatalkan QR Code sebelumnya.</p></div><div class="actions"><a class="btn" href="<?=e(url('assignments'))?>">Penugasan Petugas</a></div></div>
    <div class="table-wrap"><table><thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>QR Code</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
      <?php foreach($users as $u):?>
      <tr>
        <td><?=e($u['name'])?></td><td><?=e($u['username'])?></td><td><?=e(ucfirst($u['role']))?></td>
        <td><?php if($u['role']==='petugas'):?><span class="badge <?=$u['qr_issued_at']?'ok':'off'?>"><?=$u['qr_issued_at']?'Aktif':'Belum dibuat'?></span><?php else:?><span class="muted">-</span><?php endif;?></td>
        <td><?=$u['active']?'Aktif':'Nonaktif'?></td>
        <td class="actions">
          <details><summary class="btn btn-sm">Edit</summary>
            <form class="inline-editor" method="post" action="<?=e(url('users/'.$u['id'].'/update'))?>">
              <?=csrf_field()?>
              <input class="form-control" name="name" value="<?=e($u['name'])?>">
              <input class="form-control" name="username" value="<?=e($u['username'])?>">
              <select class="form-select" name="role">
                <option value="petugas" <?=$u['role']==='petugas'?'selected':''?>>Petugas</option>
                <option value="supervisor" <?=$u['role']==='supervisor'?'selected':''?>>Supervisor</option>
                <option value="admin" <?=$u['role']==='admin'?'selected':''?>>Admin</option>
              </select>
              <select class="form-select" name="active"><option value="1" <?=$u['active']?'selected':''?>>Aktif</option><option value="0" <?=!$u['active']?'selected':''?>>Nonaktif</option></select>
              <input class="form-control" type="password" name="password" placeholder="Password baru (opsional)">
              <button class="btn btn-primary btn-sm">Simpan</button>
            </form>
          </details>
          <?php if($u['role']==='petugas'):?>
            <form method="post" action="<?=e(url('users/'.$u['id'].'/qr'))?>" style="display:inline" onsubmit="return confirm('Buat QR Code baru? QR Code sebelumnya langsung tidak berlaku.');"><?=csrf_field()?><button class="btn btn-sm" type="submit"><?=$u['qr_issued_at']?'Reset QR Code':'Buat QR Code'?></button></form>
          <?php endif;?>
          <?php if((int)$u['id']!==Auth::id()):?><form method="post" action="<?=e(url('users/'.$u['id'].'/delete'))?>" onsubmit="return confirm('Hapus pengguna?')" style="display:inline"><?=csrf_field()?><button class="btn btn-danger btn-sm">Hapus</button></form><?php endif;?>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody></table></div>
  </div>
</div>
