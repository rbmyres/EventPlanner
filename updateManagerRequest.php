<?php 
    require_once("connection.php");
    if(isset($_POST['choice']) && isset($_POST['requesterID']) && isset($_POST['notiID']) && isset($_POST['adminID'])){

        $choice = $_POST['choice'];
        $reqID = $_POST['requesterID'];
        $notiID = $_POST['notiID'];
        $adminID = $_POST['adminID'];

        if($choice === "approved"){
            // find user and change isManager to true

            $query = "UPDATE User SET isManager = true WHERE User_ID = :userID;";
            $stmt = $conn->prepare($query);
            $stmt -> bindParam(":userID", $reqID);
            $stmt -> execute();

            // send messaage saying accepted

            // variables to create notification
            $message = "Your request to become an an event manager has been approved.";

            $sql = "INSERT INTO Notification (Message, User_ID) VALUES (:message, :userID);";
            $stmt = $conn->prepare($sql);
            $stmt -> bindParam(":message", $message);
            $stmt -> bindParam(":userID", $adminID);
            $stmt -> execute();


            $notificationID = $conn->lastInsertID();
            $sql = "INSERT INTO Recipients(Notification_ID, User_ID) VALUES (:notificationID, :reqID);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":notificationID", $notificationID, PDO::PARAM_INT);
            $stmt->bindParam(":reqID", $reqID, PDO::PARAM_INT);
            $stmt->execute();
        }

        else if ($choice === "denied"){
            // send message saying rejected
            $message = "Your request to become an an event manager has been denied. Please reach out to our Admins if you have any questions.";

            $sql = "INSERT INTO Notification (Message, User_ID) VALUES (:message, :adminID);";
            $stmt = $conn->prepare($sql);
            $stmt -> bindParam(":message", $message);
            $stmt -> bindParam(":adminID", $adminID);
            $stmt -> execute();

            $notificationID = $conn->lastInsertID();
            $sql = "INSERT INTO Recipients(Notification_ID, User_ID) VALUES (:notificationID, :reqID);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":notificationID", $notificationID, PDO::PARAM_INT);
            $stmt->bindParam(":reqID", $reqID, PDO::PARAM_INT);
            $stmt->execute();

        }

        // delete the request
        $sql = "DELETE FROM Notification WHERE Notification_ID = :notiID;";
        $stmt = $conn->prepare($sql);
        $stmt -> bindParam(":notiID", $notiID);
        $stmt -> execute();
        
        header("Location: myEvents.php");
        exit;
    }


?>