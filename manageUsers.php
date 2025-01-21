<?php
    //session stuff
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/manageUsers.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="home.php">Events</a></li>
            <li class="requests"><a href="myEvents.php">Pending Requests
            </a></li>
            <?php
                //if adminStatus is true, which is a variable from the checkAdminStatus.php file, then the user can view the Manager Users link
                include 'checkAdminStatus.php';
                if($adminStatus){
                    echo '<li><a href="manageUsers.php">Manage Users</a></li>';
                }
            ?>
            <div class="nav-right">
                <li class="profile"><a href="profile.php">Profile
                </a></li>
                <li class="logout"><a href = "logout.php">Logout</a></li>
            </div>
        </ul>
    </nav>
    <h1>Manage Users</h1>
    <table class="user-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email Address</th>
                <th>User Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // displays php errors
                ini_set('display_errors', 1);
                error_reporting(E_ALL);

                require 'connection.php';
                // Prepare SQL query to fetch desired data from database
                $sql = "SELECT User_ID, Name, Email, isManager FROM User";
                $data = $conn->query($sql);

                $deleteMessage = "Are you sure you want to delete this user?";

                // adds to the HTML table by looping through each row in the database
                 while($row = $data->fetch()){
                    $userID = $row["User_ID"];
                    echo "<tr><td>" . $row["User_ID"] . "</td><td>" 
                    . $row["Name"] . "</td><td>" 
                    . $row["Email"] . "</td>";
                    

                    $roleName; // stores the name of the user's role
                    $roles = array("Event Manager", "General User"); // will be used in a for loop below--will be helpful if newer roles are added later

                    
                    //executes a SQL query that checks if the user is in the Admin table
                    $adminQuery = "SELECT COUNT(*) as count FROM Admin WHERE User_ID = ".$userID;
                    $result = $conn->prepare($adminQuery);
                    $result -> execute();

                    // if the user's id shows up in the admin table, then display user role as "Admin"
                    if($result -> fetchColumn() > 0){
                        echo "<td>Admin</td><td></td>"; // the extra <td></td> at the end is to fill the space in the "Action" column
                    }else{
                        // Since Admins also have isManager = true, then we need to check whether the user is not an admin
                        // to display their role as "Event Manager"

                        if($row["isManager"] == 1){
                            $roleName = $roles[0];
                        }else{
                            $roleName = $roles[1];
                        }
                        
                        // creates the dropdown menu. After changing the user role from the drop down menu, the updateAccess.php file will run
                        echo "<td><form action='updateAccess.php' method='POST'>
                            <input type = 'hidden' name='userID' value ='".$userID."'>
                            <select name='role' onchange='this.form.submit()'>
                            <option value='".$roleName."'>".$roleName."</option>"; // the first option is the current role name, so the dropdown menu will have the current role shown from the manager users page
                        
                        // iterates through the roles array and creates the other options for the drop down menu
                        for($x = 0; $x < sizeof($roles); $x++){
                            // this if statement prevents duplicate drop down options
                            if($roles[$x] != $roleName){
                                echo"<option value='".$roles[$x]."'>".$roles[$x]."</option>";
                            }
                        }
                        
                        // completes the row
                        echo "</select></form></td>";

                        // creates the delete user button for each row (except if the user is an admin)
                        echo "<td><form action = 'deleteUser.php' method = 'POST'>
                        <input type = 'hidden' name='userID' value ='".$userID."'>
                        <button type='submit' value='revoke' onclick='return confirm (\"Are you sure you want to delete the account of (User ID = ".$row["User_ID"].")? This action cannot be undone.\")'>Delete User</button>
                        </form> </td></tr>";

                    }
                }
            ?>
        </tbody>
    </table>
</body>
</html>
