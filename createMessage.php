<?php 
    require "connection.php";
	if ($_SERVER["REQUEST_METHOD"]=="POST"){
        // ensures the attendees and workers checkbox is not empty
        if(isset($_POST['userID']) && isset($_POST['activityID'])){
            if((isset($_POST['attendees']) && !empty($_POST['attendees'])) || (isset($_POST['workers']) && !empty($_POST['workers'])) ){
                
                // ensures the user's message is not empty
                if(isset($_POST['typeMessage']) && !empty($_POST['typeMessage'])){
                    echo 'here';

                    // Add message to Notification table

                    $message = $_POST['typeMessage'];
                    $activityID = $_POST['activityID'];
                    $userID = $_POST['userID'];
                    $sent; // holds the alert that will be shown to user

                    try {
                        // query to insert message into Notification table. Requires the Activity's ID, the sender's message, and the sender's User ID
                        $sql = "INSERT INTO Notification(Activity_ID, Message, User_ID) values (:ActivityID, :messages, :UserID);";
                        $stmt = $conn->prepare($sql);
                        $stmt -> bindParam(':ActivityID', $activityID);
                        $stmt -> bindParam(':messages', $message);
                        $stmt -> bindParam(':UserID', $userID);
                        $stmt -> execute();

                    } catch (PDOException $e) {
                        echo "Database error: " . $e->getMessage();
                    }
                   
                    // Get the last inserted Notification_ID
                    $notificationID = $conn->lastInsertId();
                    
                    // Gather user IDs and add them to recipients table

                    if(!empty($_POST['attendees'])){
                        // send to attendees
                        $sql = "SELECT * FROM Activity_Attendees WHERE Activity_ID = :activityID;";
                        $stmt = $conn->prepare($sql);
                        $stmt -> bindParam(":activityID", $activityID);
                        $stmt -> execute();
                        $attendees = $stmt -> fetchAll(PDO::FETCH_ASSOC);

                        foreach($attendees as $attendee):

                            $sql = "INSERT INTO Recipients (Notification_ID, User_ID) VALUES (:notificationID, :userID);";
                            $stmt = $conn->prepare($sql);
                            $stmt -> bindParam(":notificationID", $notificationID);
                            $stmt -> bindParam(":userID", $attendee['User_ID']);
                            $stmt -> execute();

                        endforeach;

                    }
                     
                    if(!empty($_POST['workers'])){
                        echo 'here';
                        // send to workers
                        $sql = "SELECT User.User_ID, Activity_Workers.Verified FROM User INNER JOIN Worker ON User.User_ID = Worker.User_ID INNER JOIN 
                                Activity_Workers ON  Worker.Worker_ID = Activity_Workers.Worker_ID WHERE Activity_Workers.Activity_ID = :activityID;";
                        $stmt = $conn->prepare($sql);
                        $stmt -> bindParam(":activityID", $activityID);
                        $stmt -> execute();
                        $workers = $stmt -> fetchAll(PDO::FETCH_ASSOC);

                        foreach($workers as $worker):
                            if($worker['Verified'] === 1){
                                $sql = "INSERT INTO Recipients (Notification_ID, User_ID) VALUES (:notificationID, :userID);";
                                $stmt = $conn->prepare($sql);
                                $stmt -> bindParam(":notificationID", $notificationID);
                                $stmt -> bindParam(":userID", $worker['User_ID']);
                                $stmt -> execute();
                            }

                        endforeach;
                        
                    }

                    //echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
                    $sent = "Message sent successfully!";
                    header("Location: manageEvent.php?activityID=$activityID&userID=$userID&sent=$sent");
                    exit;
                }
                else{
                    $sent = "Message cannot be empty.";
                    header("Location: manageEvent.php?activityID=$activityID&userID=$userID&sent=$sent");
                    exit;
                }
            } 
            else{
                $sent = "Please check at least one of the checkboxes.";
                header("Location: manageEvent.php?activityID=$activityID&userID=$userID&sent=$sent");
                exit;
            }
        }
        else{
            //http_response_code(400);
            //echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
            $sent = "Invalid input. Please try again.";
            header("Location: manageEvent.php?activityID=$activityID&userID=$userID&sent=$sent");

            exit;

        }
    }
    

?>