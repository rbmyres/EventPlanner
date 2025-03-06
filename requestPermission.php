<?php 
    // Include the database connection
    require_once("connection.php");

    session_start();

    if(isset($_SESSION['user'])){


        // variables to create notification
        $userID = $_SESSION['user']['User_ID'];
        $name = $_SESSION['user']['Name'];
        $message = "User ".$name." (User ID = ".$userID.") has requested to become an event manager.";


        // first, check if user has requested to become a manager before. If so, do not create a new notificiation. 

        $sql = "SELECT COUNT(*) AS count FROM Notification WHERE User_ID = :userID && Message = :message;";
        $stmt = $conn->prepare($sql);
        $stmt -> bindParam(":message", $message);
        $stmt -> bindParam(":userID", $userID);
        $stmt->execute();
        $overlap = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!($overlap['count'] > 0)) {
            // create notification

            $sql = "INSERT INTO Notification (Message, User_ID) VALUES (:message, :userID);";
            $stmt = $conn->prepare($sql);
            $stmt -> bindParam(":message", $message);
            $stmt -> bindParam(":userID", $userID);
            $stmt -> execute();
        }

        header("Location: myEvents.php");
        exit;

    }
    else{
        header("Location: index.php");
        exit();
    }




?>