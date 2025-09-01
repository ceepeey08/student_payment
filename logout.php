<?php
session_start();
session_unset();
session_destroy();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <script>
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.pushState(null, null, location.href);
        };

        setTimeout(() => {
            location.replace('login.php');
        }, 100);
    </script>
</head>

<body>
    <p>Logging out...</p>
</body>

</html>