AVSEC LOGBOOK - PHP 8.3 - SIAP UPLOAD HOSTING
Versi 6

FITUR UTAMA
- Login Admin, Supervisor, dan Petugas
- Admin membuat jenis logbook baru
- Admin membuat rincian/field logbook secara dinamis
- Admin dapat mengedit jenis logbook
- Admin dapat MENGHAPUS jenis logbook
  PERINGATAN: penghapusan jenis logbook juga menghapus seluruh sesi dan data isian di dalam logbook tersebut secara permanen.
- Input data berurutan dalam satu sesi/tanggal/shift
- Cetak langsung melalui browser, tanpa export Excel
- Format cetak tabel menyesuaikan buku Random Check HBSCP
- Format cetak checklist X-Ray menyesuaikan lembar Check List Pengujian Harian X-Ray
- Checklist X-Ray Multi View halaman pertama dengan dua generator dan checkbox visual
- Audit trail
- Kelola user
- CSRF dan password_hash

INSTALASI BARU
1. Buat database MySQL/MariaDB dari cPanel.
2. Buat user database dan berikan ALL PRIVILEGES ke database tersebut.
3. Upload seluruh isi folder ini ke public_html atau subfolder, misalnya public_html/logbook.
4. Buka https://domainanda.com/logbook/install.php
5. Isi host, database, user database, password database, serta akun Admin.
6. Klik INSTAL SEKARANG.
7. Login dan gunakan menu Jenis Logbook.

UPDATE DARI PAKET SEBELUMNYA
- Backup database terlebih dahulu.
- Timpa/overwrite file aplikasi dengan paket revisi ini.
- JANGAN hapus file .env.php pada hosting.
- Tidak perlu import ulang schema.sql karena revisi ini tidak menambah tabel.
- Setelah update, Admin akan melihat tombol Hapus pada menu Jenis Logbook.

CETAK
- Jenis logbook dengan Layout Cetak = Tabel memakai tampilan buku Random Check HBSCP.
- Contoh bawaan HBSC menggunakan orientasi Landscape.
- Checklist X-Ray bawaan menggunakan Layout Cetak = Form/Checklist dan orientasi Portrait.
- Tombol CETAK menggunakan dialog print browser. Pilih printer atau Save as PDF jika diperlukan.
- Untuk hasil paling mirip formulir asli, gunakan Scale 100%, Margins Default/None sesuai browser, dan aktifkan Background graphics bila tersedia.

CATATAN KEAMANAN
- Hapus jenis logbook bersifat permanen dan juga menghapus data pemeriksaan di dalamnya.
- Lakukan backup database berkala.
- Setelah instalasi selesai, batasi akses ke install.php melalui hosting atau ubah nama file jika diperlukan.

============================================================
VERSI 3 - SESI BERSAMA / TAMBAH BARIS OTOMATIS
============================================================
Perubahan utama:
1. Admin dan Petugas dapat menambah baris pada sesi logbook yang sama.
2. Kunci sesi: JENIS LOGBOOK + TANGGAL + DINAS/REGU/SHIFT.
3. Jika kombinasi tersebut sudah ada, tombol Isi tidak membuat lembar baru.
   Sistem membuka sesi yang sudah ada dan input berikutnya menjadi No. berikutnya.
4. Petugas dapat melihat/cetak sesi bersama dan hanya mengedit baris miliknya sendiri.
5. Admin dapat mengedit header, mengedit/hapus semua baris, menghapus sesi, dan menghapus jenis logbook.
6. Cetak tetap mengambil seluruh baris dalam satu sesi sehingga satu tanggal/regu/shift tercetak dalam satu lembar/tabel.

JIKA INSTALASI BARU:
- Upload folder aplikasi.
- Buka install.php dan lakukan instalasi seperti biasa.
- Database versi baru sudah membatasi satu sesi untuk satu kombinasi logbook/tanggal/shift.

