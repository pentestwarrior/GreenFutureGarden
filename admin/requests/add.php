<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$errors = [];

$customer_id = "";
$service_id = "";
$location = "";
$preferred_date = "";
$description = "";
$status = "pending";


$customers = $conn->query(
    "SELECT id, full_name, phone
     FROM customers
     ORDER BY full_name ASC"
);


$services = $conn->query(
    "SELECT id, service_name, price
     FROM services
     WHERE status = 'active'
     ORDER BY service_name ASC"
);


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_id = intval($_POST["customer_id"] ?? 0);
    $service_id = intval($_POST["service_id"] ?? 0);
    $location = trim($_POST["location"] ?? "");
    $preferred_date = trim($_POST["preferred_date"] ?? "");
    $description = trim($_POST["description"] ?? "");


    if ($customer_id <= 0) {
        $errors[] = "Please select a customer.";
    }

    if ($service_id <= 0) {
        $errors[] = "Please select a service.";
    }

    if ($location === "") {
        $errors[] = "Service location is required.";
    }

    if ($description === "") {
        $errors[] = "Please provide a description of the requested service.";
    }


    if ($preferred_date !== "") {

        $dateObject = DateTime::createFromFormat(
            "Y-m-d",
            $preferred_date
        );

        if (!$dateObject || $dateObject->format("Y-m-d") !== $preferred_date) {
            $errors[] = "Please enter a valid preferred date.";
        }
    }


    // Verify customer exists

    if (empty($errors)) {

        $checkCustomer = $conn->prepare(
            "SELECT id
             FROM customers
             WHERE id = ?
             LIMIT 1"
        );

        $checkCustomer->bind_param(
            "i",
            $customer_id
        );

        $checkCustomer->execute();

        if ($checkCustomer->get_result()->num_rows !== 1) {
            $errors[] = "Selected customer does not exist.";
        }

        $checkCustomer->close();
    }


    // Verify service exists and is active

    if (empty($errors)) {

        $checkService = $conn->prepare(
            "SELECT id
             FROM services
             WHERE id = ?
             AND status = 'active'
             LIMIT 1"
        );

        $checkService->bind_param(
            "i",
            $service_id
        );

        $checkService->execute();

        if ($checkService->get_result()->num_rows !== 1) {
            $errors[] = "Selected service is unavailable.";
        }

        $checkService->close();
    }


    if (empty($errors)) {

        $stmt = $conn->prepare(
            "INSERT INTO service_requests
             (
                customer_id,
                service_id,
                location,
                preferred_date,
                description,
                status
             )
             VALUES (?, ?, ?, NULLIF(?, ''), ?, 'pending')"
        );

        $stmt->bind_param(
            "iisss",
            $customer_id,
            $service_id,
            $location,
            $preferred_date,
            $description
        );

        if ($stmt->execute()) {

            $stmt->close();

            header(
                "Location: index.php?success=Service+request+created+successfully"
            );

            exit;

        } else {

            $errors[] = "Unable to create service request.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Service Request | Green Future Flower Garden</title>

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
    font-size: 26px;
}

.back-btn {
    background: #e9efeb;
    color: #26332d;
    text-decoration: none;
    padding: 11px 17px;
    border-radius: 8px;
    font-weight: 600;
}

.form-card {
    max-width: 900px;
    background: white;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0,0,0,.06);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 14px;
}

.required {
    color: #d32f2f;
}

input,
textarea,
select {
    width: 100%;
    padding: 13px 14px;
    border: 1px solid #d5ddd8;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    font-family: inherit;
}

textarea {
    min-height: 140px;
    resize: vertical;
}

input:focus,
textarea:focus,
select:focus {
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46,125,50,.10);
}

.help {
    color: #718078;
    font-size: 12px;
    margin-top: 6px;
}

.form-actions {
    margin-top: 28px;
    display: flex;
    gap: 12px;
}

.btn-primary {
    border: none;
    background: #2e7d32;
    color: white;
    padding: 12px 22px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.btn-secondary {
    background: #eef2f0;
    color: #26332d;
    text-decoration: none;
    padding: 12px 22px;
    border-radius: 8px;
    font-weight: 600;
}

.error-box {
    max-width: 900px;
    background: #fff0f0;
    border: 1px solid #f2b8b8;
    color: #a32020;
    padding: 15px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.error-box ul {
    margin: 0;
    padding-left: 20px;
}

@media (max-width: 900px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
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

    .form-card {
        padding: 20px;
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

<h2>New Service Request</h2>

<p>Create a customer service request.</p>

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

<h1>Create Service Request</h1>

<a href="index.php" class="back-btn">
← Back to Requests
</a>

</div>


<?php if (!empty($errors)): ?>

<div class="error-box">

<ul>

<?php foreach ($errors as $error): ?>

<li><?= htmlspecialchars($error) ?></li>

<?php endforeach; ?>

</ul>

</div>

<?php endif; ?>


<div class="form-card">

<form method="POST">

<div class="form-grid">


<div class="form-group">

<label>
Customer <span class="required">*</span>
</label>

<select name="customer_id" required>

<option value="">
Select Customer
</option>

<?php while ($customer = $customers->fetch_assoc()): ?>

<option
    value="<?= $customer["id"] ?>"
    <?= $customer_id == $customer["id"] ? "selected" : "" ?>
>

<?= htmlspecialchars($customer["full_name"]) ?>
-
<?= htmlspecialchars($customer["phone"]) ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>
Service <span class="required">*</span>
</label>

<select name="service_id" required>

<option value="">
Select Service
</option>

<?php while ($service = $services->fetch_assoc()): ?>

<option
    value="<?= $service["id"] ?>"
    <?= $service_id == $service["id"] ? "selected" : "" ?>
>

<?= htmlspecialchars($service["service_name"]) ?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group full">

<label>
Service Location <span class="required">*</span>
</label>

<input
    type="text"
    name="location"
    value="<?= htmlspecialchars($location) ?>"
    placeholder="e.g. No. 15 Garden Street, Kaduna"
    required
>

</div>


<div class="form-group">

<label>
Preferred Date
</label>

<input
    type="date"
    name="preferred_date"
    value="<?= htmlspecialchars($preferred_date) ?>"
>

<span class="help">
Optional date requested by the customer.
</span>

</div>


<div class="form-group full">

<label>
Request Description <span class="required">*</span>
</label>

<textarea
    name="description"
    placeholder="Describe what the customer needs..."
    required
><?= htmlspecialchars($description) ?></textarea>

</div>


</div>


<div class="form-actions">

<button
    type="submit"
    class="btn-primary"
>
Create Request
</button>

<a
    href="index.php"
    class="btn-secondary"
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