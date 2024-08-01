<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Retrieve and sanitize input data
$full_name = $conn->real_escape_string($_POST['full_name']);
$id_number = $conn->real_escape_string($_POST['id_number']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$sex = $conn->real_escape_string($_POST['sex']);
$dob = $conn->real_escape_string($_POST['dob']);
$program = $conn->real_escape_string($_POST['program']);

// Check for duplicate ID number
$stmt = $conn->prepare("SELECT * FROM users WHERE id_number = ?");
$stmt->bind_param("s", $id_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ID number already exists
    echo json_encode(["status" => "error", "message" => "ID number already exists."]);
} else {
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, id_number, password, sex, dob, program) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_name, $id_number, $password, $sex, $dob, $program);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Signup successful."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }
}

// Close connections
$stmt->close();
$conn->close();
?>