JIKA UPGRADE DARI V2:
- Backup database terlebih dahulu.
- Overwrite seluruh file aplikasi dengan versi ini.
- JANGAN mengganti file .env.php milik hosting Anda.
- Tidak wajib import schema.sql ulang.
- Pencegahan sesi ganda tetap berjalan pada level aplikasi.
- Jika sebelumnya sudah ada sesi ganda pada tanggal/shift yang sama, data lama tidak digabung otomatis. Rapikan manual jika diperlukan.

CONTOH:
Logbook : Random Check HBSCP
Tanggal : 13-08-2026
Shift   : Pagi / Regu A

Petugas A input -> No. 1
Petugas B membuka tanggal/shift yang sama -> No. 2
Petugas C membuka tanggal/shift yang sama -> No. 3
Tidak dibuat halaman baru selama kombinasi logbook, tanggal, dan shift sama.


============================================================
VERSI 4 - CHECKLIST X-RAY SINGLE VIEW VISUAL
============================================================
Tambahan versi 4:
1. Ditambahkan logbook khusus:
   CHECK LIST PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW
2. Tampilan lembar menggunakan template visual dari formulir referensi.
3. Kotak TEST pada gambar dapat diklik langsung sebagai checkbox.
4. Kotak yang dicentang menampilkan tanda centang pada posisi kotak aslinya.
5. Kotak PASS dan FAIL juga diisi langsung pada kotak formulir.
6. PASS dan FAIL dibuat saling eksklusif pada tampilan input.
7. Header yang diisi:
   - Nama Operator Penerbangan
   - Waktu Pengujian
   - Lokasi Penempatan/Gedung
   - Merk/Tipe/Nomor Seri
   - Nomor dan Tanggal Sertifikat
8. Detail lembar juga menyediakan Catatan serta Personel Pengamanan Penerbangan 1 dan 2.
9. Hasil cetak A4 Portrait menggunakan gambar formulir sebagai template sehingga susunan, garis, diagram TEST, dan kotak mengikuti dokumen referensi.
10. Satu baris data X-Ray = satu lembar cetak. Jika satu sesi memiliki beberapa pemeriksaan, hasil cetak menjadi beberapa halaman A4.

UPGRADE DARI V3
- Backup database terlebih dahulu.
- Overwrite seluruh file aplikasi dengan paket v4.
- JANGAN hapus atau timpa file .env.php di hosting.
- Buka aplikasi seperti biasa.
- Migrasi v4 berjalan otomatis sekali dan menambahkan logbook X-Ray Single View khusus.
- Tidak perlu import database ulang.

PENGGUNAAN X-RAY SINGLE VIEW
1. Buka Jenis Logbook / Data Logbook.
2. Pilih CHECK LIST PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW.
3. Isi tanggal sesi dan identitas mesin/operator.
4. Pada halaman pengujian, klik kotak kecil langsung di gambar.
5. Isi PASS atau FAIL, catatan bila diperlukan, serta nama personel.
6. Simpan lembar.
7. Klik Cetak. Gunakan A4 Portrait, Scale 100%, Margins None. Background graphics tidak wajib karena template dicetak sebagai gambar.

CATATAN
- Logbook khusus tetap dapat dihapus oleh Admin melalui menu Jenis Logbook.
- Jangan mengubah kode logbook XRAY-SINGLE-BAGASI bila ingin mempertahankan mode visual khusus.

============================================================
VERSI 5 - ROLE SUPERVISOR, HIDE PETUGAS, CETAK RENTANG
============================================================
Tambahan versi 5:
1. ADMIN
   - Dapat menghapus data logbook yang sudah terisi dari halaman Data Logbook maupun halaman sesi.
   - Penghapusan bersifat permanen dan menghapus seluruh baris dalam sesi tersebut.
   - Memiliki menu "Cetak Rentang Tanggal".
   - Pilih satu jenis logbook + tanggal awal + tanggal akhir untuk menampilkan seluruh hasil cetak logbook pada rentang tersebut.
   - Checklist X-Ray Single View tetap memakai template visual dan checkbox seperti versi 4.
