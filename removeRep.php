<?php 
    require "connection.php";

    if(isset($_POST["repID"])){
        $repID = $_POST["repID"];
        $query = "DELETE FROM Recipients WHERE Recipient_ID = :repID;";
        $stmt = $conn->prepare($query); // Prepares the query
        $stmt->bindParam(':repID', $repID);

        if($stmt->execute()){
            header("Location: profile.php");
            exit;
        }
        else{
            header("Location: profile.php");
            exit;
        }
    }
    

?>