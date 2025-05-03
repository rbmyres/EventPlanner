<?php
	require 'connection.php';
 	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
        	// Get user input from the login form
			// creates username and password variables that hold data from HTML form submitted (from login page)
        	$email = $_POST["email"];
        	$password = $_POST["password"];
			
		try{
        		// Prepare SQL query to fetch user from database
        		$stmt = $conn->prepare("SELECT * FROM User WHERE Email = :Email");
        		$stmt->bindParam(':Email', $email);
        		$stmt->execute();
        		$user = $stmt->fetch(PDO::FETCH_ASSOC);

        		if ($user && password_verify($password, $user['Password'])) {
					session_start();
					$message="";
					$error=false;
					$_SESSION['user']=$user;
					//$_SESSION['User_ID']=$user['User_ID']; //<- THIS IS WHAT IT USED TO BE, NOW user IS AN ARRAY OF THE User TABLE
						//success page
						header("Location: home.php");
        		} else {
            			// Password is incorrect, display error message
                        $error=true;
            			$message= "Incorrect email or password";
        		}
		}
		catch(PDOException $e) {
    			echo "Connection failed: " . $e->getMessage();
		}
	}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="css/login.css">
  <script defer src="js/login.js"></script>
</head>
  <body>
	<div class="title">Welcome to the Ole Miss Events Website</div>
    <div class="wrapper">
      <div class="title"><span>Login Form</span></div>
      <form method = "POST" id="login-form" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <div class="row">
          <i class="fas fa-user"></i>
          <input type="text" id="email" name="email" placeholder="email" required />
        </div>
        <div class="row">
          <i class="fas fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="password" required />
		  
		<!-- if the $error variable is false, then display the error message stored in the $message variable -->
		<?php 
			if($error){ 
				echo "<div>".$message."</div>"; 
			} 
		?>
        </div>
        <div class="row button">
          <input type="submit" value="Login"/>
        </div>
        <div class="signup-link">
			<a href="signup.php"> Create Account</a>
		</div>
      </form>
    </div>
  </body>
</html>