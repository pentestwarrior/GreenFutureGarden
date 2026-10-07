<?php

require_once "config/database.php";

$page_title = "Contact Us | Green Future Flower Garden";

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (
        $name === '' ||
        $email === '' ||
        $subject === '' ||
        $message === ''
    ) {

        $error = "Please complete all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO contact_messages
            (
                name,
                email,
                phone,
                subject,
                message,
                status
            )
            VALUES (?, ?, ?, ?, ?, 'unread')
        ");

        $stmt->bind_param(
            "sssss",
            $name,
            $email,
            $phone,
            $subject,
            $message
        );

        if ($stmt->execute()) {

            $success =
                "Your message has been sent successfully. "
                . "We will get back to you as soon as possible.";

            $_POST = [];

        } else {

            $error =
                "Unable to send your message. Please try again.";
        }
    }
}

require_once "includes/header.php";

?>

<section class="section">

    <div class="container">

        <div class="section-title">

            <h2>Contact Green Future Flower Garden</h2>

            <p>
                Have a question or need a garden service? Send us a message.
            </p>

        </div>


        <?php if ($success !== ''): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ''): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <div class="form-container">

            <form method="POST">

                <div class="form-group">

                    <label>
                        Name *
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Email *
                    </label>

                    <input
                        type="email"
                        name="email"
                        required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Subject *
                    </label>

                    <input
                        type="text"
                        name="subject"
                        required
                        value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Message *
                    </label>

                    <textarea
                        name="message"
                        required
                        placeholder="Write your message..."
                    ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

                </div>


                <button type="submit" class="btn btn-primary">
                    Send Message
                </button>

            </form>

        </div>

    </div>

</section>

<?php require_once "includes/footer.php"; ?>