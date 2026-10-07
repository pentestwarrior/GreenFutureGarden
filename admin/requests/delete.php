<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


$stmt = $conn->prepare(
    "SELECT
        sr.id,
        sr.location,
        sr.status,
        c.full_name AS customer_name,
        s.service_name
     FROM service_requests sr
     INNER JOIN customers c ON sr.customer_id = c.id
     INNER JOIN services s ON sr.service_id = s.id
     WHERE sr.id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");

    exit;
}

$request = $result->fetch_assoc();

$stmt->close();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $delete = $conn->prepare(
        "DELETE FROM service_requests
         WHERE id = ?"
    );

    $delete->bind_param("i", $id);

    if ($delete->execute()) {

        $delete->close();

        header(
            "Location: index.php?success=Service+request+deleted+successfully"
        );

        exit;

    } else {

        $delete->close();

        header(
            "Location: index.php?error=Unable+to+delete+service+request"
        );

        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Delete Request | Green Future Flower Garden</title>

<link rel="stylesheet" href="/GreenFutureGarden/assets/css/admin.css">

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f7f6;
    color: #26332d;
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: #124d35;
    color: white;
}

.main-content {
    margin-left: 250px;
    width: calc(100% - 250px);
    min-height: 100vh;
}

.sidebar-brand {
    padding: 25px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: white;
    color: #124d35;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
}

.sidebar-brand strong {
    display: block;
}

.sidebar-brand span {
    display: block;
    font-size: 12px;
    opacity: .8;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    margin-top: 15px;
}

.sidebar-nav a {
    display: block;
    padding: 13px 20px;
    color: white;
    text-decoration: none;
    font-size: 14px;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
    background: rgba(255,255,255,.12);
}

.sidebar-bottom {
    position: absolute;
    bottom: 20px;
    width: 100%;
}

.sidebar-bottom a {
    display: block;
    padding: 13px 20px;
    color: white;
    text-decoration: none;
}

.topbar {
    min-height: 90px;
    background: white;
    border-bottom: 1px solid #e1e7e3;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topbar h2 {
    margin: 0;
}

.topbar p {
    margin: 5px 0 0;
    color: #718078;
    font-size: 14px;
}

.topbar-user {
    text-align: right;
}

.topbar-user strong {
    display: block;
}

.topbar-user span {
    display: block;
    color: #718078;
    font-size: 12px;
    margin-top: 4px;
}

.content {
    padding: 30px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0;
}

.back-btn {
    background: #e9efeb;
    color: #26332d;
    text-decoration: none;
    padding: 11px 17px;
    border-radius: 8px;
    font-weight: 600;
}

.delete-card {
    max-width: 700px;
    background: white;
    padding: 35px;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0,0,0,.06);
}

.warning-icon {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: #fff0f0;
    color: #c62828;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 20px;
}

.delete-card h2 {
    margin: 0 0 10px;
}

.delete-card p {
    color: #66736c;
    line-height: 1.6;
}

.info {
    margin: 20px 0;
    padding: 18px;
    background: #f5f8f6;
    border-radius: 9px;
}

.info strong {
    display: block;
    margin-bottom: 7px;
}

.warning {
    background: #fff7e6;
    border: 1px solid #f1d69a;
    padding: 15px;
    border-radius: 8px;
    color: #7a5b16;
}

.actions {
    margin-top: 25px;
    display: flex;
    gap: 12px;
}

.delete-btn {
    border: none;
    background: #c62828;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.cancel-btn {
    background: #eef2f0;
    color: #26332d;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
}

@media (max-width: 700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .sidebar-bottom {
        position: static;
    }

    .main-content {
        margin-left: 0;
        width: 100%;
    }

    .topbar {
        padding: 18px;
        flex-direction: column;
        align-items: flex-start;
    }

    .topbar-user {
        text-align: left;
    }

    .content {
        padding: 18px;
    }

    .delete-card {
        padding: 25px;
    }

}

</style>

</head>

<body>

<div class="admin-layout">

<aside class="sidebar">

<div class="sidebar-brand">

<div class="brand-icon">GF</div>

<div>
<strong>Green Future</strong>
<span>Flower Garden</span>
</div>

</div>

<nav class="sidebar-nav">

<a href="../index.php">Dashboard</a>
<a href="../customers/index.php">Customers</a>
<a href="../services/index.php">Services</a>
<a href="index.php" class="active">Service Requests</a>
<a href="../appointments/index.php">Appointments</a>
<a href="../quotations/index.php">Quotations</a>
<a href="../projects/index.php">Projects</a>

</nav>

<div class="sidebar-bottom">

<a href="../logout.php">Logout</a>

</div>

</aside>


<main class="main-content">

<header class="topbar">

<div>

<h2>Delete Service Request</h2>

<p>Request record management.</p>

</div>

<div class="topbar-user">

<strong><?= htmlspecialchars($_SESSION["full_name"]) ?></strong>

<span><?= htmlspecialchars($_SESSION["role"]) ?></span>

</div>

</header>


<section class="content">

<div class="page-header">

<h1>Delete Request</h1>

<a href="index.php" class="back-btn">
← Back to Requests
</a>

</div>


<div class="delete-card">

<div class="warning-icon">
!
</div>

<h2>
Confirm Deletion
</h2>

<p>
You are about to permanently delete this service request.
</p>


<div class="info">

<strong>
Customer:
<?= htmlspecialchars($request["customer_name"]) ?>
</strong>

<strong>
Service:
<?= htmlspecialchars($request["service_name"]) ?>
</strong>

<span>
Location:
<?= htmlspecialchars($request["location"]) ?>
</span>

</div>


<div class="warning">

<strong>Warning:</strong>

This action cannot be undone.

</div>


<form method="POST">

<div class="actions">

<button
    type="submit"
    class="delete-btn"
    onclick="return confirm('Are you sure you want to delete this request?');"
>
Yes, Delete Request
</button>

<a
    href="index.php"
    class="cancel-btn"
>
Cancel
</a>

</div>

</form>

</div>

</section>

</main>

</div>

</body>

</html>