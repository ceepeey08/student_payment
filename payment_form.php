<?php
include 'includes/navbar.php';
include 'dbconn.php';
$students = $conn->query("SELECT lrn, last_name, first_name, mi, grade, section, track, strand FROM students ORDER BY lrn ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Form with Payment Fields</title>
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

        .form-title {
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .payment-row {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .payment-row span {
            width: 40px;
            font-weight: bold;
        }

        .payment-row small {
            margin-left: 10px;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row g-4">

            <!-- Left Column -->
            <div class="col-md-6">
                <div class="form-section">
                    <h4 class="form-title">Student Info</h4>
                    <form>
                        <div class="mb-3">
                            <label for="lrn" class="form-label">LRN</label>
                            <select class="form-select" id="lrnDropdown" required>
                                <option value="" disabled selected>Select LRN</option>
                                <?php while ($row = $students->fetch_assoc()): ?>
                                    <option value="<?= $row['lrn'] ?>"
                                        data-firstname="<?= $row['first_name'] ?>"
                                        data-lastname="<?= $row['last_name'] ?>"
                                        data-mi="<?= $row['mi'] ?>"
                                        data-grade="<?= $row['grade'] ?>"
                                        data-section="<?= $row['section'] ?>"
                                        data-track="<?= $row['track'] ?>"
                                        data-strand="<?= $row['strand'] ?>">
                                        <?= $row['lrn'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" id="firstname" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" id="lastname" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">M.I.</label>
                            <input type="text" id="mi" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Grade</label>
                            <input type="text" id="grade" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" id="section" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Track</label>
                            <input type="text" id="track" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Strand</label>
                            <input type="text" id="strand" class="form-control" readonly>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <div class="form-section">
                    <h4 class="form-title">Payment Details</h4>

                    <div class="mb-3">
                        <label for="paymentType" class="form-label">Select Payment Type</label>
                        <select class="form-select" id="paymentType">
                            <option value="" disabled selected>Select Payment Type</option>
                            <option value="pta">PTA</option>
                            <option value="graduation">Graduation</option>
                            <option value="immersion">Immersion</option>
                        </select>
                    </div>

                    <form id="paymentForm">
                        <div class="row">
                            <div class="col-12" id="paymentFields">
                                <!-- JS will inject payment fields here -->
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-3">Save Payment</button>
                    </form>
                </div>

                <!-- Summary (No Inputs) -->
                <div class="mt-4 ps-2">
                    <p><strong>Total:</strong> <span id="summary-total">₱0.00</span></p>
                    <p><strong>Received by:</strong> <span id="summary-received-by">N/A</span></p>
                    <p><strong>Date:</strong> <span id="summary-date">--/--/----</span></p>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const paymentTypeSelect = document.getElementById('paymentType');
        const paymentFieldsContainer = document.getElementById('paymentFields');

        paymentTypeSelect.addEventListener('change', () => {
            const type = paymentTypeSelect.value;
            paymentFieldsContainer.innerHTML = '';

            if (type === 'pta') {
                for (let i = 1; i <= 10; i++) {
                    const amount = i * 50;
                    const row = `
            <div class="payment-row">
              <span>#${i}</span>
              <input type="text" class="form-control" name="payment${i}" placeholder="Enter details">
              <small>(₱${amount.toFixed(2)})</small>
            </div>`;
                    paymentFieldsContainer.insertAdjacentHTML('beforeend', row);
                }
            } else if (type === 'graduation' || type === 'immersion') {
                paymentFieldsContainer.innerHTML = `
          <div class="mb-3">
            <label for="singlePayment" class="form-label">${type.charAt(0).toUpperCase() + type.slice(1)} Payment</label>
            <input type="text" class="form-control" name="singlePayment" placeholder="Enter payment amount">
          </div>
        `;
            }
        });

        document.getElementById("lrnDropdown").addEventListener("change", function() {
        const selectedOption = this.options[this.selectedIndex];

        const firstname = selectedOption.getAttribute("data-firstname");
        const lastname = selectedOption.getAttribute("data-lastname");
        const mi = selectedOption.getAttribute("data-mi");
        const grade = selectedOption.getAttribute("data-grade");
        const section = selectedOption.getAttribute("data-section");
        const track = selectedOption.getAttribute("data-track");
        const strand = selectedOption.getAttribute("data-strand");

        document.getElementById("firstname").value = firstname;
        document.getElementById("lastname").value = lastname;
        document.getElementById("mi").value = mi;
        document.getElementById("grade").value = grade;
        document.getElementById("section").value = section;
        document.getElementById("track").value = track;
        document.getElementById("strand").value = strand;
        document.getElementById("fullname").value = `${firstname} ${mi} ${lastname}`.replace(/\s+/g, ' ').trim();
        });
    </script>

</body>

</html>