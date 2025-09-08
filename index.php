<?php
include 'includes/navbar.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'dbconn.php';

$query = "
            SELECT 
            s.lrn,
            CONCAT(s.first_name, ' ', s.last_name) AS full_name,
            s.grade,
            s.section,
            s.track,
            SUM(p.amount) AS total_payment,
            (
                SELECT p2.received_by 
                FROM payments p2 
                WHERE p2.lrn = s.lrn 
                ORDER BY p2.date_paid DESC 
                LIMIT 1
            ) AS last_received_by,
            (
                SELECT p2.date_paid 
                FROM payments p2 
                WHERE p2.lrn = s.lrn 
                ORDER BY p2.date_paid DESC 
                LIMIT 1
            ) AS last_payment_date
        FROM students s
        LEFT JOIN payments p ON s.lrn = p.lrn
        GROUP BY s.lrn, s.first_name, s.last_name, s.grade, s.section, s.track;
        ";
$students = $conn->query($query);

$details = [];
$detailQuery = "
    SELECT 
        p.lrn, p.payment_type, p.payment_label, p.amount, p.received_by, p.date_paid
    FROM payments p
    ORDER BY p.date_paid DESC
";
$result = $conn->query($detailQuery);
while ($row = $result->fetch_assoc()) {
    $details[$row['lrn']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Payment Records</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">


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
                            <th>Last Received By</th>
                            <th>Last Payment Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['lrn']) ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['grade']) ?></td>
                                <td><?= htmlspecialchars($row['section']) ?></td>
                                <td><?= htmlspecialchars($row['track']) ?></td>
                                <td>₱<?= number_format($row['total_payment'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($row['last_received_by'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['last_payment_date'] ?? '--/--/----') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-details"
                                        data-lrn="<?= $row['lrn'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Payment Type</th>
                                <th>Label</th>
                                <th>Amount</th>
                                <th>Received By</th>
                                <th>Date Paid</th>
                            </tr>
                        </thead>
                        <tbody id="detailsBody">
                            <tr>
                                <td colspan="5" class="text-center">No records</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#recordsTable').DataTable();

            const allDetails = <?= json_encode($details) ?>;

            $('.view-details').on('click', function() {
                let lrn = $(this).data('lrn');
                let rows = allDetails[lrn] || [];
                let tbody = $('#detailsBody');
                tbody.empty();

                if (rows.length > 0) {
                    rows.forEach(r => {
                        tbody.append(`
                            <tr>
                                <td>${r.payment_type}</td>
                                <td>${r.payment_label}</td>
                                <td>₱${parseFloat(r.amount).toFixed(2)}</td>
                                <td>${r.received_by}</td>
                                <td>${r.date_paid}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`<tr><td colspan="5" class="text-center">No payments found</td></tr>`);
                }
            });
        });
    </script>

</body>

</html>