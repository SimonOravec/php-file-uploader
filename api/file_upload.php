<?php
define('API_KEY', '#insert_api_key#');
define('DELKEY_SALT', '#insert_salt#');

header("Content-type: application/json");

if ($_POST["apikey"] != API_KEY) {
    http_response_code(401);
    die(json_encode(["success"=>false,"error"=>"Invalid API key."]));
}

if (!isset($_FILES["file"])) {
    http_response_code(400);
    die(json_encode(["success"=>false,"error"=>"No file provided."]));
}

$file_type = strtolower(pathinfo(basename($_FILES["file"]["name"]),PATHINFO_EXTENSION));
$name = bin2hex(openssl_random_pseudo_bytes(4)).".".$file_type;
if ($_FILES["file"]["size"] > 52428800) {
    http_response_code(400);
	die(json_encode(["success"=>false,"error"=>"Filesize exceeded - max. 52428800 bytes"]));
}
if (move_uploaded_file($_FILES["file"]["tmp_name"], "../files/$name")) {
    $delkey = urlencode(openssl_encrypt("files/{$name}", "aes256", DELKEY_SALT));
    die(json_encode(["success"=>true,"filename"=>$name,"deletion_key"=>$delkey]));
} else {
    http_response_code(500);
	die(json_encode(["success"=>false,"error"=>"Unknown error!"]));
}
?>