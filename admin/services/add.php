<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$errors = [];

$service_name = "";
$description = "";
$price = "";
$status = "active";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $service_name = trim($_POST["service_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $status = $_POST["status"] ?? "active";


    if ($service_name === "") {
        $errors[] = "Service name is required.";
    }


    if ($price === "") {
        $errors[] = "Service price is required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $errors[] = "Please enter a valid price.";
    }


    if (!in_array($status, ["active", "inactive"], true)) {
        $errors[] = "Invalid service status.";
    }


    // Check duplicate service name

    if (empty($errors)) {

        $check = $conn->prepare(
            "SELECT id
             FROM services
             WHERE service_name = ?
             LIMIT 1"
        );

        $check->bind_param("s", $service_name);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "A service with this name already exists.";
        }

        $check->close();
    }


    // Insert service

    if (empty($errors)) {

        $priceValue = (float)$price;

        $stmt = $conn->prepare(
            "INSERT INTO services
             (service_name, description, price, status)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssds",
            $service_name,
            $description,
            $priceValue,
            $status
        );

        if ($stmt->execute()) {

            $stmt->close();

            header(
                "Location: index.php?success=Service+added+successfully"
            );

            exit;

        } else {

            $errors[] = "Unable to add service. Please try again.";
        }

        $stmt->close();
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
        Add Service | Green Future Flower Garden
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
            margin-bottom: 25px;
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
            min-height: 130px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46,125,50,.10);
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

            .page-header {
                flex-wrap: wrap;
                gap: 15px;
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
                    Add Service
                </h2>

                <p>
                    Add a new gardening or landscaping service.
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
                    New Service
                </h1>

                <a
                    href="index.php"
                    class="back-btn"
                >
                    ← Back to Services
                </a>

            </div>


            <?php if (!empty($errors)): ?>

                <div class="error-box">

                    <ul>

                        <?php foreach ($errors as $error): ?>

                            <li>
                                <?= htmlspecialchars($error) ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <div class="form-card">

                <form method="POST">

                    <div class="form-grid">

                        <div class="form-group">

                            <label>
                                Service Name
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="service_name"
                                value="<?= htmlspecialchars($service_name) ?>"
                                placeholder="e.g. Garden Maintenance"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Price
                                <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                name="price"
                                value="<?= htmlspecialchars($price) ?>"
                                placeholder="e.g. 50000"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>


                        <div class="form-group full">

                            <label>
                                Service Description
                            </label>

                            <textarea
                                name="description"
                                placeholder="Describe what this service includes..."
                            ><?= htmlspecialchars($description) ?></textarea>

                        </div>


                        <div class="form-group">

                            <label>
                                Status
                            </label>

                            <select name="status">

                                <option
                                    value="active"
                                    <?= $status === "active" ? "selected" : "" ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    <?= $status === "inactive" ? "selected" : "" ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn-primary"
                        >
                            Save Service
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