2. PETUGAS
   - Memiliki tombol "Sembunyikan" pada data logbook.
   - Fitur sembunyikan bersifat PER AKUN. Data tidak dihapus dari database dan tetap terlihat oleh Admin serta Supervisor.
   - Data tersembunyi dapat dibuka melalui tab "Data Disembunyikan" lalu dipulihkan dengan tombol "Tampilkan".
   - Petugas tetap dapat mengisi dan menambah baris pada sesi bersama.
3. SUPERVISOR
   - Role baru: supervisor.
   - Bersifat monitoring/read-only. Tidak dapat membuat, mengedit, atau menghapus data.
   - Dapat melihat seluruh data logbook dan mencetak per sesi.
   - Memiliki menu "Notifikasi Pengisian".
   - Dashboard menampilkan notifikasi bila ada logbook aktif yang belum memiliki minimal satu baris isian pada hari ini.
   - Halaman notifikasi dapat memeriksa hari-hari yang belum terisi dalam rentang maksimal 93 hari.
   - Suatu logbook dianggap "terisi" pada suatu hari jika ada sesi pada tanggal tersebut dan minimal satu baris tersimpan.
4. CETAK RENTANG ADMIN
   - Menu: Cetak Rentang Tanggal.
   - Memilih satu logbook dan rentang tanggal.
   - Semua sesi dan semua baris pada rentang tersebut disusun menjadi satu hasil cetak.
   - Logbook tabel dicetak satu sesi per halaman.
   - X-Ray Single View dicetak satu lembar pengujian per halaman menggunakan template asli.

UPGRADE DARI V4
1. Backup database terlebih dahulu.
2. Overwrite seluruh file aplikasi dengan paket v5.
3. JANGAN hapus atau timpa .env.php milik hosting.
4. Buka aplikasi seperti biasa.
5. Migrasi otomatis akan:
   - menambahkan role supervisor pada tabel users;
   - membuat tabel hidden_logbook_sessions;
   - mengubah schema_version menjadi 5.
6. Setelah login Admin, buat akun Supervisor melalui menu Pengguna.

CATATAN NOTIFIKASI
- Sistem memantau semua jenis logbook yang statusnya Aktif.
- Hari sebelum tanggal pembuatan suatu logbook tidak dihitung sebagai kekurangan pengisian untuk logbook tersebut.
- Notifikasi bersifat monitoring berdasarkan data yang tersimpan di aplikasi.

============================================================
VERSI 6 - CHECKLIST X-RAY MULTI VIEW HALAMAN PERTAMA
============================================================
Tambahan versi 6:
1. Ditambahkan jenis logbook khusus:
   CHECK LIST PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS MULTI VIEW
2. Kode internal: XRAY-MULTI-BAGASI.
3. Hasil cetak menggunakan template visual halaman pertama/halaman 58 dari file referensi.
4. Struktur yang dipertahankan pada hasil cetak:
   - CHECK LIST PENGUJIAN HARIAN
   - MESIN X-RAY BAGASI JENIS MULTI VIEW
   - LEMBAR 1 DARI 1
   - identitas operator/mesin
   - GENERATOR ATAS/BAWAH
   - GENERATOR SAMPING
   - TEST 1a, TEST 1b, TEST 2a, TEST 2b, TEST 3, TEST 4, TEST 5
   - PASS / FAIL
   - CATATAN*)
   - Personel Pengamanan Penerbangan
5. Semua kotak pengujian pada dua generator dapat diklik langsung sebagai checkbox.
6. Tanda centang dicetak tepat pada kotak kecil yang dipilih.
7. PASS dan FAIL dibuat saling eksklusif pada form pengisian.
8. Nama Personel Pengamanan Penerbangan 1 dan 2 dapat diisi dan dicetak pada garis nama.
9. Satu lembar pengujian tersimpan dicetak menjadi satu halaman A4 Portrait.
10. Menu Admin Cetak Rentang Tanggal juga mendukung template Multi View ini.
11. Rincian field logbook Multi View dikunci agar posisi checkbox dan hasil cetak tidak rusak. Admin tetap dapat menghapus jenis logbook tersebut bila diperlukan.

