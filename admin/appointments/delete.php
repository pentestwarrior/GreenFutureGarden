<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php?error=Invalid appointment ID.");
    exit;
}


$stmt = $conn->prepare("
    SELECT id
    FROM appointments
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    header("Location: index.php?error=Appointment not found.");
    exit;
}


$delete = $conn->prepare("
    DELETE FROM appointments
    WHERE id = ?
");

$delete->bind_param("i", $id);


if ($delete->execute()) {

    header(
        "Location: index.php?success=Appointment deleted successfully."
    );

    exit;

}


header(
    "Location: index.php?error=Unable to delete appointment."
);

exit;