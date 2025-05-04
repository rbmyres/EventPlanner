<?php  
    session_start();

    if(!isset($_SESSION['user'])){
        header("Location: index.php");
        exit();
    }

    require "connection.php";
    require "checkAdminStatus.php";
    $user = $_SESSION['user'];


    $stmt = $conn->prepare("SELECT Activity.Activity_ID, Activity.Activity_Name, Activity.Description,  
       DATE_FORMAT(Activity.Date, '%m/%d/%Y') AS Formatted_Date, 
       DATE_FORMAT(Activity.Start_Time, '%h:%i %p') AS Formatted_Time1, 
       DATE_FORMAT(Activity.End_Time, '%h:%i %p') AS Formatted_Time2, 
       Activity.Worker_Limit, Activity.Manager_ID, Activity.Verified, User.Name,
       Building.Building_Name, Room.Room_Number, Room.Capacity
        FROM Activity 
        INNER JOIN Building ON Activity.Building_ID = Building.Building_ID
        INNER JOIN Room ON Room.Room_ID = Activity.Room_ID
        INNER JOIN User ON Activity.Manager_ID = User.User_ID  WHERE Activity.Denied = 0
        ORDER BY Activity.Date ASC, Activity.Start_Time ASC");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT * FROM Notification WHERE Message LIKE '%has requested to become an event manager.';";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $managerRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events</title>
    <link rel="stylesheet" href="css/myEvents.css">
    <script defer src="js/myEvents.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
    <body>
    <nav class="navbar">
            <ul>
                <li><a href = "home.php">Events</a></li>
                
                <?php
                    //if adminStatus is true, which is a variable from the checkAdminStatus.php file, then the user can view the Manager Users link
                    include 'checkAdminStatus.php';
                    if($adminStatus){
                        echo '<li class="requests"><a href = "myEvents.php">Pending Requests
                        </a></li>
                        <li><a href="manageUsers.php">Manage Users</a></li>';
                    }
                    else{
                        echo '<li><a href = "myEvents.php">My Events</a></li>
                        <li><a href = "planEvent.php">Plan Event</a></li>';
                    }
                ?>
             <nav class="nav-right">
                <li class="profile"><a href="profile.php">Profile
                </a></li>
                <li class="logout"><a href = "logout.php">Logout</a></li>
            </nav>
            </ul>
        </nav>

        <div class="filters">
            <div class="searchContainer">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchBar" class="searchBar" placeholder="Search events">
            </div>
            <div class="selectContainer">
                <select>
                    <option value="">Sort</option>
                    <option value="attendeeOption">Available to Attend</option>
                    <option value="workerOption">Available to Work</option>
                    <option value="bothOption">Available for Both</option>
                </select>
                <i class="arrow-icon fas fa-chevron-down"></i>
            </div>
        </div>
        
       <?php
            foreach($activities as $var):

                    $activityID = $var['Activity_ID'];
                    $userID = $_SESSION['user']['User_ID'];
                    $managerID = $var['Manager_ID'];

                    // determines the number of attendees signed up to attend the particular activity
                    $query = "SELECT COUNT(User_ID) as count FROM Activity_Attendees WHERE Activity_ID = :activityID;";
                    $stmt = $conn->prepare($query);
                    $stmt -> bindParam(":activityID", $activityID);
                    $stmt->execute();
                    $attendeeCount = $stmt->fetch(PDO::FETCH_ASSOC);

                    
                    // determines the number of workers signed up to work for the particular activity
                    $query = "SELECT COUNT(Worker_ID) as count FROM Activity_Workers WHERE Activity_ID = :activityID && Verified = 1;";
                    $stmt = $conn->prepare($query);
                    $stmt -> bindParam(":activityID", $activityID);
                    $stmt->execute();
                    $workerCount = $stmt->fetch(PDO::FETCH_ASSOC);

                    // determine if the user is a worker, attendee, or event worker for the event

                    $isWorker = false;
                    $isWorkerRequested = false;
                    $isAttendee = false;
                    $isManager = false;
                    $position = ""; // holds whether the user is a worker, attendee, or event worker
                    $workerID;
                    
                    // determine whether the user is an attendee of the event
                    $query = "SELECT Activity_ID FROM Activity_Attendees WHERE User_ID = :userID AND Activity_ID = :activityID;";
                    $attendeeStmt = $conn->prepare($query);
                    $attendeeStmt->bindParam(":userID", $userID);
                    $attendeeStmt->bindParam(":activityID", $activityID);
                    $attendeeStmt->execute();

                    // determine whether the user is a worker of the event
                    $query = "SELECT Worker.User_ID FROM Activity_Workers INNER JOIN Worker ON Activity_Workers.Worker_ID = Worker.Worker_ID WHERE Activity_ID = :activityID && Worker.User_ID = :userID;";
                    $workerStmt = $conn->prepare($query);
                    $workerStmt->bindParam(":activityID", $activityID);
                    $workerStmt->bindParam(":userID", $userID);
                    $workerStmt->execute();

                    // determine whether the user is a requested worker of the event
                    $query = "SELECT Worker.User_ID FROM Activity_Workers INNER JOIN Worker ON Activity_Workers.Worker_ID = Worker.Worker_ID WHERE Activity_ID = :activityID && Worker.User_ID = :userID;";
                    $workerRequestedStmt = $conn->prepare($query);
                    $workerRequestedStmt->bindParam(":activityID", $activityID);
                    $workerRequestedStmt->bindParam(":userID", $userID);
                    $workerRequestedStmt->execute();

                    // Holds the fetch results
                    $workerRequestedResult = $workerRequestedStmt->fetch(PDO::FETCH_ASSOC);
                    $workerResult = $workerStmt->fetch(PDO::FETCH_ASSOC);
                    $attendeeResult = $attendeeStmt->fetch(PDO::FETCH_ASSOC);

                    if ($workerResult || $isWorkerRequested) {
                        if($workerResult){
                            $isWorker = true;
                        } 

                        if($isWorkerRequested){
                            $isWorker = true;
                        }

                        // Retrieve Worker_ID
                        $query = "SELECT Worker_ID FROM Worker WHERE User_ID = :userID";
                        $query = $conn->prepare($query);
                        $query -> bindParam(":userID", $userID);
                        $query -> execute();

                        $workerID = $query->fetch(PDO::FETCH_ASSOC)['Worker_ID'];

                    } else if ($attendeeResult) {
                        $isAttendee = true;
                    }

                    else if($var['Manager_ID'] == $userID){
                        $isManager = true;
                    }

                    if($isWorker){
                        // retrieve worker ID and assign it to user
                        $query = "SELECT Worker_ID FROM Worker WHERE User_ID = :userID";
                        $stmt = $conn->prepare($query);
                        $stmt -> bindParam(":activityID", $activityID);
                        $stmt -> bindParam(":userID", $userID);
                    }

                    if($isWorker || $isWorkerRequested || $isAttendee || $isManager || ($adminStatus && $var['Verified'] == 0)){

                        echo '<div class="eventBox">
                                <div class="infoBox">
                                    <div class="eventName">'.$var['Activity_Name'].'</div>
                                    <div class="managerName">'.$var['Name'].'</div>';

                        if($isAttendee){
                            echo '<div class="position">Attending</div>';
                        }
                       else if($isWorkerRequested){
                            echo '<div class="position">Requested to Work</div>';
                        } 
                        else if($isWorker){
                            echo '<div class="position">Working</div>';
                        }
                        
                        else if($isManager){
                            echo '<div class="position">Managing</div>';
                        }
                        echo       '<div class="location">'.$var['Building_Name']." ".$var["Room_Number"].'</div>
                                    <div class="date">'.$var['Formatted_Date'].'</div>
                                    <div class="time">'.$var['Formatted_Time1']." - ".$var['Formatted_Time2'].'</div>
                                    <div class="collapsible"></div>
                                    <span class="attendees">Guests: </span>
                                    <span class="workers">Workers: </span>
                                    <div class="attendeeCapacity">
                                        <span class="numAttendees">'.$attendeeCount['count'].'</span>
                                        /
                                        <span class="totalAttendees">'.$var['Capacity'].'</span>
                                    </div>
                                    <div class="workerCapacity">
                                        <span class="numWorkers">'.$workerCount['count'].'</span>
                                        /
                                        <span class="totalWorkers">'.$var['Worker_Limit'].'</span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <hr>
                                    <div class="description">
                                        <p>'.$var['Description'].'</p>
                                </div>

                                
                                <div class="buttons">';

                                if($isAttendee || $isWorker){
                                    echo '<form action = "updateRegistration.php" method = "POST">
                                        <input type = "hidden" name="userID" value ="'.$userID.'">
                                        <input type = "hidden" name="activityID" value ="'.$activityID.'">';
                                    
                                    if($isAttendee){
                                       echo '<input type = "hidden" name="cancelAttendee" value ="cancelAttendee">
                                            <button type = "submit" name ="cancelAttendeeButton" class="removeButton" onclick = "return confirmCancel()">Cancel Attendee Registration</button>';
                                    }

                                    else if($isWorker ){
                                        echo '<input type = "hidden" name="cancelWorker" value ="cancelWorker">
                                            <input type = "hidden" name="workerID" value ="'.$workerID.'">
                                            <button type = "submit" name ="cancelWorkerButton" class="removeButton" onclick = "return confirmCancel()">Cancel Worker Registration</button>';
                                    }

                                    echo '</form>';
                                }

                                if($isManager){
                                    echo '<form action = "manageEvent.php" method = "POST">
                                        <input type = "hidden" name="userID" value ="'.$userID.'">
                                        <input type = "hidden" name="activityID" value ="'.$activityID.'">
                                        <button type = "submit" name ="editEventButton" class="removeButton">Edit Event</button>
                                        </form>
                                        
                                        <form action = "" method = "POST">
                                        <input type = "hidden" name="userID" value ="'.$userID.'">
                                        <input type = "hidden" name="activityID" value ="'.$activityID.'">
                                        <button type = "submit" name ="deleteEventButton" class="removeButton">Delete Event</button>
                                        </form>';
                                }

                                if($adminStatus){
                                    echo '<form action = "approveEvent.php" method = "POST">
                                            <input type = "hidden" name="userID" value ="'.$userID.'">
                                            <input type = "hidden" name="activityID" value ="'.$activityID.'">
                                            <input type = "hidden" name="managerID" value ="'.$managerID.'">
                                            <button type = "submit" name ="approveEvent" class="removeButton">Approve Event</button>
                                        </form>
                                    <form id="rejectEventForm" action="rejectEvent.php" method="POST"> 
                                        <input type = "hidden" name="userID" value ="'.$userID.'">
                                            <input type = "hidden" name="activityID" value ="'.$activityID.'">
                                            <input type = "hidden" name="managerID" value ="'.$managerID.'">
                                        <button type="submit" class="removeButton" onclick="confirmReject(event)">Reject Event</button>
                                    </form>';
                                    
                                }

                                echo '</div></div></div>';
                    }
                    
            endforeach;

            
            if($adminStatus){
                foreach($managerRequests as $request):

                     $requesterID = $request['User_ID'];
                     $requestID = $request['Recipient_ID'];
                     $notiID = $request['Notification_ID'];

                    // find requester's Name

                   $query = "SELECT Name FROM User WHERE User_ID = :userID;";
                    $stmt = $conn->prepare($query);
                    $stmt -> bindParam(":userID", $requesterID);
                    $stmt -> execute();

                    $requesterName = $stmt->fetch(PDO::FETCH_ASSOC);

                    echo '<div class="eventBox">
                            <div class="infoBox">
                                <div class="eventName">Event Manager Request</div>
                                <div class="managerName">'.$requesterName['Name'].' (User ID: '.$requesterID.')</div>
                            </div>
                            <div class="buttons">
                                <div class="manager-requests">
                                    <form action = "updateManagerRequest.php" method = "POST">
                                        <input type = "hidden" name="requesterID" value ="'.$requesterID.'">
                                        <input type = "hidden" name="choice" value="approved">
                                        <input type = "hidden" name="adminID" value ="'.$_SESSION['user']['User_ID'].'">
                                        <input type = "hidden" name="notiID" value ="'.$notiID.'">
                                        <button type = "submit" name ="approveRequest" class="removeButton" onclick="confirmAccept(event)">Approve Request</button>
                                    </form>
                                    <form action="updateManagerRequest.php" method="POST"> 
                                        <input type = "hidden" name="requesterID" value ="'.$requesterID.'">
                                        <input type = "hidden" name="choice" value="denied">
                                        <input type = "hidden" name="adminID" value ="'.$_SESSION['user']['User_ID'].'">
                                        <input type = "hidden" name="notiID" value ="'.$notiID.'">
                                        <button type="submit" class="removeButton" onclick="confirmReject(event)">Reject Request</button>
                                    </form>
                                </div>
                            </div>
                        </div>';
                endforeach;
            }


        ?>

    <script>
        function confirmCancel() {
            const confirmation = confirm('Are you sure you want to cancel your registration?');
            if (confirmation) {
                // Proceed with form submission
                return true;
            } else {
                // Prevent form submission
                event.preventDefault();
                return false;
            }
        }
        
        function confirmReject(event) {
            // Prevent default form submission
            const confirmation = confirm('Are you sure you want to reject this request?');
            if (confirmation) {
                // Proceed with form submission
                return true;
            } else {
                // Prevent form submission
                event.preventDefault();
                return false;
            }
        }

        function confirmAccept(event) {
            // Prevent default form submission
            const confirmation = confirm('Are you sure you want to accept this request?');
            if (confirmation) {
                // Proceed with form submission
                return true;
            } else {
                // Prevent form submission
                event.preventDefault();
                return false;
            }
        }

        
        
        document.addEventListening("DOMContentLoaded", function() {
            fetch("showNotif.php")
                .then(response => response.json())
                .then(number => {
                    if (number.Notif_Count) {
                        document.querySelector(".request-counter").textContent = number.Notif_Count;
                    }else{
                        console.error("Error fetching notifications: ", number.error);
                    }
                })
                .catch(err => console.error("Fetch error: ", err));
        });
        
        

    </script>


        
    </body>
</html>