UPGRADE DARI V5
1. Backup database.
2. Overwrite semua file aplikasi menggunakan paket v6.
3. JANGAN hapus atau overwrite file .env.php milik hosting.
4. Buka aplikasi seperti biasa.
5. Migrasi otomatis versi 6 akan menambahkan jenis logbook X-Ray Multi View satu kali.
6. Tidak perlu import schema.sql ulang.

PENGATURAN CETAK
- Paper: A4
- Orientation: Portrait
- Scale: 100%
- Margins: None
- Header and footer browser: Off
- Gunakan tombol CETAK dari aplikasi.


VERSI 7
-------
Ditambahkan 3 salinan khusus dari Daily Check X-Ray Multi View:
1. DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS MULTI VIEW
2. DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS MULTI VIEW
3. DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS MULTI VIEW

Ketiganya memakai struktur field, checkbox visual, Generator Atas/Bawah, Generator Samping,
PASS/FAIL, personel, dan tata letak cetak yang sama dengan X-Ray Bagasi Multi View.
Judul pada lembar pengisian dan hasil cetak berubah sesuai jenis CABIN, SSCP, atau CARGO.

UPDATE DARI V6:
- Backup database.
- Overwrite seluruh file aplikasi v7.
- JANGAN hapus/timpa .env.php yang sudah ada.
- Buka aplikasi satu kali. Migrasi otomatis menambahkan ketiga jenis logbook baru.

Nama logbook Bagasi Multi View juga diseragamkan menjadi DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS MULTI VIEW. Bentuk lembar cetak tetap mengikuti template sumber.


============================================================
VERSI 8 - COPY X-RAY SINGLE VIEW CABIN / SSCP / CARGO
============================================================
Tambahan versi 8:
1. Nama Bagasi Single View diseragamkan menjadi:
   DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW
2. Ditambahkan 3 salinan khusus Single View:
   - DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS SINGLE VIEW
   - DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS SINGLE VIEW
   - DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS SINGLE VIEW
3. Semua memakai struktur checkbox visual, TEST 1a/1b/2a/2b/3/4/5, PASS/FAIL, Catatan, dan Personel yang sama dengan Bagasi Single View.
4. Judul pada form visual, cetak per sesi, dan Cetak Rentang Tanggal berubah otomatis sesuai BAGASI, CABIN, SSCP, atau CARGO.
5. Template khusus tetap dikunci agar koordinat checkbox tidak berubah. Admin tetap dapat menghapus jenis logbook dan data terisi sesuai hak akses.

UPDATE DARI V7:
- Backup database terlebih dahulu.
- Overwrite seluruh file aplikasi v8 ke folder hosting.
- JANGAN hapus atau timpa file .env.php yang sudah dipakai hosting.
- Buka aplikasi satu kali. Migrasi otomatis menambahkan 3 logbook Single View baru dan menaikkan schema_version menjadi 8.
- Tidak perlu import database ulang.

PENGATURAN CETAK SINGLE VIEW:
- Paper A4
- Portrait
- Scale 100%
- Margins None
- Browser Header/Footer Off

============================================================
UPDATE v9 - TANDA TANGAN LANGSUNG PERSONEL 1
============================================================
- Seluruh DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY Single View dan Multi View memiliki signature pad.
- Posisi input: di sebelah kanan isian "Personel Pengamanan Penerbangan 1".
- Tanda tangan dapat ditulis langsung menggunakan mouse, stylus, atau jari pada layar sentuh.
- Tombol "Hapus Tanda Tangan" tersedia sebelum data disimpan.
- Saat edit lembar, tanda tangan lama dimuat kembali dan dapat diganti.
- Hasil cetak menempatkan tanda tangan Personel 1 pada garis tanda tangan sebelah kanan Personel 1 di template asli.
- Fitur juga berlaku pada Cetak Rentang Tanggal milik Admin.
- Data tanda tangan disimpan sebagai PNG di database. Kolom nilai detail otomatis ditingkatkan menjadi MEDIUMTEXT agar tidak terpotong.

