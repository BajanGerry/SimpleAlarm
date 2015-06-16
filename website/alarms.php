<?php
require "alarmconfig.php"; 

    if(empty($_SESSION['user'])) //SESSION Array details eg:( [user] => Array ( [id] => 3 [username] => fred [company] => Kirk) )
    { 
        // If they are not, we redirect them to the login page. 
        header("Location: login.php"); 
         
        // Remember that this die statement is absolutely critical.  Without it, 
        // people can view your members-only content without logging in. 
        die("Redirecting to login.php"); 

    }   
	
$company = $_SESSION['user']['company'];
if (empty ($company)) {$company = "%";}

$pbx = $_GET['pbxname'];

if (isset($pbx)) {
	mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWD) or die(mysql_error());
	mysql_select_db($MYSQL_DB) or die(mysql_error());  
	$data = mysql_query("SELECT * FROM mitel_alarms 
	WHERE name = '$pbx' 
	AND company LIKE '$company'
	ORDER BY date DESC") or die(mysql_error());
}
else{
mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASSWD) or die(mysql_error());
  mysql_select_db($MYSQL_DB) or die(mysql_error());  
  $data = mysql_query("SELECT * FROM mitel_alarms 
WHERE id IN (
SELECT MAX(id) FROM mitel_alarms WHERE company LIKE '$company' GROUP BY name)
GROUP BY name ORDER BY date DESC") or die(mysql_error());

}

?>
<html>

<head>
<meta http-equiv="refresh" content="60;URL=alarms.php">
<link rel="icon" type="image/png" href="exclamation5.png" />
<LINK href="style.css" rel="stylesheet" type="text/css">

<title>Simple Alarm Monitor</title>

</head>
<body>
<h3><? print "Simple Alarm Monitor";?></h3>

<?
if (isset($pbx)) {
?>	

<form class="center" action="alarms.php">
    <input type="submit" value="Return to Current Status">
</form>
<?
}
?>

<table id="alarms">
  <tr> 
    <th>Customer</th> 
    <th>PBX Name</th> 
    <th>PBX IP</th>
    <th>Current State</th>
    <th>Current Alarm</th>
    <th>Time of Alarm</th>
    <th>Time of Last Alarm</th>
  </tr>
<?
while($info = mysql_fetch_array( $data ))  {
	$color = "FF0033";
	if ($info['state'] == OK) { $color = "00FF00";}
	if ($info['state'] == MINOR) { $color = "FFFF00";}
	if ($info['state'] == MAJOR) { $color = "FF9900";}

?>
  <tr bgcolor="<? echo $color ?>">
    <td><?php echo $info['company'];?></td>
    <td><a href='alarms.php?pbxname=<?php echo $info['name'];?>'</a><?php echo $info['name'];?></td>
    <td><?php echo $info['ip'];?></td>
    <td><?php echo $info['state'];?></td>
    <td><?php echo $info['alarm'];?></td>
    <td><?php echo $info['date'];?></td>
    <td><?php echo $info['last'];?></td>
  </tr>
  <?php
  }
?>
</table>
<div align='center'><strong>
<?
date_default_timezone_set('EST');
echo date('l jS \of F Y h:i:s A');
?>
</strong></div>
<a href="logout.php" style="float: right;">Logout </a>
<div class="ads"> 

</div>
</body>


