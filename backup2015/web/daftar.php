<?php include "header.php";?>
		<div class="container">
			<h3 class="text-left text_kursusles page-header">HALAMAN PENDAFTARAN</h3>
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
					<div class="boxSquare segmenDaftar">
						<form class="form-horizontal">
							<div class="form-group">
								<label for="nama_lengkap" class="col-sm-3 control-label">Nama Lengkap <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<input type="text" class="form-control" id="nama_lengkap" placeholder="Nama lengkap sesuai dengan KTP ditambah gelar" required>
								</div>
							</div>
							<div class="form-group">
								<label for="tempat_lahir" class="col-sm-3 control-label">Tempat Lahir <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<input type="text" class="form-control" id="tempat_lahir" placeholder="Tempat lahir sesuai KTP" required>
								</div>
							</div>
							<div class="form-group">
								<label for="tanggal_lahir" class="col-sm-3 control-label">Tanggal Lahir <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<input type="text" class="form-control" id="tgl_lahir" placeholder="Tanggal lahir sesuai KTP" required>
								</div>
							</div>
							<div class="form-group">
								<label for="jenis_lahir" class="col-sm-3 control-label">Jenis Kelamin <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<label class="radio-inline">
										<input type="radio" name="jenis_kelamin" id="laki" value="Laki-laki" required /> Laki-laki
									</label>
									<label class="radio-inline">
										<input type="radio" name="jenis_kelamin" id="perempuan" value="Perempuan" required /> Perempuan
									</label>
								</div>
							</div>
							<div class="form-group">
								<label for="alamat" class="col-sm-3 control-label">Alamat <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<textarea class="form-control" placeholder="Alamat tempat Anda tinggal sekarang" required></textarea>
								</div>
							</div>
							<div class="form-group">
								<label for="provinsi" class="col-sm-3 control-label">Provinsi <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<select class="form-control" required>
										<option value="">== Pilih Provinsi ==</option>
										<option value="Jawa Barat">Jawa Barat</option>
										<option value="Jawa Tengah">Jawa Tengah</option>
										<option value="Jawa Timur">Jawa Timur</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="kota_kabupaten" class="col-sm-3 control-label">Kota / Kabupaten <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<select class="form-control" required>
										<option value="">== Pilih Kota / Kabupaten ==</option>
										<option value="Jawa Barat">Jawa Barat</option>
										<option value="Jawa Tengah">Jawa Tengah</option>
										<option value="Jawa Timur">Jawa Timur</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="kecamatan" class="col-sm-3 control-label">Kecamatan <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<select class="form-control" required>
										<option value="">== Pilih Kecamatan ==</option>
										<option value="Jawa Barat">Jawa Barat</option>
										<option value="Jawa Tengah">Jawa Tengah</option>
										<option value="Jawa Timur">Jawa Timur</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="kelurahan" class="col-sm-3 control-label">Kelurahan <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<select class="form-control" required>
										<option value="">== Pilih Kelurahan ==</option>
										<option value="Jawa Barat">Jawa Barat</option>
										<option value="Jawa Tengah">Jawa Tengah</option>
										<option value="Jawa Timur">Jawa Timur</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="kode_pos" class="col-sm-3 control-label">Kode Pos <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<input type="text" class="form-control" id="kode_pos" placeholder="Kode pos" required>
								</div>
							</div>
							<div class="form-group">
								<label for="telp_hp" class="col-sm-3 control-label">No. Telp / No. HP <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<input type="text" class="form-control" id="telp_hp" placeholder="No. telp atau no. hp yang aktif" required>
								</div>
							</div>
							<h4 class="page-header">Data Akun</h4>
							<div class="form-group">
								<label for="jenis_lahir" class="col-sm-3 control-label">Daftar Sebagai <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<label class="radio-inline">
										<input type="radio" name="jenis_akun" id="murid" value="Murid" required /> Murid
									</label>
									<label class="radio-inline">
										<input type="radio" name="jenis_akun" id="pengajar" value="Pengajar" required /> Pengajar
									</label>
								</div>
							</div>
							<div class="form-group">
								<label for="email" class="col-sm-3 control-label">Email <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<input type="email" class="form-control" id="email" placeholder="Email">
								</div>
							</div>	
							<div class="form-group">
								<label for="username" class="col-sm-3 control-label">Username <small class="text-danger">*</small></label>
								<div class="col-sm-9">
									<input type="text" class="form-control" id="username" placeholder="Username">
								</div>
							</div>
							<div class="form-group">
								<label for="password" class="col-sm-3 control-label">Password</label>
								<div class="col-sm-9">
									<input type="password" class="form-control" id="password" placeholder="Password">
								</div>
							</div>
							<div class="form-group">
								<label for="ulangi_password" class="col-sm-3 control-label">Ulangi Password</label>
								<div class="col-sm-9">
									<input type="password" class="form-control" id="ulangi_password" placeholder="Ulangi Password">
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
									<div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
									<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang;?>"></script>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
									<button type="submit" class="btn btn-custom">DAFTAR</button>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
					<div class="boxSquare">
						<div class="segmenPaket">
							<div class="panel panel-default">
								<div class="panel-heading">Pengajar Terbaru</div>
								<div class="panel-body">
									<div class="media">
										<div class="media-left">
											<a href="#">
												<img class="media-object" src="holder.js/70x70/vine" alt="Andi Siswanto" class="">
											</a>
										</div>
										<div class="media-body">
											<h4 class="media-heading"><a href="#">Andi Siswanto</a></h4>
											Jalan Arcadia Atas Larangan Indah, Ciledug
										</div>
									</div>
									<div class="media">
										<div class="media-left">
											<a href="#">
												<img class="media-object" src="holder.js/70x70/vine" alt="Andi Siswanto" class="">
											</a>
										</div>
										<div class="media-body">
											<h4 class="media-heading"><a href="#">Andi Siswanto</a></h4>
											Jalan Arcadia Atas Larangan Indah, Ciledug
										</div>
									</div>
									<div class="media">
										<div class="media-left">
											<a href="#">
												<img class="media-object" src="holder.js/70x70/vine" alt="Andi Siswanto" class="">
											</a>
										</div>
										<div class="media-body">
											<h4 class="media-heading"><a href="#">Andi Siswanto</a></h4>
											Jalan Arcadia Atas Larangan Indah, Ciledug
										</div>
									</div>
									<div class="media">
										<div class="media-left">
											<a href="#">
												<img class="media-object" src="holder.js/70x70/vine" alt="Andi Siswanto" class="">
											</a>
										</div>
										<div class="media-body">
											<h4 class="media-heading"><a href="#">Andi Siswanto</a></h4>
											Jalan Arcadia Atas Larangan Indah, Ciledug
										</div>
									</div>
									<div class="media">
										<div class="media-left">
											<a href="#">
												<img class="media-object" src="holder.js/70x70/vine" alt="Andi Siswanto" class="">
											</a>
										</div>
										<div class="media-body">
											<h4 class="media-heading"><a href="#">Andi Siswanto</a></h4>
											Jalan Arcadia Atas Larangan Indah, Ciledug
										</div>
									</div>
									<a href="#" class="btn btn-custom btn-xs">Lihat semua pengajar</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php include "footer.php";?>