UPDATE DARI v8:
1. Backup database terlebih dahulu.
2. Extract ZIP v9 dan overwrite seluruh file aplikasi lama.
3. JANGAN hapus atau timpa file .env.php yang sudah berisi konfigurasi database hosting.
4. Buka aplikasi sekali. Migrasi otomatis menambahkan field tanda tangan ke seluruh logbook X-Ray dan menaikkan schema_version ke 9.
5. Tidak perlu import ulang database.

============================================================
UPDATE v10 - TAMPILAN RESPONSIVE HP / TABLET
============================================================
- Seluruh dashboard, menu, form, daftar logbook, data logbook, pengguna, notifikasi, dan laporan dibuat responsif untuk layar HP/tablet.
- Sidebar desktop berubah menjadi menu geser (drawer) pada HP. Menu dapat ditutup dengan menekan area gelap, memilih menu, atau tombol Escape.
- Tombol dan kolom input diperbesar pada layar sentuh agar lebih mudah digunakan dengan jari.
- Input memakai ukuran font mobile yang mencegah zoom otomatis pada browser iPhone saat mengetik.
- Tabel tetap mempertahankan semua kolom dan dapat digeser horizontal pada layar kecil agar data tidak terpotong.
- Tombol aksi pada kartu dan sesi akan membungkus/menumpuk otomatis pada layar sempit.
- Signature Pad Personel 1 menyesuaikan lebar HP dan tetap dapat ditandatangani menggunakan jari/stylus.
- Form visual Daily Check X-Ray Single View dan Multi View memiliki 2 mode pada HP:
  1. Mode normal: seluruh lembar menyesuaikan lebar layar HP.
  2. Tombol "Perbesar Form": memperbesar lembar dan mengaktifkan geser horizontal agar kotak checklist kecil lebih mudah dipilih.
- Hasil CETAK tidak diubah. Template print tetap A4 dan tetap mengikuti layout X-Ray yang telah dibuat pada versi sebelumnya.

UPDATE DARI v9:
1. Backup database sebagai langkah pengamanan.
2. Extract ZIP v10 lalu overwrite file aplikasi lama.
3. JANGAN hapus atau timpa file .env.php milik hosting.
4. Tidak ada perubahan tabel database pada v10. Tidak perlu import SQL ulang.
5. Bila browser HP masih menampilkan CSS lama, lakukan refresh paksa atau bersihkan cache browser.

============================================================
UPDATE v11 - TEMPLATE TETAP KHUSUS DAILY CHECK X-RAY
============================================================
Seluruh Daily Check Pengujian Harian Mesin X-Ray Single View dan Multi View sekarang menjadi TEMPLATE TETAP sistem.

Yang DIKUNCI dan tidak dapat diubah dari menu Jenis Logbook:
- Nama logbook Daily Check X-Ray.
- Kode logbook.
- Status aktif.
- Struktur/rincian field.
- Urutan field.
- Tipe input.
- Posisi checkbox pada gambar.
- Layout cetak.
- Orientasi cetak.
- Jenis logbook Daily Check X-Ray tidak dapat dihapus.

Yang TETAP BISA DIEDIT:
- Data pengujian yang sudah diisi.
- Checkbox TEST 1a/1b/2a/2b/3/4/5.
- PASS/FAIL.
- Catatan.
- Personel Pengamanan Penerbangan.
- Tanda tangan Personel 1.
- Data hasil pengujian tetap dapat dihapus oleh Admin sesuai hak akses.

Tujuan penguncian ini adalah agar operator dapat mengedit isi pemeriksaan tanpa mengubah bentuk template dan tanpa menggeser hasil cetak.

UPDATE DARI v10:
1. Backup database terlebih dahulu.
2. Extract ZIP v11 dan overwrite file aplikasi lama.
3. JANGAN hapus atau timpa .env.php milik hosting.
4. Buka aplikasi sekali. Migrasi otomatis mengembalikan nama/layout/orientasi Daily Check X-Ray ke nilai tetap dan menaikkan schema_version menjadi 11.
5. Tidak perlu import database ulang.

