<?php 
    session_start();
    require "connection.php";

    if (isset($_SESSION['user'])) {
        $userID = $_SESSION['user']['User_ID'];
        
        // Query to fetch messages from Notification
        $query = "SELECT n.Message, n.Activity_ID, n.Notification_ID, DATE_FORMAT(n.Creation_Time,'%Y-%m-%d %h:%i:%s %p') AS formattedDateTime, n.User_ID AS Sender_ID, r.Is_Read, r.Recipient_ID
                  FROM Notification n
                  INNER JOIN Recipients r ON n.Notification_ID = r.Notification_ID 
                  WHERE r.User_ID = :userID ORDER BY Creation_Time DESC;";
        
        $stmt = $conn->prepare($query); // Prepares the query
        $stmt->bindParam(':userID', $userID); // Binds parameter
        $stmt->execute(); // Executes the query
    
        // Fetch all the rows from the query
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //$_SESSION['messages'] = $messages; // stores the messages in the session

    } else {
        header("Location: index.php");
        exit;
    }

    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <!-- Add Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="js/profile.js"></script>
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
                <div class = "nav-right">
                    <li class="profile"><a href = "profile.php">Profile
                    </a></li>
                    <li class="logout"><a href = "logout.php">Logout</a></li>
                </div>
            </ul>
        </nav>

    <main>
            <div class="profile-container">
                <!-- Profile Picture and Name -->
                <div class="profile-info">
                    <!-- <img src="images/default-profile.png" alt="Profile Picture" class="profile-picture"> -->
                    
                    <!-- User Name and Edit Option -->
                    <div id="name-container" class="name-container">
                        <?php 
                            session_start(); // starts the session to access user information

                            if(!isset($_SESSION['user'])){
                                header("Location: index.php");
                                exit();
                            }

                            if(isset($_SESSION['User_ID'])){
                                $userID = $_SESSION['User_ID'];
                                $sql = "SELECT Name FROM User WHERE User_ID = :userID";
                                $stmt = $conn->prepare($sql); // prepares the sql code
                                $stmt->bindParam(':userID', $userID); // binds ":userID" to the userID variable
                                $stmt->execute(); // executes the sql code
                                
                                $result = $stmt->fetch(PDO::FETCH_ASSOC); // fetches the result of the sql query
                                
                                if($result){
                                    $_SESSION['Name'] = $result['Name']; // Sets the user's name in the sesson
                                }
                            }
                            
                        ?>
                        <h2 id="userName"><?php echo $_SESSION['user']['Name'];?></h2>
                        <button id="editNameButton" class="edit-button" onclick="showUpdate()">Edit Name</button>
                    </div>
                    <form method = "POST" id="edit-name" action="updateUser.php">
                        <div id="edit-name-container" class="hidden">
                            <input type="text" name = "editNameInput" id="editNameInput" value="<?php echo $_SESSION['user']['Name'];?>" required>
                            <button id="saveNameButton" class="save-button">Save</button>
                        </div>
                    </form>
                </div>

                <hr>

                <!-- Notifications -->

                <?php
                 foreach($notifications as $noti):
                    $notiID = $noti['Notification_ID'];
                    $repID = $noti['Recipient_ID'];
                    $senderID = $noti['Sender_ID'];
                    $activityID = $noti['Activity_ID'];
                    $creationTime = $noti['formattedDateTime'];
                    $message = $noti['Message'];

                    // get sender name
                    $query = "SELECT Name FROM User WHERE User_ID = :senderID;";
                    $stmt = $conn->prepare($query); // Prepares the query
                    $stmt->bindParam(':senderID', $senderID);
                    $stmt->execute();

                    $senderName = $stmt->fetch(PDO::FETCH_ASSOC);

                    // get activity name
                    $query = "SELECT Activity_Name FROM Activity WHERE Activity_ID = :activityID;";
                    $stmt = $conn->prepare($query); // Prepares the query
                    $stmt->bindParam(':activityID', $activityID);
                    $stmt->execute();
                    
                    $activityName = $stmt->fetch(PDO::FETCH_ASSOC);

                    echo '<div class="notifications">
                        <div class="notification-container">
                            <div class="eventName">'.$activityName['Activity_Name'].'</div>
                            <div class="managerName">'.$senderName['Name'].'</div>
                            <div class="time">'.$creationTime.'</div>
                            <div class="message">'.$message.'</div>
                            <form action = "removeRep.php" method = "POST">
                                <input type = "hidden" name="repID" value ="'.$repID.'">
                            <button class="remove-message-button" type = "submit" onclick="confirmDelete()">X</button>
                            </form>
                        </div>';


                    // upon viewing page, all notifications' isRead is changed to true
                    $query = "UPDATE Recipients SET Is_Read = 1 WHERE Recipient_ID = :repID;";
                    $stmt = $conn->prepare($query); // Prepares the query
                    $stmt->bindParam(':repID', $repID);
                    $stmt->execute();

                endforeach;
                ?>
            </div>
    </main>






    <script>
        function showUpdate(){
            var originalName = document.getElementById("userName");
            var update = document.getElementById("edit-name-container");
            var editNameButton = document.getElementById("editNameButton");

            originalName.style.display = "none";
            editNameButton.style.display = "none";
            update.style.display = "block";
        }

        function confirmDelete() {
            const confirmation = confirm('Are you sure you want to delete this message?');
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
