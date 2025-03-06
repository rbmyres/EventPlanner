<?php  
    session_start();
    require "connection.php";

    if(!isset($_SESSION['user'])){
        header("Location: index.php");
        exit();
    }

    // Initialize variables
    $buildings = [];
    $rooms = [];
    $message = "";


    $activityID;
    $userID;
    $activity;

    
    if(isset($_POST['activityID']) && $_POST['userID']){
        $activityID = $_POST['activityID'];
        $userID = $_POST['userID'];

        $sql = "SELECT * FROM Activity WHERE Activity_ID = :activityID;";
        $stmt = $conn->prepare($sql);
        $stmt -> bindParam(":activityID", $activityID);
        $stmt -> execute();

        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // checks for the activityID and userID that is passed from updateEvents.php
    else if(isset($_GET['activityID']) && isset($_GET['userID'])){
        $activityID = $_GET['activityID'];
        $userID = $_GET['userID'];

        $sql = "SELECT * FROM Activity WHERE Activity_ID = :activityID;";
        $stmt = $conn->prepare($sql);
        $stmt -> bindParam(":activityID", $activityID);
        $stmt -> execute();

        $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    }
    else{
        //output an error and redirect user to my events page
        header("Location: myEvents.php");
    }

    // Fetch buildings
    try {
        $buildingsQuery = "SELECT Building_ID, Building_Name FROM Building";
        $stmt = $conn->prepare($buildingsQuery);
        $stmt->execute();
        $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching buildings: " . $e->getMessage();
    }

    // Fetch rooms
    try {
        $roomsQuery = "SELECT Room_ID, Room_Number FROM Room";
        $stmt = $conn->prepare($roomsQuery);
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching rooms: " . $e->getMessage();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event</title>
    <link rel="stylesheet" href="css/manageEvent.css">
    <script defer src="js/manageEvent.js"></script>
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
                        echo '<li><a href = "myEvents.php">Pending Requests</a></li>
                        <li><a href="manageUsers.php">Manage Users</a></li>';
                    }
                    else{
                        echo '<li><a href = "myEvents.php">My Events</a></li>
                        <li><a href = "planEvent.php">Plan Event</a></li>';
                    }
                ?>
                <nav class="nav-right">
                    <li class="profile"><a href = "profile.php">Profile</a></li>
                    <li class="logout"><a href = "logout.php">Logout</a></li>
                </nav>
            </ul>
        </nav>

        <div class="manageBox">

            <div class="mini-navbar">
                <ul>
                    <li id="editEvent">Edit Event</li>
                    <li id="viewAttendees">View Attendees</li>
                    <li id="viewWorkers">View Workers</li>
                    <li id="sendMessage">Send Message</li>
                </ul>
            </div>

            <div id="boxContent" class="boxContent">

                <div id="editContent">
                    <h2>Edit Event</h2>
                    <form method="POST" action="updateEvent.php">
                        <!-- holds activity ID -->
                        <input type = "hidden" name="activityID" value = <?php echo $activityID; ?>>
                        <!-- Event Name -->
                        <div class="form-group">
                            <label for="eventName">Event Name</label>
                            <input type="text" id="eventName" name="eventName" value = "<?php echo $activity['Activity_Name'];?>" required>
                        </div>

                        <!-- Event Date -->
                        <div class="form-group">
                            <label for="eventDate">Event Date</label>
                            <input type="date" id="eventDate" name="eventDate" value = <?php echo $activity['Date'];?> required>
                        </div>

                        <!-- Event Start and End Time -->
                        <div class="form-group">
                        <label for="eventTime">Event Start Time</label>
                        <input type="time" id="startTime" name="startTime" value = <?php echo $activity['Start_Time'];?> required>
                        </div>

                        <div class="form-group">
                        <label for="eventTime">Event End Time</label>
                        <input type="time" id="endTime" name="endTime" value = <?php echo $activity['End_Time'];?> required>
                        </div>


                        <!-- Building Dropdown -->
                        <div class="form-group">
                            <div class="selectContainer">
                                <label for="building">Building</label>
                                <select id="building" name="building" required onchange="fetchRooms(this.value)">
                                    <?php foreach ($buildings as $building): ?>
                                        <option value="<?= htmlspecialchars($building['Building_ID']) ?>" 
                                            <?php if ($building['Building_ID'] == $activity['Building_ID']) echo 'selected'; ?>>
                                            <?= htmlspecialchars($building['Building_Name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="dropdown-icon fas fa-chevron-down"></i>
                            </div>
                        </div>

                        <!-- Room Dropdown -->
                        <div class="form-group">
                            <div class="selectContainer">
                                <label for="room">Room</label>
                                <select id="room" name="room" required>
                                    <!-- Initial room options will be populated dynamically based on the building selection -->
                                    <!-- Empty placeholder initially -->
                                </select>
                                <i class="dropdown-icon fas fa-chevron-down"></i>
                            </div>
                        </div>

                        <!-- Worker Limit Dropdown -->
                        <div class="form-group">
                            <div class="selectContainer">
                                <label for="workerLimit">Number of Workers</label>
                                <select id="workerLimit" name="workerLimit" required>
                                    <option value="">Select Worker Limit</option>
                                    <?php for ($i = 1; $i <= 20; $i++): ?>
                                        <option value="<?= $i ?>" <?php if ($i == $activity['Worker_Limit']) echo 'selected'; ?>>
                                            <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <i class="dropdown-icon fas fa-chevron-down"></i>
                            </div>
                        </div>

                        <!-- Event Description -->
                        <div class="form-group">
                            <label for="eventDescription">Event Description</label>
                            <textarea id="eventDescription" name="eventDescription" rows="4" required><?php echo $activity['Description'];?></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group-button">
                            <button type="submit">Submit Changes</button>  
                        </div>
                    </form>
                </div>

                <?php
                    session_start(); // Start the session

                    // Display error message if exists
                    if (isset($_SESSION['error'])) {
                        echo "<div style='color: red;'>{$_SESSION['error']}</div>";
                        unset($_SESSION['error']); // Clear the error message
                    }

                    // Display success message if exists
                    if (isset($_SESSION['success'])) {
                        echo "<div style='color: green;'>{$_SESSION['success']}</div>";
                        unset($_SESSION['success']); // Clear the success message
                    }
                ?>

                <div id="attendeeContent" hidden>
                    <h2>Attendees</h2>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>User Email</th>
                            </tr>
                        </thead>
                        <?php 

                            $query = "SELECT User.Name, User.Email
                                    FROM  User
                                    INNER JOIN Activity_Attendees 
                                    ON User.User_ID = Activity_Attendees.User_ID
                                    WHERE Activity_Attendees.Activity_ID = :activityID;";
                            
                            $stmt = $conn->prepare($query);
                            $stmt -> bindParam(":activityID", $activityID);
                            $stmt -> execute();

                            $attendees = $stmt -> fetchAll(PDO::FETCH_ASSOC);

                            foreach ($attendees as $var):
                                echo '<tr>
                                    <td>'.$var['Name'].'</td>
                                    <td>'.$var['Email'].'</td>
                                    </tr>';
                            endforeach;
                        ?>
                    </table>
                </div>

                <div id="workerContent" hidden>
                    <h2>Workers</h2>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>User Email</th>
                                <th>Approve?</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                               $query = "SELECT User.Name, User.Email, Activity_Workers.Verified, Activity_Workers.Worker_ID FROM User INNER JOIN Worker ON User.User_ID = Worker.User_ID INNER JOIN 
                                Activity_Workers ON  Worker.Worker_ID = Activity_Workers.Worker_ID WHERE Activity_Workers.Activity_ID = :activityID;";

                                $stmt = $conn->prepare($query);
                                $stmt -> bindParam(":activityID", $activityID);
                                $stmt -> execute();
                                $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);


                                foreach ($workers as $worker): 
                                    if($worker['Verified'] == 1){
                                        $verified = "Verified";
                                        $statusClass = "verified";
                                    } else {
                                        $verified = "Not Verified";
                                        $statusClass = "not-verified";
                                    } ?>
                                    <tr>
                                        <td><?php echo $worker['Name']; ?></td>
                                        <td><?php echo $worker['Email']; ?></td>
                                        <td class="approval-table" style="text-align:center;">
                                            <form action="workReg.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="workerID" value="<?php echo $worker['Worker_ID']; ?>">
                                                <input type="hidden" name="activityID" value="<?php echo $activityID; ?>">
                                                <input type="hidden" name="decision" value="accept">
                                                <p class="<?php echo $statusClass; ?>"><?php echo $verified?></p>
                                                <button type="submit" class="accept-button">Accept Worker</button>
                                            </form>
                                            <form action="workReg.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="workerID" value="<?php echo $worker['Worker_ID']; ?>">
                                                <input type="hidden" name="activityID" value="<?php echo $activityID; ?>">
                                                <input type="hidden" name="decision" value="deny">
                                                <button type="submit" class="deny-button">Deny Worker</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                </div>

                <div id="messageContent" hidden>
                    <h2>Send Message</h2>
                    <form id="sendMessageArea" action="createMessage.php" method="POST">
                    <input type = "hidden" name="userID" value = <?php echo $userID; ?>>
                    <input type = "hidden" name="activityID" value = <?php echo $activityID; ?>>
                        <div class="form-group">
                            <label for="typeMessage">Type Message</label>
                            <textarea id="typeMessage" name="typeMessage" rows="4" required></textarea>
                        </div>
                        <div class="bottomPortion">
                            <div class="attendeeCheckBox">
                                <input type="checkbox" name="attendees" id="attendeesBox">Attendees
                            </div>
                            <div class="workerCheckBox">
                                <input type="checkbox" name="workers" id="workersBox">Workers    
                            </div>
                            <div class="messageButton">
                               <button id="sendMessageButton" class="sendMessageButton" onclick="valForm(event)">Send Message</button>
                               <!--<button id="sendMessageButton" type = "submit" class="sendMessageButton">Send Message</button> -->
                            </div>
                        </div>
                    </form>


                
                </div>

            </div>
                
        </div>
    <script>
        function valForm(event){
            // Stops the form from submitting initially
            event.preventDefault();

            const form = document.getElementById('sendMessageArea');

            var checkbox1 = document.getElementById("attendeesBox");
            var checkbox2 = document.getElementById("workersBox");
            var messageBox = document.getElementById("typeMessage");

            // if the message is empty, output a message
            if(messageBox.value.trim() == ""){
                alert("Message cannot be empty.");
            }
            
            // if the user did not check at least one checkbox, output an error
            else if (!checkbox1.checked && !checkbox2.checked){
                alert("Please check a checkbox."); 
            } 
            
            else {
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const buildingID = document.getElementById('building').value;
            let count = 0;
            if(buildingID){
                fetchRooms(buildingID, count);
                count++;
            }
        })

        function fetchRooms(buildingID, count) {
            // this function is called whenever the user changes the building selection from the drop down.
            const dropdown = document.getElementById('room');
            dropdown.innerHTML = '<option value="">Select a Room</option>';

            // If no building is selected, return nothing
            if (!buildingID) {
                return;
            }

            // creates and sends AJAX request to get rooms from selected building
            const request = new XMLHttpRequest();
            request.open('GET', 'getRooms.php?buildingID=' + buildingID, true);
            request.onload = function () {

                // checks if there is a proper HTTP response (200 indicates a successful response)
                if (this.status === 200) {
                    const rooms = JSON.parse(this.responseText);

                    // then, more options will be added depending on how many rooms there are for each building
                    rooms.forEach(room => {
                        const newRoom = document.createElement('option');

                        // the value of the option is the room's Room_ID.
                        newRoom.value = room.Room_ID;

                        // The text displayed to is the Room_Number from the database
                        newRoom.textContent = room.Room_Number;

                        // Pre-select the room that matches the event's room
                        if (count == 0 && room.Room_ID == <?= $activity['Room_ID'] ?>) {
                            newRoom.selected = true;
                        }

                        dropdown.appendChild(newRoom); // adds the new room to the options list
                    });

                } else {
                    // if there isn't a request, then output an error
                    console.error('Error fetching rooms');
                }
            };
            request.send();
        }

        // when the window loads, run the function.
        window.onload = function () {
            const params = new URLSearchParams(window.location.search);
            const message = params.get('message');
            const sent = params.get('sent');

            // if message is retrieved from the php file, then output it. 
            if (message) {
                alert(decodeURIComponent(message));
            }
            else if(sent){
                alert(decodeURIComponent(sent));
            }

            //remove query parameters
            history.replaceState(null, "", window.location.pathname); 
        }
    </script>
    </body>
</html>