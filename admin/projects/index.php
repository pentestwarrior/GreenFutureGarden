<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$allowed_statuses = [
    'planning',
    'in_progress',
    'on_hold',
    'completed',
    'cancelled'
];

$sql = "
    SELECT
        p.id,
        p.project_name,
        p.location,
        p.start_date,
        p.expected_completion_date,
        p.actual_completion_date,
        p.budget,
        p.status,
        p.created_at,
        c.full_name AS customer_name,
        s.service_name
    FROM projects p
    INNER JOIN customers c
        ON p.customer_id = c.id
    LEFT JOIN services s
        ON p.service_id = s.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== '') {

    $sql .= "
        AND (
            p.project_name LIKE ?
            OR p.location LIKE ?
            OR c.full_name LIKE ?
            OR s.service_name LIKE ?
        )
    ";

    $term = "%" . $search . "%";

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;

    $types .= "ssss";
}

if (in_array($status, $allowed_statuses, true)) {

    $sql .= " AND p.status = ?";

    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY p.id DESC";

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

<title>Projects | Green Future Flower Garden</title>

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
    min-width: 250px;
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
    color: #fff;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
}

.btn-view {
    background: #0d6efd;
    color: #fff;
}

.btn-edit {
    background: #ffc107;
    color: #222;
}

.btn-delete {
    background: #dc3545;
    color: #fff;
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
}

th {
    background: #f8f9fa;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-planning {
    background: #e2e3e5;
    color: #41464b;
}

.status-in_progress {
    background: #cfe2ff;
    color: #084298;
}

.status-on_hold {
    background: #fff3cd;
    color: #856404;
}

.status-completed {
    background: #d1e7dd;
    color: #0f5132;
}

.status-cancelled {
    background: #f8d7da;
    color: #842029;
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
    flex-wrap: wrap;
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

    .table-container {
        overflow-x: auto;
    }

    table {
        min-width: 1000px;
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

        <li>
            <a href="../index.php">
                Dashboard
            </a>
        </li>

        <li>
            <a href="../customers/index.php">
                Customers
            </a>
        </li>

        <li>
            <a href="../services/index.php">
                Services
            </a>
        </li>

        <li>
            <a href="../requests/index.php">
                Service Requests
            </a>
        </li>

        <li>
            <a href="../appointments/index.php">
                Appointments
            </a>
        </li>

        <li>
            <a href="../quotations/index.php">
                Quotations
            </a>
        </li>

        <li>
            <a href="index.php" class="active">
                Projects
            </a>
        </li>

        <li>
            <a href="../logout.php">
                Logout
            </a>
        </li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <div>

            <h3>Project Management</h3>

            <p>
                Track gardening and landscaping projects.
            </p>

        </div>

        <div>

            Welcome,
            <strong>
                <?= htmlspecialchars(
                    $_SESSION['full_name'] ?? 'Admin'
                ) ?>
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

                <h1>
                    Projects
                </h1>

                <p>
                    Manage and monitor customer projects.
                </p>

            </div>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                + Add Project
            </a>

        </div>


        <div class="filter-box">

            <form
                method="GET"
                class="filter-form"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="Search project, customer, service or location..."
                    value="<?= htmlspecialchars($search) ?>"
                >


                <select name="status">

                    <option value="">
                        All Statuses
                    </option>

                    <?php foreach (
                        $allowed_statuses as $item
                    ): ?>

                        <option
                            value="<?= $item ?>"
                            <?= $status === $item
                                ? 'selected'
                                : '' ?>
                        >

                            <?= ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $item
                                )
                            ) ?>

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

                        <th>
                            ID
                        </th>

                        <th>
                            Project
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Service
                        </th>

                        <th>
                            Location
                        </th>

                        <th>
                            Start Date
                        </th>

                        <th>
                            Expected Completion
                        </th>

                        <th>
                            Budget
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while (
                        $row = $result->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                #<?= (int)$row['id'] ?>
                            </td>


                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $row['project_name']
                                    ) ?>
                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['customer_name']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['service_name']
                                    ?: 'Not specified'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['location']
                                ) ?>

                            </td>


                            <td>

                                <?= $row['start_date']
                                    ? date(
                                        'd M Y',
                                        strtotime(
                                            $row['start_date']
                                        )
                                    )
                                    : 'Not set' ?>

                            </td>


                            <td>

                                <?= $row['expected_completion_date']
                                    ? date(
                                        'd M Y',
                                        strtotime(
                                            $row['expected_completion_date']
                                        )
                                    )
                                    : 'Not set' ?>

                            </td>


                            <td>

                                ₦<?= number_format(
                                    (float)$row['budget'],
                                    2
                                ) ?>

                            </td>


                            <td>

                                <span
                                    class="status status-<?= htmlspecialchars(
                                        $row['status']
                                    ) ?>"
                                >

                                    <?= ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $row['status']
                                        )
                                    ) ?>

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
                                        onclick="return confirm('Delete this project?');"
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
                            style="text-align:center;padding:35px;"
                        >

                            No projects found.

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