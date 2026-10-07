<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $stmt = $conn->prepare("
        SELECT id, full_name, email, phone, address, created_at
        FROM customers
        WHERE full_name LIKE ?
           OR email LIKE ?
           OR phone LIKE ?
        ORDER BY id DESC
    ");

    $term = "%" . $search . "%";

    $stmt->bind_param("sss", $term, $term, $term);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT id, full_name, email, phone, address, created_at
        FROM customers
        ORDER BY id DESC
    ");
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

    <title>Customers | Green Future Flower Garden</title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 8px;
            max-width: 450px;
        }

        .search-form input {
            width: 280px;
            padding: 11px 13px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
        }

        .search-form input:focus {
            border-color: #1f6b46;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-primary {
            background: #1f6b46;
            color: white;
        }

        .btn-primary:hover {
            background: #174f34;
        }

        .btn-edit {
            background: #eaf5ef;
            color: #1f6b46;
        }

        .btn-delete {
            background: #fde8e8;
            color: #a32626;
        }

        .customer-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        }

        .customer-table table {
            margin: 0;
        }

        .customer-count {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .actions {
            display: flex;
            gap: 6px;
        }

        @media (max-width: 700px) {

            .page-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form {
                max-width: none;
            }

            .search-form input {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">

            <h2>Green Future</h2>

            <p>
                Flower Garden Management
            </p>

        </div>

        <div class="nav-title">
            Main Menu
        </div>

        <ul class="nav-menu">

            <li>
                <a href="../index.php">
                    <span class="nav-icon">⌂</span>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="index.php" class="active">
                    <span class="nav-icon">♙</span>
                    <span>Customers</span>
                </a>
            </li>

            <li>
                <a href="../services/index.php">
                    <span class="nav-icon">✿</span>
                    <span>Services</span>
                </a>
            </li>

            <li>
                <a href="../requests/index.php">
                    <span class="nav-icon">▣</span>
                    <span>Service Requests</span>
                </a>
            </li>

            <li>
                <a href="../appointments/index.php">
                    <span class="nav-icon">◷</span>
                    <span>Appointments</span>
                </a>
            </li>

            <li>
                <a href="../quotations/index.php">
                    <span class="nav-icon">▤</span>
                    <span>Quotations</span>
                </a>
            </li>

            <li>
                <a href="../projects/index.php">
                    <span class="nav-icon">◆</span>
                    <span>Projects</span>
                </a>
            </li>

        </ul>

        <div class="logout-link">

            <ul class="nav-menu">

                <li>
                    <a href="../logout.php">
                        <span class="nav-icon">↪</span>
                        <span>Logout</span>
                    </a>
                </li>

            </ul>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="main">

        <header class="topbar">

            <div class="topbar-title">

                <h1>
                    Customers
                </h1>

                <p>
                    Manage Green Future Flower Garden customers
                </p>

            </div>

            <div class="user-area">

                <div class="user-avatar">
                    <?= htmlspecialchars(
                        strtoupper(
                            substr(
                                $_SESSION["full_name"] ?? "A",
                                0,
                                1
                            )
                        )
                    ) ?>
                </div>

                <div class="user-info">

                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION["full_name"] ?? "Administrator"
                        ) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            ucfirst(
                                $_SESSION["role"] ?? "admin"
                            )
                        ) ?>
                    </span>

                </div>

            </div>

        </header>


        <section class="content">

            <div class="page-actions">

                <form
                    method="GET"
                    class="search-form"
                >

                    <input
                        type="search"
                        name="search"
                        placeholder="Search customers..."
                        value="<?= htmlspecialchars($search) ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Search
                    </button>

                </form>

                <a
                    href="add.php"
                    class="btn btn-primary"
                >
                    + Add Customer
                </a>

            </div>


            <?php

            $customer_count = $result->num_rows;

            ?>

            <div class="customer-count">

                <?= $customer_count ?> customer(s) found.

            </div>


            <div class="customer-table">

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Phone
                                </th>

                                <th>
                                    Address
                                </th>

                                <th>
                                    Registered
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($customer_count === 0): ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="empty"
                                >
                                    No customers found.
                                </td>

                            </tr>

                        <?php else: ?>

                            <?php while ($customer = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?= (int) $customer["id"] ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $customer["full_name"]
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $customer["email"] ?? "-"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $customer["phone"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $customer["address"] ?? "-"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            date(
                                                "d M Y",
                                                strtotime(
                                                    $customer["created_at"]
                                                )
                                            )
                                        ) ?>
                                    </td>

                                    <td>

                                        <div class="actions">

                                            <a
                                                href="edit.php?id=<?= (int) $customer["id"] ?>"
                                                class="btn btn-edit"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= (int) $customer["id"] ?>"
                                                class="btn btn-delete"
                                                onclick="return confirm('Are you sure you want to delete this customer?');"
                                            >
                                                Delete
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

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