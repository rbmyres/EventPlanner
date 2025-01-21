<?php 

    session_start();
    require "connection.php";
    
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activityID'])){

        //retrieve activity id and user id
        $activityID = $_POST['activityID'];
        $userID = $_SESSION['user']['User_ID'];

        if(isset($_POST['attendEvent'])){
    
            // add user to Activity_Attendees table
            $query = "INSERT INTO Activity_Attendees(Activity_ID, User_ID) VALUES(:activityID, :userID)";
            
            // prepare and execute the query
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':activityID', $activityID);
            $stmt->bindParam(':userID', $userID);
            $stmt->execute();
            
        }
        else if(isset($_POST['workEvent'])){
            // check first if user is in the worker table. if not, add them in. Otherwise, use their worker ID to insert into Activity_Workers

            $query = "SELECT Worker_ID FROM Worker WHERE User_ID = :userID";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':userID', $userID);
            $stmt->execute();

            if($stmt->fetch() < 1){
                // user not in worker table. add them
                $addUser = "INSERT INTO Worker(User_ID) VALUES(:userID);";
                $add = $conn->prepare($addUser);
                $add->bindParam(':userID', $userID);
                $add->execute();
            }

            $queryWorker = "SELECT Worker_ID FROM Worker WHERE User_ID = :userID;";
            $stmtWorker = $conn->prepare($queryWorker);
            $stmtWorker -> bindParam(':userID', $userID);
            $stmtWorker -> execute();

            $workerID = $stmtWorker->fetch(PDO::FETCH_ASSOC)['Worker_ID'];

            // add user to Activity_Workers table
            $query2 = "INSERT INTO Activity_Workers(Activity_ID, Worker_ID, Signup_Date, Verified) VALUES(:activityID, :workerID, NOW(), 0);";
        
            // prepare and execute the query
            $stmt2 = $conn->prepare($query2);
            $stmt2->bindParam(':activityID', $activityID);
            $stmt2->bindParam(':workerID', $workerID);
            $stmt2->execute();
        }

       // take user back to login page
       header('Location: home.php');

    }



?>