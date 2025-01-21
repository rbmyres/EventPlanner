<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workerID = $_POST['workerID'];
    $activityID = $_POST['activityID'];
    $decision = $_POST['decision']; // "accept" or "deny"

    try {
        if ($decision === "accept") {
            // Mark worker as verified
            $stmt = $conn->prepare("UPDATE Activity_Workers SET Verified = 1 WHERE Worker_ID = :workerID AND Activity_ID = :activityID");
            $stmt->bindParam(':workerID', $workerID, PDO::PARAM_INT);
            $stmt->bindParam(':activityID', $activityID, PDO::PARAM_INT);
            $stmt->execute();
            $message = "Worker(s) approved successfully.";

        } else if ($decision === "deny") {
            // Remove the worker request
            $stmt = $conn->prepare("DELETE FROM Activity_Workers WHERE Worker_ID = :workerID AND Activity_ID = :activityID");
            $stmt->bindParam(':workerID', $workerID, PDO::PARAM_INT);
            $stmt->bindParam(':activityID', $activityID, PDO::PARAM_INT);
            $stmt->execute();
            $message = "Worker request(s) denied.";
        } else {
            throw new Exception("Invalid decision.");
        }

        // Redirect back with a success message
        header("Location: manageEvent.php?activityID=$activityID&userID=$userID&message=$message");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
