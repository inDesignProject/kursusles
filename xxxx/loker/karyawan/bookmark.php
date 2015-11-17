<?php

	include('../php/lib/db_connection.php');
	include('../php/lib/enkripsi.php');
	include('../php/lib/session.php');
	require "../php/lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	if($_SESSION['KursusLesLoker']['TYPEUSER'] <> 2){
		echo "<script>window.location.href = '".APP_URL."login?authResult=".$enkripsi->encode('5')."'</script>";
		die();
	} else {
		$idkary	=	$_SESSION['KursusLesLoker']['IDUSER'];
	}
	
	//AMBIL DATA BOOKMARK
	$sql		=	sprintf("SELECT B.NAMA_PERUSAHAAN AS NAMA_BOOKMARK, B.ALAMAT_KANTOR, C.NAMA_PROPINSI AS PROP_PERUSAHAAN, 
									D.NAMA_KOTA AS KOTA_PERUSAHAAN, B.EMAIL, B.JENIS_USAHA,
									F.NAMA_PERUSAHAAN, E.JUDUL, G.NAMA_BIDANG, H.NAMA_POSISI, I.NAMA_PROPINSI, J.NAMA_KOTA,
									A.JNSBOOKMARK, A.IDCHILD, A.TGLTAMBAH
							 FROM t_bookmark A
							 LEFT JOIN m_perusahaan B ON A.IDCHILD = B.IDPERUSAHAAN
							 LEFT JOIN m_propinsi C ON B.IDPROPINSI = C.IDPROPINSI
							 LEFT JOIN m_kota D ON B.IDKOTA = D.IDKOTA
							 LEFT JOIN t_lowongan E ON A.IDCHILD = E.IDLOWONGAN
							 LEFT JOIN m_perusahaan F ON E.IDPERUSAHAAN = F.IDPERUSAHAAN
							 LEFT JOIN m_bidang G ON E.IDBIDANG = G.IDBIDANG
							 LEFT JOIN m_posisi H ON E.IDPOSISI = H.IDPOSISI
							 LEFT JOIN m_propinsi I ON E.IDPROPINSI = I.IDPROPINSI
							 LEFT JOIN m_kota J ON E.IDKOTA = J.IDKOTA
							 WHERE A.JNSPEMILIK = 2 AND A.IDPEMILIK = %s
							 ORDER BY A.TGLTAMBAH DESC"
							, $idkary
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		
		foreach($result as $key){
			
			$type	=	$key['JNSBOOKMARK'];
			$idchild=	$enkripsi->encode($key['IDCHILD']);
			$data	.=	"";
			
			if($key['JNSBOOKMARK'] == 1){
				
				$data	.=	"    <div class='row'>
									<div class='col-lg-2 col-md-2 col-sm-2 col-xs-12'>
										<img src='".APP_IMG_URL."icon/paket.png' class='img-circle pull-right'>
									</div>
									<div class='col-lg-10 col-md-10 col-sm-10 col-xs-12'>
										<h5>
											<b>(Lowongan Kerja) ".$key['JUDUL']."</b>
											<small class='pull-right'>
												Ditambahkan pada :<br/> ".$key['TGLTAMBAH']."
											</small>
										</h5>
										<i class='fa fa-user'></i><small> ".$key['NAMA_PERUSAHAAN']."</small><br />
										<i class='fa fa-steam'></i><small> ".$key['NAMA_BIDANG']."</small><br />
										<i class='fa fa-chevron-right'></i><small> ".$key['NAMA_POSISI']."</small><br />
										<i class='fa fa-home'></i><small> ".$key['NAMA_KOTA'].", ".$key['NAMA_PROPINSI']."</small>
										<a href='lowongan?i=".$idchild."' class='btn btn-custom btn-xs pull-right' target='_blank'><i class='fa fa-book'></i> Kunjungi Laman > </a>
									</div>
								</div>
								<hr />";
							 
			} else {
				
				switch($key['JENIS_USAHA']){
					case "1"	:	$jnsusaha	=	"Dagang"; break;
					case "2"	:	$jnsusaha	=	"Jasa"; break;
					case "3"	:	$jnsusaha	=	"Manufaktur"; break;
					case "4"	:	$jnsusaha	=	"Agribisnis"; break;
					default		:	$jnsusaha	=	"Tidak diketahui"; break;
				}
				
				$data	.=	"    <div class='row'>
									<div class='col-lg-2 col-md-2 col-sm-2 col-xs-12'>
										<img src='".APP_IMG_URL."icon/perusahaan.png' class='img-circle pull-right'>
									</div>
									<div class='col-lg-10 col-md-10 col-sm-10 col-xs-12'>
										<h5>
											<b>(Perusahaan) ".$key['NAMA_BOOKMARK']."</b>
											<small class='pull-right'>
												Ditambahkan pada :<br/> ".$key['TGLTAMBAH']."
											</small>
										</h5>
										<i class='fa fa-home'></i><small> ".$key['ALAMAT_KANTOR']."</small><br />
										<i class='fa fa-chevron-right'></i><small> ".$key['KOTA_PERUSAHAAN'].", ".$key['PROP_PERUSAHAAN']." </small><br />
										<i class='fa fa-envelope'></i><small> ".$key['EMAIL']."</small><br />
										<i class='fa fa-steam'></i><small> ".$jnsusaha."</small><br />
										<a href='perusahaan?q=".$idchild."' class='btn btn-custom btn-xs pull-right' target='_blank'><i class='fa fa-user'></i> Kunjungi Profil > </a>
									</div>
								</div>
								<hr />";
	
			}
			
		}
		
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='7'><center>Tidak ada data</center></td></tr>";
	}
?>
<div class="boxSquareWhite">
    <h4>Data Bookmark</h4>
    <?=$data?>
</div>