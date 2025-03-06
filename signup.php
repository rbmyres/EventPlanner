<?php
	require "connection.php";
	if ($_SERVER["REQUEST_METHOD"]=="POST")
	{
        // Get user input from the login form
        // ensures that the email and the password are not empty
        if(isset($_POST['email']) && !empty($_POST['email']) && !empty($_POST['password1'])){
            $username = $_POST['email'];  // assigns the user input (in the email section) to the username variable
            $query = "select Email from User where Email = :Email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':Email', $username); //binds parameter (which is to a variable username
            $stmt->execute();
            
            //if the email is already in the system
            if($stmt->fetchColumn() > 0)
            {
                $error=true;
                $message= 'User already in system';
            }
            else{
                // assigns firstname and lastname variable to the first name and last name entered in the sign up form
                $firstname = ucwords($_POST["firstname"]);
                $lastname = ucwords($_POST["lastname"]);
                // first password entered
                $password1 = $_POST["password1"];
                // re-entered password
                $password2 = $_POST['password2'];

                if ($password1 != $password2)
                {
                    // passwords do not match
                    $error=true;
                    $message= "Passwords do not match!";
                }
                else{
                    // no error, no message
                    $error=false;
                    $message;
                    // insert user input into database. 0 is entered for isManager because 0 represents false.
                    $sql = "insert into User (Name, Email, Password, isManager) values (:Name, :Email, :Password, 0)";
                    $stmt = $conn->prepare($sql);

                    // concatenates firstname and lastname field from sign up page
                    $fullname = $firstname . ' ' . $lastname;

                    $stmt->bindParam(':Name', $fullname);
                    $stmt->bindParam(':Email', $username);
                    
                    //hash the password
                    $stmt->bindParam(':Password', password_hash($password1, PASSWORD_DEFAULT));

                    //executes the sql code
                    $stmt->execute();

                    // take user back to login page
                    header('Location: index.php');
                }
            }
        }
        else{
            // empty
            $error=true;
            $message='Input cannot be empty';
        }

    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup</title>
  <link rel="stylesheet" href="css/signup.css">
  <!-- <script defer src="js/login.js"></script> -->
</head>
  <body>
    <div class="wrapper">
      <div class="title"><span>Create Account</span></div>
      <form method = "POST" id="signup-form " action="<?php echo $_SERVER['PHP_SELF'];?>">
        <div class="half-row">
            <i class="fas fa-user"></i>
            <input type="text" id="firstname" name="firstname" placeholder="firstname" required />
        </div>
        <div class="half-row">
            <i class="fas fa-user"></i>
            <input type="text" id="lastname" name="lastname" placeholder="lastname" required />
        </div>
        <div class="row">
            <i class="fas fa-user"></i>
            <input type="text" id="email" name="email" placeholder="enter email" required />
        </div>
        <div class="row">
          <i class="fas fa-lock"></i>
          <input type="password" id="password1" name="password1" placeholder="create password" required />
        </div>
        <div class="row">
          <i class="fas fa-lock"></i>
          <input type="password" id="password2" name="password2" placeholder="re-enter password" required />
          <?php 
            if($error){
             echo "<div>".$message."</div>";
            }
          ?>
        </div>
        <div class="row button">
          <input type="submit" value="Signup"/>
        </div>
      </form>
    </div>
  </body>
</html>
