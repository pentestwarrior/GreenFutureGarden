<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    header(
        "Location: index.php?error=Invalid project ID."
    );

    exit;
}


$check = $conn->prepare("
    SELECT id
    FROM projects
    WHERE id = ?
    LIMIT 1
");

$check->bind_param(
    "i",
    $id
);

$check->execute();

$result = $check->get_result();


if ($result->num_rows === 0) {

    header(
        "Location: index.php?error=Project not found."
    );

    exit;
}


$delete = $conn->prepare("
    DELETE FROM projects
    WHERE id = ?
");

$delete->bind_param(
    "i",
    $id
);


if ($delete->execute()) {

    header(
        "Location: index.php?success=Project deleted successfully."
    );

    exit;

}


header(
    "Location: index.php?error=Unable to delete project."
);

exit;