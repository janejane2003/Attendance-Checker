<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Retrieve and sanitize input data
$data = json_decode(file_get_contents('php://input'), true);
$id_number = $conn->real_escape_string($data['id_number']);

if (empty($id_number)) {
    echo json_encode(["status" => "error", "message" => "ID number is required."]);
    $conn->close();
    exit();
}

// Check if the ID number exists in the database
$stmt = $conn->prepare("SELECT * FROM users WHERE id_number = ?");
$stmt->bind_param("s", $id_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $timestamp = date('Y-m-d H:i:s');
    
    // Check if the user is already checked in without a check-out time
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_number = ? AND time_in IS NOT NULL AND time_out IS NULL");
    $stmt->bind_param("s", $id_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User is already checked in, update time_out
        $stmt = $conn->prepare("UPDATE users SET time_out = ? WHERE id_number = ? AND time_in IS NOT NULL AND time_out IS NULL");
        $stmt->bind_param("ss", $timestamp, $id_number);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Checked out successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to check out! " . $stmt->error]);
        }
    } else {
        // User is not checked in, insert time_in
        $stmt = $conn->prepare("UPDATE users SET time_in = ?, time_out = NULL WHERE id_number = ?");
        $stmt->bind_param("ss", $timestamp, $id_number);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Checked in successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to check in! " . $stmt->error]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "ID number not found."]);
}

$stmt->close();
$conn->close();
?>
