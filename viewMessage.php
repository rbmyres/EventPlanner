<?php 
    session_start();
    require "connection.php";

    if (isset($_SESSION['user'])) {
        $userID = $_SESSION['user']['User_ID'];
        
        // Query to fetch messages from Notification
        $query = "SELECT n.Message 
                  FROM Notification n
                  INNER JOIN Recipients r ON n.Notification_ID = r.Notification_ID 
                  WHERE r.User_ID = :userID;";
        
        $stmt = $conn->prepare($query); // Prepares the query
        $stmt->bindParam(':userID', $userID); // Binds parameter
        $stmt->execute(); // Executes the query
    
        // Fetch all the rows from the query
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['messages'] = $messages; // stores the messages in the session

        // redirect back to profile page
        header("Location: profile.php");
        exit();



    } else {
        header("Location: index.php");
        exit();
    }

    

?>