============================================================
UPDATE v12 - ISIAN LANGSUNG DI TEMPLATE DAILY CHECK X-RAY
============================================================
1. Catatan diisi langsung pada area CATATAN di gambar/template X-Ray.
2. Personel Pengamanan Penerbangan 1 dan 2 diisi langsung pada garis nama di template.
3. Tanda tangan Personel 1 dilakukan langsung pada area tanda tangan sebelah kanan Personel 1.
4. Berlaku untuk semua Daily Check X-Ray Single View dan Multi View: Bagasi, Cabin, SSCP, dan Cargo.
5. Hasil cetak tetap menggunakan template X-Ray tetap dan menempatkan Catatan, nama personel, dan tanda tangan pada posisi yang sama.
6. Multi View lama otomatis mendapat field Catatan melalui migrasi schema 12 tanpa menghapus data lama.
7. Untuk update dari v11: backup database, overwrite file aplikasi, jangan hapus .env.php, lalu buka aplikasi sekali agar migrasi berjalan.


============================================================
VERSI 13 - MENU DIALY CHECK HARIAN TERPISAH
============================================================
Perubahan utama:
1. Ditambahkan menu utama khusus: DIALY CHECK HARIAN.
2. Menu Daftar Logbook sekarang hanya menampilkan logbook umum/dinamis.
3. Delapan template tetap X-Ray dipindahkan secara tampilan ke menu DIALY CHECK HARIAN dengan urutan:
   1) Bagasi Single View
   2) Cabin Single View
   3) SSCP Single View
   4) Cargo Single View
   5) Bagasi Multi View
   6) Cabin Multi View
   7) SSCP Multi View
   8) Cargo Multi View
4. Kode Bagasi diubah menjadi lebih eksplisit:
   - XRAY-SINGLE-EXACT / XRAY-SINGLE-ACT -> XRAY-SINGLE-BAGASI
   - XRAY-MULTI-EXACT -> XRAY-MULTI-BAGASI
5. Perubahan kode dilakukan pada record logbook yang sama sehingga ID, sesi, baris, checkbox, catatan, personel, tanda tangan, dan riwayat data tetap terhubung.
6. Kode template lain tetap:
   - XRAY-SINGLE-CABIN
   - XRAY-SINGLE-SSCP
   - XRAY-SINGLE-CARGO
   - XRAY-MULTI-CABIN
   - XRAY-MULTI-SSCP
   - XRAY-MULTI-CARGO
7. Template X-Ray tetap dikunci. Admin tidak dapat mengubah struktur atau menghapus template, tetapi dapat menghapus data hasil pengisian sesuai hak akses.

UPGRADE DARI V12
1. Backup database terlebih dahulu.
2. Overwrite file aplikasi dengan paket v13.
3. JANGAN hapus atau timpa .env.php milik hosting.
4. Buka aplikasi sekali setelah upload.
5. Migrasi schema 13 otomatis mengganti kode Bagasi lama dan menampilkan menu DIALY CHECK HARIAN.
6. Tidak perlu import schema.sql ulang.


FITUR v14 - MASTER DATA X-RAY
- Admin memiliki menu Master Data X-Ray.
- Operator, Lokasi, Mesin, dan Sertifikat tersimpan pada tabel master tersendiri.
- Pada Daily Check X-Ray, keempat data tersebut dipilih dengan dropdown langsung pada area header template.
- Berlaku untuk 8 template Single View dan Multi View.


============================================================
UPDATE v15 - DAILY CHECK XRAY SATU KALI PER HARI
============================================================
- Satu jenis Daily Check X-Ray hanya satu lembar per tanggal.
- Tidak ada penambahan baris kedua untuk Daily Check X-Ray.
- Admin dapat Aktifkan/Nonaktifkan per Daily Check dari menu DIALY CHECK HARIAN.
- Nonaktif menghentikan pengisian baru dan kewajiban notifikasi, tetapi riwayat/cetak tetap aman.
- Update hosting: backup database, overwrite file, pertahankan .env.php, lalu buka aplikasi sekali.

