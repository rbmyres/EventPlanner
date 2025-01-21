<?php 
require "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $eventName = $_POST['eventName'] ?? '';
    $description = $_POST['eventDescription'] ?? '';
    $eventDate = $_POST['eventDate'] ?? '';
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';
    $workerLimit = $_POST['workerLimit'] ?? '';
    $buildingID = $_POST['building'] ?? '';
    $roomID = $_POST['room'] ?? '';
    $activityID = $_POST['activityID'] ?? '';

    // Validate fields
    if (!$eventName || !$eventDate || !$startTime || !$endTime || !$buildingID || !$roomID || !$workerLimit || !$description || !$activityID) {
        $message = "Please fill in all fields to update the event.";
        header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
        exit;
    }

    // Ensure start time is earlier than end time
    try {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        if ($start >= $end) {
            $message = "The start time must be earlier than the end time.";
            header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
            exit;
        }
    } catch (Exception $e) {
        $message = "Invalid date or time format.";
        header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
        exit;
    }

    // Ensure the date and time hasn't already passed
    $currentDateTime = new DateTime();
    try {
        $eventStartDateTime = new DateTime("$eventDate $startTime");
        if ($eventStartDateTime <= $currentDateTime) {
            $message = "The event date and time must be in the future.";
            header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
            exit;
        }
    } catch (Exception $e) {
        $message = "Invalid date or time format.";
        header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
        exit;
    }

    // Check for overlapping events
    $sql = "
    SELECT COUNT(*) AS count
    FROM Activity
    WHERE Building_ID = :buildingID
      AND Room_ID = :roomID
      AND Activity_ID != :activityID
      AND Date = :eventDate
      AND (
          (Start_Time < :endTime AND End_Time > :startTime)
      );";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":buildingID", $buildingID);
    $stmt->bindParam(":roomID", $roomID);
    $stmt->bindParam(":activityID", $activityID);
    $stmt->bindParam(":eventDate", $eventDate);
    $stmt->bindParam(":startTime", $startTime);
    $stmt->bindParam(":endTime", $endTime);

    $stmt->execute();
    $overlap = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($overlap['count'] > 0) {
        $message = "Another event is already scheduled in the same room and building during the specified time.";
        header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
        exit;
    }

    // Perform the update
    try {
        $updateQuery = "
            UPDATE Activity
            SET Activity_Name = :eventName,
                Description = :eventDescription,
                Date = :eventDate,
                Start_Time = :startTime,
                End_Time = :endTime,
                Worker_Limit = :workerLimit,
                Building_ID = :buildingID,
                Room_ID = :roomID,
                Verified = 0
            WHERE Activity_ID = :activityID; ";
        $stmt = $conn->prepare($updateQuery);

        $stmt->bindParam(":activityID", $activityID);
        $stmt->bindParam(":eventName", $eventName);
        $stmt->bindParam(":eventDescription", $description);
        $stmt->bindParam(":eventDate", $eventDate);
        $stmt->bindParam(":startTime", $startTime);
        $stmt->bindParam(":endTime", $endTime);
        $stmt->bindParam(":workerLimit", $workerLimit);
        $stmt->bindParam(":buildingID", $buildingID);
        $stmt->bindParam(":roomID", $roomID);

        $stmt->execute();

       $message = "Your+event+was+updated+successfully+and+is+currently+under+review+for+approval.+Thank+you!";
       header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
       exit;


    } catch (PDOException $e) {
       $message = "Error updating event: " . $e->getMessage();
    }
}
?>