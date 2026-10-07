<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $stmt = $conn->prepare(
        "SELECT id, service_name, description, price, status, created_at
         FROM services
         WHERE service_name LIKE ?
            OR description LIKE ?
         ORDER BY id DESC"
    );

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $services = $stmt->get_result();

} else {

    $stmt = $conn->prepare(
        "SELECT id, service_name, description, price, status, created_at
         FROM services
         ORDER BY id DESC"
    );

    $stmt->execute();

    $services = $stmt->get_result();
}

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
        Services | Green Future Flower Garden
    </title>

    <link
        rel="stylesheet"
        href="/GreenFutureGarden/assets/css/admin.css"
    >

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

        .admin-layout {
            min-height: 100vh;
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
            gap: 20px;
        }

        .topbar h2 {
            margin: 0;
            font-size: 22px;
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
            font-size: 14px;
        }

        .topbar-user span {
            display: block;
            margin-top: 4px;
            color: #718078;
            font-size: 12px;
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
            display: inline-block;
            background: #2e7d32;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .add-btn:hover {
            background: #256b29;
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
            max-width: 650px;
        }

        .search-form input {
            flex: 1;
            padding: 12px 14px;
            border: 1px solid #d5ddd8;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .search-form input:focus {
            border-color: #2e7d32;
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
            min-width: 850px;
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

        tr:hover td {
            background: #fafcfb;
        }

        .service-name {
            font-weight: 700;
            color: #1d2d25;
        }

        .description {
            max-width: 300px;
            color: #68756f;
            line-height: 1.4;
        }

        .price {
            font-weight: 700;
            white-space: nowrap;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-active {
            background: #e4f5e7;
            color: #237333;
        }

        .status-inactive {
            background: #f0f1f1;
            color: #68716d;
        }

        .actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .edit-btn,
        .delete-btn {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .edit-btn {
            background: #e8f1ff;
            color: #245a9b;
        }

        .delete-btn {
            background: #fff0f0;
            color: #b32626;
        }

        .empty {
            padding: 45px 20px;
            text-align: center;
            color: #718078;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #e6f6e9;
            border: 1px solid #b8dfbe;
            color: #236b2d;
        }

        .alert-error {
            background: #fff0f0;
            border: 1px solid #f0baba;
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

            .search-form {
                flex-direction: column;
                max-width: none;
            }

        }

    </style>

</head>

<body>

<div class="admin-layout">

    <aside class="sidebar">

        <div class="sidebar-brand">

            <div class="brand-icon">
                GF
            </div>

            <div>
                <strong>Green Future</strong>
                <span>Flower Garden</span>
            </div>

        </div>

        <nav class="sidebar-nav">

            <a href="../index.php">
                Dashboard
            </a>

            <a href="../customers/index.php">
                Customers
            </a>

            <a href="index.php" class="active">
                Services
            </a>

            <a href="../requests/index.php">
                Service Requests
            </a>

            <a href="../appointments/index.php">
                Appointments
            </a>

            <a href="../quotations/index.php">
                Quotations
            </a>

            <a href="../projects/index.php">
                Projects
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="../logout.php">
                Logout
            </a>

        </div>

    </aside>


    <main class="main-content">

        <header class="topbar">

            <div>

                <h2>
                    Service Management
                </h2>

                <p>
                    Manage gardening and landscaping services.
                </p>

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

                    <h1>
                        Services
                    </h1>

                    <p>
                        Create, update and manage available services.
                    </p>

                </div>

                <a
                    href="add.php"
                    class="add-btn"
                >
                    + Add Service
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

                <form
                    method="GET"
                    class="search-form"
                >

                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search services..."
                    >

                    <button
                        type="submit"
                        class="search-btn"
                    >
                        Search
                    </button>

                    <?php if ($search !== ""): ?>

                        <a
                            href="index.php"
                            class="clear-btn"
                        >
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

                                <th>
                                    #
                                </th>

                                <th>
                                    Service
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Created
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($services->num_rows > 0): ?>

                            <?php $counter = 1; ?>

                            <?php while ($service = $services->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?= $counter++ ?>
                                    </td>

                                    <td>

                                        <div class="service-name">

                                            <?= htmlspecialchars(
                                                $service["service_name"]
                                            ) ?>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="description">

                                            <?= htmlspecialchars(
                                                $service["description"] ?: "No description"
                                            ) ?>

                                        </div>

                                    </td>

                                    <td>

                                        <span class="price">

                                            ₦<?= number_format(
                                                (float)$service["price"],
                                                2
                                            ) ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?php if ($service["status"] === "active"): ?>

                                            <span class="status status-active">
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span class="status status-inactive">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?= date(
                                            "d M Y",
                                            strtotime($service["created_at"])
                                        ) ?>

                                    </td>

                                    <td>

                                        <div class="actions">

                                            <a
                                                href="edit.php?id=<?= $service["id"] ?>"
                                                class="edit-btn"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= $service["id"] ?>"
                                                class="delete-btn"
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
                                    colspan="7"
                                    class="empty"
                                >

                                    No services found.

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