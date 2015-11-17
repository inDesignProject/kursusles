<?php include "header.php";?>
		<div class="container">
			<div class="jobsSearch">
				<h3>Pencarian Lowongan Kerja</h3>
				<form class="form-inline form-searchjobs" method="POST" action="">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-search"></i></div>
							<input type="text" class="form-control" placeholder="Jabatan / nama perusahaan">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-map-marker"></i></div>
							<input type="text" class="form-control" placeholder="Semua lokasi pekerjaan">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-briefcase"></i></div>
							<input type="text" class="form-control" placeholder="Semua fungsi pekerjaan">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><i class="fa fa-graduation-cap"></i></div>
							<input type="text" class="form-control" placeholder="Semua pendidikan">
						</div>
					</div>
					<input type="submit" value="Cari" class="btn btn-default" />
				</form>
			</div>
		</div>
		<div class="coklatslogan">
			<div class="container">
				<h3 class=" text-left">DAFTAR PEKERJAAN</h3>
			</div>
		</div><br/><br/>
		<div class="container">
			<div class="row">
				<div class="col-sm-9">
					<div role="tabpanel">
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#fungsi" aria-controls="fungsi" role="tab" data-toggle="tab">FUNGSI</a></li>
							<li role="presentation"><a href="#industri" aria-controls="industri" role="tab" data-toggle="tab">INDUSTRI</a></li>
							<li role="presentation"><a href="#lokasi" aria-controls="lokasi" role="tab" data-toggle="tab">LOKASI</a></li>
						</ul>
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="fungsi">
								<div class="row">
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">ADMIN &amp; HRD <small><em>(100)</em></small></a>
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">AKUNTING <small><em>(10)</em></small></a>
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">ASURANSI <small><em>(20)</em></small></a>
										</div>
									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="industri">
								<div class="row">
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">AKUNTING / AUDIT / LAYANAN PAJAK <small><em>(100)</em></small></a>
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">AMAL / LAYANAN SOSIAL / ORGANISASI NIRLABA <small><em>(10)</em></small></a>
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">ARSITEKTUR / PEMBANGUNAN / KONSTRUKSI <small><em>(50)</em></small></a>
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">ARSITEKTUR / PEMBANGUNAN / KONSTRUKSI <small><em>(50)</em></small></a>
										</div>
									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="lokasi">
								<div class="row">
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">BALI <small><em>(100)</em></small></a>
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">BANTEN <small><em>(100)</em></small></a>
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
										<div class="tab-mapel">
											<a href="#">BENGKULU <small><em>(100)</em></small></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<h4>Lowongan Kerja Terbaru</h4>
					<div class="list-group">
						<a href="#" class="list-group-item">Cras justo odio</a>
						<a href="#" class="list-group-item">Dapibus ac facilisis in</a>
						<a href="#" class="list-group-item">Morbi leo risus</a>
						<a href="#" class="list-group-item">Porta ac consectetur ac</a>
						<a href="#" class="list-group-item">Vestibulum at eros</a>
					</div>
				</div>
			</div>
		</div>
		<div class="infoslogan text-center">
			<div class="container">
				<div class="row">
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3>2000<br/>PENCARI KERJA</h3></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3>2790<br/>LOWONGAN KERJA</h3></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><h3>4080<br/>PEMBERI KERJA</h4></div>
				</div>
			</div>
		</div>
<?php include "footer.php";?>