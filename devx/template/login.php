<form class="form-inline" method="POST" action="APP_URLauth">
    <div class="form-group">
		<label><small>Login sebagai: </small></label>
        <input type="radio" id="login_type" name="login_type" value="1" param_t1 /> <small>Pengajar</small>
        <input type="radio" id="login_type" name="login_type" value="2" param_t2 /> <small>Murid</small>
    </div><br/>
    <div class="form-group">
        <input type="text" class="form-control input-sm" placeholder="Username" name="username" required value="param_username" />
    </div>
    <div class="form-group">
        <input type="password" class="form-control input-sm" placeholder="Password" name="password" required value="param_password"  />
    </div>
	<div class="form-group">
        <button type="submit" class="btn btn-custom2 btn-sm">MASUK</button>
    </div>
    <br/>
    <div class="tombol text-right">
        <a href="forgot_password"><small>Lupa password?</small></a>&nbsp;&nbsp;
		<a href="signup" class="btn btn-custom btn-xs">DAFTAR</a>
    </div>
</form>