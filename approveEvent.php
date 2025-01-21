<?php 

    require "connection.php";

    if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['userID']) && isset($_POST['activityID']) && isset($_POST['managerID']) ){
        // initialize variables
        $userID = $_POST['userID'];
        $activityID = $_POST['activityID'];
        $managerID = $_POST['managerID'];


        try{

            // update the activity to be verified
            $sql = "UPDATE Activity SET Verified = 1 WHERE Activity_ID = :activityID;";
            $stmt = $conn->prepare($sql);
            $stmt -> bindParam(":activityID", $activityID);
            $stmt -> execute();

            // retrieve event name, date, and time. 
            $sql = "SELECT Activity_Name, DATE_FORMAT(Date, '%m/%d/%Y') as Formatted_Date, DATE_FORMAT(Start_Time, '%h:%i %p') AS Formatted_Time1, 
            DATE_FORMAT(End_Time, '%h:%i %p') AS Formatted_Time2 FROM Activity WHERE Activity_ID = :activityID;";
            $check = $conn->prepare($sql);
            $check -> bindParam(":activityID", $activityID);
            $check -> execute();

            $activityInfo = $check->fetch(PDO::FETCH_ASSOC);
            
            $message = 'Your Event "'.$activityInfo['Activity_Name'].'" on '.$activityInfo['Formatted_Date'].' from '.$activityInfo['Formatted_Time1'].' to '.$activityInfo['Formatted_Time2'].' has been approved.';
            $sql = "INSERT INTO Notification(Activity_ID, Message, User_ID) VALUES 
            (:activityID, :message, :userID);";
                

                $noti = $conn->prepare($sql);
                $noti -> bindParam(":activityID", $activityID);
                $noti -> bindParam(":message", $message);
                $noti -> bindParam(":userID", $userID);
                $noti -> execute();
    
                $notificationID = $conn->lastInsertID();

                $sql = "INSERT INTO Recipients(Notification_ID, User_ID) VALUES (:notificationID, :managerID);";
                $noti = $conn->prepare($sql);
                $noti -> bindParam(":notificationID", $notificationID);
                $noti -> bindParam(":managerID", $managerID);
                $noti -> execute();

        }
        catch (Exception $e) {
            echo 'Error! Cannot Execute SQL code: ' . $e->getMessage();
        }
        
        header("Location: myEvents.php");
        
    }






?>