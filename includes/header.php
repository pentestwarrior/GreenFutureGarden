<?php
$page_title = $page_title ?? "Green Future Flower Garden";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Green Future Flower Garden provides professional landscaping, garden design, lawn care, maintenance and flower decoration services."
    >

    <title>
        <?= htmlspecialchars($page_title) ?>
    </title>

    <link rel="stylesheet" href="/GreenFutureGarden/assets/css/style.css">

</head>

<body>

<header class="site-header">

    <div class="container nav-container">

        <a href="/GreenFutureGarden/index.php" class="logo">

            <span>Green Future</span>

            <small>Flower Garden</small>

        </a>


        <button class="menu-toggle" onclick="toggleMenu()">
            ☰
        </button>


        <nav id="mainNav">

            <a href="/GreenFutureGarden/index.php">
                Home
            </a>

            <a href="/GreenFutureGarden/about.php">
                About
            </a>

            <a href="/GreenFutureGarden/services.php">
                Services
            </a>

            <a href="/GreenFutureGarden/projects.php">
                Projects
            </a>

            <a href="/GreenFutureGarden/request-service.php">
                Request Service
            </a>

            <a href="/GreenFutureGarden/contact.php">
                Contact
            </a>

            <a href="/GreenFutureGarden/admin/login.php" class="admin-link">
                Admin
            </a>

        </nav>

    </div>

</header>

<main>