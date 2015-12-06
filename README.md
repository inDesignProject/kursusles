# kursusles
Kursusles.com

Folder yang digunakan hanya :
- admin
- dev
- loker

Selebihnya hanya digunakan keperluan development (isinya sama saja)


1. Folder Dev
  1a. Folder-folder
    - Folder CSS : berisi file css yang digunakan pada web kursusles.
    - Folder Font : berisi font yang digunakan pada web kursusles.
    - Folder Images : berisi gambar-gambar yang ada di web kursusles, upload identitas juga akan masuk ke folder ini dengan subfolder   sendiri.
    - Folder JS : berisi file js yang digunakan pada web kursusles.
    - Folder PHP : berisi function dan library PHP untuk web kursusles.
    - Folder Template : berisi template web kursusles yang digunakan di semua page.

  1b. File di folder Dev->PHP->lib
    - PHPMailerAutoload.php : file yang berisi function PHPMailer Autoload yang digunakan untuk mengirim email.
    - SimpleImage.php : file yang berisi class dan funtion untuk upload image.
    - ajax_response.php : file yang berisi function php dengan respon ajax.
    - class.phpmailer.php : file yang berisi class dari PHPMailer untuk mengirim email.
    - class.smtp.php : file yang berisi class dari PHP mailer untuk mail server SMTP.
    - db_connection : file yang berisi class untuk melakukan koneksi ke database.
    - define.php : file yang berisi API key google recaptcha.
    - enkripsi.php : file yang berisi class dan function untuk melakukan enkripsi data agar lebih aman.
    - file_request.php : digunakan untuk memberi respon terhadap file yang membutuhkan / memanggil file javascript atau css style
		parameter yang dikirim berupa string terenkripsi dengan kode tertentu dalam variabel $_GET['get'] di dalam URL request.
		- recaptchalib.php : file recaptcha dari google.
		- session.php : file yang berisi function untuk malakukan pengecekan, menyimpan, menghapus session dari user.
		- upload_foto_ctrl.php : file yang berisi function untuk upload foto.
		- upload_foto_ktp.php : file yang berisi function untuk upload foto ktp/identitas.
	 
	1c. File di folder Dev->PHP->page
    - Kjadwal.php : file yang berisi function untuk menyimpan jadwal pengajar.
    - level.php : file yang berisi function untuk validasi penambahan / pengurangan level / mapel
    - map.php : file yang berisi function untuk menyimpan data perubahan lokasi user.
    - mbalance.php : file yang berisi function yang berhubungan dengan saldo user.
    - mbookmark.php : file yang berisi function untuk mengambil data bookmark.
    - mhelpdesk.php : file yang berisi function pengaduan.
    - mkursus.php : file yang berisi data kurusus yang diikuti oleh murid, terdapat juga function untuk klaim, dan konfirmasi.
    - mpenawarwanted.php : file yang berisi data penawaran kursus.
    - mpencarian.php : file yang berisi function untuk pencarian.
    - mprofil.php : file yang berisi function profil seperti lihat dan edit profil.
    - mwithdraw.php : file yang berisi function untuk withdraw.
    - pesan.php : file yang berisi function untuk mengirim pesan.
    - profil.php : file untuk menampilkan halaman profil dan mengirim pesan ke pengajar.
    - testimoni.php : file untuk menampilkan testimoni dan function untuk mengirim testimoni oleh murid.
    - upload_foto.php : file yang berisi function untuk mengupload foto profil (pengajar).
    - upload_foto_ktp.php : file yang berisi funtion untuk mengupload scan ktp.
    - upload_foto_murid.php : file yang berisi function untuk mengupload foto profil (murid).
    
    1d. File di folder Dev->Template
    - footer.php : file berisi html untuk bagian footer.
    - header.php : file berisi html untuk bagian header.
    - login.php : file berisi html untuk bagian login.
    - top-header.php : file berisi html untuk bagian top hedaer
    
    1e. File - file di folder Dev
    - .htaccess : file htaccess untuk mengkonfigurasi url.
    - auth.php : file yang berisi function untuk melakukan pengecekan ketika login.
    - belipaket.php : file yang berisi function untuk membeli paket dari pengajar.
    - daftar_rekening.php : file yang berisi function untuk menambah rekening.
    - detail_paket.php : file untuk menampilkan detail paket.
    - editdata.php : file yang berisi function untuk edit data pengajar.
    - forgot_password.php : file php untuk request password.
    - index.php : file index website.
    - index_global.php : file index profil menampilkan profil pengajar dan murid.
    - index_murid.php : file index profil menampilkan hanya profil murid.
    - index_pengajar.php : file index profil menampilkan hanya profil pengajar.
    - login.php : file untuk menampilkan form login.
    - logout.php : file untuk menghapus session user.
    - msg_center.php : file untuk menampilkan daftar pesan.
    - msg_compose.php : file untuk membuat/kirim pesan.
    - pengajar_kursus.php : file untuk menampilkan halaman daftar kursus.
    - pengajar_profil.php : file untuk menampilkan halaman profil pengajar.
    - search_detail.php : file untuk menampilkan halaman search detail.
    - search_result.php : file untuk menampilkan halaman hasil pencarian.
    - send_report.php : file untuk melaporkan pengajar.
    - signup.php : file untuk menampilakan form daftar.
    - signup_activation.php : file untuk aktifasi pendaftaran.
    - signup_submit.php : file untuk memasukan data signup ke db.
    - tutorial.php : file untuk menampilkan halaman tutorial.
    - veri_akun.php : file untuk verifikasi akun.
    - withdraw.php : file untuk menampilkan halaman withdraw.