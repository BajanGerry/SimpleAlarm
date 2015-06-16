<?php 

 require "alarmconfig.php"; 

    if(!empty($_POST))     { 
        if(empty($_POST['username'])) 
        { die("Please enter a username."); 
        } 
         
        // Ensure that the user has entered a non-empty password 
        if(empty($_POST['password'])) 
        { die("Please enter a password."); 
        } 
         if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
        { die("Invalid E-Mail Address"); 
        } 
         
        $query = "SELECT 1 FROM users WHERE username = :username"; 
         $query_params = array( 
            ':username' => $_POST['username'] 
        ); 
        
        try { 
            // These two statements run the query against your database table. 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        {  
            die("Failed to run query: " . $ex->getMessage()); 
        } 

        $row = $stmt->fetch(); 

        if($row) 
        { die("This username is already in use"); 
        } 

        $query = " SELECT 1 FROM users WHERE email = :email"; 
         
        $query_params = array(':email' => $_POST['email']); 
         
        try 
        { 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        { die("Failed to run query: " . $ex->getMessage()); 
        } 
         
        $row = $stmt->fetch(); 
         
        if($row) 
        { die("This email address is already registered"); 
        } 
         
        $query = "INSERT INTO users (username, password, salt, email, company)
			VALUES (:username, :password, :salt, :email, :company)"; 
         
        $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647)); 
        $password = hash('sha256', $_POST['password'] . $salt); 
          
        for($round = 0; $round < 65536; $round++) 
        {$password = hash('sha256', $password . $salt); 
        } 

        $query_params = array(':username' => $_POST['username'], ':password' => $password, ':salt' => $salt, ':email' => $_POST['email'], ':company' => $_POST['company']); 
         
        try 
        { 
            // Execute the query to create the user 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        {  
            die("Failed to run query: " . $ex->getMessage()); 
        } 
          //Email information
  $admin_email = "simplealarm@strongware.ky";
  $email = $_POST['email'];
  $subject = "New SimpleAlarm registration";
  $comment = "The has been a new registration for ". $_POST['username'] ." of company ". $_POST['company'];
  
  //send email
  mail($admin_email, "$subject", $comment, "From:" . $email); 
        // This redirects the user back to the login page after they register 
        header("Location: login.php"); 
 
        die("Redirecting to login.php"); 
    } 
     
?> 
<head>
<link rel="icon" type="image/png" href="exclamation5.png" />
<LINK href="style.css" rel="stylesheet" type="text/css">
<title>SimpleAlarm - Free Mitel PBX alarm monitoring registration</title>
</head>

<h1><i>Simple</i>Alarm Registration</h1> 
<form class="center" action="register.php" method="post"> 
    Username:<br /> 
    <input type="text" name="username" value="" /> 
    <br /><br /> 
	Company:<br /> 
    <input type="text" name="company" value="" /> 
    <br /><br />
    E-Mail:<br /> 
    <input type="text" name="email" value="" /> 
    <br /><br /> 
    Password:<br /> 
    <input type="password" name="password" value="" /> 
    <br /><br /> 
    <input type="submit" value="Register" /> 
</form>

<div class="center"> <a href="login.php">Go to Login page if already registered</a>
<div class="center"> <a href="index.html">Return to <i>Simple</i>Alarm Login</a>


</div>