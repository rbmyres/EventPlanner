<?php
    session_start(); // starts the session to access user information
    require 'connection.php'; // provides connection details

    $adminStatus; // holds true or false depending on whether the user's ID is in the Admin table or not

    // checks if the user has admin status (which is determined by checking if their user ID is in the Admin table)
                // if the user does have admin status, then the adminStatus variable is set to true.
    if(isset($_SESSION['user'])){
        $userID = $_SESSION['user']['User_ID']; // Access the User_ID key from the array
        $sql = "SELECT * FROM Admin WHERE User_ID = :userID";
        $stmt = $conn->prepare($sql); // prepares the sql code
        $stmt->bindParam(':userID', $userID); // binds ":userID" to the userID variable
        $stmt->execute(); // executes the sql code

        // if there is an user ID to fetch the the admin table, adminStatus = true. Otherwise, it is false.
        if($stmt->fetch()){ 
            $adminStatus = true;
        }
        else{
            $adminStatus = false;
        }
    }
                
?>