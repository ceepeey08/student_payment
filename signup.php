<?php
require_once 'dbconn.php';

$swal = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username       = trim($_POST["username"]);
    $first_name     = trim($_POST["first_name"]);
    $last_name      = trim($_POST["last_name"]);
    $password_raw   = $_POST["password"];
    $confirm_pass   = $_POST["confirm_password"];

    if ($password_raw !== $confirm_pass) {
        $swal = [
            'icon' => 'error',
            'title' => 'Oops...',
            'text'  => 'Passwords do not match.'
        ];
    } else {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, last_name, first_name, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $last_name, $first_name, $password);

        if ($stmt->execute()) {
            $swal = [
                'icon' => 'success',
                'title' => 'Signup Successful!',
                'text'  => 'You will be redirected to login.',
                'redirect' => true
            ];
        } else {
            $swal = [
                'icon' => 'error',
                'title' => 'Database Error',
                'text'  => htmlspecialchars($stmt->error)
            ];
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                <div class="card border border-light-subtle rounded-3 shadow-sm">
                    <div class="card-body p-3 p-md-4 p-xl-5">
                        <div class="text-center mb-3">
                            <a href="#!">
                                <img src="./assets/images/JII.png" alt="Logo" width="200" height="100">
                            </a>
                        </div>
                        <h2 class="fs-6 fw-normal text-center text-secondary mb-4">Create your account</h2>

                        <form method="POST" action="">
                            <div class="row gy-2 overflow-hidden">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                                        <label for="username">Username</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" required>
                                        <label for="first_name">First Name</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" required>
                                        <label for="last_name">Last Name</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating mb-3 position-relative">
                                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                        <label for="password">Password</label>
                                        <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer toggle-password" data-target="password" style="cursor:pointer;"></i>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3 position-relative">
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                                        <label for="confirm_password">Confirm Password</label>
                                        <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer toggle-password" data-target="confirm_password" style="cursor:pointer;"></i>
                                    </div>
                                    <div id="passwordHelp" class="form-text text-danger d-none">Passwords do not match.</div>
                                </div>

                                <div class="col-12">
                                    <div class="d-grid my-3">
                                        <button class="btn btn-success btn-lg" type="submit" id="submitBtn">Sign up</button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <p class="m-0 text-secondary text-center">Already have an account?
                                        <a href="login.php" class="link-primary text-decoration-none">Login</a>
                                    </p>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

<script>
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', () => {
            const targetId = icon.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    });

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordHelp = document.getElementById('passwordHelp');
    const submitBtn = document.getElementById('submitBtn');

    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            passwordHelp.classList.remove('d-none');
            submitBtn.disabled = true;
        } else {
            passwordHelp.classList.add('d-none');
            submitBtn.disabled = false;
        }
    }

    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
</script>
<?php if (!empty($swal)): ?>
    <script>
        Swal.fire({
            icon: '<?= $swal['icon'] ?>',
            title: '<?= $swal['title'] ?>',
            text: '<?= $swal['text'] ?>',
            timer: <?= isset($swal['redirect']) ? 1000 : 0 ?>,
            timerProgressBar: <?= isset($swal['redirect']) ? 'true' : 'false' ?>,
            showConfirmButton: true
        }).then(() => {
            <?php if (isset($swal['redirect'])): ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        });
    </script>
<?php endif; ?>