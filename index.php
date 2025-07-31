<?php
include 'includes/navbar.php'; // Ensure this file includes the Bootstrap navbar
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

            <!-- Left Column: Student Form -->
            <div class="col-md-6">
                <div class="form-section">
                    <h4 class="form-title">Student Info</h4>
                    <form>
                        <div class="mb-3">
                            <label for="lrn" class="form-label">LRN</label>
                            <input type="text" class="form-control" id="lrn" required>
                        </div>

                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" required>
                        </div>

                        <div class="mb-3">
                            <label for="grade" class="form-label">Grade Level</label>
                            <input type="text" class="form-control" id="grade" required>
                        </div>

                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <input type="text" class="form-control" id="section" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Track & Payment -->
            <div class="col-md-6">

                <div class="form-section mb-4">
                    <h4 class="form-title">Track</h4>
                    <div class="mb-3">
                        <label for="track" class="form-label">Choose a Track</label>
                        <select class="form-select" id="track" required>
                            <option value="" disabled selected>Select a track</option>
                            <option value="tvl">TVL</option>
                            <option value="academic">Academic</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-title">Payment Details</h4>

                    <!-- Payment Type Dropdown -->
                    <div class="mb-3">
                        <label for="paymentType" class="form-label">Select Payment Type</label>
                        <select class="form-select" id="paymentType">
                            <option value="" disabled selected>Choose type</option>
                            <option value="PTA">PTA</option>
                            <option value="Graduation">Graduation</option>
                            <option value="Immersion">Immersion</option>
                        </select>
                    </div>

                    <!-- PTA Payment Fields -->
                    <form id="ptaFields" class="d-none">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <div class="payment-row">
                                <span>#<?= $i ?></span>
                                <input type="text" class="form-control" name="pta_payment<?= $i ?>" placeholder="Enter details">
                                <small>(₱<?= number_format($i * 100, 2) ?>)</small>
                            </div>
                        <?php endfor; ?>
                        <button type="submit" class="btn btn-success w-100 mt-3">Save PTA Payment</button>
                    </form>

                    <!-- Single Payment Field -->
                    <form id="singlePayment" class="d-none">
                        <div class="mb-3">
                            <label for="singleAmount" class="form-label">Payment Amount</label>
                            <input type="number" class="form-control" id="singleAmount" name="single_amount" placeholder="₱ Amount">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Save Payment</button>
                    </form>

                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const paymentType = document.getElementById('paymentType');
        const ptaFields = document.getElementById('ptaFields');
        const singlePayment = document.getElementById('singlePayment');

        paymentType.addEventListener('change', function() {
            const value = this.value;

            if (value === 'PTA') {
                ptaFields.classList.remove('d-none');
                singlePayment.classList.add('d-none');
            } else if (value === 'Graduation' || value === 'Immersion') {
                ptaFields.classList.add('d-none');
                singlePayment.classList.remove('d-none');
            } else {
                ptaFields.classList.add('d-none');
                singlePayment.classList.add('d-none');
            }
        });
    </script>
</body>

</html>