<?php  
    session_start();

    if(!isset($_SESSION['user'])){
        header("Location: index.php");
        exit();
    }

    require "connection.php";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
    <body>
    <nav class="navbar">
        <ul>
            <li><a href="home.php">Events</a></li>
            <?php
            include 'checkAdminStatus.php';
            if($adminStatus){
                echo '<li><a href = "myEvents.php"> Pending Requests</a></li>
                      <li><a href = "manageUsers.php"> Manage Users</a></li>';
            }
                else{
                    echo '<li><a href = "myEvents.php"> My Events</a></li>
                          <li><a href = "planEvent.php"> Plan Event</a></li>';
                }
            ?>
            <nav class="nav-right">
                <li class="profile"><a href="profile.php">Profile</a></li>
                <li class="logout"><a href="logout.php">Logout</a></li>
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
                    // determines the number of attendees signed up to attend the particular activity
                    if($var['Verified'] == 1){
                    $query = "SELECT COUNT(User_ID) as count FROM Activity_Attendees WHERE Activity_ID = :activityID;";
                    $stmt = $conn->prepare($query);
                    $stmt -> bindParam(":activityID", $var['Activity_ID']);
                    $stmt->execute();
                    $attendeeCount = $stmt->fetch(PDO::FETCH_ASSOC);

                    
                    // determines the number of workers signed up to work for the particular activity
                    $query = "SELECT COUNT(Worker_ID) as count FROM Activity_Workers WHERE Activity_ID = :activityID && Verified = 1;";
                    $stmt = $conn->prepare($query);
                    $stmt -> bindParam(":activityID", $var['Activity_ID']);
                    $stmt->execute();
                    $workerCount = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo '<div class="eventBox">
                            <div class="infoBox">
                                <div class="eventName">'.$var['Activity_Name'].'</div>
                                <div class="managerName">'.$var['Name'].'</div>
                                <div class="location">'.$var['Building_Name']." ".$var["Room_Number"].'</div>
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

                            //check if user is already associated with the event (as in, user is a manager, worker, or attendee of event)
                            // counts the associations.
                            $checkQuery = "SELECT COUNT(*) AS association_count
                                FROM (
                                SELECT 1 FROM Activity WHERE Manager_ID = :userID AND Activity_ID = :activityID
                                UNION ALL
                                SELECT 1 FROM Activity_Workers
                                INNER JOIN Worker ON Worker.Worker_ID = Activity_Workers.Worker_ID
                                WHERE Worker.User_ID = :userID AND Activity_Workers.Activity_ID = :activityID
                                UNION ALL
                                SELECT 1 FROM Activity_Attendees
                                WHERE User_ID = :userID AND Activity_ID = :activityID
                                ) AS associations;";

                                $checkStmt = $conn->prepare($checkQuery);
                                $checkStmt->bindParam(':userID', $_SESSION['user']['User_ID']);
                                $checkStmt->bindParam(':activityID', $var['Activity_ID']);
                                $checkStmt->execute();
    
                                $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
                                    
                                if ($result['association_count'] > 0) {
                                    // User is associated with the event --> display a button that sends user to My Events Page
                                    echo '<form method = "post" action = "myEvents.php">
                                        <button id="editEventDetails" name = "viewEventDetails" class="removeButton">View in My Events</button>';
                                } else if (!$adminStatus) {
                                    // User is not associated --> display signup & request to work button
                                    echo '<form method = "post" action = "requestWork.php">
                                        <input type="hidden" name="activityID" value="'.$var['Activity_ID'].'"><button id="attendEvent" name = "attendEvent" class="removeButton">Attend Event</button>
                                        <button id="workEvent" name = "workEvent" class="removeButton">Request to Work</button>';
                                } else if($adminStatus){
                                    echo '<form id="rejectEventForm" action="removeEvent.php" method="POST"> 
                                            <input type = "hidden" name="userID" value ="'.$userID.'">
                                            <input type = "hidden" name="activityID" value ="'.$var['Activity_ID'].'">
                                            <input type = "hidden" name="managerID" value ="'.$var['Manager_ID'].'">
                                            <button type="submit" class="removeButton" onclick ="confirmReject(event)">Remove Event</button>
                                        </form>';
                                }

                                   
                                echo '</div>
                                     </form>
                                     </div>
                                    </div>';
                            }
    
            endforeach;
        ?>

<script>
        function confirmReject(event) {
            const confirmation = confirm('Are you sure you want to remove this event?');
            if (confirmation) {
                // Proceed with form submission
                return true;
            } else {
                // Prevent form submission
                event.preventDefault();
                return false;
            }
        }

        
        
    </script>
    </body>
</html>