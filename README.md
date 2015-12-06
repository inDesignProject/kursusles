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