<?php
require_once "config.php";
require_once 'user-state.php';

$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (empty(trim($_POST["name"]))) {
		$name_err = "Please enter your name.";
	} else {
		$name = trim($_POST["name"]);
	}

	if (empty(trim($_POST["email"]))) {
		$email_err = "Please enter your email.";
	} else {
		$sql = "SELECT user_id FROM users WHERE email = ?";

		if ($stmt = mysqli_prepare($link, $sql)) {
			mysqli_stmt_bind_param($stmt, "s", $param_email);

			$param_email = trim($_POST["email"]);

			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_store_result($stmt);

				if (mysqli_stmt_num_rows($stmt) == 1) {
					$email_err = "This email is already taken.";
				} else {
					$email = trim($_POST["email"]);
				}
			} else {
				echo "Oops! Something went wrong. Please try again later.";
			}

			mysqli_stmt_close($stmt);
		}
	}

	if (empty(trim($_POST["password"]))) {
		$password_err = "Please enter a password.";
	} elseif (strlen(trim($_POST["password"])) < 6) {
		$password_err = "Password must have at least 6 characters.";
	} else {
		$password = trim($_POST["password"]);
	}

	if (empty(trim($_POST["confirm_password"]))) {
		$confirm_password_err = "Please confirm password.";
	} else {
		$confirm_password = trim($_POST["confirm_password"]);
		if (empty($password_err) && ($password != $confirm_password)) {
			$confirm_password_err = "Password did not match.";
		}
	}

	echo "Name Error: " . $name_err . "<br>";
	echo "Email Error: " . $email_err . "<br>";
	echo "Password Error: " . $password_err . "<br>";
	echo "Confirm Password Error: " . $confirm_password_err . "<br>";

	if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {

		$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";

		if ($stmt = mysqli_prepare($link, $sql)) {
			mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_email, $param_password);

			$param_name = $name;
			$param_email = $email;
			$param_password = password_hash($password, PASSWORD_DEFAULT);

			if (mysqli_stmt_execute($stmt)) {
				header("location: login.php");
			} else {
				echo mysqli_error($link);
				echo "Oops! Something went wrong. Please try again later.";
			}

			mysqli_stmt_close($stmt);
		}
	}

	mysqli_close($link);
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Create Account</title>
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
		<form action="register.php" method="post">
			<h2>Create Account</h2>
			<div class="form-group">
				<label for="name"><b>Name</b></label>
				<input type="text" name="name" id="name" required placeholder="Enter name">
			</div>
			<div class="form-group">
				<label for="email"><b>Email</b></label>
				<input type="email" placeholder="Enter Email" name="email" required>
			</div>
			<div class="form-group">
				<label for="password"><b>Password</b></label>
				<input type="password" placeholder="Enter Password" name="password" required>
			</div>
			<div class="form-group">
				<label for="confirm_password"><b>Confirm Password</b></label>
				<input type="password" placeholder="Confirm Password" name="confirm_password" required>
			</div>
			<button type="submit">Create Account</button>
		</form>
		<div class="register-link">
			Already have an account? <a href="login.php">Login Here</a>.
		</div>
	</div>

	<footer>
		<p>&copy; 2023 Tristan Event Company (not real)</p>
	</footer>
</body>

</html>