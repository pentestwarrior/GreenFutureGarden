<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    header(
        "Location: index.php?error=Invalid project ID."
    );

    exit;
}


$stmt = $conn->prepare("
    SELECT
        p.*,
        c.full_name AS customer_name,
        c.email AS customer_email,
        c.phone AS customer_phone,
        c.address AS customer_address,
        s.service_name
    FROM projects p
    INNER JOIN customers c
        ON p.customer_id = c.id
    LEFT JOIN services s
        ON p.service_id = s.id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header(
        "Location: index.php?error=Project not found."
    );

    exit;
}


$project = $result->fetch_assoc();

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
    Project Details | Green Future Flower Garden
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

.project-card {
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    max-width: 1000px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
}

.project-header {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    margin-bottom: 30px;
}

.project-header h1 {
    margin-bottom: 5px;
}

.status {
    display: inline-block;
    padding: 7px 13px;
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

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.info-box {
    background: #f8f9fa;
    padding: 18px;
    border-radius: 10px;
}

.info-box strong {
    display: block;
    margin-bottom: 5px;
    color: #555;
}

.description {
    margin-top: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.buttons {
    margin-top: 30px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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

@media (max-width: 900px) {

    .sidebar {
        position: relative;
        width: 100%;
    }

    .main-content {
        margin-left: 0;
        width: 100%;
    }

    .project-header {
        flex-direction: column;
    }

    .info-grid {
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

        <li><a href="../appointments/index.php">Appointments</a></li>

        <li><a href="../quotations/index.php">Quotations</a></li>

        <li>
            <a href="index.php" class="active">
                Projects
            </a>
        </li>

        <li><a href="../logout.php">Logout</a></li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <div>

            <h3>
                Project Details
            </h3>

            <p>
                View project information.
            </p>

        </div>

    </div>


    <div class="content">

        <div class="project-card">


            <div class="project-header">

                <div>

                    <h1>
                        <?= htmlspecialchars(
                            $project['project_name']
                        ) ?>
                    </h1>

                    <p>
                        Project #<?= (int)$project['id'] ?>
                    </p>

                </div>


                <div>

                    <span
                        class="status status-<?= htmlspecialchars(
                            $project['status']
                        ) ?>"
                    >

                        <?= ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $project['status']
                            )
                        ) ?>

                    </span>

                </div>

            </div>


            <div class="info-grid">


                <div class="info-box">

                    <strong>
                        Customer
                    </strong>

                    <?= htmlspecialchars(
                        $project['customer_name']
                    ) ?>

                </div>


                <div class="info-box">

                    <strong>
                        Service
                    </strong>

                    <?= htmlspecialchars(
                        $project['service_name']
                        ?: 'Not specified'
                    ) ?>

                </div>


                <div class="info-box">

                    <strong>
                        Customer Phone
                    </strong>

                    <?= htmlspecialchars(
                        $project['customer_phone']
                    ) ?>

                </div>


                <div class="info-box">

                    <strong>
                        Customer Email
                    </strong>

                    <?= htmlspecialchars(
                        $project['customer_email']
                        ?: 'Not provided'
                    ) ?>

                </div>


                <div class="info-box">

                    <strong>
                        Location
                    </strong>

                    <?= htmlspecialchars(
                        $project['location']
                    ) ?>

                </div>


                <div class="info-box">

                    <strong>
                        Budget
                    </strong>

                    ₦<?= number_format(
                        (float)$project['budget'],
                        2
                    ) ?>

                </div>


                <div class="info-box">

                    <strong>
                        Start Date
                    </strong>

                    <?= $project['start_date']
                        ? date(
                            'd M Y',
                            strtotime(
                                $project['start_date']
                            )
                        )
                        : 'Not set' ?>

                </div>


                <div class="info-box">

                    <strong>
                        Expected Completion
                    </strong>

                    <?= $project['expected_completion_date']
                        ? date(
                            'd M Y',
                            strtotime(
                                $project['expected_completion_date']
                            )
                        )
                        : 'Not set' ?>

                </div>


                <div class="info-box">

                    <strong>
                        Actual Completion
                    </strong>

                    <?= $project['actual_completion_date']
                        ? date(
                            'd M Y',
                            strtotime(
                                $project['actual_completion_date']
                            )
                        )
                        : 'Not completed' ?>

                </div>


            </div>


            <div class="description">

                <h3>
                    Project Description
                </h3>

                <p>

                    <?= $project['description']
                        ? nl2br(
                            htmlspecialchars(
                                $project['description']
                            )
                        )
                        : 'No project description provided.' ?>

                </p>

            </div>


            <div class="buttons">

                <a
                    href="edit.php?id=<?= (int)$project['id'] ?>"
                    class="btn btn-primary"
                >
                    Edit Project
                </a>


                <a
                    href="delete.php?id=<?= (int)$project['id'] ?>"
                    class="btn btn-secondary"
                    onclick="return confirm('Delete this project?');"
                >
                    Delete Project
                </a>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Back to Projects
                </a>

            </div>


        </div>

    </div>

</div>

</body>

</html>