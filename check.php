<?php
// Database connection
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "table2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If form is submitted
if(isset($_POST['submit'])) {
    // Retrieve form data
    $check_number = $_POST['check_number'];
    
    // Check if the check number exists in not_checked_received table
    $sql_select_check = "SELECT * FROM not_checked_received WHERE check_number = '$check_number'";
    $result = $conn->query($sql_select_check);

    if ($result->num_rows > 0) {
        // Fetch the row from not_checked_received table
        $row = $result->fetch_assoc();
        $amount = $row['amount'];

        // Start transaction
        $conn->begin_transaction();

        // Insert data into check_received table
        $sql_insert_check_received = "INSERT INTO check_received (check_number, amount) VALUES ('$check_number', '$amount')";
        if ($conn->query($sql_insert_check_received) === TRUE) {
            // Delete corresponding row from not_checked_received table
            $sql_delete_not_checked = "DELETE FROM not_checked_received WHERE check_number = '$check_number'";
            if ($conn->query($sql_delete_not_checked) === TRUE) {
                // Commit transaction
                $conn->commit();
                echo "Data inserted into check_received table and corresponding row deleted from not_checked_received table successfully.";
            } else {
                // Rollback transaction in case of error
                $conn->rollback();
                echo "Error deleting row from not_checked_received table: " . $conn->error;
            }
        } else {
            // Rollback transaction in case of error
            $conn->rollback();
            echo "Error inserting data into check_received table: " . $conn->error;
        }
    } else {
        echo "Check number does not exist in not_checked_received table.";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Check Received</title>
</head>
<body>
    <h2>Insert Check Received</h2>
    <form method="post">
        <label for="check_number">Check Number:</label><br>
        <input type="text" id="check_number" name="check_number"><br><br>
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>
