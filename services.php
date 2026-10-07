<?php

require_once "config/database.php";

$page_title = "Our Services | Green Future Flower Garden";

require_once "includes/header.php";

?>

<section class="section">

    <div class="container">

        <div class="section-title">

            <h2>Our Services</h2>

            <p>
                Professional garden and outdoor services.
            </p>

        </div>


        <div class="grid">

            <?php

            $result = $conn->query("
                SELECT *
                FROM services
                WHERE status = 'active'
                ORDER BY service_name ASC
            ");

            if ($result && $result->num_rows > 0):

                while ($service = $result->fetch_assoc()):

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

                                Starting from
                                ₦<?= number_format($service['price'], 2) ?>

                            </div>

                        <?php endif; ?>

                        <br>

                        <a
                            href="request-service.php?service=<?= (int)$service['id'] ?>"
                            class="btn btn-primary"
                        >
                            Request Service
                        </a>

                    </div>

            <?php

                endwhile;

            else:

            ?>

                <div class="card">

                    <h3>No services available</h3>

                    <p>
                        Please check back later.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>