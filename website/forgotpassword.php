<html>
<head>
<link rel="icon" type="image/png" href="exclamation5.png" />
<LINK href="style.css" rel="stylesheet" type="text/css">
<title>SimpleAlarm - Free Mitel PBX alarm monitoring</title>
</head>
<body>
<?php
if(!isset($_GET['email'])){	
                  echo'<form action="forgotpassword.php" align="center">
				  Enter Your Email Address:	                         
				  <input type="text" name="email" />	                        
				  <input type="submit" value="Reset My Password" />	                         
				  </form>'; 
//				  exit();?>


<div class="center"> <a href="http://simplealarm.strongware.ky/login.php">Return to Login page</a></div>
<div class="center"> <a href="http://simplealarm.strongware.ky/index.html">Return to <i>Simple</i>Alarm</a></div>			
</html>
<?
exit();
				  }
	  
$email=$_GET['email'];
include("../../lib/alarmconfig.php");
$con = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWD) or die(mysql_error());
mysql_select_db($MYSQL_DB) or die(mysql_error());
  $q="select email from users where email='$email'";
  $r=mysql_query($q) or die(mysql_error());
  $n=mysql_num_rows($r);
if($n==0){echo "address is not registered"; die();}$token=getRandomString(10);
$q="insert into tokens (token,email) values ('".$token."','".$email."')";

mysql_query($q);
function getRandomString($length) 	   {
    $validCharacters = "ABCDEFGHIJKLMNPQRSTUXYVWZ123456789";
    $validCharNumber = strlen($validCharacters);
    $result = "";
    for ($i = 0; $i < $length; $i++) {
        $index = mt_rand(0, $validCharNumber - 1);
        $result .= $validCharacters[$index];    
		}	
	return $result;
	}
 function mailresetlink($to,$token){
	 $subject = "Forgot Password on SimpleAlarm";
	 $uri = 'http://'. $_SERVER['HTTP_HOST'] ;
	 $message = '
	 <html>
	 <head><title>Forgot Password For SimpleAlarm</title></head>
	 <body>
	 <p>Click on the given link to reset your password <a href="'.$uri.'/reset.php?token='.$token.'">Reset Password</a></p>
	 </body>
	 </html>';
	 $headers = "MIME-Version: 1.0" . "\r\n";
	 $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	 $headers .= 'From: SimpleAlarm<simplealarm@strongware.ky>' . "\r\n";
	 $headers .= 'Cc: bajangerry@gmail.com' . "\r\n";
	 
	if(mail($to,$subject,$message,$headers)){
		echo "We have sent the password reset link to your  email id <b>".$to."</b>"; 
		}
}
if(isset($_GET['email']))mailresetlink($email,$token);
?>

<body>

</body>
</html>