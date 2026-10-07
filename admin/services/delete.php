<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Service
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id, service_name, price
     FROM services
     WHERE id = ?
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

$service = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Check Related Records
|--------------------------------------------------------------------------
*/

$related_records = 0;

$tables = [
    "service_requests",
    "appointments",
    "quotation_items",
    "projects"
];

foreach ($tables as $table) {

    $check = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM $table
         WHERE service_id = ?"
    );

    $check->bind_param("i", $id);
    $check->execute();

    $check_result = $check->get_result();

    $row = $check_result->fetch_assoc();

    $related_records += intval($row["total"]);

    $check->close();
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($related_records > 0) {

        header(
            "Location: index.php?error=This+service+cannot+be+deleted+because+it+is+already+used+in+business+records"
        );

        exit;
    }


    $delete = $conn->prepare(
        "DELETE FROM services
         WHERE id = ?"
    );

    $delete->bind_param("i", $id);

    if ($delete->execute()) {

        $delete->close();

        header(
            "Location: index.php?success=Service+deleted+successfully"
        );

        exit;

    } else {

        $delete->close();

        header(
            "Location: index.php?error=Unable+to+delete+service"
        );

        exit;
    }
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
        Delete Service | Green Future Flower Garden
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
            margin-bottom: 25px;
            gap: 15px;
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

        .service-info {
            margin: 20px 0;
            padding: 18px;
            background: #f5f8f6;
            border-radius: 9px;
        }

        .service-info strong {
            display: block;
            margin-bottom: 6px;
        }

        .warning-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #fff7e6;
            border: 1px solid #f1d69a;
            color: #7a5b16;
            line-height: 1.5;
        }

        .actions {
            margin-top: 25px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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
                    Delete Service
                </h2>

                <p>
                    Service record management
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

                <h1>
                    Delete Service
                </h1>

                <a
                    href="index.php"
                    class="back-btn"
                >
                    ← Back to Services
                </a>

            </div>


            <div class="delete-card">

                <div class="warning-icon">
                    !
                </div>


                <h2>
                    Confirm Service Deletion
                </h2>


                <p>
                    You are attempting to delete the following service:
                </p>


                <div class="service-info">

                    <strong>
                        <?= htmlspecialchars(
                            $service["service_name"]
                        ) ?>
                    </strong>

                    <span>
                        Price:
                        ₦<?= number_format(
                            (float)$service["price"],
                            2
                        ) ?>
                    </span>

                </div>


                <?php if ($related_records > 0): ?>

                    <div class="warning-box">

                        <strong>
                            Deletion blocked
                        </strong>

                        <br><br>

                        This service is currently connected to
                        <strong>
                            <?= $related_records ?>
                        </strong>
                        business record(s).

                        <br><br>

                        It cannot be deleted because doing so could
                        break existing customer, appointment,
                        quotation, or project records.

                        <br><br>

                        <strong>
                            Recommended action:
                        </strong>

                        Edit the service and change its status to
                        <strong>Inactive</strong> instead.

                    </div>


                    <div class="actions">

                        <a
                            href="index.php"
                            class="cancel-btn"
                        >
                            Return to Services
                        </a>

                        <a
                            href="edit.php?id=<?= $id ?>"
                            class="cancel-btn"
                        >
                            Edit Service
                        </a>

                    </div>


                <?php else: ?>


                    <div class="warning-box">

                        <strong>
                            Warning:
                        </strong>

                        This action permanently removes the service
                        from the database and cannot be undone.

                    </div>


                    <form method="POST">

                        <div class="actions">

                            <button
                                type="submit"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to permanently delete this service?');"
                            >
                                Yes, Delete Service
                            </button>

                            <a
                                href="index.php"
                                class="cancel-btn"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>