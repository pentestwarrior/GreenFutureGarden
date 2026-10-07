<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$error = "";

$customers = $conn->query("
    SELECT id, full_name, phone
    FROM customers
    ORDER BY full_name ASC
");

$services = $conn->query("
    SELECT id, service_name, price
    FROM services
    WHERE status = 'active'
    ORDER BY service_name ASC
");


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $service_id = (int)($_POST['service_id'] ?? 0);
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $status = $_POST['status'] ?? 'pending';


    $allowed_statuses = [
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ];


    if ($customer_id <= 0) {

        $error = "Please select a customer.";

    } elseif ($service_id <= 0) {

        $error = "Please select a service.";

    } elseif ($appointment_date === '') {

        $error = "Please select an appointment date.";

    } elseif ($appointment_time === '') {

        $error = "Please select an appointment time.";

    } elseif ($location === '') {

        $error = "Please enter the appointment location.";

    } elseif (!in_array($status, $allowed_statuses, true)) {

        $error = "Invalid appointment status.";

    } else {

        $customerCheck = $conn->prepare("
            SELECT id
            FROM customers
            WHERE id = ?
            LIMIT 1
        ");

        $customerCheck->bind_param("i", $customer_id);
        $customerCheck->execute();

        if ($customerCheck->get_result()->num_rows === 0) {

            $error = "Selected customer does not exist.";

        } else {

            $serviceCheck = $conn->prepare("
                SELECT id
                FROM services
                WHERE id = ?
                AND status = 'active'
                LIMIT 1
            ");

            $serviceCheck->bind_param("i", $service_id);
            $serviceCheck->execute();

            if ($serviceCheck->get_result()->num_rows === 0) {

                $error = "Selected service does not exist or is inactive.";

            } else {

                $stmt = $conn->prepare("
                    INSERT INTO appointments
                    (
                        customer_id,
                        service_id,
                        appointment_date,
                        appointment_time,
                        location,
                        notes,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "iisssss",
                    $customer_id,
                    $service_id,
                    $appointment_date,
                    $appointment_time,
                    $location,
                    $notes,
                    $status
                );


                if ($stmt->execute()) {

                    header("Location: index.php?success=Appointment created successfully.");
                    exit;

                } else {

                    $error = "Failed to create appointment.";
                }
            }
        }
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

    <title>Add Appointment | Green Future Flower Garden</title>

    <link
        rel="stylesheet"
        href="/GreenFutureGarden/assets/css/admin.css"
    >

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

        .form-card {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            max-width: 850px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            margin-bottom: 7px;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        .form-actions {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 11px 18px;
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

        .alert {
            background: #f8d7da;
            color: #842029;
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
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

        <li><a href="index.php" class="active">Appointments</a></li>

        <li><a href="../quotations/index.php">Quotations</a></li>

        <li><a href="../projects/index.php">Projects</a></li>

        <li><a href="../logout.php">Logout</a></li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <div>

            <h3>Add Appointment</h3>

            <p>Create a new customer appointment.</p>

        </div>

        <div>

            Welcome,
            <strong>
                <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?>
            </strong>

        </div>

    </div>


    <div class="content">

        <?php if ($error): ?>

            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <div class="form-card">

            <h1>New Appointment</h1>

            <p>
                Schedule an appointment for a gardening or landscaping service.
            </p>


            <form method="POST">

                <div class="form-grid">


                    <div class="form-group">

                        <label for="customer_id">
                            Customer *
                        </label>

                        <select
                            name="customer_id"
                            id="customer_id"
                            required
                        >

                            <option value="">
                                Select Customer
                            </option>

                            <?php while ($customer = $customers->fetch_assoc()): ?>

                                <option
                                    value="<?= (int)$customer['id'] ?>"
                                    <?= ($_POST['customer_id'] ?? '') == $customer['id'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($customer['full_name']) ?>

                                    -
                                    <?= htmlspecialchars($customer['phone']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="service_id">
                            Service *
                        </label>

                        <select
                            name="service_id"
                            id="service_id"
                            required
                        >

                            <option value="">
                                Select Service
                            </option>

                            <?php while ($service = $services->fetch_assoc()): ?>

                                <option
                                    value="<?= (int)$service['id'] ?>"
                                    <?= ($_POST['service_id'] ?? '') == $service['id'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($service['service_name']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="appointment_date">
                            Appointment Date *
                        </label>

                        <input
                            type="date"
                            name="appointment_date"
                            id="appointment_date"
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($_POST['appointment_date'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="appointment_time">
                            Appointment Time *
                        </label>

                        <input
                            type="time"
                            name="appointment_time"
                            id="appointment_time"
                            value="<?= htmlspecialchars($_POST['appointment_time'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="form-group full">

                        <label for="location">
                            Location *
                        </label>

                        <input
                            type="text"
                            name="location"
                            id="location"
                            placeholder="e.g. No. 10 Kaduna Street, Kaduna"
                            value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="form-group full">

                        <label for="status">
                            Status
                        </label>

                        <select name="status" id="status">

                            <?php

                            $statuses = [
                                'pending',
                                'confirmed',
                                'completed',
                                'cancelled'
                            ];

                            foreach ($statuses as $item):

                            ?>

                                <option
                                    value="<?= $item ?>"
                                    <?= ($_POST['status'] ?? 'pending') === $item ? 'selected' : '' ?>
                                >

                                    <?= ucfirst($item) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group full">

                        <label for="notes">
                            Notes
                        </label>

                        <textarea
                            name="notes"
                            id="notes"
                            placeholder="Additional appointment information..."
                        ><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>

                    </div>


                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Appointment
                    </button>

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>