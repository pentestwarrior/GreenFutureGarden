<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php?error=Invalid appointment ID.");
    exit;
}

$stmt = $conn->prepare("
    SELECT
        a.*,
        c.full_name AS customer_name,
        c.email AS customer_email,
        c.phone AS customer_phone,
        c.address AS customer_address,
        s.service_name,
        s.description AS service_description,
        s.price AS service_price
    FROM appointments a
    INNER JOIN customers c ON a.customer_id = c.id
    INNER JOIN services s ON a.service_id = s.id
    WHERE a.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php?error=Appointment not found.");
    exit;
}

$appointment = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View Appointment | Green Future Flower Garden</title>

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
        }

        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        .details-card {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            max-width: 900px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            padding: 14px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .detail-item strong {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            color: #666;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-completed {
            background: #cff4fc;
            color: #055160;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: #198754;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
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

            .detail-grid {
                grid-template-columns: 1fr;
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

            <h3>Appointment Details</h3>

            <p>View complete appointment information.</p>

        </div>

    </div>


    <div class="content">

        <div class="details-card">


            <div class="section">

                <h2>Appointment Information</h2>

                <div class="detail-grid">

                    <div class="detail-item">

                        <strong>Appointment ID</strong>

                        #<?= (int)$appointment['id'] ?>

                    </div>


                    <div class="detail-item">

                        <strong>Status</strong>

                        <span
                            class="status status-<?= htmlspecialchars($appointment['status']) ?>"
                        >
                            <?= ucfirst($appointment['status']) ?>
                        </span>

                    </div>


                    <div class="detail-item">

                        <strong>Date</strong>

                        <?= date(
                            'd M Y',
                            strtotime($appointment['appointment_date'])
                        ) ?>

                    </div>


                    <div class="detail-item">

                        <strong>Time</strong>

                        <?= date(
                            'h:i A',
                            strtotime($appointment['appointment_time'])
                        ) ?>

                    </div>


                    <div class="detail-item">

                        <strong>Location</strong>

                        <?= htmlspecialchars($appointment['location']) ?>

                    </div>


                    <div class="detail-item">

                        <strong>Created</strong>

                        <?= date(
                            'd M Y h:i A',
                            strtotime($appointment['created_at'])
                        ) ?>

                    </div>

                </div>

            </div>


            <div class="section">

                <h2>Customer Information</h2>

                <div class="detail-grid">

                    <div class="detail-item">

                        <strong>Full Name</strong>

                        <?= htmlspecialchars($appointment['customer_name']) ?>

                    </div>


                    <div class="detail-item">

                        <strong>Phone</strong>

                        <?= htmlspecialchars($appointment['customer_phone']) ?>

                    </div>


                    <div class="detail-item">

                        <strong>Email</strong>

                        <?= htmlspecialchars($appointment['customer_email'] ?: 'Not provided') ?>

                    </div>


                    <div class="detail-item">

                        <strong>Address</strong>

                        <?= htmlspecialchars($appointment['customer_address'] ?: 'Not provided') ?>

                    </div>

                </div>

            </div>


            <div class="section">

                <h2>Service Information</h2>

                <div class="detail-grid">

                    <div class="detail-item">

                        <strong>Service</strong>

                        <?= htmlspecialchars($appointment['service_name']) ?>

                    </div>


                    <div class="detail-item">

                        <strong>Service Price</strong>

                        ₦<?= number_format(
                            (float)$appointment['service_price'],
                            2
                        ) ?>

                    </div>


                    <div
                        class="detail-item"
                        style="grid-column:1/-1;"
                    >

                        <strong>Description</strong>

                        <?= nl2br(
                            htmlspecialchars(
                                $appointment['service_description']
                                ?: 'No service description available.'
                            )
                        ) ?>

                    </div>

                </div>

            </div>


            <div class="section">

                <h2>Appointment Notes</h2>

                <div class="detail-item">

                    <?= $appointment['notes']
                        ? nl2br(htmlspecialchars($appointment['notes']))
                        : 'No additional notes.' ?>

                </div>

            </div>


            <div class="buttons">

                <a
                    href="edit.php?id=<?= (int)$appointment['id'] ?>"
                    class="btn btn-primary"
                >
                    Edit Appointment
                </a>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Back to Appointments
                </a>

            </div>


        </div>

    </div>

</div>

</body>
</html>