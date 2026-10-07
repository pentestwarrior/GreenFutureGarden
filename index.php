<?php

require_once "config/database.php";

$page_title = "Green Future Flower Garden | Kaduna";

require_once "includes/header.php";

?>

<section class="hero">

    <div class="container">

        <div class="hero-content">

            <h1>
                Beautiful Gardens.
                Better Outdoor Spaces.
            </h1>

            <p>
                Green Future Flower Garden provides professional
                landscaping, garden design, lawn care, maintenance
                and flower decoration services in Kaduna.
            </p>

            <a href="request-service.php" class="btn btn-primary">
                Request a Service
            </a>

            <a href="services.php" class="btn btn-light">
                Explore Services
            </a>

        </div>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="section-title">

            <h2>What We Do</h2>

            <p>
                Professional solutions for homes, gardens and outdoor spaces.
            </p>

        </div>


        <div class="grid">

            <?php

            $services = $conn->query("
                SELECT *
                FROM services
                WHERE status = 'active'
                ORDER BY id DESC
                LIMIT 6
            ");

            while ($service = $services->fetch_assoc()):

            ?>

                <div class="card">

                    <h3>
                        <?= htmlspecialchars($service['service_name']) ?>
                    </h3>

                    <p>
                        <?= htmlspecialchars($service['description']) ?>
                    </p>

                    <?php if ($service['price'] > 0): ?>

                        <div class="service-price">
                            Starting from ₦<?= number_format($service['price'], 2) ?>
                        </div>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        </div>

    </div>

</section>


<section class="section cta">

    <div class="container">

        <h2>Ready to Improve Your Garden?</h2>

        <p>
            Tell us what you need and our team can review your request.
        </p>

        <br>

        <a href="request-service.php" class="btn btn-light">
            Request Service
        </a>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>