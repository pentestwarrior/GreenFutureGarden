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
| Check if customer exists
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id FROM customers WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Check Related Records
|--------------------------------------------------------------------------
|
| A customer may already have:
| - Service Requests
| - Appointments
| - Quotations
| - Projects
|
| We don't want to accidentally delete important business records.
|--------------------------------------------------------------------------
*/

$related_records = 0;

$tables = [
    "service_requests",
    "appointments",
    "quotations",
    "projects"
];

foreach ($tables as $table) {

    $check = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM $table
         WHERE customer_id = ?"
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
| Delete Customer
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($related_records > 0) {

        header(
            "Location: index.php?error=This+customer+cannot+be+deleted+because+related+records+exist"
        );

        exit;
    }


    $delete = $conn->prepare(
        "DELETE FROM customers WHERE id = ?"
    );

    $delete->bind_param("i", $id);

    if ($delete->execute()) {

        $delete->close();

        header(
            "Location: index.php?success=Customer+deleted+successfully"
        );

        exit;

    } else {

        $delete->close();

        header(
            "Location: index.php?error=Unable+to+delete+customer"
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
        Delete Customer | Green Future Flower Garden
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
            opacity: 0.8;
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
            background: rgba(255,255,255,0.12);
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

        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-actions h1 {
            margin: 0;
            font-size: 26px;
        }

        .back-btn {
            padding: 11px 17px;
            background: #e9efeb;
            color: #26332d;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        .delete-card {
            max-width: 650px;
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        }

        .warning-icon {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: #fff1f1;
            color: #d32f2f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin-bottom: 20px;
        }

        .delete-card h2 {
            margin: 0 0 10px;
        }

        .delete-card p {
            color: #66736c;
            line-height: 1.6;
        }

        .warning-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #fff7e6;
            border: 1px solid #f1d69a;
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

        .delete-btn:hover {
            background: #a91f1f;
        }

        .cancel-btn {
            padding: 12px 20px;
            background: #eef2f0;
            color: #26332d;
            text-decoration: none;
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

    <!-- SIDEBAR -->

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

            <a href="index.php" class="active">
                Customers
            </a>

            <a href="../services/index.php">
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


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <header class="topbar">

            <div>

                <h2>
                    Delete Customer
                </h2>

                <p>
                    Customer record management
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

            <div class="page-actions">

                <h1>
                    Delete Customer
                </h1>

                <a href="index.php" class="back-btn">
                    ← Back to Customers
                </a>

            </div>


            <div class="delete-card">

                <div class="warning-icon">
                    !
                </div>


                <h2>
                    Confirm Customer Deletion
                </h2>


                <?php if ($related_records > 0): ?>

                    <p>
                        This customer cannot be deleted because
                        there are existing records connected to this
                        customer.
                    </p>

                    <div class="warning-box">

                        <strong>
                            Deletion blocked
                        </strong>

                        <br><br>

                        This customer has
                        <strong>
                            <?= $related_records ?>
                        </strong>
                        related business record(s).

                        <br><br>

                        To protect the integrity of the system,
                        the customer must remain in the database.

                    </div>


                    <div class="actions">

                        <a
                            href="index.php"
                            class="cancel-btn"
                        >
                            Return to Customers
                        </a>

                    </div>


                <?php else: ?>


                    <p>
                        You are about to permanently delete this
                        customer record.
                    </p>


                    <div class="warning-box">

                        <strong>
                            Warning:
                        </strong>

                        This action cannot be undone.

                    </div>


                    <form method="POST">

                        <div class="actions">

                            <button
                                type="submit"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to permanently delete this customer?');"
                            >
                                Yes, Delete Customer
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