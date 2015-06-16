<?php
require "../../lib/alarmconfig.php"; 

    if(empty($_SESSION['user'])) //SESSION Array details eg:( [user] => Array ( [id] => 3 [username] => fred [company] => Kirk) )
    { 
        // If they are not, we redirect them to the login page. 
        header("Location: http://simplealarm.strongware.ky/login.php"); 
         
        // Remember that this die statement is absolutely critical.  Without it, 
        // people can view your members-only content without logging in. 
        die("Redirecting to http://simplealarm.strongware.ky/login.php"); 

    }
$username = $_SESSION['user']['username'];
$company = $_SESSION['user']['company'];

if (isset($username)) {
	mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWD) or die(mysql_error());
	mysql_select_db($MYSQL_DB) or die(mysql_error());  
	$data = mysql_query("SELECT * FROM users
	WHERE username = '$username' 
	AND company LIKE '$company'"
	) or die(mysql_error());
	}
?>	
<html>

<head>
<link rel="icon" type="image/png" href="exclamation5.png" />
<LINK href="style.css" rel="stylesheet" type="text/css">

<title>SimpleAlarm - Free Mitel PBX alarm monitoring</title>

</head>
<table id="user">
  <tr> 
    <th>Name</th> 
    <th>Email Address</th>
    <th>Company</th>
  </tr>
<?
while($info = mysql_fetch_array( $data ))  {

?>
  <tr bgcolor="<? echo $color ?>">
    <td><?php echo $info['username'];?></td>
    <td><?php echo $info['email'];?></td>
    <td><?php echo $info['company'];?></td>

  </tr>
  <?php
  }

?>
</table>