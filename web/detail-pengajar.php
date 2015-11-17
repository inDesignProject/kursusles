<?php include "header.php";?>
		<div class="container">
			<h3 class="text-left text_kursusles page-header">DETAIL PENGAJAR</h3>
			<div class="row">
				<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
					<div class="boxSquare">
						<div class="row">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-left">
								<a onClick="history.back();" class="btn btn-kursusles btn-sm">< Kembali ke halaman sebelumnya</a>
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
								<a href="#" class="btn btn-kursusles btn-sm">Cara memilih pengajar</a>
							</div>
						</div><br/>
						<div class="boxSquareWhite">
							<div class="row">
								<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
									<img src="holder.js/180x180/gray" class="img-responsive img-circle img-profile" />
								</div>
								<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
									<span class="tutor_name">Ahmad Habir Samsudin</span>
									<div class="row">
										<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
											<img src="dist/images/icon/verified.png" class="img-responsive img-verified" alt="verified member"/>
										</div>
										<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
											<div class="rating text_gold">
												<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-o"></i>
											</div>
										</div>
									</div><hr>
									<p class="about_text">
										Ini kutipan profil si pengajar (profil lengkap yang di tab profil), atau visi dan misi si pengajar.
									</p>
									<div class="info">
										<ul class="list-inline">
											<li class="icon"><img src="dist/images/icon/street.png" alt="street address" class="img-responsive"/></li>
											<li class="address"><small>Jalan Arcadia Atas Larangan Indah, Ciledug</small></li>
										</ul>
										<ul class="list-inline">
											<li class="icon"><img src="dist/images/icon/materi.png" alt="materi" class="img-responsive"/></li>
											<li class="materi"><small>Bahasa Indonesia, Bahasa Inggris, TOEFL, Matematika, Komputer</small></li>
										</ul>
										<ul class="list-inline">
											<li class="icon"><img src="dist/images/icon/level.png" alt="tingkat pendidikan" class="img-responsive"/></li>
											<li class="level"><small>SD, SMP, SMA, UMUM</small></li>
										</ul>
									</div>
									<button class="btn btn-custom btn-xs pull-right"><i class="fa fa-envelope"></i> Kirim Pesan Pribadi</button>
								</div>
							</div>
						</div>
						<div class="report text-right"><a href="#" class="btn btn-danger btn-xs"><small><i class="fa fa-exclamation-circle"></i> LAPORKAN PENGAJAR</small></a></div>
						<div role="tabpanel">
							<ul class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active"><a href="#pesan-pengunjung" aria-controls="pesan-pengunjung" role="tab" data-toggle="tab">Pesan Pengunjung</a></li>
								<li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Profil</a></li>
								<li role="presentation"><a href="#lokasi" aria-controls="lokasi" role="tab" data-toggle="tab">Jangkauan</a></li>
								<li role="presentation"><a href="#testimonial" aria-controls="testimonial" role="tab" data-toggle="tab">Testimonial/Review</a></li>
								<li role="presentation"><a href="#jadwal" aria-controls="jadwal" role="tab" data-toggle="tab">Jadwal</a></li>
							</ul>
							<div class="tab-content">
								<div role="tabpanel" class="tab-pane active" id="pesan-pengunjung">
									<form id="formPesan" method="POST" action="#">
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<div class="form-group">
													<input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required />
												</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<div class="form-group">
													<input type="email" name="email" class="form-control" placeholder="Alamat email" required />
												</div>
											</div>
										</div>
										<div class="form-group">
											<textarea name="pesan" required class="form-control" placeholder="Tulis pesan disini" rows="5"></textarea>
										</div>
										<div class="form-group">
											<div class="g-recaptcha" data-sitekey="<?php echo $siteKey;?>"></div>
											<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang;?>"></script>
										</div>
										<input type="submit" class="btn btn-custom2 pull-right" value="Kirim pesan ini" />
									</form>
									<button id="pesan" class="btn btn-sm btn-custom2">Buka form pesan</button>
									<div class="disclaimer">
										Penerima pesan akan menerima notifikasi secara instan lewat email dan sms mengenai pesan anda. Admin juga akan secara manual memonitor pesan anda.
										Lindungi privasi anda. Tidak diperkenankan untuk saling memberikan nomor kontak, email maupun jenis data koneksi lainnya diluar sepengetahuan kursusles.com. Melanggar ketentuan ini akan menyebabkan <strong>ban permanen</strong> dari kami.
										Yuk lindungi privasi dan reputasi kita dengan lebih baik :)
									</div>
									<hr>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="text-center">
										<button class="btn btn-custom btn-xs">load more on scroll</button>
									</div>
								</div>
								<div role="tabpanel" class="tab-pane" id="profile">
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur in turpis enim. Sed blandit lacinia condimentum. Mauris imperdiet bibendum tortor sed posuere. Pellentesque suscipit sit amet dolor sit amet egestas. Aenean finibus maximus mauris, a sollicitudin ipsum egestas eget. Mauris vitae felis leo. Vestibulum magna tortor, dictum non arcu eu, aliquet posuere ex.</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur in turpis enim. Sed blandit lacinia condimentum. Mauris imperdiet bibendum tortor sed posuere. Pellentesque suscipit sit amet dolor sit amet egestas. Aenean finibus maximus mauris, a sollicitudin ipsum egestas eget. Mauris vitae felis leo. Vestibulum magna tortor, dictum non arcu eu, aliquet posuere ex.</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur in turpis enim. Sed blandit lacinia condimentum. Mauris imperdiet bibendum tortor sed posuere. Pellentesque suscipit sit amet dolor sit amet egestas. Aenean finibus maximus mauris, a sollicitudin ipsum egestas eget. Mauris vitae felis leo. Vestibulum magna tortor, dictum non arcu eu, aliquet posuere ex.</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur in turpis enim. Sed blandit lacinia condimentum. Mauris imperdiet bibendum tortor sed posuere. Pellentesque suscipit sit amet dolor sit amet egestas. Aenean finibus maximus mauris, a sollicitudin ipsum egestas eget. Mauris vitae felis leo. Vestibulum magna tortor, dictum non arcu eu, aliquet posuere ex.</p>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur in turpis enim. Sed blandit lacinia condimentum. Mauris imperdiet bibendum tortor sed posuere. Pellentesque suscipit sit amet dolor sit amet egestas. Aenean finibus maximus mauris, a sollicitudin ipsum egestas eget. Mauris vitae felis leo. Vestibulum magna tortor, dictum non arcu eu, aliquet posuere ex.</p>
								</div>
								<div role="tabpanel" class="tab-pane" id="lokasi">
									yang ini di isi yang peta mas
								</div>
								<div role="tabpanel" class="tab-pane" id="testimonial">
									<div class="alert alert-success alert-dismissible" role="alert">
										<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<strong><small>Anda harus login sebagai murid untuk memberi testimonial/review kepada pengajar ini.</small></strong>
									</div>
									<form id="formTesti" method="POST" action="#">
										<div class="form-group">
											<textarea name="pesan" required class="form-control" placeholder="Tulis testimonial disini" rows="5"></textarea>
										</div>
										<div class="form-group">
											<label>Rating: </label>
											<div id="rating">
												<input type="radio" name="star" class="star-1" id="star-1" value="1"/><label class="star-1" for="star-1">1</label>
												<input type="radio" name="star" class="star-2" id="star-2" value="2"/><label class="star-2" for="star-2">2</label>
												<input type="radio" name="star" class="star-3" id="star-3" value="3"/><label class="star-3" for="star-3">3</label>
												<input type="radio" name="star" class="star-4" id="star-4" value="4"/><label class="star-4" for="star-4">4</label>
												<input type="radio" name="star" class="star-5" id="star-5" value="5"/><label class="star-5" for="star-5">5</label>
												<span></span>
											</div>
										</div>
										<input type="submit" class="btn btn-custom2 pull-right" value="Kirim Testimonial/Review" />
									</form>
									<button id="testi" class="btn btn-sm btn-custom2">Buka form testimonial</button>
									<hr>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong> <small>| User rating: <span class="text-gold"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-o"></i></span></small></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong> <small>| User rating: <span class="text-gold"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i></span></small></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong> <small>| User rating: <span class="text-gold"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i></span></small></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong> <small>| User rating: <span class="text-gold"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i></span></small></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="listPesan">
										<div class="media">
											<div class="media-body">
												<h5 class="media-heading"><strong>Andi Siswanto <small>- <i class="fa fa-envelope"></i> andiisis14@gmail.com</small></strong> <small>| User rating: <span class="text-gold"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></span></small></h5>
												<p>
													<small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</small>
												</p>
												<small><i class="fa fa-calendar"></i> 17-02-2015 &nbsp;&nbsp;<i class="fa fa-clock-o"></i> 00:03:24</small>
											</div>
										</div>
									</div>
									<div class="text-center">
										<button class="btn btn-custom btn-xs">load more on scroll</button>
									</div>
								</div>
								<div role="tabpanel" class="tab-pane" id="jadwal">
									<strong>Ketersediaan Jadwal</strong>
									<div class="table-responsive">
										<table class="table table-bordered">
											<thead>
												<tr>
													<th class="text-center">Waktu/Hari</th>
													<th class="text-center">Senin</th>
													<th class="text-center">Selasa</th>
													<th class="text-center">Rabu</th>
													<th class="text-center">Kamis</th>
													<th class="text-center">Jumat</th>
													<th class="text-center">Sabtu</th>
													<th class="text-center">Minggu</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<th>Pagi</th>
													<td class="text-center"><i class="fa fa-check-circle fa-lg"></i></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
												</tr>
												<tr>
													<th>Siang</th>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"><i class="fa fa-check-circle fa-lg"></i></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"><i class="fa fa-check-circle fa-lg"></i></td>
													<td class="text-center"></td>
												</tr>
												<tr>
													<th>Sore</th>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"><i class="fa fa-check-circle fa-lg"></i></td>
													<td class="text-center"></td>
												</tr>
												<tr>
													<th>Malem</th>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
													<td class="text-center"></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
					<div class="boxSquare">
						<div class="segmenPaket">
							<div class="panel panel-default">
								<div class="panel-heading">Daftar Paket Belajar</div>
								<div class="panel-body">
									<form role="form" method="POST" action="#">
										<div class="form-group">
											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<input type="radio" name="paket_belajar" id="paket1" required /> Paket 1<br/>
													<small>Biaya: Rp 500.000</small><br/>
													<small>Level: SD, SMP, SMA</small><br/>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<small>
														<ul>
															<li>8x pertemuan</li>
															<li>Maks 8 anak</li>
															<li>Materi: Bahasa Inggris, TOEFL</li>
														</ul>
													</small>
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<input type="radio" name="paket_belajar" id="paket1" required /> Paket 2<br/>
													<small>Biaya: Rp 400.000</small><br/>
													<small>Level: SD, SMP, SMA</small><br/>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<small>
														<ul>
															<li>5x pertemuan</li>
															<li>Maks 10 anak</li>
															<li>Materi: Bahasa Inggris, TOEFL</li>
														</ul>
													</small>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="segmenKeahlian">
							<div class="panel panel-default">
								<div class="panel-heading">Keahlian Pengajar</div>
								<div class="panel-body">
									<div role="tabpanel">
										<ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active"><a href="#pre-school" aria-controls="pre-school" role="tab" data-toggle="tab"><span class="icon-preschool"><img src="dist/images/icon/preschool.png" alt="preschool" class="img-responsive"/></span></a></li>
                                            <li role="presentation"><a href="#sd" aria-controls="sd" role="tab" data-toggle="tab"><span class="icon-sd"><img src="dist/images/icon/sd.png" alt="sd" class="img-responsive"/></span></a></li>
                                            <li role="presentation"><a href="#smp" aria-controls="smp" role="tab" data-toggle="tab"><span class="icon-smp"><img src="dist/images/icon/smp.png" alt="smp" class="img-responsive"/></span></a></li>
                                            <li role="presentation"><a href="#sma" aria-controls="sma" role="tab" data-toggle="tab"><span class="icon-sma"><img src="dist/images/icon/sma.png" alt="sma" class="img-responsive"/></span></a></li>
                                            <li role="presentation"><a href="#umum" aria-controls="umum" role="tab" data-toggle="tab"><span class="icon-umum"><img src="dist/images/icon/umum.png" alt="umum" class="img-responsive"/></span></a></li>
                                        </ul>
										<div class="tab-content">
											<div role="tabpanel" class="tab-pane active" id="pre-school">
												<small>Tidak memiliki keahlian pada level ini.</small>
											</div>
											<div role="tabpanel" class="tab-pane" id="sd">
												<ul class="list-unstyled">
													<li><i class="fa fa-check"></i> Bahasa Indonesia</li>
													<li><i class="fa fa-check"></i> Bahasa Inggris</li>
													<li><i class="fa fa-check"></i> TOEFL</li>
												</ul>
											</div>
											<div role="tabpanel" class="tab-pane" id="smp">
												<ul class="list-unstyled">
													<li><i class="fa fa-check"></i> Matematika</li>
													<li><i class="fa fa-check"></i> Bahasa Inggris</li>
													<li><i class="fa fa-check"></i> TOEFL</li>
												</ul>
											</div>
											<div role="tabpanel" class="tab-pane" id="sma">
												<ul class="list-unstyled">
													<li><i class="fa fa-check"></i> Komputer</li>
													<li><i class="fa fa-check"></i> Bahasa Inggris</li>
													<li><i class="fa fa-check"></i> TOEFL</li>
												</ul>
											</div>
											<div role="tabpanel" class="tab-pane" id="umum">
												<ul class="list-unstyled">
													<li><i class="fa fa-check"></i> Bahasa Inggris</li>
													<li><i class="fa fa-check"></i> TOEFL</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php include "footer.php";?>