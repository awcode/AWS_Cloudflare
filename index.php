<html>
<head>
<title>AWS Cloudflare Security Group Test</title>
</head>
<body>
Test Functions - uncomment to test<br>
<?php

include("aws-cloudflare.class.php");
/*Config options can also be set in separate config file*/
$config = array(
	'access_key_id' => 'YOUR_ACCESS_KEY',
	'secret_access_key' => 'YOUR_SECRET_KEY',
	'region' => 'ap-southeast-1',
	'security_group' => 'sg-123456789'
);

$awsCf = new AwsCf($config);;

$ip_arr = array(array("CidrIp"=>"1.2.3.4/24"));

//$res = $awsCf->open_port($ip_arr, "1234", "tcp");
//$res = $awsCf->close_port($ip_arr, "1234", "tcp");

//$awsCf->close_all_by_port("1234");

$res = $awsCf->cloudflare_firewall("12345");

if($res === true){echo("Success");}else{echo("Failure - ".$res);}

?>

</body>
</html>
