<?php 

    require 'connection.php';

    if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['userID']) && isset($_POST['activityID']) && (isset($_POST['cancelWorker']) || isset($_POST['cancelAttendee'])) ){

        $activityID = $_POST['activityID'];
        $userID = $_POST['userID'];

        // check if user requested to cancel their attendee registration or their worker registration
        if(isset($_POST['cancelWorker']) && isset($_POST['workerID'])){

            // remove user from Activity_Workers table
            $workerID = $_POST['workerID'];

            $query = "DELETE FROM Activity_Workers WHERE Activity_ID = :activityID && Worker_ID = :workerID;";
            $stmt = $conn->prepare($query);
            $stmt -> bindParam(":activityID", $activityID);
            $stmt -> bindParam(":workerID", $workerID);
            $stmt -> execute();
        } 

       // remove user from Activity_Attendees table
       else if(isset($_POST['cancelAttendee'])){
            $query = "DELETE FROM Activity_Attendees WHERE Activity_ID = :activityID && User_ID = :userID;";
            $stmt = $conn->prepare($query);
            $stmt -> bindParam(":activityID", $activityID);
            $stmt -> bindParam(":userID", $userID);
            $stmt -> execute();
        }


        //return to myEvents page
        header("Location: myEvents.php");


    }


?>