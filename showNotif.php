<?php
session_start();
include 'connection.php'; // Ensure this file sets up a PDO connection

if (isset($_SESSION['user'])) {
    try {
        // Get the user's ID from the session
        $userID = $_SESSION['user']['User_ID'];

        $stmt = $conn->prepare("SELECT COUNT(*) AS Notif_Count FROM Notification WHERE User_ID = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $number = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['Notif_Count' => $number['Notif_Count']]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'User session not found']);
}
?>
