<?php
session_start(); // Start the session

$servername = "localhost";
$username = "root";        // Replace with your database username
$password = "";            // Replace with your database password
$dbname = "signup_db";     // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and sanitize form data
$id_number = $_POST['id_number'];
$password = $_POST['password'];

// Prepare and bind
$stmt = $conn->prepare("SELECT password FROM users WHERE id_number = ?");
$stmt->bind_param("s", $id_number);

// Execute statement
$stmt->execute();
$result = $stmt->get_result();

$response = [];

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Verify password
    if (password_verify($password, $user['password'])) {
        $_SESSION['id_number'] = $id_number; // Set the session variable
        $response = ["status" => "success"];
    } else {
        $response = ["status" => "error", "message" => "Incorrect ID number or password"];
    }
} else {
    $response = ["status" => "error", "message" => "Invalid ID number or password"];
}

// Send response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
