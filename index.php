<?php
include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Payment Records</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }

        .table-section {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table-title {
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h4 class="table-title">Student Payment Records</h4>
        <div class="table-section">
            <div class="table-responsive">
                <table id="recordsTable" class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>LRN</th>
                            <th>Full Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Track</th>
                            <th>Total Payment</th>
                            <th>Received By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Static Row -->
                        <tr>
                            <td>1234567890</td>
                            <td>Juan Dela Cruz</td>
                            <td>12</td>
                            <td>St. Michael</td>
                            <td>TVL</td>
                            <td>₱1000.00</td>
                            <td>Cashier A</td>
                            <td>2025-08-01</td>
                        </tr>
                        <!-- Add PHP loop here later if pulling from database -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#recordsTable').DataTable();
        });
    </script>

</body>

</html>