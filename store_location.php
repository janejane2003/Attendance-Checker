<?php
session_start();
include 'database_connection.php'; // Ensure this file exists and is correctly referenced

// Check if the session variable is set
if (!isset($_SESSION['id_number'])) {
    die("ID number is not set in the session.");
}

// Retrieve the id_number from the session and the address from the form
$id_number = $_SESSION['id_number'];
$address = $_POST['address'];

// Check if the id_number exists in the database
$query = "SELECT * FROM users WHERE id_number = ?";
$stmt = $conn->prepare($query);

// Check if the statement was prepared successfully
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$stmt->bind_param("s", $id_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // id_number exists, update the user's address
    $update_query = "UPDATE users SET address = ? WHERE id_number = ?";
    $update_stmt = $conn->prepare($update_query);

    // Check if the statement was prepared successfully
    if (!$update_stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $update_stmt->bind_param("ss", $address, $id_number);
    $update_stmt->execute();

    // Redirect to qrscanner.html
    header("Location: qrscanner.html");
    exit();
} else {
    // id_number does not exist, show an error message or handle accordingly
    echo "ID number does not exist in the database.";
    exit();
}
?>
