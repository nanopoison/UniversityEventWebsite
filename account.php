<?php
require_once 'config.php';
require_once 'user-state.php';

if (!isset($_SESSION['loggedin'])) {
	header('Location: login.php');
	exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['update_name'])) {
		$name = $_POST['name'];

		$stmt = $link->prepare('UPDATE users SET name=? WHERE user_id=?');
		$stmt->bind_param('si', $name, $_SESSION['id']);
		$stmt->execute();
		$stmt->close();

		$_SESSION['name'] = $name;
		header('Location: account.php');
		exit;
	}

	if (isset($_POST['update_password'])) {
		$old_password = $_POST['previous_password'];
		$new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

		$stmt = $link->prepare('SELECT password FROM users WHERE user_id=?');
		$stmt->bind_param('i', $_SESSION['id']);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$stmt->close();

		if (password_verify($old_password, $row['password'])) {
			$stmt = $link->prepare('UPDATE users SET password=? WHERE id=?');
			$stmt->bind_param('si', $new_password, $_SESSION['id']);
			$stmt->execute();
			$stmt->close();

			header('Location: account.php');
			exit;
		} else {
			$error = 'Incorrect password';
		}
	}
}

$stmt = $link->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>

<head>
	<title>Account</title>
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

	<h1>Your Account</h1>

	<div class="container">
		<form method="POST">
			<div class="form-group">
				<label for="name">Name:</label>
				<input type="text" name="name" id="name" value="<?php echo $user['name']; ?>" required>
				<button type="submit" name="update_name">Update Name</button>
			</div>
		</form>
		<form method="POST">
			<div class="form-group">
				<label for="old_password">Previous Password:</label>
				<input type="password" name="previous_password" id="previous_password" required>
				<label for="new_password">New Password:</label>
				<input type="password" name="new_password" id="new_password" required>
				<button type="submit" name="update_password">Update Password</button>
			</div>
		</form>
	</div>

	<footer>
		<div class="footer-container">
			<p>&copy; 2023 Tristan Event Company (not real)</p>
		</div>
	</footer>
</body>

</html>