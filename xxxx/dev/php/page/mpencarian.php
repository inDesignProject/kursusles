<?php
	
	include('../lib/db_connection.php');
	include('../lib/enkripsi.php');
	include('../lib/session.php');
	require "../lib/defines.php";
	
	$db			=	new	Db_connection();
	$enkripsi	=	new Enkripsi();
	$session	=	new Session();
	
	session_start();
	$idmurid	=	$_GET['q'] == "" && !isset($_GET['q']) ? $_SESSION['KursusLes']['IDUSER'] : $enkripsi->decode($_GET['q']) ;
	
	//GET WANTED LIST
	$sql		=	sprintf("SELECT IDWANTED,JUDUL,HARGA,JENIS,JML_PERTEMUAN,JML_MURID,TGL_BATAS
							 FROM t_wantedlist
							 WHERE IDMURID = %s AND STATUS = 1
							 ORDER BY TGL_BATAS DESC"
							, $idmurid
							);
	$result		=	$db->query($sql);
	
	if($result <> false && $result <> ""){
		foreach($result as $key){
			$jenis	=	$key['JENIS'] == 1 ? "Privat" : "Grup";
			$data	.=	"<tr id='row".$enkripsi->encode($key['IDWANTED'])."' class='rowdata'>
							<td>".$key['JUDUL']."</td>
							<td align='right'>Rp. ".number_format($key['HARGA'],0,",",".")."</td>
							<td>".$jenis."</td>
							<td>".$key['JML_PERTEMUAN']."</td>
							<td>".$key['JML_MURID']."</td>
							<td align='center'>".date("Y-m-d",strtotime($key['TGL_BATAS']))."</td>
							<td align='center'><input type='button' value='Hapus' class='btn btn-sm btn-custom2' onclick='hpsData(\"".$enkripsi->encode($key['IDWANTED'])."\")'></td>
						 </tr>";
		}
	} else {
		$data	=	"<tr id='rownodata'><td colspan ='7'><center>Tidak ada data</center></td></tr>";
	}
	//HABIS -- GET WANTED LIST
	
	//FUNGSI TAMBAH DATA
	if( $enkripsi->decode($_GET['func']) == "addData" && isset($_GET['func'])){
		
		//CEK DATA HARGA
		if(!is_numeric($_POST['harga']) || ($_POST['harga'] * 1) < 0){
			echo json_encode(array("respon_code"=>"00001",
								   "respon_msg"=>"Masukkan nominal harga yang valid."));
			die();
		}
	
		//CEK DATA JML PERTEMUAN
		if(!is_numeric($_POST['jmlpertemuan']) || ($_POST['jmlpertemuan'] * 1) < 0){
			echo json_encode(array("respon_code"=>"00002",
								   "respon_msg"=>"Masukkan jumlah pertemuan yang valid."));
			die();
		}
		
		//CEK DATA JML MURID
		if(!is_numeric($_POST['jmlmurid']) || ($_POST['jmlmurid'] * 1) < 0){
			echo json_encode(array("respon_code"=>"00003",
								   "respon_msg"=>"Masukkan jumlah murid yang valid."));
			die();
		}
		
		$idmapel		=	$enkripsi->decode($_POST['idmapel']);
		$sqlIns			=	sprintf("INSERT INTO t_wantedlist
									 (IDMURID,JUDUL,HARGA,JENIS,JML_PERTEMUAN,JML_MURID,KETERANGAN,IDMAPEL,TGL_BATAS)
									 VALUES
									 ('%s','%s','%s','%s','%s','%s','%s','%s','%s')"
									, $_SESSION['KursusLes']['IDUSER']
									, $db->db_text($_POST['judul'])
									, $_POST['harga']
									, $_POST['jns_paket']
									, $_POST['jmlpertemuan']
									, $_POST['jmlmurid']
									, $db->db_text($_POST['keterangan'])
									, $idmapel
									, $_POST['tglbatas']
									);
		$lastID			=	$db->execSQL($sqlIns, 1);
			
		//JIKA LAST ID > 0
		if($lastID > 0){
			
			$jns_paket	=	$_POST['jns_paket'] <> '2' ? "Privat" : "Grup";
			$idwanted	=	$enkripsi->encode($lastID);
			$respon_msg	=	"	<tr id='row".$idwanted."' class='rowdata'>
									<td>".$_POST['judul']."</td>
									<td align='right'>Rp. ".number_format($_POST['harga'],0,',','.')."</td>
									<td>".$jns_paket."</td>
									<td>".$_POST['jmlpertemuan']."</td>
									<td>".$_POST['jmlmurid']."</td>
									<td align='center'>". $_POST['tglbatas']."</td>
									<td align='center'><input value='Hapus' class='btn btn-sm btn-custom2' onclick='hpsData(\"".$idwanted."\")' type='button'></td>
								 </tr>";
			echo json_encode(array("respon_code"=>"00000",
								   "respon_msg"=>$respon_msg));
			die();
			
		} else {
			echo json_encode(array("respon_code"=>"00004",
								   "respon_msg"=>"Gagal menyimpan data."));
			die();
		}
		
	}
	
	//FUNGSI HAPUS DATA
	if( $enkripsi->decode($_GET['func']) == "hpsData" && isset($_GET['func'])){
		
		$idwanted	=	$enkripsi->decode($_POST['value']);
		$sqlUpd		=	sprintf("UPDATE t_wantedlist SET STATUS = 0 WHERE IDWANTED = %s", $idwanted);
		$affected	=	$db->execSQL($sqlUpd, 0);
			
		//JIKA LAST ID > 0
		if($affected > 0){
			echo json_encode(array("respon_code"=>"00000",
								   "respon_msg"=>"Data terhapus"));
		} else {
			echo json_encode(array("respon_code"=>"00001",
								   "respon_msg"=>"Gagal menghapus data"));
		}
		die();
		
	}

