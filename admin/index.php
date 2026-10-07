<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

function getCount($conn, $table)
{
    $allowed_tables = [
        "customers",
        "services",
        "service_requests",
        "appointments",
        "projects",
        "quotations"
    ];

    if (!in_array($table, $allowed_tables, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

    if ($result) {
        $row = $result->fetch_assoc();
        return (int) $row["total"];
    }

    return 0;
}

$total_customers = getCount($conn, "customers");
$total_services = getCount($conn, "services");
$total_requests = getCount($conn, "service_requests");
$total_appointments = getCount($conn, "appointments");
$total_projects = getCount($conn, "projects");
$total_quotations = getCount($conn, "quotations");

/*
|--------------------------------------------------------------------------
| Recent Service Requests
|--------------------------------------------------------------------------
*/

$recent_requests = [];

$request_sql = "
    SELECT
        sr.id,
        c.full_name AS customer_name,
        s.service_name,
        sr.status,
        sr.created_at
    FROM service_requests sr
    INNER JOIN customers c
        ON sr.customer_id = c.id
    INNER JOIN services s
        ON sr.service_id = s.id
    ORDER BY sr.created_at DESC
    LIMIT 5
";

$request_result = $conn->query($request_sql);

if ($request_result) {
    while ($row = $request_result->fetch_assoc()) {
        $recent_requests[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| Recent Projects
|--------------------------------------------------------------------------
*/

$recent_projects = [];

$project_sql = "
    SELECT
        p.id,
        p.project_name,
        c.full_name AS customer_name,
        p.status,
        p.created_at
    FROM projects p
    INNER JOIN customers c
        ON p.customer_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 5
";

$project_result = $conn->query($project_sql);

if ($project_result) {
    while ($row = $project_result->fetch_assoc()) {
        $recent_projects[] = $row;
    }
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$role = $_SESSION["role"] ?? "admin";

$first_name = explode(" ", trim($full_name))[0];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard | Green Future Flower Garden
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<div class="dashboard">

    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar">

        <div class="logo">

            <h2>
                Green Future
            </h2>

            <p>
                Flower Garden Management
            </p>

        </div>

        <div class="nav-title">
            Main Menu
        </div>

        <ul class="nav-menu">

            <li>
                <a
                    href="index.php"
                    class="active"
                >
                    <span class="nav-icon">⌂</span>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="customers/index.php">
                    <span class="nav-icon">♙</span>
                    <span>Customers</span>
                </a>
            </li>

            <li>
                <a href="services/index.php">
                    <span class="nav-icon">✿</span>
                    <span>Services</span>
                </a>
            </li>

            <li>
                <a href="requests/index.php">
                    <span class="nav-icon">▣</span>
                    <span>Service Requests</span>
                </a>
            </li>

            <li>
                <a href="appointments/index.php">
                    <span class="nav-icon">◷</span>
                    <span>Appointments</span>
                </a>
            </li>

            <li>
                <a href="quotations/index.php">
                    <span class="nav-icon">▤</span>
                    <span>Quotations</span>
                </a>
            </li>

            <li>
                <a href="projects/index.php">
                    <span class="nav-icon">◆</span>
                    <span>Projects</span>
                </a>
            </li>

        </ul>

        <div class="logout-link">

            <ul class="nav-menu">

                <li>
                    <a href="logout.php">
                        <span class="nav-icon">↪</span>
                        <span>Logout</span>
                    </a>
                </li>

            </ul>

        </div>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================== -->

    <main class="main">

        <!-- TOPBAR -->

        <header class="topbar">

            <div class="topbar-title">

                <h1>
                    Dashboard
                </h1>

                <p>
                    Garden Management System
                </p>

            </div>

            <div class="user-area">

                <div class="user-avatar">
                    <?= htmlspecialchars(strtoupper(substr($first_name, 0, 1))) ?>
                </div>

                <div class="user-info">

                    <strong>
                        <?= htmlspecialchars($full_name) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(ucfirst($role)) ?>
                    </span>

                </div>

            </div>

        </header>


        <!-- CONTENT -->

        <section class="content">

            <!-- WELCOME -->

            <div class="welcome">

                <h2>
                    Welcome back, <?= htmlspecialchars($first_name) ?> 👋
                </h2>

                <p>
                    Here's an overview of Green Future Flower Garden's
                    current activities and operations.
                </p>

            </div>


            <!-- STATISTICS -->

            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Total Customers
                        </span>

                        <div class="stat-icon">
                            ♙
                        </div>

                    </div>

                    <div class="stat-number">
                        <?= $total_customers ?>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Service Requests
                        </span>

                        <div class="stat-icon">
                            ▣
                        </div>

                    </div>

                    <div class="stat-number">
                        <?= $total_requests ?>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Appointments
                        </span>

                        <div class="stat-icon">
                            ◷
                        </div>

                    </div>

                    <div class="stat-number">
                        <?= $total_appointments ?>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Active Projects
                        </span>

                        <div class="stat-icon">
                            ◆
                        </div>

                    </div>

                    <div class="stat-number">
                        <?= $total_projects ?>
                    </div>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="section-title">

                <h2>
                    Quick Actions
                </h2>

            </div>

            <div class="quick-actions">

                <a
                    href="customers/add.php"
                    class="quick-action"
                >

                    <strong>
                        + Add Customer
                    </strong>

                    <span>
                        Register a new customer
                    </span>

                </a>


                <a
                    href="services/add.php"
                    class="quick-action"
                >

                    <strong>
                        + Add Service
                    </strong>

                    <span>
                        Create a new garden service
                    </span>

                </a>


                <a
                    href="requests/index.php"
                    class="quick-action"
                >

                    <strong>
                        View Requests
                    </strong>

                    <span>
                        Review customer requests
                    </span>

                </a>


                <a
                    href="projects/add.php"
                    class="quick-action"
                >

                    <strong>
                        + New Project
                    </strong>

                    <span>
                        Create a project record
                    </span>

                </a>

            </div>


            <!-- RECENT DATA -->

            <div class="dashboard-grid">


                <!-- SERVICE REQUESTS -->

                <div class="panel">

                    <div class="panel-header">

                        <h3>
                            Recent Service Requests
                        </h3>

                        <a href="requests/index.php">
                            View all
                        </a>

                    </div>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Service
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php if (empty($recent_requests)): ?>

                                <tr>

                                    <td
                                        colspan="3"
                                        class="empty"
                                    >
                                        No service requests yet.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($recent_requests as $request): ?>

                                    <?php
                                    $status_class =
                                        "status-" .
                                        str_replace(
                                            " ",
                                            "-",
                                            strtolower($request["status"])
                                        );
                                    ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($request["customer_name"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($request["service_name"]) ?>
                                        </td>

                                        <td>

                                            <span
                                                class="status <?= htmlspecialchars($status_class) ?>"
                                            >
                                                <?= htmlspecialchars(ucfirst($request["status"])) ?>
                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- PROJECTS -->

                <div class="panel">

                    <div class="panel-header">

                        <h3>
                            Recent Projects
                        </h3>

                        <a href="projects/index.php">
                            View all
                        </a>

                    </div>

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        Project
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php if (empty($recent_projects)): ?>

                                <tr>

                                    <td
                                        colspan="3"
                                        class="empty"
                                    >
                                        No projects yet.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($recent_projects as $project): ?>

                                    <?php
                                    $project_status_class =
                                        "status-" .
                                        str_replace(
                                            "_",
                                            "-",
                                            strtolower($project["status"])
                                        );
                                    ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($project["project_name"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($project["customer_name"]) ?>
                                        </td>

                                        <td>

                                            <span
                                                class="status <?= htmlspecialchars($project_status_class) ?>"
                                            >
                                                <?= htmlspecialchars(
                                                    ucwords(
                                                        str_replace(
                                                            "_",
                                                            " ",
                                                            $project["status"]
                                                        )
                                                    )
                                                ) ?>
                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>