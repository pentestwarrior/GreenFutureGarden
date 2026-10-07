<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$errors = [];

$full_name = "";
$email = "";
$phone = "";
$address = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");

    // Validation
    if ($full_name === "") {
        $errors[] = "Customer full name is required.";
    }

    if ($phone === "") {
        $errors[] = "Phone number is required.";
    }

    if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Check duplicate email
    if ($email !== "") {

        $check = $conn->prepare(
            "SELECT id FROM customers WHERE email = ? LIMIT 1"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "A customer with this email address already exists.";
        }

        $check->close();
    }

    // Save customer
    if (empty($errors)) {

        $stmt = $conn->prepare(
            "INSERT INTO customers (full_name, email, phone, address)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $full_name,
            $email,
            $phone,
            $address
        );

        if ($stmt->execute()) {

            $stmt->close();

            header("Location: index.php?success=Customer+added+successfully");
            exit;

        } else {

            $errors[] = "Unable to add customer. Please try again.";
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

    <title>Add Customer | Green Future Flower Garden</title>

    <!-- Correct absolute CSS path -->
    <link rel="stylesheet" href="/GreenFutureGarden/assets/css/admin.css">

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7f6;
            color: #26332d;
        }

        /* MAIN LAYOUT */

        .admin-layout {
            min-height: 100vh;
            width: 100%;
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

        /* SIDEBAR */

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
            background: #ffffff;
            color: #124d35;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .sidebar-brand strong {
            display: block;
            font-size: 16px;
        }

        .sidebar-brand span {
            display: block;
            font-size: 12px;
            margin-top: 3px;
            opacity: 0.8;
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
            transition: 0.2s;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.12);
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

        .sidebar-bottom a:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        /* TOPBAR */

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
            white-space: nowrap;
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

        /* CONTENT */

        .content {
            padding: 30px;
            width: 100%;
        }

        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .page-actions h1 {
            margin: 0;
            font-size: 26px;
        }

        .back-btn {
            display: inline-block;
            padding: 11px 17px;
            background: #e9efeb;
            color: #26332d;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .back-btn:hover {
            background: #dce6e0;
        }

        /* FORM */

        .form-card {
            background: white;
            border-radius: 14px;
            padding: 30px;
            width: 100%;
            max-width: 900px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #26332d;
            font-size: 14px;
        }

        .required {
            color: #d32f2f;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d5ddd8;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background: white;
        }

        .form-group input:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.10);
        }

        /* BUTTONS */

        .form-actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            background: #2e7d32;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary:hover {
            background: #256b29;
        }

        .btn-secondary {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 8px;
            background: #eef2f0;
            color: #26332d;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-secondary:hover {
            background: #dfe7e2;
        }

        /* ERRORS */

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

        .error-box li {
            margin-bottom: 5px;
        }

        /* TABLET */

        @media (max-width: 1000px) {

            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
                width: calc(100% - 220px);
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

        }

        /* MOBILE */

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

            .page-actions {
                align-items: flex-start;
            }

            .page-actions h1 {
                font-size: 22px;
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

                <h2>Add Customer</h2>

                <p>
                    Register a new Green Future Flower Garden customer.
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

                <div>

                    <h1>
                        New Customer
                    </h1>

                </div>

                <a href="index.php" class="back-btn">
                    ← Back to Customers
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

                        <!-- FULL NAME -->

                        <div class="form-group">

                            <label>
                                Full Name
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                value="<?= htmlspecialchars($full_name) ?>"
                                placeholder="Enter customer's full name"
                                required
                            >

                        </div>


                        <!-- PHONE -->

                        <div class="form-group">

                            <label>
                                Phone Number
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="phone"
                                value="<?= htmlspecialchars($phone) ?>"
                                placeholder="e.g. 08012345678"
                                required
                            >

                        </div>


                        <!-- EMAIL -->

                        <div class="form-group">

                            <label>
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="<?= htmlspecialchars($email) ?>"
                                placeholder="customer@example.com"
                            >

                        </div>


                        <!-- ADDRESS -->

                        <div class="form-group">

                            <label>
                                Address
                            </label>

                            <input
                                type="text"
                                name="address"
                                value="<?= htmlspecialchars($address) ?>"
                                placeholder="Customer address"
                            >

                        </div>

                    </div>


                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn-primary"
                        >
                            Save Customer
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