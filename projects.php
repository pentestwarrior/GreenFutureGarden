<?php

require_once "config/database.php";

$page_title = "Our Projects | Green Future Flower Garden";

require_once "includes/header.php";

?>

<section class="section">

    <div class="container">

        <div class="section-title">

            <h2>Our Projects</h2>

            <p>
                A selection of garden and landscaping projects managed by our team.
            </p>

        </div>


        <div class="grid">

            <?php

            $stmt = $conn->prepare("
                SELECT
                    p.*,
                    s.service_name
                FROM projects p
                LEFT JOIN services s
                    ON p.service_id = s.id
                WHERE p.status IN ('in_progress', 'completed')
                ORDER BY p.created_at DESC
            ");

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0):

                while ($project = $result->fetch_assoc()):

            ?>

                    <div class="card">

                        <h3>
                            <?= htmlspecialchars($project['project_name']) ?>
                        </h3>

                        <?php if (!empty($project['service_name'])): ?>

                            <p>
                                <strong>Service:</strong>
                                <?= htmlspecialchars($project['service_name']) ?>
                            </p>

                        <?php endif; ?>

                        <?php if (!empty($project['location'])): ?>

                            <p>
                                <strong>Location:</strong>
                                <?= htmlspecialchars($project['location']) ?>
                            </p>

                        <?php endif; ?>

                        <br>

                        <p>
                            <?= htmlspecialchars($project['description']) ?>
                        </p>

                        <br>

                        <p>
                            <strong>Status:</strong>
                            <?= htmlspecialchars(str_replace('_', ' ', $project['status'])) ?>
                        </p>

                    </div>

            <?php

                endwhile;

            else:

            ?>

                <div class="card">

                    <h3>Projects Coming Soon</h3>

                    <p>
                        Project information will appear here as projects are added
                        through the management system.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>