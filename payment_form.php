<?php
session_start();
if (!isset($_SESSION['first_name']) || !isset($_SESSION['last_name'])) {
    die("Not logged in");
}
$receivedBy = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
?>

<?php
include 'dbconn.php';


if (isset($_GET['action']) && $_GET['action'] === 'get_paid_pta') {
    $lrn = $_GET['lrn'] ?? '';
    $paid = [];

    if ($lrn) {
        $stmt = $conn->prepare("SELECT payment_label FROM payments WHERE lrn = ? AND payment_type = 'pta'");
        $stmt->bind_param("s", $lrn);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $paid[] = $row['payment_label'];
        }
        $stmt->close();
    }

    header('Content-Type: application/json');
    echo json_encode($paid);
    exit;
}

$students = $conn->query("SELECT lrn, last_name, first_name, mi, grade, section, track, strand FROM students ORDER BY lrn ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lrn = $_POST['lrn'] ?? '';
    $paymentType = $_POST['paymentType'] ?? '';

    $checkStudent = $conn->prepare("SELECT COUNT(*) FROM students WHERE lrn = ?");
    $checkStudent->bind_param("s", $lrn);
    $checkStudent->execute();
    $checkStudent->bind_result($exists);
    $checkStudent->fetch();
    $checkStudent->close();

    if ($exists == 0) {
        die("<script>alert('Invalid LRN selected. Please choose a valid student.'); window.history.back();</script>");
    }

    if ($paymentType === 'pta' && isset($_POST['pta_payments'])) {
        foreach ($_POST['pta_payments'] as $label => $amount) {
            $stmt = $conn->prepare("INSERT INTO payments (lrn, payment_type, payment_label, amount, received_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssds", $lrn, $paymentType, $label, $amount, $receivedBy);
            $stmt->execute();
        }
    } elseif ($paymentType === 'graduation' && !empty($_POST['graduation_payment'])) {
        $amount = $_POST['graduation_payment'];
        $label = "Graduation Fee";
        $stmt = $conn->prepare("INSERT INTO payments (lrn, payment_type, payment_label, amount, received_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $lrn, $paymentType, $label, $amount, $receivedBy);
        $stmt->execute();
    } elseif ($paymentType === 'immersion' && !empty($_POST['immersion_payment'])) {
        $amount = $_POST['immersion_payment'];
        $label = "Immersion Fee";
        $stmt = $conn->prepare("INSERT INTO payments (lrn, payment_type, payment_label, amount, received_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $lrn, $paymentType, $label, $amount, $receivedBy);
        $stmt->execute();
    } elseif ($paymentType === 'others' && !empty($_POST['others_label']) && !empty($_POST['others_payment'])) {
        $amount = $_POST['others_payment'];
        $label = $_POST['others_label'];
        $stmt = $conn->prepare("INSERT INTO payments (lrn, payment_type, payment_label, amount, received_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $lrn, $paymentType, $label, $amount, $receivedBy);
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
    <?php include 'includes/navbar.php'; ?>
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
        let paidPta = [];

        const receivedBy = <?php echo json_encode($receivedBy); ?>;

        function updateSummary(total) {
            document.getElementById('summary-total').textContent = `₱${total.toFixed(2)}`;
            document.getElementById('summary-received-by').textContent = receivedBy && receivedBy.trim() !== '' ? receivedBy : 'N/A';
            document.getElementById('summary-date').textContent = new Date().toLocaleDateString();
        }

        function resetSummary() {
            document.getElementById('summary-total').textContent = '₱0.00';
            document.getElementById('summary-received-by').textContent = receivedBy && receivedBy.trim() !== '' ? receivedBy : 'N/A';
            document.getElementById('summary-date').textContent = '--/--/----';
        }

        function renderPtaFields() {
            paymentFieldsContainer.innerHTML = '';
            ptaFees.forEach((fee, index) => {
                const isPaid = paidPta.includes(fee.label);
                let row = `
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input pta-checkbox"
                    name="pta_payments[${fee.label}]"
                    value="${fee.amount}" data-amount="${fee.amount}" id="pta${index}"
                    ${isPaid ? 'disabled checked' : ''}>
                <label for="pta${index}" class="form-check-label d-flex justify-content-between w-100">
                    <span>${fee.label}</span>`;

                if (isPaid) {
                    row += `<span class="badge bg-success">Paid</span>`;
                } else {
                    row += `<span>₱${fee.amount.toFixed(2)}</span>`;
                }

                row += `</label></div>`;
                paymentFieldsContainer.insertAdjacentHTML('beforeend', row);
            });
            updateTotal();
        }


        function renderGraduationFields() {
            paymentFieldsContainer.innerHTML = `
                <div class="mb-3">
                    <label>Graduation Payment</label>
                    <input type="number" name="graduation_payment" class="form-control" id="graduationPayment" required>
                </div>`;
            resetSummary();
            document.getElementById('graduationPayment').addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                updateSummary(amount);
            });
        }

        function renderImmersionFields() {
            paymentFieldsContainer.innerHTML = `
                <div class="mb-3">
                    <label>Immersion Payment</label>
                    <input type="number" name="immersion_payment" class="form-control" id="immersionPayment" required>
                </div>`;
            resetSummary();
            document.getElementById('immersionPayment').addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                updateSummary(amount);
            });
        }

        function renderOthersFields() {
            paymentFieldsContainer.innerHTML = `
                <div class="mb-3">
                    <label>Other Payment Description</label>
                    <input type="text" name="others_label" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Amount</label>
                    <input type="number" name="others_payment" class="form-control" id="othersPayment" required>
                </div>`;
            resetSummary();
            document.getElementById('othersPayment').addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                updateSummary(amount);
            });
        }

        function updateTotal() {
            const checkboxes = document.querySelectorAll('.pta-checkbox');
            let total = 0;
            checkboxes.forEach(cb => {
                if (cb.checked && !cb.disabled) {
                    total += parseFloat(cb.dataset.amount);
                }
            });
            updateSummary(total);
        }

        paymentTypeSelect.addEventListener('change', () => {
            const type = paymentTypeSelect.value;
            if (type === 'pta') renderPtaFields();
            else if (type === 'graduation') renderGraduationFields();
            else if (type === 'immersion') renderImmersionFields();
            else if (type === 'others') renderOthersFields();
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
            document.getElementById("fullname").value = [fname, mi, lname].filter(Boolean).join(' ');
            document.getElementById("grade").value = selected.getAttribute("data-grade");
            document.getElementById("section").value = selected.getAttribute("data-section");
            document.getElementById("track").value = selected.getAttribute("data-track");
            document.getElementById("strand").value = selected.getAttribute("data-strand");
            document.getElementById("hiddenLrn").value = selected.value;

            const lrn = selected.value;
            if (lrn) {
                fetch("payment_form.php?action=get_paid_pta&lrn=" + lrn)
                    .then(res => res.json())
                    .then(data => {
                        paidPta = data;
                        if (paymentTypeSelect.value === "pta") {
                            renderPtaFields();
                        }
                    });
            }
        });

        document.getElementById("paymentForm").addEventListener("submit", function(e) {
            if (!document.getElementById("hiddenLrn").value) {
                e.preventDefault();
                alert("Please select a student first!");
            }
        });
    </script>

</body>

</html>