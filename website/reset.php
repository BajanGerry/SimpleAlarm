<?php
session_start();
$token=$_GET['token'];
include("../../lib/alarmconfig.php");
$con = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWD) or die(mysql_error());
mysql_select_db($MYSQL_DB) or die(mysql_error()); 
if(!isset($_POST['password'])){
	$q="select email from tokens where token='".$token."' and used=0";
	$r=mysql_query($q);while($row=mysql_fetch_array($r))   {
		$email=$row['email'];
		}
If ($email!=''){
	$_SESSION['email']=$email;
	}
else die("Invalid link or Password already changed");
}
$pass=$_POST['password'];
$email=$_SESSION['email'];
if(!isset($pass)){
	echo '<form method="post" align="center">Please enter your new password:<input type="password" name="password" /><input type="submit" value="Change Password"></form>';
	}
if(isset($_POST['password'])&&isset($_SESSION['email'])){
	$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647)); 
	$password = hash('sha256', $pass . $salt); 
	for($round = 0; $round < 65536; $round++)         
	{$password = hash('sha256', $password . $salt);}
$q="update users set password='".$password."', salt ='".$salt."' where email='".$email."'";
$r=mysql_query($q);
if($r)mysql_query("update tokens set used=1 where token='".$token."'");
echo "Your password is changed successfully\n";
if(!$r)echo "An error occurred";
}
?>
<head>
<link rel="icon" type="image/png" href="exclamation5.png" />
<LINK href="style.css" rel="stylesheet" type="text/css">
<title>SimpleAlarm - Free Mitel PBX alarm monitoring registration</title></head><div class="center"> 
<a href="http://simplealarm.strongware.ky/login.php">Go to Login page if already registered</a></div>
<div class="center"> <a href="http://simplealarm.strongware.ky/index.html">Return to <i>Simple</i>Alarm Login</a></div>