=== RIWAYAT V16: PENUGASAN PETUGAS & BARCODE (DIGANTI QR CODE PADA V18) ===
- Admin > Penugasan Petugas: tentukan petugas untuk masing-masing logbook.
- Logbook umum dapat ditugaskan ke satu atau beberapa Petugas.
- Setiap DIALY CHECK HARIAN X-RAY hanya memiliki satu Petugas yang ditugaskan.
- Petugas hanya melihat, membuka, mengisi, dan mencetak logbook yang ditugaskan kepadanya.
- Petugas baru otomatis dibuatkan barcode login saat akun dibuat.
- Barcode dapat di-reset dari Admin > Pengguna. Reset membuat barcode lama langsung tidak berlaku.
- Login barcode dapat memakai scanner USB/Bluetooth atau kamera browser yang mendukung BarcodeDetector.
- Untuk scan kamera, hosting wajib menggunakan HTTPS.
- Barcode adalah kredensial login. Jangan memfoto/menyebarkan barcode. Reset segera jika kartu hilang.


=== FITUR V17: ADMIN HAPUS DAILY CHECK X-RAY ===
- Admin dapat menghapus permanen salah satu jenis DIALY CHECK HARIAN X-RAY dari menu DIALY CHECK HARIAN.
- Tombol Hapus Daily Check tersedia terpisah dari Aktifkan/Nonaktifkan.
- Penghapusan bersifat permanen dan menghapus template, sesi, data pengisian, checkbox, tanda tangan, serta penugasan petugas pada Daily Check tersebut.
- Daily Check yang hanya dinonaktifkan tidak menghapus data dan dapat diaktifkan kembali.
- Template X-Ray yang masih ada tetap dikunci dari perubahan nama, kode, field, posisi checkbox, dan layout cetak.
- Update dari v16: backup database, overwrite file aplikasi, jangan hapus .env.php, lalu buka aplikasi sekali agar schema_version menjadi 17.

=== FITUR V18: LOGIN PETUGAS QR CODE ===
- Login Petugas menggunakan QR Code, bukan barcode 1D.
- Petugas baru otomatis dibuatkan QR Code login saat akun dibuat.
- Admin dapat membuat/reset QR Code dari menu Pengguna.
- Reset QR Code langsung membatalkan QR Code sebelumnya.
- QR Code dapat dicetak sebagai kartu login Petugas.
- Halaman login menyediakan scan QR Code dengan kamera HP.
- Hosting wajib HTTPS agar akses kamera browser dapat digunakan.
- QR scanner USB/Bluetooth dapat digunakan melalui kolom Kode QR.
- Saat update dari v17, barcode lama tidak digunakan lagi. Admin perlu membuat QR Code baru untuk Petugas lama.
- QR Code merupakan kredensial login. Simpan kartu QR dengan aman dan reset jika hilang.

=== FITUR V19: KOMPATIBILITAS SCAN QR KAMERA ===
- Scan kamera tidak lagi berhenti hanya karena BarcodeDetector tidak tersedia.
- Sistem memakai BarcodeDetector bila tersedia dan otomatis beralih ke jsQR bila tidak tersedia.
- Hosting tetap wajib HTTPS agar getUserMedia dapat membuka kamera.
- Jika izin kamera ditolak, buka Site settings browser dan izinkan Camera.
- Tidak ada perubahan database dari v18 ke v19.

=== FITUR V20: DAILY CHECK X-RAY SATU TEMPLATE PENUH ===
- Header dropdown, checkbox TEST, PASS/FAIL, Catatan, Personel 1/2, dan tanda tangan Personel 1 diisi langsung pada lembar template yang sama.
- Pembuatan Daily Check baru langsung menyimpan satu lembar lengkap. Tidak ada langkah tambah baris setelah membuat header.
- Edit Daily Check juga memakai lembar template yang sama, termasuk dropdown header.
- Daily Check tetap dibatasi satu lembar per jenis X-Ray per tanggal.
- Tidak ada perubahan database dari v19 ke v20.
