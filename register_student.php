<?php
include 'includes/navbar.php';
include 'dbconn.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lrn = $_POST['lrn'];
    $last_name = $_POST['lastname'];
    $first_name = $_POST['firstname'];
    $mi = $_POST['mi'];
    $grade = $_POST['grade'];
    $section = $_POST['section'];
    $track = $_POST['track'];
    $strand = $_POST['strand'];

    $check = $conn->prepare("SELECT lrn FROM students WHERE lrn = ?");
    $check->bind_param("s", $lrn);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Student with LRN $lrn already exists!";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO students (lrn, last_name, first_name, mi, grade, section, track, strand)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $lrn, $last_name, $first_name, $mi, $grade, $section, $track, $strand);

        if ($stmt->execute()) {
            $message = "Student registered successfully!";
            $messageType = "success";
        } else {
            $message = "Error saving student: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 100px;
        }

        .register-form {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-10">
                <div class="register-form">

                    <div class="d-flex justify-content-end mb-3">
                        <a href="update_students.php" class="btn btn-warning">Update Student Details</a>
                    </div>

                    <h4 class="form-title">Register Student</h4>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="lrn" class="form-label">LRN</label>
                            <input type="text" name="lrn" id="lrn" class="form-control" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" name="lastname" id="lastname" class="form-control" required>
                            </div>
                            <div class="col">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" name="firstname" id="firstname" class="form-control" required>
                            </div>
                            <div class="col">
                                <label for="mi" class="form-label">M.I.</label>
                                <input type="text" name="mi" id="mi" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label for="grade" class="form-label">Grade Level</label>
                                <select name="grade" id="grade" class="form-select" required>
                                    <option value="" disabled selected>Select Grade</option>
                                    <option value="Grade 12">Grade 12</option>
                                    <option value="Grade 11">Grade 11</option>
                                </select>
                            </div>
                            <div class="col">
                                <label for="section" class="form-label">Section</label>
                                <input type="text" name="section" id="section" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="track" class="form-label">Track</label>
                            <select name="track" id="track" class="form-select" required>
                                <option value="" disabled selected>Select Track</option>
                                <option value="Academic">Academic</option>
                                <option value="TVL">TVL</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="strand" class="form-label">Strand</label>
                            <input type="text" name="strand" id="strand" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($message)) : ?>
        <script>
            Swal.fire({
                icon: '<?= $messageType ?>',
                title: '<?= $messageType === "success" ? "Success" : "Oops..." ?>',
                text: '<?= $message ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>