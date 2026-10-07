<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$allowed_statuses = [
    'draft',
    'sent',
    'accepted',
    'rejected',
    'expired'
];

$sql = "
    SELECT
        q.id,
        q.quotation_number,
        q.quotation_date,
        q.valid_until,
        q.subtotal,
        q.discount,
        q.total,
        q.status,
        q.created_at,
        c.full_name AS customer_name,
        c.phone AS customer_phone
    FROM quotations q
    INNER JOIN customers c ON q.customer_id = c.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== '') {

    $sql .= "
        AND (
            q.quotation_number LIKE ?
            OR c.full_name LIKE ?
            OR c.phone LIKE ?
        )
    ";

    $term = "%" . $search . "%";

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;

    $types .= "sss";
}

if (in_array($status, $allowed_statuses, true)) {

    $sql .= " AND q.status = ?";

    $params[] = $status;
    $types .= "s";
}

$sql .= "
    ORDER BY q.id DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Quotations | Green Future Flower Garden</title>

<link rel="stylesheet" href="/GreenFutureGarden/assets/css/admin.css">

<style>

.sidebar {
    width: 250px;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
    z-index: 1000;
}

.main-content {
    margin-left: 250px;
    width: calc(100% - 250px);
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filter-box,
.table-container {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
}

.filter-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-form input,
.filter-form select {
    padding: 11px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.filter-form input {
    flex: 1;
    min-width: 230px;
}

.btn {
    display: inline-block;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #198754;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-view {
    background: #0d6efd;
    color: white;
}

.btn-edit {
    background: #ffc107;
    color: #222;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 13px;
    border-bottom: 1px solid #eee;
    text-align: left;
    white-space: nowrap;
}

th {
    background: #f8f9fa;
}

.status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-draft {
    background: #e2e3e5;
    color: #41464b;
}

.status-sent {
    background: #cfe2ff;
    color: #084298;
}

.status-accepted {
    background: #d1e7dd;
    color: #0f5132;
}

.status-rejected {
    background: #f8d7da;
    color: #842029;
}

.status-expired {
    background: #fff3cd;
    color: #856404;
}

.alert {
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d1e7dd;
    color: #0f5132;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
}

.actions {
    display: flex;
    gap: 5px;
}

@media (max-width: 900px) {

    .sidebar {
        position: relative;
        width: 100%;
    }

    .main-content {
        margin-left: 0;
        width: 100%;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}

</style>

</head>

<body>

<div class="sidebar">

    <div class="sidebar-header">
        <h2>Green Future</h2>
        <p>Admin Panel</p>
    </div>

    <ul class="sidebar-menu">

        <li><a href="../index.php">Dashboard</a></li>

        <li><a href="../customers/index.php">Customers</a></li>

        <li><a href="../services/index.php">Services</a></li>

        <li><a href="../requests/index.php">Service Requests</a></li>

        <li><a href="../appointments/index.php">Appointments</a></li>

        <li>
            <a href="index.php" class="active">
                Quotations
            </a>
        </li>

        <li><a href="../projects/index.php">Projects</a></li>

        <li><a href="../logout.php">Logout</a></li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <div>
            <h3>Quotation Management</h3>
            <p>Create and manage customer quotations.</p>
        </div>

        <div>
            Welcome,
            <strong>
                <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?>
            </strong>
        </div>

    </div>


    <div class="content">

        <?php if ($success): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <div class="page-header">

            <div>

                <h1>Quotations</h1>

                <p>
                    Manage service quotations and pricing.
                </p>

            </div>

            <a href="add.php" class="btn btn-primary">
                + Create Quotation
            </a>

        </div>


        <div class="filter-box">

            <form method="GET" class="filter-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search quotation number, customer or phone..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="status">

                    <option value="">
                        All Statuses
                    </option>

                    <?php foreach ($allowed_statuses as $item): ?>

                        <option
                            value="<?= $item ?>"
                            <?= $status === $item ? 'selected' : '' ?>
                        >
                            <?= ucfirst($item) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Search
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Reset
                </a>

            </form>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Quotation No.</th>

                        <th>Customer</th>

                        <th>Date</th>

                        <th>Valid Until</th>

                        <th>Subtotal</th>

                        <th>Discount</th>

                        <th>Total</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                #<?= (int)$row['id'] ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($row['quotation_number']) ?>
                                </strong>
                            </td>

                            <td>

                                <?= htmlspecialchars($row['customer_name']) ?>

                                <br>

                                <small>
                                    <?= htmlspecialchars($row['customer_phone']) ?>
                                </small>

                            </td>

                            <td>
                                <?= date(
                                    'd M Y',
                                    strtotime($row['quotation_date'])
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    'd M Y',
                                    strtotime($row['valid_until'])
                                ) ?>
                            </td>

                            <td>
                                ₦<?= number_format(
                                    (float)$row['subtotal'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                ₦<?= number_format(
                                    (float)$row['discount'],
                                    2
                                ) ?>
                            </td>

                            <td>

                                <strong>

                                    ₦<?= number_format(
                                        (float)$row['total'],
                                        2
                                    ) ?>

                                </strong>

                            </td>

                            <td>

                                <span class="status status-<?= htmlspecialchars($row['status']) ?>">

                                    <?= ucfirst($row['status']) ?>

                                </span>

                            </td>

                            <td>

                                <div class="actions">

                                    <a
                                        href="view.php?id=<?= (int)$row['id'] ?>"
                                        class="btn btn-view"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="edit.php?id=<?= (int)$row['id'] ?>"
                                        class="btn btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete.php?id=<?= (int)$row['id'] ?>"
                                        class="btn btn-delete"
                                        onclick="return confirm('Delete this quotation?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="10"
                            style="text-align:center;padding:30px;"
                        >
                            No quotations found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>