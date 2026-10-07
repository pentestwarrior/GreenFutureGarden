<?php

require_once "../../includes/auth.php";
require_once "../../config/database.php";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {

    header(
        "Location: index.php?error=Invalid quotation ID."
    );

    exit;
}


$stmt = $conn->prepare("
    SELECT *
    FROM quotations
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header(
        "Location: index.php?error=Quotation not found."
    );

    exit;
}


$quotation = $result->fetch_assoc();


$customers = $conn->query("
    SELECT id, full_name, phone
    FROM customers
    ORDER BY full_name ASC
");


$itemStmt = $conn->prepare("
    SELECT *
    FROM quotation_items
    WHERE quotation_id = ?
    ORDER BY id ASC
");

$itemStmt->bind_param("i", $id);

$itemStmt->execute();

$itemsResult = $itemStmt->get_result();


$items = [];

while ($item = $itemsResult->fetch_assoc()) {
    $items[] = $item;
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_id =
        (int)($_POST['customer_id'] ?? 0);

    $quotation_date =
        trim($_POST['quotation_date'] ?? '');

    $valid_until =
        trim($_POST['valid_until'] ?? '');

    $discount =
        (float)($_POST['discount'] ?? 0);

    $notes =
        trim($_POST['notes'] ?? '');

    $status =
        $_POST['status'] ?? 'draft';


    $descriptions =
        $_POST['item_description'] ?? [];

    $quantities =
        $_POST['item_quantity'] ?? [];

    $prices =
        $_POST['item_price'] ?? [];


    $allowed_statuses = [
        'draft',
        'sent',
        'accepted',
        'rejected',
        'expired'
    ];


    if ($customer_id <= 0) {

        $error = "Please select a customer.";

    } elseif ($quotation_date === '') {

        $error = "Please select quotation date.";

    } elseif ($valid_until === '') {

        $error = "Please select valid until date.";

    } elseif (!in_array($status, $allowed_statuses, true)) {

        $error = "Invalid quotation status.";

    } else {


        $newItems = [];

        $subtotal = 0;


        for (
            $i = 0;
            $i < count($descriptions);
            $i++
        ) {

            $description =
                trim($descriptions[$i] ?? '');

            $quantity =
                (float)($quantities[$i] ?? 0);

            $unit_price =
                (float)($prices[$i] ?? 0);


            if (
                $description === '' ||
                $quantity <= 0 ||
                $unit_price < 0
            ) {
                continue;
            }


            $itemTotal =
                $quantity * $unit_price;


            $subtotal += $itemTotal;


            $newItems[] = [
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'total' => $itemTotal
            ];
        }


        if (empty($newItems)) {

            $error =
                "Please add at least one valid item.";

        } else {


            if ($discount < 0) {
                $discount = 0;
            }

            if ($discount > $subtotal) {
                $discount = $subtotal;
            }


            $total =
                $subtotal - $discount;


            $conn->begin_transaction();


            try {


                $update = $conn->prepare("
                    UPDATE quotations
                    SET
                        customer_id = ?,
                        quotation_date = ?,
                        valid_until = ?,
                        subtotal = ?,
                        discount = ?,
                        total = ?,
                        notes = ?,
                        status = ?
                    WHERE id = ?
                ");


                $update->bind_param(
                    "issdddssi",
                    $customer_id,
                    $quotation_date,
                    $valid_until,
                    $subtotal,
                    $discount,
                    $total,
                    $notes,
                    $status,
                    $id
                );


                if (!$update->execute()) {

                    throw new Exception(
                        "Failed to update quotation."
                    );
                }


                $deleteItems = $conn->prepare("
                    DELETE FROM quotation_items
                    WHERE quotation_id = ?
                ");

                $deleteItems->bind_param(
                    "i",
                    $id
                );

                $deleteItems->execute();


                $insertItem = $conn->prepare("
                    INSERT INTO quotation_items
                    (
                        quotation_id,
                        service_id,
                        description,
                        quantity,
                        unit_price,
                        total
                    )
                    VALUES (?, NULL, ?, ?, ?, ?)
                ");


                foreach ($newItems as $item) {

                    $insertItem->bind_param(
                        "isddd",
                        $id,
                        $item['description'],
                        $item['quantity'],
                        $item['unit_price'],
                        $item['total']
                    );


                    if (!$insertItem->execute()) {

                        throw new Exception(
                            "Failed to save quotation item."
                        );
                    }
                }


                $conn->commit();


                header(
                    "Location: view.php?id=" . $id
                );

                exit;


            } catch (Exception $e) {

                $conn->rollback();

                $error = $e->getMessage();
            }
        }
    }


    $quotation['customer_id'] =
        $customer_id;

    $quotation['quotation_date'] =
        $quotation_date;

    $quotation['valid_until'] =
        $valid_until;

    $quotation['discount'] =
        $discount;

    $quotation['notes'] =
        $notes;

    $quotation['status'] =
        $status;


    $items = $newItems ?? [];
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

<title>Edit Quotation | Green Future Flower Garden</title>

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
    max-width: 1100px;
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
    min-height: 120px;
}

.items-box {
    margin-top: 25px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.items-table input {
    width: 100%;
}

.remove-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 10px;
    border-radius: 6px;
    cursor: pointer;
}

.add-item {
    margin-top: 12px;
    background: #198754;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 7px;
    cursor: pointer;
}

.summary {
    max-width: 350px;
    margin-left: auto;
    margin-top: 25px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.summary-total {
    font-size: 20px;
    font-weight: 700;
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

    .items-wrapper {
        overflow-x: auto;
    }

    .items-table {
        min-width: 800px;
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

        <li>
            <a href="index.php" class="active">
                Quotations
            </a>
        </li>

        <li><a href="../projects/index.php">Projects</a></li>

        <li><a href="../logout.php">Logout</a></li>

    </ul>

</div>


<div class="main-content">

    <div class="topbar">

        <div>

            <h3>Edit Quotation</h3>

            <p>
                <?= htmlspecialchars(
                    $quotation['quotation_number']
                ) ?>
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
                Edit Quotation
            </h1>


            <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$id ?>"
                >


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Customer *
                        </label>

                        <select
                            name="customer_id"
                            required
                        >

                            <?php while (
                                $customer =
                                $customers->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?= (int)$customer['id'] ?>"
                                    <?= $quotation['customer_id'] == $customer['id']
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
                            Status
                        </label>

                        <select name="status">

                            <?php

                            foreach (
                                [
                                    'draft',
                                    'sent',
                                    'accepted',
                                    'rejected',
                                    'expired'
                                ] as $item
                            ):

                            ?>

                                <option
                                    value="<?= $item ?>"
                                    <?= $quotation['status'] === $item
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= ucfirst($item) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Quotation Date
                        </label>

                        <input
                            type="date"
                            name="quotation_date"
                            value="<?= htmlspecialchars(
                                $quotation['quotation_date']
                            ) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Valid Until
                        </label>

                        <input
                            type="date"
                            name="valid_until"
                            value="<?= htmlspecialchars(
                                $quotation['valid_until']
                            ) ?>"
                            required
                        >

                    </div>


                </div>


                <div class="items-box">

                    <h2>
                        Quotation Items
                    </h2>


                    <div class="items-wrapper">

                        <table class="items-table">

                            <thead>

                                <tr>

                                    <th>
                                        Description
                                    </th>

                                    <th>
                                        Quantity
                                    </th>

                                    <th>
                                        Unit Price
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="itemsBody">

                            <?php foreach (
                                $items as $item
                            ): ?>

                                <tr>

                                    <td>

                                        <input
                                            type="text"
                                            name="item_description[]"
                                            value="<?= htmlspecialchars(
                                                $item['description']
                                            ) ?>"
                                            required
                                        >

                                    </td>

                                    <td>

                                        <input
                                            class="quantity"
                                            type="number"
                                            name="item_quantity[]"
                                            value="<?= htmlspecialchars(
                                                $item['quantity']
                                            ) ?>"
                                            min="0.01"
                                            step="0.01"
                                            oninput="calculateTotals()"
                                            required
                                        >

                                    </td>

                                    <td>

                                        <input
                                            class="price"
                                            type="number"
                                            name="item_price[]"
                                            value="<?= htmlspecialchars(
                                                $item['unit_price']
                                            ) ?>"
                                            min="0"
                                            step="0.01"
                                            oninput="calculateTotals()"
                                            required
                                        >

                                    </td>

                                    <td>

                                        <strong class="item-total">
                                            ₦<?= number_format(
                                                $item['total'],
                                                2
                                            ) ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <button
                                            type="button"
                                            class="remove-btn"
                                            onclick="removeItem(this)"
                                        >
                                            Remove
                                        </button>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                    <button
                        type="button"
                        class="add-item"
                        onclick="addItem()"
                    >
                        + Add Another Item
                    </button>

                </div>


                <div class="form-grid" style="margin-top:25px;">

                    <div class="form-group">

                        <label>
                            Discount (₦)
                        </label>

                        <input
                            type="number"
                            id="discount"
                            name="discount"
                            value="<?= htmlspecialchars(
                                $quotation['discount']
                            ) ?>"
                            min="0"
                            step="0.01"
                            oninput="calculateTotals()"
                        >

                    </div>


                    <div></div>


                    <div class="form-group full">

                        <label>
                            Notes
                        </label>

                        <textarea
                            name="notes"
                        ><?= htmlspecialchars(
                            $quotation['notes'] ?? ''
                        ) ?></textarea>

                    </div>

                </div>


                <div class="summary">

                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <strong id="subtotal">
                            ₦0.00
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Discount
                        </span>

                        <strong id="discountDisplay">
                            ₦0.00
                        </strong>

                    </div>


                    <div class="summary-row summary-total">

                        <span>
                            Total
                        </span>

                        <strong id="grandTotal">
                            ₦0.00
                        </strong>

                    </div>

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Quotation
                    </button>

                    <a
                        href="view.php?id=<?= (int)$id ?>"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>


            </form>

        </div>

    </div>

</div>


<script>

function addItem() {

    const body =
        document.getElementById("itemsBody");

    const row =
        document.createElement("tr");

    row.innerHTML = `

        <td>

            <input
                type="text"
                name="item_description[]"
                placeholder="Description"
                required
            >

        </td>

        <td>

            <input
                class="quantity"
                type="number"
                name="item_quantity[]"
                value="1"
                min="0.01"
                step="0.01"
                oninput="calculateTotals()"
                required
            >

        </td>

        <td>

            <input
                class="price"
                type="number"
                name="item_price[]"
                value="0"
                min="0"
                step="0.01"
                oninput="calculateTotals()"
                required
            >

        </td>

        <td>

            <strong class="item-total">
                ₦0.00
            </strong>

        </td>

        <td>

            <button
                type="button"
                class="remove-btn"
                onclick="removeItem(this)"
            >
                Remove
            </button>

        </td>
    `;

    body.appendChild(row);

    calculateTotals();
}


function removeItem(button) {

    const rows =
        document.querySelectorAll(
            "#itemsBody tr"
        );

    if (rows.length <= 1) {

        alert(
            "A quotation must have at least one item."
        );

        return;
    }

    button
        .closest("tr")
        .remove();

    calculateTotals();
}


function calculateTotals() {

    const rows =
        document.querySelectorAll(
            "#itemsBody tr"
        );

    let subtotal = 0;


    rows.forEach(row => {

        const quantity =
            parseFloat(
                row.querySelector(".quantity").value
            ) || 0;

        const price =
            parseFloat(
                row.querySelector(".price").value
            ) || 0;

        const total =
            quantity * price;

        subtotal += total;


        row.querySelector(".item-total")
            .textContent =
            "₦" +
            total.toLocaleString(
                "en-NG",
                {
                    minimumFractionDigits: 2
                }
            );

    });


    let discount =
        parseFloat(
            document.getElementById(
                "discount"
            ).value
        ) || 0;


    if (discount > subtotal) {
        discount = subtotal;
    }


    const total =
        subtotal - discount;


    document.getElementById(
        "subtotal"
    ).textContent =
        "₦" +
        subtotal.toLocaleString(
            "en-NG",
            {
                minimumFractionDigits: 2
            }
        );


    document.getElementById(
        "discountDisplay"
    ).textContent =
        "₦" +
        discount.toLocaleString(
            "en-NG",
            {
                minimumFractionDigits: 2
            }
        );


    document.getElementById(
        "grandTotal"
    ).textContent =
        "₦" +
        total.toLocaleString(
            "en-NG",
            {
                minimumFractionDigits: 2
            }
        );
}


document.addEventListener(
    "DOMContentLoaded",
    calculateTotals
);

</script>

</body>
</html>