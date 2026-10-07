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
        sr.*,
        c.full_name AS customer_name,
        c.email AS customer_email,
        c.phone AS customer_phone,
        c.address AS customer_address,
        s.service_name,
        s.price AS service_price
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

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>View Request | Green Future Flower Garden</title>

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
    text-transform: capitalize;
}

.content {
    padding: 30px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.page-header h1 {
    margin: 0;
}

.back-btn,
.edit-btn {
    text-decoration: none;
    padding: 11px 17px;
    border-radius: 8px;
    font-weight: 600;
}

.back-btn {
    background: #e9efeb;
    color: #26332d;
}

.edit-btn {
    background: #2e7d32;
    color: white;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0,0,0,.05);
}

.card.full {
    grid-column: 1 / -1;
}

.card h3 {
    margin: 0 0 20px;
    font-size: 17px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 12px 0;
    border-bottom: 1px solid #edf1ee;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #718078;
    font-size: 13px;
}

.info-value {
    text-align: right;
    font-weight: 600;
}

.description {
    line-height: 1.7;
    color: #53615a;
    white-space: pre-line;
}

.status {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-reviewed {
    background: #e7f0ff;
    color: #245a9b;
}

.status-approved {
    background: #e4f5e7;
    color: #237333;
}

.status-rejected {
    background: #fff0f0;
    color: #b32626;
}

.status-completed {
    background: #e9e4ff;
    color: #5b42a6;
}

@media (max-width: 800px) {

    .grid {
        grid-template-columns: 1fr;
    }

    .card.full {
        grid-column: auto;
    }

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

<h2>Request Details</h2>

<p>Complete information about this service request.</p>

</div>

<div class="topbar-user">

<strong><?= htmlspecialchars($_SESSION["full_name"]) ?></strong>

<span><?= htmlspecialchars($_SESSION["role"]) ?></span>

</div>

</header>


<section class="content">

<div class="page-header">

<h1>
Request #<?= $request["id"] ?>
</h1>

<div>

<a href="index.php" class="back-btn">
← Back
</a>

<a href="edit.php?id=<?= $request["id"] ?>" class="edit-btn">
Edit Request
</a>

</div>

</div>


<div class="grid">


<div class="card">

<h3>Customer Information</h3>

<div class="info-row">

<span class="info-label">Name</span>

<span class="info-value">
<?= htmlspecialchars($request["customer_name"]) ?>
</span>

</div>

<div class="info-row">

<span class="info-label">Email</span>

<span class="info-value">
<?= htmlspecialchars($request["customer_email"] ?: "Not provided") ?>
</span>

</div>

<div class="info-row">

<span class="info-label">Phone</span>

<span class="info-value">
<?= htmlspecialchars($request["customer_phone"]) ?>
</span>

</div>

<div class="info-row">

<span class="info-label">Address</span>

<span class="info-value">
<?= htmlspecialchars($request["customer_address"] ?: "Not provided") ?>
</span>

</div>

</div>


<div class="card">

<h3>Service Information</h3>

<div class="info-row">

<span class="info-label">Service</span>

<span class="info-value">
<?= htmlspecialchars($request["service_name"]) ?>
</span>

</div>

<div class="info-row">

<span class="info-label">Standard Price</span>

<span class="info-value">
₦<?= number_format((float)$request["service_price"], 2) ?>
</span>

</div>

<div class="info-row">

<span class="info-label">Preferred Date</span>

<span class="info-value">

<?= !empty($request["preferred_date"])
    ? date("d M Y", strtotime($request["preferred_date"]))
    : "Not specified"
?>

</span>

</div>

<div class="info-row">

<span class="info-label">Status</span>

<span class="info-value">

<span class="status status-<?= htmlspecialchars($request["status"]) ?>">
<?= htmlspecialchars($request["status"]) ?>
</span>

</span>

</div>

</div>


<div class="card full">

<h3>Service Location</h3>

<p>
<?= htmlspecialchars($request["location"]) ?>
</p>

</div>


<div class="card full">

<h3>Request Description</h3>

<div class="description">

<?= htmlspecialchars($request["description"]) ?>

</div>

</div>


<div class="card full">

<h3>Request Information</h3>

<div class="info-row">

<span class="info-label">Request ID</span>

<span class="info-value">
#<?= $request["id"] ?>
</span>

</div>

<div class="info-row">

<span class="info-label">Created</span>

<span class="info-value">
<?= date("d M Y, h:i A", strtotime($request["created_at"])) ?>
</span>

</div>

</div>


</div>

</section>

</main>

</div>

</body>

</html>