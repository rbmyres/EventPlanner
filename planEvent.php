<?php
    session_start();
    require 'connection.php';

    // Redirect to login if user is not logged in
    if (!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit();
    }

    $managerID = intval($_SESSION['user']['User_ID']); // Safely cast to an integer

    if($_SESSION['user']['isManager'] == 0){

        // variables to check if manager request already exists
        $userID = $_SESSION['user']['User_ID'];
        $name = $_SESSION['user']['Name'];
        $message = "User ".$name." (User ID = ".$userID.") has requested to become an event manager.";

        $sql = "SELECT COUNT(*) AS count FROM Notification WHERE User_ID = :userID AND Message = :message;";
       $stmt = $conn->prepare($sql);
        $stmt -> bindParam(":message", $message);
        $stmt -> bindParam(":userID", $userID);
        $stmt->execute();
        $overlap = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!($overlap['count'] > 0)) {
        echo '<script>
        
            let result = confirm("Before creating an event, you must have event manager permissions. \n\nPlease click OK below if you would like to request to become an Event Manager. Otherwise, click Cancel.");
        
            if (result) {
                // Redirect to the permissions request page (adjust the URL as needed)
                window.location.href = "requestPermission.php";

            } else {
                // Redirect back to the home page
                window.location.href = "home.php";

            }
            </script>';
        } else{

            echo '<script>
            alert("Your event manager request is still under review."); 
            window.location.href = "home.php";

            </script>';
            
        }
        exit;
    }

    // Include the database connection
    require_once("connection.php");

    // Initialize variables
    $buildings = [];
    $rooms = [];
    $message = "";

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

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $success = false; // determines whether the user successfully created the event or not
        // Assume data is collected from the form
        $eventName = $_POST['eventName'] ?? '';
        $description = $_POST['eventDescription'] ?? '';
        $eventDate = $_POST['eventDate'] ?? '';
        $startTime = $_POST['startTime'] ?? '';
        $endTime = $_POST['endTime'] ?? '';
        $workerLimit = $_POST['workerLimit'] ?? '';
        $buildingID = $_POST['building'] ?? '';
        $roomID = $_POST['room'] ?? '';

        // Insert into the Activity table
        if ($eventName && $eventDate && $startTime && $endTime && $buildingID && $roomID && $workerLimit && $description) {
            try {

                // Makes sure the start time is earlier than end tim
                if (new DateTime($startTime) >= new DateTime($endTime)) {
                    $message = "Error:+The+start+time+must+be+earlier+than+the+end+time.";
                    header("Location: planEvent.php?status=success&message=".$message);
                    exit;
                }

                // makes sure the date isn't in the past
                $currentDateTime = new DateTime();
                $eventStartDateTime = new DateTime("$eventDate $startTime");

                if ($eventStartDateTime <= $currentDateTime) {
                    $message = "Error:+The+event+date+and+time+must+be+in+the+future.";
                    header("Location: planEvent.php?status=success&message=".$message);
                    exit;
                }

                // makes sure there isn't already an event at this date, time, etc. 

                // Checks if there are any events at the same date, time, etc
                $sql = "SELECT COUNT(*) AS count FROM Activity WHERE Building_ID = :buildingID AND Room_ID = :roomID AND Date = :eventDate
                AND ((Start_Time < :endTime AND End_Time > :startTime)) AND Activity.Denied = 0;";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":buildingID", $buildingID);
                $stmt->bindParam(":roomID", $roomID);
                $stmt->bindParam(":eventDate", $eventDate);
                $stmt->bindParam(":startTime", $startTime);
                $stmt->bindParam(":endTime", $endTime);

                $stmt->execute();
                $overlap = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($overlap['count'] > 0) {
                    $message = "Error:+Another+event+is+already+scheduled+in+the+same+room+and+building+during+the+specified+time.";
                    header("Location: planEvent.php?status=success&message=".$message);
                    exit;
                }


                $query = "INSERT INTO Activity (Activity_Name, Description, Date, Start_Time, End_Time, Worker_Limit, Manager_ID, Building_ID, Room_ID) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);

                $stmt->execute([
                    $eventName,
                    $description,
                    $eventDate,
                    $startTime,
                    $endTime,
                    $workerLimit,
                    $managerID, // Manager_ID from the session
                    $buildingID,
                    $roomID
                ]);

                $status = true;
                $message = "Your+event+was+created+successfully+and+is+currently+under+review+for+approval.+Thank+you!";
                header("Location: planEvent.php?status=success&message=".$message);
                exit;
            } catch (PDOException $e) {
                echo "Error creating event: " . $e->getMessage();
            }
        } else {
            $message = "Something+went+wrong.+Please+try+again.";
            header("Location: planEvent.php?status=success&message=".$message);
        }

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Event</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="home.php">Events</a></li>
            <li><a href="myEvents.php">My Events</a></li>
            <li><a href="planEvent.php">Plan Event</a></li>
            <?php
                include 'checkAdminStatus.php';
                if ($adminStatus) {
                    echo '<li><a href="manageUsers.php">Manage Users</a></li>';
                }
            ?>
            <nav class="nav-right">
                <li class="profile"><a href="profile.php">Profile
                </a></li>
                <li class="logout"><a href="logout.php">Logout</a></li>
            </nav>
        </ul>
    </nav>

    <main>
        <div class="event-form">
            <h2>Plan an Event</h2>

            <!-- Display Message -->
            <?php if (!empty($message)): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="planEvent.php" method="POST">
                <!-- Event Name -->
                <div class="form-group">
                    <label for="eventName">Event Name</label>
                    <input type="text" id="eventName" name="eventName" required>
                </div>

                <!-- Event Date -->
                <div class="form-group">
                    <label for="eventDate">Event Date</label>
                    <input type="date" id="eventDate" name="eventDate" required>
                </div>

                <!-- Event Start and End Time -->
                <div class="form-group">
                    <label for="startTime">Start Time</label>
                    <input type="time" id="startTime" name="startTime" required>
                </div>
                <div class="form-group">
                    <label for="endTime">End Time</label>
                    <input type="time" id="endTime" name="endTime" required>
                </div>

                <!-- Building Dropdown -->
                <div class="form-group">
                    <div class="selectContainer">
                        <label for="building">Building</label>
                        <select id="building" name="building" required onchange="fetchRooms(this.value)">
                            <option value="">Select a Building</option>
                            <?php foreach ($buildings as $building): ?>
                                <option value="<?= htmlspecialchars($building['Building_ID']) ?>">
                                    <?= htmlspecialchars($building['Building_Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="dropdown-icon fas fa-chevron-down"></i>
                    </div>
                </div>

                <div class="form-group">
                    <div class="selectContainer">
                        <label for="room">Room</label>
                        <select id="room" name="room" required>
                            <option value="">Select a Room</option>
                            <!-- Rooms will be populated dynamically -->
                        </select>
                        <i class="dropdown-icon fas fa-chevron-down"></i>
                    </div>
                </div>

                <!-- Worker Dropdown -->
                <div class="form-group">
                    <div class="selectContainer">
                        <label for="workerLimit">Number of Workers</label>
                        <select id="workerLimit" name="workerLimit" required>
                            <option value="">Select Worker Limit</option>
                            <?php for ($i = 1; $i <= 20; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <i class="dropdown-icon fas fa-chevron-down"></i>
                    </div>
                </div>

                <!-- Event Description -->
                <div class="form-group">
                    <label for="eventDescription">Event Description</label>
                    <textarea id="eventDescription" name="eventDescription" rows="4" required></textarea>
                </div>

                <!-- Submit Button -->
                <div class="form-group-button">
                    <button type="submit">Create Event</button>
                </div>
            </form>
        </div>
    </main>
    <script>
        function fetchRooms(buildingID) {
            // this function is called whenever the user changes the building selection from the drop down.

            // if the user selects 'Select a Building'. then the only room option will change to "Select a Room"
            if (!buildingID) {
                document.getElementById('room').innerHTML = '<option value="">Select a Room</option>';
                return;
            }

            // creates and sends AJAX request to get rooms from selected building
            const request = new XMLHttpRequest();
            request.open('GET', 'getRooms.php?buildingID=' + buildingID, true);
            request.onload = function () {

                // checks if there is a proper HTTP response (200 indicates a successful response)
                if (this.status === 200) {
                    const rooms = JSON.parse(this.responseText);
                    const dropdown = document.getElementById('room');

                    // ensures the initial option is "Select a Room"
                    dropdown.innerHTML = '<option value="">Select a Room</option>';

                    // then, more options will be added depending on how many rooms there are for each building
                    rooms.forEach(room => {
                        const newRoom = document.createElement('option');

                        // the value of the option is the room's Room_ID.
                        newRoom.value = room.Room_ID;

                        // The text displayed to is the Room_Number from the database
                        newRoom.textContent = room.Room_Number;
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
            const status = params.get('status');
            const message = params.get('message');

            // if message is retrieved from the php file, then output it. 
            if (message) {
                alert(decodeURIComponent(message));
            }

            //remove query parameters
            history.replaceState(null, "", window.location.pathname); 
        };
    </script>
</body>
</html>

