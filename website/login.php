<?php 
    // First we execute our common code to connection to the database and start the session 
    require "alarmconfig.php"; 

    $submitted_username = ''; 

    if(!empty($_POST)) 
    { 
        $query = "SELECT id, username, password, salt, company
            FROM users 
            WHERE username = :username"; 
         
        // The parameter values 
        $query_params = array( 
            ':username' => $_POST['username'] 
        ); 
         
        try 
        { 
            // Execute the query against the database 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        { 
             die("Failed to run query: " . $ex->getMessage()); 
        } 

        $login_ok = false; 

        $row = $stmt->fetch(); 
        if($row) 
        { 
            $check_password = hash('sha256', $_POST['password'] . $row['salt']); 
            for($round = 0; $round < 65536; $round++) 
            { 
                $check_password = hash('sha256', $check_password . $row['salt']); 
            } 
             
            if($check_password === $row['password']) 
            { 
                // If they do, then we flip this to true 
                $login_ok = true; 
            } 
        } 

        if($login_ok) 
        { 
            unset($row['salt']); 
            unset($row['password']); 

            $_SESSION['user'] = $row; 
         
            //Redirect the user to the private members-only page. 
            header("Location: alarms.php"); 
            die("Redirecting to: alarms.php");

        } 
        else 
        { 
            // Tell the user they failed 
            print("Login Failed."); 

            $submitted_username = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8'); 
        } 
    } 
     
?> 
<head>
<link rel="icon" type="image/png" href="exclamation5.png"/>
<LINK href="style.css" rel="stylesheet" type="text/css">

<title>Simple Alarm Monitor</title>
</head>
<h1>Simple Alarm Monitor Login</h1> 

<form class="center" action="login.php" method="post"> 
    Username:<br /> 
    <input type="text" name="username" value="<?php echo $submitted_username; ?>" /> 
    <br /><br /> 
    Password:<br /> 
    <input type="password" name="password" value="" /> 
    <br /><br /> 
    <input type="submit" value="Login" /> 
</form> 
<div class="center"> <a href="register.php">If you wish to access this application please register</a></div>
<div class="center"> <a href="index.html">Return to <i>Simple</i>Alarm Login</a>

</div>