<?php
    session_start(); // starts the session to access user information
    // This file will facilitate editing user information such as the user's name from the Profile page
    require "connection.php";

	if ($_SERVER["REQUEST_METHOD"]=="POST"){
        if(isset($_SESSION['user']['User_ID']) && $_POST['editNameInput'] && !empty($_POST['editNameInput'])){
            $userID = $_SESSION['user']['User_ID'];
            $newName = $_POST['editNameInput'];  // assigns the user input (in the update name section) to the newName variable

            $sql = "UPDATE User Set Name = :new WHERE User_ID = :userID";
            $stmt = $conn->prepare($sql); // prepares the sql code
            $stmt->bindParam(':userID', $userID); // binds ":userID" to the userID variable
            $stmt->bindParam(':new', $newName); // binds ":new" to the newName variable
    
            if($stmt->execute()){
                $_SESSION['user']['Name'] = $newName;
                header('Location: profile.php');
            }
        }
        else {
            header('Location: profile.php');
        }
    }

?>