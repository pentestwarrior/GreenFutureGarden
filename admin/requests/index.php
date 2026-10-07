<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$search = trim($_GET["search"] ?? "");
$status = trim($_GET["status"] ?? "");

$sql = "SELECT
            sr.id,
            sr.location,
            sr.preferred_date,
            sr.description,
            sr.status,
            sr.created_at,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            s.service_name
        FROM service_requests sr
        INNER JOIN customers c ON sr.customer_id = c.id
        INNER JOIN services s ON sr.service_id = s.id
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (
                c.full_name LIKE ?
                OR c.phone LIKE ?
                OR s.service_name LIKE ?
                OR sr.location LIKE ?
              )";

    $term = "%" . $search . "%";

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;

    $types .= "ssss";
}

$allowed_statuses = [
    "pending",
    "reviewed",
    "approved",
    "rejected",
    "completed"
];

if ($status !== "" && in_array($status, $allowed_statuses, true)) {

    $sql .= " AND sr.status = ?";

    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY sr.id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$requests = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Service Requests | Green Future Flower Garden</title>

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
    z-index: 1000;
    overflow-y: auto;
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
    margin-top: 3px;
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
    font-size: 26px;
}

.page-header p {
    margin: 6px 0 0;
    color: #718078;
    font-size: 14px;
}

.add-btn {
    background: #2e7d32;
    color: white;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 8px;
    font-weight: 600;
}

.toolbar {
    background: white;
    padding: 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 5px 18px rgba(0,0,0,.04);
}

.search-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-form input,
.search-form select {
    padding: 12px 14px;
    border: 1px solid #d5ddd8;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
}

.search-form input {
    flex: 1;
    min-width: 220px;
}

.search-form select {
    min-width: 160px;
}

.search-btn {
    border: none;
    background: #26332d;
    color: white;
    padding: 12px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.clear-btn {
    display: inline-flex;
    align-items: center;
    padding: 12px 15px;
    background: #eef2f0;
    color: #26332d;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
}

.table-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0,0,0,.05);
    overflow: hidden;
}

.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1000px;
}

th {
    background: #f4f7f5;
    color: #52615a;
    text-align: left;
    padding: 15px;
    font-size: 13px;
    white-space: nowrap;
}

td {
    padding: 15px;
    border-top: 1px solid #edf1ee;
    font-size: 14px;
    vertical-align: middle;
}

.customer-name,
.service-name {
    font-weight: 700;
}

.customer-phone {
    color: #718078;
    font-size: 12px;
    margin-top: 4px;
}

.location {
    max-width: 220px;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 11px;
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

.actions {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
}

.action-btn {
    padding: 7px 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
}

.view-btn {
    background: #e8f1ff;
    color: #245a9b;
}

.edit-btn {
    background: #e7f5e9;
    color: #237333;
}

.delete-btn {
    background: #fff0f0;
    color: #b32626;
}

.empty {
    text-align: center;
    padding: 45px 20px;
    color: #718078;
}

.alert {
    padding: 14px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #e6f6e9;
    color: #236b2d;
}

.alert-error {
    background: #fff0f0;
    color: #a32424;
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

<h2>Service Requests</h2>

<p>Manage customer requests for gardening and landscaping services.</p>

</div>

<div class="topbar-user">

<strong>
<?= htmlspecialchars($_SESSION["full_name"]) ?>
</strong>

<span>
<?= htmlspecialchars($_SESSION["role"]) ?>
</span>

</div>

</header>


<section class="content">

<div class="page-header">

<div>

<h1>Service Requests</h1>

<p>Review, approve and track customer service requests.</p>

</div>

<a href="add.php" class="add-btn">
+ New Request
</a>

</div>


<?php if (!empty($_GET["success"])): ?>

<div class="alert alert-success">
<?= htmlspecialchars($_GET["success"]) ?>
</div>

<?php endif; ?>


<?php if (!empty($_GET["error"])): ?>

<div class="alert alert-error">
<?= htmlspecialchars($_GET["error"]) ?>
</div>

<?php endif; ?>


<div class="toolbar">

<form method="GET" class="search-form">

<input
    type="text"
    name="search"
    value="<?= htmlspecialchars($search) ?>"
    placeholder="Search customer, service or location..."
>


<select name="status">

<option value="">All Statuses</option>

<?php foreach ($allowed_statuses as $item): ?>

<option
    value="<?= $item ?>"
    <?= $status === $item ? "selected" : "" ?>
>
<?= ucfirst($item) ?>
</option>

<?php endforeach; ?>

</select>


<button type="submit" class="search-btn">
Search
</button>


<?php if ($search !== "" || $status !== ""): ?>

<a href="index.php" class="clear-btn">
Clear
</a>

<?php endif; ?>

</form>

</div>


<div class="table-card">

<div class="table-wrapper">

<table>

<thead>

<tr>

<th>#</th>
<th>Customer</th>
<th>Service</th>
<th>Location</th>
<th>Preferred Date</th>
<th>Status</th>
<th>Created</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php if ($requests->num_rows > 0): ?>

<?php $counter = 1; ?>

<?php while ($request = $requests->fetch_assoc()): ?>

<tr>

<td><?= $counter++ ?></td>

<td>

<div class="customer-name">
<?= htmlspecialchars($request["customer_name"]) ?>
</div>

<div class="customer-phone">
<?= htmlspecialchars($request["customer_phone"]) ?>
</div>

</td>

<td>

<div class="service-name">
<?= htmlspecialchars($request["service_name"]) ?>
</div>

</td>

<td>

<div class="location">
<?= htmlspecialchars($request["location"]) ?>
</div>

</td>

<td>

<?= !empty($request["preferred_date"])
    ? date("d M Y", strtotime($request["preferred_date"]))
    : "Not specified"
?>

</td>

<td>

<span class="status status-<?= htmlspecialchars($request["status"]) ?>">
<?= htmlspecialchars($request["status"]) ?>
</span>

</td>

<td>

<?= date(
    "d M Y",
    strtotime($request["created_at"])
) ?>

</td>

<td>

<div class="actions">

<a
    href="view.php?id=<?= $request["id"] ?>"
    class="action-btn view-btn"
>
View
</a>

<a
    href="edit.php?id=<?= $request["id"] ?>"
    class="action-btn edit-btn"
>
Edit
</a>

<a
    href="delete.php?id=<?= $request["id"] ?>"
    class="action-btn delete-btn"
>
Delete
</a>

</div>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="8" class="empty">

No service requests found.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</section>

</main>

</div>

</body>

</html>