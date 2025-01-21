<?php 
    require 'connection.php';

    if ($_SERVER["REQUEST_METHOD"]=="POST"){
        try{
            $userID = $_POST["userID"]; // retrieves the user's user ID
            $role = $_POST["role"]; // retrieves the role chosen from the dropdown menu

            $query; // used in the if/else statement below
            
            if($role == "Event Manager"){
                // executes query that changes the user's "isManager" boolean to true (grant permissions)
                $query = "UPDATE User SET isManager = 1 WHERE User_ID = $userID";
            }

            else{
                // otherwise, execute query that changes the user's "isManager" boolean to false (revoke permissions)
                $query = "UPDATE User SET isManager = 0 WHERE User_ID = $userID";
            }

            $result = $conn->prepare($query);
            if($result -> execute()){
                // redirect to the managerUsers.php page
                header("Location: manageUsers.php");
            }
            else{
                echo "Error";
            }
        }
        catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }









?>

