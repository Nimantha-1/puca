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
    $check_numbers_string = $_POST['check_numbers'];
    $check_numbers_array = explode(',', $check_numbers_string);
    
    // Start transaction
    $conn->begin_transaction();

    // Iterate through each check number
    foreach($check_numbers_array as $check_number) {
        // Check if the check number exists in not_checked_received table
        $sql_select_check = "SELECT * FROM not_checked_received WHERE check_number = '$check_number'";
        $result = $conn->query($sql_select_check);

        if ($result->num_rows > 0) {
            // Fetch the row from not_checked_received table
            $row = $result->fetch_assoc();
            $amount = $row['amount'];

            // Insert data into check_received table
            $sql_insert_check_received = "INSERT INTO check_received (check_number, amount) VALUES ('$check_number', '$amount')";
            if ($conn->query($sql_insert_check_received) === TRUE) {
                // Delete corresponding row from not_checked_received table
                $sql_delete_not_checked = "DELETE FROM not_checked_received WHERE check_number = '$check_number'";
                if ($conn->query($sql_delete_not_checked) !== TRUE) {
                    // Rollback transaction in case of error
                    $conn->rollback();
                    echo "Error deleting row from not_checked_received table: " . $conn->error;
                    exit;
                }
            } else {
                // Rollback transaction in case of error
                $conn->rollback();
                echo "Error inserting data into check_received table: " . $conn->error;
                exit;
            }
        } else {
            echo "Check number '$check_number' does not exist in not_checked_received table.<br>";
        }
    }

    // Commit transaction
    $conn->commit();
    echo "Data inserted into check_received table and corresponding rows deleted from not_checked_received table successfully.";
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
        <label for="check_numbers">Check Numbers (comma-separated):</label><br>
        <input type="text" id="check_numbers" name="check_numbers"><br><br>
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>
