<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';

$allowed_statuses = ['unread', 'read', 'replied'];

if (!$id || !in_array($status, $allowed_statuses, true)) {
    header("Location: index.php?error=Invalid message information");
    exit;
}

$stmt = $conn->prepare("
    UPDATE contact_messages
    SET status = ?
    WHERE id = ?
");

$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {

    header("Location: view.php?id=$id");
    exit;

}

header("Location: index.php?error=Unable to update message");

exit;