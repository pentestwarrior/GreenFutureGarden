<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

function getCount($conn, $table)
{
    $allowed_tables = [
        "customers",
        "services",
        "service_requests",
        "appointments",
        "projects",
        "quotations",
        "contact_messages"
    ];

    if (!in_array($table, $allowed_tables, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

    if ($result) {
        $row = $result->fetch_assoc();
        return (int) $row["total"];
    }

    return 0;
}

$total_customers = getCount($conn, "customers");
$total_services = getCount($conn, "services");
$total_requests = getCount($conn, "service_requests");
$total_appointments = getCount($conn, "appointments");
$total_projects = getCount($conn, "projects");
$total_quotations = getCount($conn, "quotations");
$total_messages = getCount($conn, "contact_messages");


/*
|--------------------------------------------------------------------------
| Unread Messages
|--------------------------------------------------------------------------
*/

$unread_messages = 0;

$unread_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM contact_messages
    WHERE status = 'unread'
");

if ($unread_result) {
    $unread_row = $unread_result->fetch_assoc();
    $unread_messages = (int) $unread_row["total"];
}


/*
|--------------------------------------------------------------------------
| Search and Filter
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";

$allowed_statuses = [
    "unread",
    "read",
    "replied"
];

if (!in_array($status, $allowed_statuses, true)) {
    $status = "";
}


/*
|--------------------------------------------------------------------------
| Get Contact Messages
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        email,
        phone,
        subject,
        message,
        status,
        created_at
    FROM contact_messages
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= "
        AND (
            name LIKE ?
            OR email LIKE ?
            OR subject LIKE ?
            OR message LIKE ?
        )
    ";

    $search_term = "%" . $search . "%";

    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;

    $types .= "ssss";
}

if ($status !== "") {

    $sql .= " AND status = ?";

    $params[] = $status;

    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Session Information
|--------------------------------------------------------------------------
*/

$full_name = $_SESSION["full_name"] ?? "Administrator";
$role = $_SESSION["role"] ?? "admin";

$first_name = explode(" ", trim($full_name))[0];

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
        Contact Messages | Green Future Flower Garden
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

    <style>

        .message-toolbar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .message-toolbar input,
        .message-toolbar select {
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            min-width: 220px;
        }

        .message-toolbar button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .reset-button {
            display: inline-flex;
            align-items: center;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            background: #eee;
            color: #333;
            font-weight: 600;
        }

        .message-count {
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }

        .message-subject {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .message-preview {
            color: #777;
            font-size: 13px;
            line-height: 1.5;
        }

        .sender-name {
            font-weight: 600;
        }

        .sender-phone {
            display: block;
            color: #777;
            font-size: 12px;
            margin-top: 4px;
        }

        .message-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-view-message,
        .btn-delete-message {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-view-message {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .btn-delete-message {
            background: #ffebee;
            color: #c62828;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-unread {
            background: #fff3cd;
            color: #856404;
        }

        .status-read {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-replied {
            background: #d4edda;
            color: #155724;
        }

        .unread-stat {
            color: #c62828;
        }

        .empty {
            text-align: center;
            padding: 40px !important;
            color: #777;
        }

    </style>

</head>


<body>

<div class="dashboard">


    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar">

        <div class="logo">

            <h2>
                Green Future
            </h2>

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

                    <span>
                        Dashboard
                    </span>

                </a>

            </li>


            <li>

                <a href="../customers/index.php">

                    <span class="nav-icon">♙</span>

                    <span>
                        Customers
                    </span>

                </a>

            </li>


            <li>

                <a href="../services/index.php">

                    <span class="nav-icon">✿</span>

                    <span>
                        Services
                    </span>

                </a>

            </li>


            <li>

                <a href="../requests/index.php">

                    <span class="nav-icon">▣</span>

                    <span>
                        Service Requests
                    </span>

                </a>

            </li>


            <li>

                <a href="../appointments/index.php">

                    <span class="nav-icon">◷</span>

                    <span>
                        Appointments
                    </span>

                </a>

            </li>


            <li>

                <a href="../quotations/index.php">

                    <span class="nav-icon">▤</span>

                    <span>
                        Quotations
                    </span>

                </a>

            </li>


            <li>

                <a href="../projects/index.php">

                    <span class="nav-icon">◆</span>

                    <span>
                        Projects
                    </span>

                </a>

            </li>


            <li>

                <a
                    href="index.php"
                    class="active"
                >

                    <span class="nav-icon">✉</span>

                    <span>
                        Messages
                    </span>

                    <?php if ($unread_messages > 0): ?>

                        <span
                            style="
                                margin-left:auto;
                                background:#d32f2f;
                                color:white;
                                border-radius:20px;
                                padding:3px 8px;
                                font-size:11px;
                            "
                        >
                            <?= $unread_messages ?>
                        </span>

                    <?php endif; ?>

                </a>

            </li>

        </ul>


        <div class="logout-link">

            <ul class="nav-menu">

                <li>

                    <a href="../logout.php">

                        <span class="nav-icon">↪</span>

                        <span>
                            Logout
                        </span>

                    </a>

                </li>

            </ul>

        </div>

    </aside>



    <!-- =========================
         MAIN CONTENT
    ========================== -->

    <main class="main">


        <!-- TOPBAR -->

        <header class="topbar">

            <div class="topbar-title">

                <h1>
                    Contact Messages
                </h1>

                <p>
                    Customer communication management
                </p>

            </div>


            <div class="user-area">

                <div class="user-avatar">

                    <?= htmlspecialchars(
                        strtoupper(
                            substr($first_name, 0, 1)
                        )
                    ) ?>

                </div>


                <div class="user-info">

                    <strong>
                        <?= htmlspecialchars($full_name) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(ucfirst($role)) ?>
                    </span>

                </div>

            </div>

        </header>



        <!-- CONTENT -->

        <section class="content">


            <!-- WELCOME -->

            <div class="welcome">

                <h2>
                    Customer Messages ✉️
                </h2>

                <p>
                    View and manage messages submitted through the Green Future Flower Garden website.
                </p>

            </div>



            <!-- STATISTICS -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Total Messages
                        </span>

                        <div class="stat-icon">
                            ✉
                        </div>

                    </div>

                    <div class="stat-number">
                        <?= $total_messages ?>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Unread Messages
                        </span>

                        <div class="stat-icon">
                            !
                        </div>

                    </div>

                    <div class="stat-number unread-stat">
                        <?= $unread_messages ?>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Customers
                        </span>

                        <div class="stat-icon">
                            ♙
                        </div>

                    </div>

                    <div class="stat-number">
                        <?= $total_customers ?>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-label">
                            Service Requests
                        </span>

                        <div class="stat-icon">
                            ▣
                        </div>

                    </div>

                    <div class="stat-number">
                        <?= $total_requests ?>
                    </div>

                </div>


            </div>



            <!-- SEARCH -->

            <div class="panel">

                <div class="panel-header">

                    <h3>
                        Search Messages
                    </h3>

                </div>


                <div style="padding:20px;">

                    <form
                        method="GET"
                        class="message-toolbar"
                    >

                        <input
                            type="text"
                            name="search"
                            placeholder="Search name, email, subject or message..."
                            value="<?= htmlspecialchars($search) ?>"
                        >


                        <select name="status">

                            <option value="">
                                All Status
                            </option>

                            <option
                                value="unread"
                                <?= $status === "unread" ? "selected" : "" ?>
                            >
                                Unread
                            </option>

                            <option
                                value="read"
                                <?= $status === "read" ? "selected" : "" ?>
                            >
                                Read
                            </option>

                            <option
                                value="replied"
                                <?= $status === "replied" ? "selected" : "" ?>
                            >
                                Replied
                            </option>

                        </select>


                        <button type="submit">
                            Search
                        </button>


                        <a
                            href="index.php"
                            class="reset-button"
                        >
                            Reset
                        </a>

                    </form>

                </div>

            </div>



            <!-- MESSAGES -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h3>
                            Received Messages
                        </h3>

                        <p class="message-count">
                            <?= $result->num_rows ?> message(s) found
                        </p>

                    </div>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Sender
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Subject & Message
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if ($result->num_rows === 0): ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="empty"
                                >

                                    No contact messages found.

                                </td>

                            </tr>

                        <?php else: ?>


                            <?php while ($message = $result->fetch_assoc()): ?>


                                <tr>


                                    <td>

                                        <span class="sender-name">

                                            <?= htmlspecialchars(
                                                $message["name"]
                                            ) ?>

                                        </span>


                                        <?php if (!empty($message["phone"])): ?>

                                            <span class="sender-phone">

                                                <?= htmlspecialchars(
                                                    $message["phone"]
                                                ) ?>

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $message["email"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <div class="message-subject">

                                            <?= htmlspecialchars(
                                                $message["subject"]
                                            ) ?>

                                        </div>


                                        <div class="message-preview">

                                            <?= htmlspecialchars(
                                                mb_substr(
                                                    $message["message"],
                                                    0,
                                                    90
                                                )
                                            ) ?>

                                            <?php if (
                                                mb_strlen(
                                                    $message["message"]
                                                ) > 90
                                            ): ?>

                                                ...

                                            <?php endif; ?>

                                        </div>

                                    </td>


                                    <td>


                                        <?php

                                        $message_status =
                                            $message["status"];

                                        $status_class =
                                            "status-" .
                                            strtolower(
                                                $message_status
                                            );

                                        ?>


                                        <span
                                            class="status <?= htmlspecialchars(
                                                $status_class
                                            ) ?>"
                                        >

                                            <?= htmlspecialchars(
                                                ucfirst(
                                                    $message_status
                                                )
                                            ) ?>

                                        </span>


                                    </td>


                                    <td>

                                        <?= date(
                                            "d M Y, h:i A",
                                            strtotime(
                                                $message["created_at"]
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <div class="message-actions">


                                            <a
                                                href="view.php?id=<?= (int)$message["id"] ?>"
                                                class="btn-view-message"
                                            >
                                                View
                                            </a>


                                            <a
                                                href="delete.php?id=<?= (int)$message["id"] ?>"
                                                class="btn-delete-message"
                                                onclick="return confirm('Are you sure you want to delete this message?');"
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