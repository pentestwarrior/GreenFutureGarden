<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php?error=Invalid message ID");
    exit;
}

$stmt = $conn->prepare("
    DELETE FROM contact_messages
    WHERE id = ?
");

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: index.php?success=Message deleted successfully");
    exit;

}

header("Location: index.php?error=Unable to delete message");

exit;