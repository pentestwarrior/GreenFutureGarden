<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php?error=Invalid quotation ID.");
    exit;
}


$stmt = $conn->prepare("
    SELECT
        q.*,
        c.full_name AS customer_name,
        c.email AS customer_email,
        c.phone AS customer_phone,
        c.address AS customer_address
    FROM quotations q
    INNER JOIN customers c
        ON q.customer_id = c.id
    WHERE q.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header(
        "Location: index.php?error=Quotation not found."
    );

    exit;
}


$quotation = $result->fetch_assoc();


$itemStmt = $conn->prepare("
    SELECT
        qi.*,
        s.service_name
    FROM quotation_items qi
    LEFT JOIN services s
        ON qi.service_id = s.id
    WHERE qi.quotation_id = ?
    ORDER BY qi.id ASC
");

$itemStmt->bind_param("i", $id);

$itemStmt->execute();

$items = $itemStmt->get_result();

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
    <?= htmlspecialchars(
        $quotation['quotation_number']
    ) ?>
    | Green Future Flower Garden
</title>

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

.quote-card {
    background: #fff;
    padding: 35px;
    border-radius: 14px;
    max-width: 1000px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
}

.quote-header {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    margin-bottom: 30px;
}

.quote-title h1 {
    margin-bottom: 5px;
}

.quote-number {
    font-size: 14px;
    color: #666;
}

.quote-meta {
    text-align: right;
}

.customer-box {
    background: #f8f9fa;
    padding: 18px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 13px;
    border-bottom: 1px solid #eee;
}

.items-table th {
    background: #f8f9fa;
    text-align: left;
}

.totals {
    max-width: 350px;
    margin-left: auto;
    margin-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.grand-total {
    font-size: 21px;
    font-weight: 700;
}

.status {
    display: inline-block;
    padding: 6px 12px;
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

.buttons {
    margin-top: 30px;
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

@media print {

    .sidebar,
    .topbar,
    .buttons {
        display: none !important;
    }

    .main-content {
        margin-left: 0;
        width: 100%;
    }

    .content {
        padding: 0;
    }

    .quote-card {
        box-shadow: none;
        max-width: none;
    }
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

    .quote-header {
        flex-direction: column;
    }

    .quote-meta {
        text-align: left;
    }

    .items-table {
        min-width: 700px;
    }

    .items-wrapper {
        overflow-x: auto;
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
            <h3>Quotation Details</h3>
            <p>
                <?= htmlspecialchars(
                    $quotation['quotation_number']
                ) ?>
            </p>
        </div>

    </div>


    <div class="content">

        <div class="quote-card">


            <div class="quote-header">

                <div class="quote-title">

                    <h1>
                        GREEN FUTURE FLOWER GARDEN
                    </h1>

                    <p>
                        Gardening & Landscaping Services
                    </p>

                    <div class="quote-number">

                        Quotation:
                        <strong>
                            <?= htmlspecialchars(
                                $quotation['quotation_number']
                            ) ?>
                        </strong>

                    </div>

                </div>


                <div class="quote-meta">

                    <p>

                        <strong>
                            Date:
                        </strong>

                        <?= date(
                            'd M Y',
                            strtotime(
                                $quotation['quotation_date']
                            )
                        ) ?>

                    </p>


                    <p>

                        <strong>
                            Valid Until:
                        </strong>

                        <?= date(
                            'd M Y',
                            strtotime(
                                $quotation['valid_until']
                            )
                        ) ?>

                    </p>


                    <p>

                        <span class="status status-<?= htmlspecialchars(
                            $quotation['status']
                        ) ?>">

                            <?= ucfirst(
                                $quotation['status']
                            ) ?>

                        </span>

                    </p>

                </div>

            </div>


            <div class="customer-box">

                <h3>
                    Customer Information
                </h3>

                <strong>
                    <?= htmlspecialchars(
                        $quotation['customer_name']
                    ) ?>
                </strong>

                <br>

                Phone:
                <?= htmlspecialchars(
                    $quotation['customer_phone']
                ) ?>

                <br>

                Email:
                <?= htmlspecialchars(
                    $quotation['customer_email']
                    ?: 'Not provided'
                ) ?>

                <br>

                Address:
                <?= htmlspecialchars(
                    $quotation['customer_address']
                    ?: 'Not provided'
                ) ?>

            </div>


            <h3>
                Quotation Items
            </h3>


            <div class="items-wrapper">

                <table class="items-table">

                    <thead>

                        <tr>

                            <th>
                                Description
                            </th>

                            <th>
                                Quantity
                            </th>

                            <th>
                                Unit Price
                            </th>

                            <th>
                                Total
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while (
                        $item =
                        $items->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $item['description']
                                ) ?>

                            </td>

                            <td>

                                <?= number_format(
                                    (float)$item['quantity'],
                                    2
                                ) ?>

                            </td>

                            <td>

                                ₦<?= number_format(
                                    (float)$item['unit_price'],
                                    2
                                ) ?>

                            </td>

                            <td>

                                ₦<?= number_format(
                                    (float)$item['total'],
                                    2
                                ) ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>


            <div class="totals">

                <div class="total-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>
                        ₦<?= number_format(
                            (float)$quotation['subtotal'],
                            2
                        ) ?>
                    </strong>

                </div>


                <div class="total-row">

                    <span>
                        Discount
                    </span>

                    <strong>
                        ₦<?= number_format(
                            (float)$quotation['discount'],
                            2
                        ) ?>
                    </strong>

                </div>


                <div class="total-row grand-total">

                    <span>
                        Total
                    </span>

                    <strong>
                        ₦<?= number_format(
                            (float)$quotation['total'],
                            2
                        ) ?>
                    </strong>

                </div>

            </div>


            <div style="margin-top:30px;">

                <h3>
                    Notes
                </h3>

                <p>

                    <?= $quotation['notes']
                        ? nl2br(
                            htmlspecialchars(
                                $quotation['notes']
                            )
                        )
                        : 'No additional notes.' ?>

                </p>

            </div>


            <div class="buttons">

                <a
                    href="edit.php?id=<?= (int)$quotation['id'] ?>"
                    class="btn btn-primary"
                >
                    Edit Quotation
                </a>

                <button
                    onclick="window.print()"
                    class="btn btn-primary"
                    style="border:none;cursor:pointer;"
                >
                    Print Quotation
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Back
                </a>

            </div>


        </div>

    </div>

</div>

</body>
</html>