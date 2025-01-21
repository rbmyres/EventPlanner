<?php
require 'connection.php';


// rejected from event requests
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['userID']) && isset($_POST['activityID']) && isset($_POST['managerID']) ){
    // initialize variables
    $userID = $_POST['userID'];
    $activityID = $_POST['activityID'];
    $managerID = $_POST['managerID'];
    
    // Debugging: Print POST data
    if (!$activityID || !$userID || !$managerID) {
        echo "Error: Missing required data.";
        exit();
    }

    try {
        // retrieve event name, date, and time. 
        $sql = "SELECT Activity_Name, DATE_FORMAT(Date, '%m/%d/%Y') as Formatted_Date, DATE_FORMAT(Start_Time, '%h:%i %p') AS Formatted_Time1, 
        DATE_FORMAT(End_Time, '%h:%i %p') AS Formatted_Time2 FROM Activity WHERE Activity_ID = :activityID;";
        $check = $conn->prepare($sql);
        $check -> bindParam(":activityID", $activityID);
        $check -> execute();

        $activityInfo = $check->fetch(PDO::FETCH_ASSOC);
        
        $rejectMessage = 'Your Event Request for your event "'.$activityInfo['Activity_Name'].'" on '.$activityInfo['Formatted_Date'].' from '.$activityInfo['Formatted_Time1'].' to '.$activityInfo['Formatted_Time2'].' has been rejected.';

        // Insert rejection notification
        $sql = "INSERT INTO Notification(Activity_ID, Message, User_ID) 
                VALUES (:activityID, :rejectMessage, :userID);";
        $stmt = $conn->prepare($sql);
        $stmt -> bindParam(":activityID", $activityID);
        $stmt->bindParam(":rejectMessage", $rejectMessage, PDO::PARAM_STR);
        $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);
        $stmt->execute();

        // Insert into Recipients
        $notificationID = $conn->lastInsertID();
        $sql = "INSERT INTO Recipients(Notification_ID, User_ID) VALUES (:notificationID, :managerID);";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":notificationID", $notificationID, PDO::PARAM_INT);
        $stmt->bindParam(":managerID", $managerID, PDO::PARAM_INT);
        $stmt->execute();

        // Delete the activity
        $sql = "UPDATE Activity SET Denied = 1 WHERE Activity_ID = :activityID;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":activityID", $activityID, PDO::PARAM_INT);


        if ($stmt->execute()) {
            header("Location: myEvents.php");
            exit();
        } else {
            echo "Error deleting the event.";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}
?>
