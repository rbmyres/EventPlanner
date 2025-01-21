<?php
    require 'connection.php';

    if (isset($_GET['buildingID'])) {

        // assign the buildingID pulled from the Plan event page to the $buildingID variable
        $buildingID = $_GET['buildingID'];

        try {
            // query to find all the rooms associated with the particular building
            $query = "SELECT Room_ID, Room_Number FROM Room WHERE Building_ID = :buildingID";
            $stmt = $conn->prepare($query);
            $stmt->bindparam(":buildingID", $buildingID);
            $stmt -> execute();

            // fetches all rooms and assigns to $rooms variable. This includes the room's ID and the room's number
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // sends back to plan event page
            header('Content-Type: application/json');
            echo json_encode($rooms);

        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error fetching rooms: ' . $e->getMessage()]);
        }
    }
?>
