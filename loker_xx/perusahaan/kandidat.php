<?php

	include('../php/lib/db_connection.php');
	include('../php/lib/enkripsi.php');
	include('../php/lib/session.php');
	require "../php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 1){
		echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
		die();
	} else {
		$idpers	=	$_SESSION['KursusLesLoker']['IDUSER'];
	}
	
	//AMBIL DATA BOOKMARK
	$sql		=	sprintf("SELECT A.NAMA, A.ALAMAT, A.TELPON, A.EMAIL, A.FOTO, A.TGL_LAHIR, A.JK, B.NAMA_POSISI, 
									C.NAMA_BIDANG, D.NAMA_PENDIDIKAN, A.TGL_AWALKERJA, A.TGL_AKHIRKERJA, 
									A.TENTANG, A.IDBIDANG, A.IDPOSISI, A.IDPENDIDIKAN, A.JURUSAN, A.IDKARYAWAN,
									E.NAMA_PROPINSI, F.NAMA_KOTA, X.TGLTAMBAH, X.IDCHILD
							 FROM t_bookmark X
							 LEFT JOIN m_karyawan A ON X.IDCHILD = A.IDKARYAWAN
							 LEFT JOIN m_posisi B ON A.IDPOSISI = B.IDPOSISI
							 LEFT JOIN m_bidang C ON A.IDBIDANG = C.IDBIDANG
							 LEFT JOIN m_pendidikan D ON A.IDPENDIDIKAN = D.IDPENDIDIKAN
							 LEFT JOIN m_propinsi E ON A.IDPROPINSI = E.IDPROPINSI
							 LEFT JOIN m_kota F ON A.IDKOTA = F.IDKOTA
							 WHERE X.JNSPEMILIK = 1 AND X.IDPEMILIK = %s
							 ORDER BY X.TGLTAMBAH DESC"
							, $idpers
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		
		foreach($result as $key){
			
			$type	=	$key['JNSBOOKMARK'];
			$idchild=	$enkripsi->encode($key['IDCHILD']);
			switch($key['JK']){
				case "L"	:	$jeniskelamin	=	"Laki-Laki"; break;
				case "P"	:	$jeniskelamin	=	"Perempuan"; break;
				default		:	$jeniskelamin	=	"Tidak Diketahui"; break;
			}
				
			$data	.=	"    <div class='row'>
								<div class='col-lg-2 col-md-2 col-sm-2 col-xs-12'>
									<img src='".APP_IMG_URL."generate_pic.php?type=kr&q=".$enkripsi->encode($key['FOTO'])."&w=90&h=90' class='img-responsive img-circle img-profile' style='margin-left:auto; margin-right:auto'>
								</div>
								<div class='col-lg-10 col-md-10 col-sm-10 col-xs-12'>
									<h5>
										<b>".$key['NAMA']."</b>
										<small class='pull-right'>
											Ditambahkan pada :<br/> ".$key['TGLTAMBAH']."
										</small>
									</h5>
								    <i class='fa fa-home'></i> ".$key['ALAMAT']." ".$key['NAMA_KOTA']." ".$key['NAMA_PROPINSI']."<br/>
								    <i class='fa fa-envelope-o'></i> ".$key['EMAIL']."<br/>
								    <i class='fa fa-phone'></i> ".$key['TELPON']."<br/>
								    <i class='fa fa-user'></i> ".$jeniskelamin."<br/>
								    <i class='fa fa-calendar'></i> ".$key['TGL_LAHIR']."<br/>
								    <i class='fa fa-graduation-cap'></i> ".$key['NAMA_PENDIDIKAN']." ".$key['JURUSAN']."<br/>
								    <i class='fa fa-building'></i> ".$key['NAMA_BIDANG']." ".$key['NAMA_POSISI']."<br/>
									<a href='".APP_URL."profil_kary?q=".$idchild."' class='btn btn-custom btn-xs pull-right' target='_blank'><i class='fa fa-book'></i> Kunjungi Profil > </a>
								</div>
							</div>
							<hr />";
							 
		}
		
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='7'><center>Tidak ada data".$idpers."</center></td></tr>";
	}
?>
<div class="boxSquareWhite">
    <h4>Data Bookmark</h4>
    <?=$data?>
</div>