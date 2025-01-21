<?php 
    require 'connection.php';
    if ($_SERVER["REQUEST_METHOD"]=="POST"){
        try{
            $userID = $_POST["userID"]; 
            echo $userID;
            $query = "DELETE from User WHERE User_ID = $userID";

            
            // prepares the query
            $result = $conn->prepare($query);

            // if the query executes, then return to the manageUsers page. Otherwise, output an error. 
            if($result -> execute()){
                // redirect to the managerUsers.php page
                header("Location: manageUsers.php");
            }
            else{
                echo "Error: query did not execute successfully.";
            }
        }
        catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }



?>

