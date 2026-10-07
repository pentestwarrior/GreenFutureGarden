<?php

require_once "../config/database.php";

$full_name = "Green Future Administrator";
$username = "admin";
$email = "admin@greenfutureflowergarden.com";
$password = "Admin@2026";
$role = "admin";

$check = $conn->prepare(
    "SELECT id FROM users WHERE username = ? OR email = ?"
);

$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("Admin account already exists.");
}

$check->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (full_name, username, email, password, role, status)
     VALUES (?, ?, ?, ?, ?, 'active')"
);

$stmt->bind_param(
    "sssss",
    $full_name,
    $username,
    $email,
    $hashed_password,
    $role
);

if ($stmt->execute()) {
    echo "<h2>Admin account created successfully.</h2>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> Admin@2026</p>";
    echo "<p>Please delete <strong>create_admin.php</strong> after successful creation.</p>";
} else {
    echo "Error creating admin account: " . $stmt->error;
}

$stmt->close();
$conn->close();