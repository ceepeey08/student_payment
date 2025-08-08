<?php
// include 'includes/navbar.php';
include 'dbconn.php';

$students = $conn->query("SELECT lrn, last_name, first_name, mi, grade, section, track, strand FROM students ORDER BY lrn ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = $_POST['lrn'];
    $paymentType = $_POST['paymentType'];

    if ($paymentType === 'pta' && isset($_POST['pta_payments'])) {
        foreach ($_POST['pta_payments'] as $label => $amount) {
            $stmt = $conn->prepare("INSERT INTO payments (lrn, payment_type, payment_label, amount) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $lrn, $paymentType, $label, $amount);
            $stmt->execute();
        }
    } elseif (in_array($paymentType, ['graduation', 'immersion', 'others']) && isset($_POST['singlePayment'])) {
        $amount = $_POST['singlePayment'];
        $label = ucfirst($paymentType) . " Fee";
        $stmt = $conn->prepare("INSERT INTO payments (lrn, payment_type, payment_label, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $lrn, $paymentType, $label, $amount);
        $stmt->execute();
    }
    echo "<script>alert('Payment saved successfully!'); window.location.href='payment_form.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Payment Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 100px;
        }

        .form-section {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="form-section">
                    <h4 class="mb-4">Student Info</h4>
                    <form id="studentForm">
                        <div class="mb-3">
                            <label for="lrnDropdown" class="form-label">LRN</label>
                            <select class="form-select" name="lrn" id="lrnDropdown" required>
                                <option value="" disabled selected>Select LRN</option>
                                <?php while ($row = $students->fetch_assoc()): ?>
                                    <option
                                        value="<?= $row['lrn'] ?>"
                                        data-firstname="<?= htmlspecialchars($row['first_name']) ?>"
                                        data-lastname="<?= htmlspecialchars($row['last_name']) ?>"
                                        data-mi="<?= htmlspecialchars($row['mi']) ?>"
                                        data-grade="<?= htmlspecialchars($row['grade']) ?>"
                                        data-section="<?= htmlspecialchars($row['section']) ?>"
                                        data-track="<?= htmlspecialchars($row['track']) ?>"
                                        data-strand="<?= htmlspecialchars($row['strand']) ?>">
                                        <?= htmlspecialchars($row['lrn']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <input type="text" id="fullname" class="form-control mb-2" placeholder="Full Name" readonly>
                        <input type="text" id="grade" class="form-control mb-2" placeholder="Grade" readonly>
                        <input type="text" id="section" class="form-control mb-2" placeholder="Section" readonly>
                        <input type="text" id="track" class="form-control mb-2" placeholder="Track" readonly>
                        <input type="text" id="strand" class="form-control mb-2" placeholder="Strand" readonly>
                    </form>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <div class="form-section">
                    <h4 class="mb-4">Payment Details</h4>
                    <form method="POST" id="paymentForm">
                        <input type="hidden" name="lrn" id="hiddenLrn">
                        <div class="mb-3">
                            <label for="paymentType" class="form-label">Payment Type</label>
                            <select class="form-select" name="paymentType" id="paymentType" required>
                                <option value="" disabled selected>Select Payment Type</option>
                                <option value="pta">PTA</option>
                                <option value="graduation">Graduation</option>
                                <option value="immersion">Immersion</option>
                                <option value="others">Others</option>
                            </select>
                        </div>

                        <div id="paymentFields"></div>

                        <div class="mt-3">
                            <p><strong>Total:</strong> <span id="summary-total">₱0.00</span></p>
                            <p><strong>Received by:</strong> <span id="summary-received-by">N/A</span></p>
                            <p><strong>Date:</strong> <span id="summary-date">--/--/----</span></p>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Save Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ptaFees = [{
                label: 'GPTA Project',
                amount: 200
            },
            {
                label: 'Membership Fee',
                amount: 50
            },
            {
                label: 'SSLG',
                amount: 60
            },
            {
                label: 'Journalism (English and Filipino)',
                amount: 90
            },
            {
                label: 'BSP and GSP',
                amount: 100
            },
            {
                label: 'DBC',
                amount: 30
            },
            {
                label: 'Religious Activity',
                amount: 30
            },
            {
                label: 'Department Aid',
                amount: 200
            },
            {
                label: 'Athletics',
                amount: 75
            },
            {
                label: 'Insurance',
                amount: 25
            }
        ];

        const paymentTypeSelect = document.getElementById('paymentType');
        const paymentFieldsContainer = document.getElementById('paymentFields');

        function updateTotal() {
            const checkboxes = document.querySelectorAll('.pta-checkbox');
            let total = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) total += parseFloat(cb.dataset.amount);
            });
            document.getElementById('summary-total').textContent = `₱${total.toFixed(2)}`;
        }

        paymentTypeSelect.addEventListener('change', () => {
            const type = paymentTypeSelect.value;
            paymentFieldsContainer.innerHTML = '';

            if (type === 'pta') {
                ptaFees.forEach((fee, index) => {
                    const row = `
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input pta-checkbox" 
                    name="pta_payments[${fee.label}]" 
                    value="${fee.amount}" data-amount="${fee.amount}" id="pta${index}">
                <label for="pta${index}" class="form-check-label d-flex justify-content-between w-100">
                    <span>${fee.label}</span>
                    <span>₱${fee.amount.toFixed(2)}</span>
                </label>
            </div>`;
                    paymentFieldsContainer.insertAdjacentHTML('beforeend', row);
                });
                document.getElementById('summary-received-by').textContent = 'Your Name';
                document.getElementById('summary-date').textContent = new Date().toLocaleDateString();
                updateTotal();
            } else {
                paymentFieldsContainer.innerHTML = `
        <div class="mb-3">
            <label>${type.charAt(0).toUpperCase() + type.slice(1)} Payment</label>
            <input type="number" name="singlePayment" class="form-control" required>
        </div>`;
                document.getElementById('summary-total').textContent = '₱0.00';
                document.getElementById('summary-received-by').textContent = 'N/A';
                document.getElementById('summary-date').textContent = '--/--/----';
            }
        });

        paymentFieldsContainer.addEventListener('change', e => {
            if (e.target.classList.contains('pta-checkbox')) {
                updateTotal();
            }
        });

        document.getElementById("lrnDropdown").addEventListener("change", function() {
            const selected = this.options[this.selectedIndex];
            const fname = selected.getAttribute("data-firstname") || '';
            const mi = selected.getAttribute("data-mi") || '';
            const lname = selected.getAttribute("data-lastname") || '';
            const fullName = [fname, mi, lname].filter(Boolean).join(' ');
            document.getElementById("fullname").value = fullName;
            document.getElementById("grade").value = selected.getAttribute("data-grade");
            document.getElementById("section").value = selected.getAttribute("data-section");
            document.getElementById("track").value = selected.getAttribute("data-track");
            document.getElementById("strand").value = selected.getAttribute("data-strand");
            document.getElementById("hiddenLrn").value = selected.value;
        });
    </script>
</body>

</html>