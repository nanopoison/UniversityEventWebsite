<?php
require_once 'config.php';
require_once 'user-state.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    header("Location: account.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT user_id, email, password, admin_level FROM users WHERE email = ?";

    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $email);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $admin_level);

                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['id'] = $id;
                        $_SESSION['email'] = $email;
                        $_SESSION['admin_level'] = $admin_level;

                        $_SESSION['university_id'] = get_user_university_id($link, $id);
                        $_SESSION['rso_id'] = get_user_rso_id($link, $id);

                        header("Location: account.php");
                    } else {
                        $error_message = "Incorrect password.";
                    }
                }
            } else {
                $error_message = "Email not found.";
            }
        } else {
            $error_message = "Something went wrong. Please try again later.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Something went wrong. Please try again later.";
    }

    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="login.css">
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                    $user_id = $_SESSION['id'];
                    $university_id = get_user_university_id($link, $user_id);
                    if ($university_id) { ?>
                        <li><a href="university.php">Browse Universities</a></li>
                        <li><a href="university_page.php?university_id=<?php echo $university_id; ?>">University</a></li>
                    <?php } else { ?>
                        <li><a href="university.php">Browse Universities</a></li>
                    <?php } ?>
                    <li><a href="account.php">Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php } else { ?>
                    <li class="active"><a href="login.php">Login</a></li>
                <?php } ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        <form action="login.php" method="post">
            <h2>Login</h2>
            <?php if (isset($error_message)) { ?>
                <div class="error-message"><?php echo $error_message ?></div>
            <?php } ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>.
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2023 Tristan Event Company (not real)</p>
    </footer>
</body>

</html>