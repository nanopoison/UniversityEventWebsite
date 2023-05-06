<?php
require_once 'config.php';
require_once 'user-state.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Home Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style.css">
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


    <main>
        <div class="main-container">
            <h1>Welcome to the University Event Website!</h1>
            <p>Here you can view upcoming events happening on any campus.</p>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <p>&copy; 2023 Tristan Event Company (not real)</p>
        </div>
    </footer>
</body>

</html>