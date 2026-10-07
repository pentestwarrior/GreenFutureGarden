<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php?error=Invalid message ID");
    exit;
}

$stmt = $conn->prepare("
    SELECT *
    FROM contact_messages
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php?error=Message not found");
    exit;
}

$message = $result->fetch_assoc();

if ($message['status'] === 'unread') {

    $update = $conn->prepare("
        UPDATE contact_messages
        SET status = 'read'
        WHERE id = ?
    ");

    $update->bind_param("i", $id);
    $update->execute();

    $message['status'] = 'read';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Message | Green Future Flower Garden</title>

    <link rel="stylesheet" href="/GreenFutureGarden/assets/css/admin.css">

    <style>

        .sidebar {
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            background: #143d2b;
            color: white;
            overflow-y: auto;
        }

        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        .message-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
            max-width: 900px;
        }

        .message-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .message-header h2 {
            margin-bottom: 10px;
        }

        .sender-info {
            line-height: 1.8;
            color: #555;
        }

        .message-content {
            line-height: 1.8;
            white-space: pre-wrap;
            color: #333;
            font-size: 16px;
        }

        .status-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .status-form select {
            padding: 10px;
            border-radius: 7px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-primary {
            background: #198754;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 800px) {

            .sidebar {
                width: 100%;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

        }

    </style>

</head>

<body>

<aside class="sidebar">

    <div class="sidebar-header">
        <h2>Green Future</h2>
        <p>Garden Management</p>
    </div>

    <nav>

        <a href="../index.php">Dashboard</a>

        <a href="../customers/index.php">Customers</a>

        <a href="../services/index.php">Services</a>

        <a href="../requests/index.php">Service Requests</a>

        <a href="../appointments/index.php">Appointments</a>

        <a href="../quotations/index.php">Quotations</a>

        <a href="../projects/index.php">Projects</a>

        <a href="index.php" class="active">Messages</a>

        <a href="../logout.php">Logout</a>

    </nav>

</aside>


<main class="main-content">

    <header class="topbar">

        <div>

            <h1>Message Details</h1>

            <p>View and manage customer communication.</p>

        </div>

    </header>


    <section class="content">

        <div class="message-box">

            <div class="message-header">

                <h2>
                    <?= htmlspecialchars($message['subject'] ?: 'No Subject') ?>
                </h2>

                <div class="sender-info">

                    <strong>
                        From:
                    </strong>

                    <?= htmlspecialchars($message['name']) ?>

                    <br>

                    <strong>
                        Email:
                    </strong>

                    <?= htmlspecialchars($message['email']) ?>

                    <?php if (!empty($message['phone'])): ?>

                        <br>

                        <strong>
                            Phone:
                        </strong>

                        <?= htmlspecialchars($message['phone']) ?>

                    <?php endif; ?>

                    <br>

                    <strong>
                        Date:
                    </strong>

                    <?= date('d M Y, h:i A', strtotime($message['created_at'])) ?>

                </div>

            </div>


            <div class="message-content">

                <?= htmlspecialchars($message['message']) ?>

            </div>


            <form method="POST" action="edit.php" class="status-form">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$message['id'] ?>"
                >

                <label>
                    <strong>Message Status</strong>
                </label>

                <br><br>

                <select name="status">

                    <option
                        value="unread"
                        <?= $message['status'] === 'unread' ? 'selected' : '' ?>
                    >
                        Unread
                    </option>

                    <option
                        value="read"
                        <?= $message['status'] === 'read' ? 'selected' : '' ?>
                    >
                        Read
                    </option>

                    <option
                        value="replied"
                        <?= $message['status'] === 'replied' ? 'selected' : '' ?>
                    >
                        Replied
                    </option>

                </select>

                <button type="submit" class="btn btn-primary">
                    Update Status
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Back
                </a>

            </form>

        </div>

    </section>

</main>

</body>
</html>