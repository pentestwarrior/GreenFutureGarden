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
    SELECT id, service_name
    FROM services
    WHERE status = 'active'
    ORDER BY service_name ASC
");


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_id =
        (int)($_POST['customer_id'] ?? 0);

    $project_name =
        trim($_POST['project_name'] ?? '');

    $service_id =
        (int)($_POST['service_id'] ?? 0);

    $description =
        trim($_POST['description'] ?? '');

    $location =
        trim($_POST['location'] ?? '');

    $start_date =
        trim($_POST['start_date'] ?? '');

    $expected_completion_date =
        trim($_POST['expected_completion_date'] ?? '');

    $actual_completion_date =
        trim($_POST['actual_completion_date'] ?? '');

    $budget =
        (float)($_POST['budget'] ?? 0);

    $status =
        $_POST['status'] ?? 'planning';


    $allowed_statuses = [
        'planning',
        'in_progress',
        'on_hold',
        'completed',
        'cancelled'
    ];


    if ($customer_id <= 0) {

        $error = "Please select a customer.";

    } elseif ($project_name === '') {

        $error = "Please enter project name.";

    } elseif ($location === '') {

        $error = "Please enter project location.";

    } elseif (!in_array($status, $allowed_statuses, true)) {

        $error = "Invalid project status.";

    } elseif ($budget < 0) {

        $error = "Budget cannot be negative.";

    } else {


        if ($service_id <= 0) {
            $service_id = null;
        }


        $customerCheck = $conn->prepare("
            SELECT id
            FROM customers
            WHERE id = ?
            LIMIT 1
        ");

        $customerCheck->bind_param(
            "i",
            $customer_id
        );

        $customerCheck->execute();


        if (
            $customerCheck
                ->get_result()
                ->num_rows === 0
        ) {

            $error = "Selected customer does not exist.";

        } else {


            if ($service_id === null) {

                $stmt = $conn->prepare("
                    INSERT INTO projects
                    (
                        customer_id,
                        project_name,
                        service_id,
                        description,
                        location,
                        start_date,
                        expected_completion_date,
                        actual_completion_date,
                        budget,
                        status
                    )
                    VALUES (?, ?, NULL, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?, ?)
                ");

                $stmt->bind_param(
                    "issssssds",
                    $customer_id,
                    $project_name,
                    $description,
                    $location,
                    $start_date,
                    $expected_completion_date,
                    $actual_completion_date,
                    $budget,
                    $status
                );

            } else {

                $stmt = $conn->prepare("
                    INSERT INTO projects
                    (
                        customer_id,
                        project_name,
                        service_id,
                        description,
                        location,
                        start_date,
                        expected_completion_date,
                        actual_completion_date,
                        budget,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?, ?)
                ");

                $stmt->bind_param(
                    "isisssssds",
                    $customer_id,
                    $project_name,
                    $service_id,
                    $description,
                    $location,
                    $start_date,
                    $expected_completion_date,
                    $actual_completion_date,
                    $budget,
                    $status
                );
            }


            if ($stmt->execute()) {

                header(
                    "Location: view.php?id=" .
                    $conn->insert_id
                );

                exit;

            } else {

                $error =
                    "Failed to create project.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Project | Green Future Flower Garden</title>

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

.form-card {
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    max-width: 1000px;
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

.full {
    grid-column: 1 / -1;
}

label {
    margin-bottom: 7px;
    font-weight: 600;
}

input,
select,
textarea {
    padding: 11px 13px;
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

    .full {
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
                Add Project
            </h3>

            <p>
                Create a new customer project.
            </p>

        </div>

    </div>


    <div class="content">

        <?php if ($error): ?>

            <div class="alert">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <div class="form-card">

            <h1>
                New Project
            </h1>

            <p>
                Enter the details of the gardening or landscaping project.
            </p>


            <form method="POST">


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Customer *
                        </label>

                        <select
                            name="customer_id"
                            required
                        >

                            <option value="">
                                Select Customer
                            </option>

                            <?php while (
                                $customer =
                                $customers->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?= (int)$customer['id'] ?>"
                                    <?= ($_POST['customer_id'] ?? '') == $customer['id']
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= htmlspecialchars(
                                        $customer['full_name']
                                    ) ?>

                                    -
                                    <?= htmlspecialchars(
                                        $customer['phone']
                                    ) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Service
                        </label>

                        <select name="service_id">

                            <option value="">
                                Select Service
                            </option>

                            <?php while (
                                $service =
                                $services->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?= (int)$service['id'] ?>"
                                    <?= ($_POST['service_id'] ?? '') == $service['id']
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= htmlspecialchars(
                                        $service['service_name']
                                    ) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="form-group full">

                        <label>
                            Project Name *
                        </label>

                        <input
                            type="text"
                            name="project_name"
                            placeholder="e.g. Residential Garden Landscaping"
                            value="<?= htmlspecialchars(
                                $_POST['project_name'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Project Location *
                        </label>

                        <input
                            type="text"
                            name="location"
                            placeholder="e.g. Kabala Doki, Kaduna"
                            value="<?= htmlspecialchars(
                                $_POST['location'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Project Description
                        </label>

                        <textarea
                            name="description"
                            placeholder="Describe the project requirements..."
                        ><?= htmlspecialchars(
                            $_POST['description'] ?? ''
                        ) ?></textarea>

                    </div>


                    <div class="form-group">

                        <label>
                            Start Date
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            value="<?= htmlspecialchars(
                                $_POST['start_date'] ?? ''
                            ) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Expected Completion
                        </label>

                        <input
                            type="date"
                            name="expected_completion_date"
                            value="<?= htmlspecialchars(
                                $_POST['expected_completion_date'] ?? ''
                            ) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Actual Completion
                        </label>

                        <input
                            type="date"
                            name="actual_completion_date"
                            value="<?= htmlspecialchars(
                                $_POST['actual_completion_date'] ?? ''
                            ) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Budget (₦)
                        </label>

                        <input
                            type="number"
                            name="budget"
                            min="0"
                            step="0.01"
                            value="<?= htmlspecialchars(
                                $_POST['budget'] ?? '0'
                            ) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Project Status
                        </label>

                        <select name="status">

                            <?php

                            foreach (
                                [
                                    'planning',
                                    'in_progress',
                                    'on_hold',
                                    'completed',
                                    'cancelled'
                                ] as $item
                            ):

                            ?>

                                <option
                                    value="<?= $item ?>"
                                    <?= ($_POST['status'] ?? 'planning') === $item
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $item
                                        )
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Project
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