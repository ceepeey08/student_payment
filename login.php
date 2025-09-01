<?php
require_once 'dbconn.php';

$swal = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password, first_name, last_name FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashed_password, $first_name, $last_name);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name']  = $last_name;

            $swal = [
                'icon' => 'success',
                'title' => 'Login Successful!',
                'text' => 'Redirecting to dashboard...',
                'redirect' => true
            ];
        } else {
            $swal = [
                'icon' => 'error',
                'title' => 'Login Failed',
                'text' => 'Incorrect password.'
            ];
        }
    } else {
        $swal = [
            'icon' => 'error',
            'title' => 'Login Failed',
            'text' => 'User not found.'
        ];
    }

    $stmt->close();
    $conn->close();
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
                                <img src="./assets/images/JII.png" alt="BootstrapBrain Logo" width="200" height="100">
                            </a>
                        </div>
                        <h2 class="fs-6 fw-normal text-center text-secondary mb-4">Sign in to your account</h2>

                        <form method="POST" action="login.php">
                            <div class="row gy-2 overflow-hidden">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                                        <label for="username">Username</label>
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
                                    <div class="d-flex gap-2 justify-content-between">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="rememberMe">
                                            <label class="form-check-label text-secondary" for="rememberMe">Keep me logged in</label>
                                        </div>
                                        <a href="forgot_passwords.php" class="link-primary text-decoration-none">Forgot password?</a>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid my-3">
                                        <button class="btn btn-primary btn-lg" type="submit">Log in</button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <p class="m-0 text-secondary text-center">Don't have an account?
                                        <a href="signup.php" class="link-primary text-decoration-none">Sign up</a>
                                    </p>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    window.location.href = 'index.php';
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
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
</script>