<?php
define('DELKEY_SALT', '#insert_salt#');

session_name("session_id");
session_start();

if (isset($_GET["delfile"])) {
	$key = $_GET["delkey"];
	if (empty($key)) {
		$_SESSION["message_type"] = "alert-danger";
		$_SESSION["message"] = "<h5>Invalid deleteion key or the file is already deleted!</h5>";
		header("Location: ./");
		die;
	}

	if (base64_encode(base64_decode($key, true)) != $key){
		$_SESSION["message_type"] = "alert-danger";
		$_SESSION["message"] = "<h5>Invalid deleteion key or the file is already deleted!</h5>";
		header("Location: ./");
		die;
	}

	$subor = openssl_decrypt($key, "aes256", DELKEY_SALT);
	if (!file_exists($subor)) {
		$_SESSION["message_type"] = "alert-danger";
		$_SESSION["message"] = "<h5>Invalid deleteion key or the file is already deleted!</h5>";
		header("Location: ./");
		die;
	}

	unlink($subor);
	$_SESSION["message_type"] = "alert-info";
	$_SESSION["message"] = "<h5>File <b><i>".str_replace("files/", "", $subor)."</i></b> was uploaded successfully!</h5>";
	header("Location: ./");
	die;
}

if (isset($_POST["upload"])) {
$file_type = strtolower(pathinfo(basename($_FILES["file"]["name"]),PATHINFO_EXTENSION));
$name = "files/".bin2hex(openssl_random_pseudo_bytes(4)).".".$file_type;

$tries = 0;
while (file_exists($name)) {
	if ($tries > 10) {
		$_SESSION["message_type"] = "alert-danger";
		$_SESSION["message"] = "Failed to upload file! Try later.";
		die;
	}

	$name = "files/".bin2hex(openssl_random_pseudo_bytes(4)).".".$file_type;
	$tries++;
}

if ($_FILES["file"]["size"] > 104857600) {
	$_SESSION["message_type"] = "alert-danger";
	$_SESSION["message"] = "Maximum filesize is 100MB!";
	die;
}
if (move_uploaded_file($_FILES["file"]["tmp_name"], $name)) {
	$delkey = urlencode(openssl_encrypt($name, "aes256", DELKEY_SALT));
	$_SESSION["message_type"] = "alert-success";
	$_SESSION["message"] = "<h4><b>File was uploaded successfully!</b></h4><b style='font-size:16px;'>Links:</b><table style='font-size:14px;'><tr><th style='color:#3c763d;'>File: </th><td><input style='width:350px;' onClick='this.select();' value='https://{$_SERVER['HTTP_HOST']}/$name' readonly></td></tr><tr style='height:5px;'></tr><tr><th style='color:#3c763d;'>File deletion:&nbsp;&nbsp;</th><td><input style='width:350px;' onClick='this.select();' value='https://{$_SERVER['HTTP_HOST']}/?delfile&delkey=$delkey' readonly></td></tr></table>";
	die;
} else {
	$_SESSION["message_type"] = "alert-danger";
	$_SESSION["message"] = "<h5>Failed to upload file! Try later.</h5>";
	die;
}
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Uploader</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<style>
	h5 {
		font-size: 16px;
		padding: 0;
		margin: 0;
	}
	body {
		height: 100%;
    	width: 100%;
	}
	div.background {
		margin: 0;
		padding: 0;
		width: 100%;
		height: 100%;
		background: rgb(73, 181, 230);
		background: linear-gradient(210deg, rgb(21, 113, 154) 30%, rgb(5, 46, 108) 84%);
	}
	.alert-sm {
		padding: 5px;
		width: 60%;
		font-size: 11px;
	}
	</style>
</head>
<body>
<div class="background">
<div class="container" style="width:600px;padding-top:100px;">
<div class="panel panel-default">
	<div class="panel-heading"><b>File Uploader</b></div>
	<div class="panel-body">
		<form class="form form-horizontal" role="form" id="form" method="post" enctype="multipart/form-data">
			<div class="container-fluid">
				<input hidden name="upload" value="1">
                <input type="file" name="file" id="file" required><br>
				<button type="success" id="btn_upload" class="btn btn-success form-control">Upload file</button>
			</div>
			<center><small>*Maximum filesize 100MB</small></center>
			<div id="msg">
			<?php
			if (isset($_SESSION["message"])) { ?>
				<br><div class="alert <?php echo $_SESSION["message_type"]; ?>"><?php echo $_SESSION["message"]; ?></div>
			<?php 
			session_unset();
			} ?>
			</div>
        </form>
		<div id="upload_progress" style="text-align:center;display:none;">
		<b>Súbor sa nahráva...</b>
		<div class="progress">
			<div id="progress_bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:40%"><center><span id="percent">0%</span></center></div>
		</div>
		</div>
	</div>
</div>
</div>
</div>
</body>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>
<script>
$('#file').bind('change', function() {
	if (this.files[0].size > 104857600) {
		$("#file").val("");
		alert("Maximum filesize is 100MB!");
	}
});

$('#form').ajaxForm({
	beforeSend: function() {
		$('#msg').css('display', 'none');
		$('#upload_progress').css('display', 'block');

		$('#file').prop('disabled', true);
		$('#btn_upload').prop('disabled', true);
	},
	uploadProgress: function(event, position, total, percentComplete) {
		var percentVal = percentComplete + '%';
		$('#progress_bar').attr('aria-valuenow', percentComplete).css('width', percentVal);
		$('#percent').html(percentVal);
	},
	complete: function(xhr) {
		setTimeout(function() {
			location.reload();
		}, 500);
	}
});
</script>
</html>