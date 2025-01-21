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
            echo "activitiy: ". $activityID."<br>";
            echo "user: ".$userID."<br>";
            echo "manager: ".$managerID."<br>";
            exit();
        }

        try {

             // gets every worker's information
        $query = "SELECT User.Name, User.Email, Activity_Workers.Verified FROM User INNER JOIN Worker ON User.User_ID = Worker.User_ID INNER JOIN 
        Activity_Workers ON  Worker.Worker_ID = Activity_Workers.Worker_ID WHERE Activity_Workers.Activity_ID = :activityID;";

        $stmt = $conn->prepare($query);
        $stmt -> bindParam(":activityID", $activityID);
        $stmt -> execute();
        $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // gets every attendee's information
        $query = "SELECT User.Name, User.Email
                    FROM  User
                    INNER JOIN Activity_Attendees 
                    ON User.User_ID = Activity_Attendees.User_ID
                    WHERE Activity_Attendees.Activity_ID = :activityID;";
            
            $stmt = $conn->prepare($query);
            $stmt -> bindParam(":activityID", $activityID);
            $stmt -> execute();
            $attendees = $stmt -> fetchAll(PDO::FETCH_ASSOC);


            // retrieve event name, date, and time. 
            $sql = "SELECT Activity_Name, DATE_FORMAT(Date, '%m/%d/%Y') as Formatted_Date, DATE_FORMAT(Start_Time, '%h:%i %p') AS Formatted_Time1, 
            DATE_FORMAT(End_Time, '%h:%i %p') AS Formatted_Time2 FROM Activity WHERE Activity_ID = :activityID;";
            $check = $conn->prepare($sql);
            $check -> bindParam(":activityID", $activityID);
            $check -> execute();

            $activityInfo = $check->fetch(PDO::FETCH_ASSOC);
            
            $rejectMessage = 'Your event "'.$activityInfo['Activity_Name'].'" on '.$activityInfo['Formatted_Date'].' from '.$activityInfo['Formatted_Time1'].' to '.$activityInfo['Formatted_Time2'].' has been removed. Please reach out to our Admins if you have any questions.';

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


            // create message for all workers and attendees.

            $rejectMessage2 = 'The Event "'.$activityInfo['Activity_Name'].'" on '.$activityInfo['Formatted_Date'].' from '.$activityInfo['Formatted_Time1'].' to '.$activityInfo['Formatted_Time2'].' has been removed. We apologize for the inconvience. Please reach out if you have any questions.';
        
            // Insert rejection notification
            $sql = "INSERT INTO Notification(Message, User_ID) 
                    VALUES (:rejectMessage, :userID);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":rejectMessage", $rejectMessage2, PDO::PARAM_STR);
            $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);
            $stmt->execute();
            $notificationID = $conn->lastInsertID();

            foreach ($workers as $var):
                $notificationID = $conn->lastInsertID();
                $sql = "INSERT INTO Recipients(Notification_ID, User_ID) VALUES (:notificationID, :userID);";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":notificationID", $notificationID, PDO::PARAM_INT);
                $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);
                $stmt->execute();

            endforeach;

            foreach ($attendees as $var):
                $notificationID = $conn->lastInsertID();
                $sql = "INSERT INTO Recipients(Notification_ID, User_ID) VALUES (:notificationID, :userID);";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":notificationID", $notificationID, PDO::PARAM_INT);
                $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);
                $stmt->execute();
            endforeach;

            // Delete the activity
            $sql = "UPDATE Activity SET Denied = 1 WHERE Activity_ID = :activityID;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":activityID", $activityID, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header("Location: home.php");
                exit();
            } else {
                echo "Error deleting the event.";
            }
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
        }
    }



?>