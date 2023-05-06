<?php
include 'config.php';
include 'user-state.php';

function create_university($link, $university_name, $university_description, $user_id)
{

    $sql = "INSERT INTO universities (name, description) VALUES (?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("ss", $university_name, $university_description);
    $stmt->execute();

    $university_id = $link->insert_id;

    $sql = "UPDATE super_admins SET university_id = :university_id WHERE user_id = :user_id";
    $stmt = $link->prepare($sql);
    $stmt->bindParam(':university_id', $university_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header("Location: university_page.php?university_id=$university_id");
    exit;
}

if (isset($_POST)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["createUniversity"])) {
        $name = $_POST['universityName'];
        $description = $_POST['universityDescription'];

        create_university($link, $name, $description, $_SESSION['id']);
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>University</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="university.css">
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
    <h1>Find Your University</h1>
    <?php
    if ($_SESSION["admin_level"] == "super_admin") {
        echo '<button id="createUniversityBtn" class="create-university-button">Create a University</button>';
    }
    ?>
    <form method="post">
        <label for="search">Search for a university by name or id:</label>
        <input type="text" id="search" name="search">
        <input type="submit" value="Search">
    </form>
    <?php
    $query = "SELECT * FROM universities";

    if (isset($_POST['search'])) {
        $search = mysqli_real_escape_string($link, $_POST['search']);
        $query .= " WHERE university_id LIKE '%$search%' OR name LIKE '%$search%'";
    }

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {

        echo "<table class='university-table'>";
        echo "<tr><th class='table-header'>University ID</th><th class='table-header'>University Name</th><th class='table-header'>Description</th><th class='table-header'></th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr class='table-row'>";
            echo "<td class='table-cell'>" . $row['university_id'] . "</td>";
            echo "<td class='table-cell'><a href='university_page.php?university_id=" . $row['university_id'] . "'>" . $row['name'] . "</a></td>";
            echo "<td class='table-cell'>" . $row['description'] . "</td>";
            echo "<input type='hidden' name='joining' value='true'>";
            echo "<td class='table-cell'><button class='join-button' onclick='joinUniversity(" . $row['university_id'] . ")'>Join</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='no-results'>No results found.</p>";
    }
    ?>

    <?php
    if ($_SESSION['admin_level'] == 'super_admin') {
    ?>
        <div id="createUniversityModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h1>Create a University</h1>
                <form action="university.php" method="post">
                    <label for="universityName">University Name:</label>
                    <input type="text" id="universityName" name="universityName" required>
                    <br><br>
                    <label for="universityDescription">University Description:</label>
                    <textarea id="universityDescription" name="universityDescription" rows="4" cols="50" required></textarea>
                    <br><br>
                    <input type="submit" value="Create University" name="createUniversity">
                </form>
            </div>
        </div>

    <?php
    }
    ?>

    <script src="redirect.js"></script>

    <footer>
        <div class="footer-container">
            <p>&copy; 2023 Tristan Event Company (not real)</p>
        </div>
    </footer>
</body>

</html>