?>
<div class="boxSquareWhite">
    <h4>Tambahkan Data Pencarian Kursus</h4>
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <form action="#" id="addForm" method="post">
                <div class="form-group">
                    <input type="text" name="judul" id="judul" class="form-control" maxlength="75" placeholder="Judul" autocomplete="off" />
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="harga" id="harga" class="form-control" maxlength="9" placeholder="Fee Jasa Kursus" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="tglbatas" id="tglbatas" class="form-control" maxlength="10" placeholder="Tanggal Batas Penawaran" autocomplete="off" readonly />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    Jenis Paket &nbsp; &nbsp;
                    <input name="jns_paket" value="1" id="jns_paket1" type="radio" onClick="$('#jmlpertemuan').val('1').prop('readonly', true);"/> <small>Privat</small> &nbsp; 
                    <input name="jns_paket" value="2" id="jns_paket2" type="radio" onClick="$('#jmlpertemuan').prop('readonly', false);" checked/> <small>Grup</small>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="jmlpertemuan" id="jmlpertemuan" class="form-control" maxlength="2" placeholder="Jumlah Pertemuan" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <input type="text" name="jmlmurid" id="jmlmurid" class="form-control" maxlength="2" placeholder="Jumlah Maksimum Murid" autocomplete="off" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <select id="idjenjang" name="idjenjang" class="form-control" required onchange="getDataOpt('getDataMapel','jenjang','idmapel',this.value,'- Pilih Mata Pelajaran -')">
                                <option value="">- Pilih Mata Pelajaran -</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <select id="idmapel" name="idmapel" class="form-control" required>
                                <option value="">- Pilih Mata Pelajaran -</option>
                            </select>
                        </div>
	                </div>
                </div>
                <div class="form-group">
					<textarea name="keterangan" required class="form-control" id="keterangan" placeholder="Keterangan" rows="5"></textarea>
				</div>
                <span class="devider"></span>
    
                <div id="button_container">
                    <input type="button" id="submit" name="submit" value="Simpan" class="btn btn-sm btn-custom2" onclick="addData()" />
                </div>
            </form>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><b>Informasi</b></div>
                <div class="panel-body">
                    <p>Isilah data secara lengkap dan jelas agar mendapatkan pengajar yang tepat dan pengajar bisa mengerti yang anda inginkan</p>
                </div>
            </div>
        </div>
    </div>
</div><hr>
<div class="boxSquareWhite">
    <h4>Data Kursus Dicari</h4>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
         <table class="table table-bordered" id="Kjadwal_table">
            <thead>
                <tr>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Harga</th>
                    <th class="text-center">Jenis</th>
                    <th class="text-center">Jumlah<br/>Pertemuan</th>
                    <th class="text-center">Jumlah<br/>Murid</th>
                    <th class="text-center">Tgl Batas</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody id="tableData">
            	<?=$data?>
            </tbody>
          </table>
  		</div>
    </div>
</div>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssdatepicker');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssvalidate');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssajax');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141213'.'jssindex');?>.jsfile"></script>
<script src="<?=APP_URL?>php/lib/file_request.php?get=<?=$enkripsi->encode('20141225'.'jssmpencarian');?>.jsfile"></script>