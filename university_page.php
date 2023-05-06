<?php
require_once 'config.php';
require_once 'user-state.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

global $university_id;

if (isset($_GET['university_id'])) {
    $university_id = $_GET['university_id'];
} else {
    header('Location: index.php');
    exit();
}

function create_rso($link, $rso_name, $user_id)
{
    global $university_id;

    $sql = "INSERT INTO rsos (name, university_id) VALUES (?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("ss", $rso_name, $university_id);
    $stmt->execute();

    $rso_id = $link->insert_id;

    $sql = "UPDATE admins SET rso_id = ? WHERE user_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('ss', $rso_id, $user_id);
    $stmt->execute();
}

function create_event(
    $link,
    $event_name,
    $event_location_name,
    $event_location_address,
    $event_type,
    $phone,
    $email,
    $event_description,
    $visibility,
    $event_date,
    $event_time,
    $user_id
) {
    $sql = "SELECT rso_id FROM admins WHERE user_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rso_id = $result->fetch_assoc()['rso_id'];

    $_SESSION['rso_id'] = $rso_id;

    $sql = "INSERT INTO events (name, event_category, description, time, date, 
        location, contact_phone, contact_email, rso_id, visibility, lname)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param(
        "sssssssssss",
        $event_name,
        $event_type,
        $event_description,
        $event_time,
        $event_date,
        $event_location_address,
        $phone,
        $email,
        $rso_id,
        $visibility,
        $event_location_name
    );
    $stmt->execute();
}

function create_comment($link, $event_id, $user_id, $content, $rating)
{

    $sql = "INSERT INTO comments (event_id, user_id, content, rating) VALUES (?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("ssss", $event_id, $user_id, $content, $rating);
    $stmt->execute();
}

if (isset($_POST)) {

    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["createRSO"])) {
        $name = $_POST['rso-name'];
        create_rso($link, $name, $_SESSION['id']);
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['createEvent'])) {
        $name = $_POST['event-name'];
        $lname = $_POST['event-location-name'];
        $address = $_POST['event-location-address'];
        $type = $_POST['event-type'];
        $phone = $_POST['event-phone'];
        $email = $_POST['event-email'];
        $description = $_POST['event-description'];
        $visibility = $_POST['visibility'];
        $event_date = $_POST['event-date'];
        $event_time = $_POST['event-time'];
        create_event(
            $link,
            $name,
            $lname,
            $address,
            $type,
            $phone,
            $email,
            $description,
            $visibility,
            $event_date,
            $event_time,
            $_SESSION['id']
        );
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['createComment'])) {
        create_comment($link, $_POST['event_id'], $_SESSION['id'], $_POST['content'], $_POST['rating']);
    }
}

if (isset($_GET['joinUniversity'])) {
    $user_id = $_SESSION['id'];
    $university_id = $_GET['university_id'];

    $sql_student = "SELECT * FROM students WHERE user_id = ?";
    $stmt_student = $link->prepare($sql_student);
    $stmt_student->bind_param("i", $user_id);
    $stmt_student->execute();
    $result_student = $stmt_student->get_result();

    $sql_admin = "SELECT * FROM admins WHERE user_id = ?";
    $stmt_admin = $link->prepare($sql_admin);
    $stmt_admin->bind_param("i", $user_id);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_student->num_rows === 1) {
        $sql_update_student = "UPDATE students SET university_id = ? WHERE user_id = ?";
        $stmt_update_student = $link->prepare($sql_update_student);
        $stmt_update_student->bind_param("ii", $university_id, $user_id);
        $stmt_update_student->execute();

        $_SESSION['university_id'] = $university_id;
    } else if ($result_admin->num_rows === 1) {
        $sql_update_admin = "UPDATE admins SET university_id = ? WHERE user_id = ?";
        $stmt_update_admin = $link->prepare($sql_update_admin);
        $stmt_update_admin->bind_param("ii", $university_id, $user_id);
        $stmt_update_admin->execute();

        $_SESSION['university_id'] = $university_id;
    } else {
        echo '<script>alert("You can\'t join as a super admin!")</script>';
    }

}

$user_id = $_SESSION['id'];
$sql = "SELECT * FROM admins WHERE user_id = '$user_id' AND university_id = '$university_id'";
$result = mysqli_query($link, $sql);

if ($result !== false && mysqli_num_rows($result) == 1) {
    $row = $result->fetch_assoc();
    if (!isset($row['rso_id'])) {
        $create_rso_button = '<button id="create-rso-button" class="create-rso-button">Create RSO</button>';
        $create_event_button = '';
        $scr = '
        <script>
        var createRSOModal = document.getElementById("create-rso-modal");

        // Get the button that opens the modals
        var createRSOButton = document.getElementById("create-rso-button");
        // When the user clicks on the button, open the modal
        createRSOButton.onclick = function() {
            createRSOModal.style.display = "block";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == createRSOModal) {
                createRSOModal.style.display = "none";
            }
        }
        </script>
        ';
    } else {
        $create_rso_button = '';
        $create_event_button = '<button id="create-event-button" class="create-event-button">Create Event</button>';
        $scr = '
        <script>
        var createEventModal = document.getElementById("create-event-modal");
        var createEventButton = document.getElementById("create-event-button");

        createEventButton.onclick = function() {
            createEventModal.style.display = "block";
        }

        window.onclick = function(event) {
            if (event.target == createEventModal) {
                createEventModal.style.display = "none";
            }
        }
        </script>
    ';
    }
} else {
    $create_rso_button = '';
    $create_event_button = '';
    $scr = '';
}

$sql = "SELECT name FROM universities WHERE university_id = '$university_id'";
$result = mysqli_query($link, $sql);

$row = mysqli_fetch_assoc($result);
$university_name = $row['name'];

$sql = "SELECT event_id, name, event_category, description, time, date, location, contact_phone, contact_email, rso_id, visibility, lname FROM events WHERE rso_id IN
        (SELECT rso_id FROM rsos WHERE university_id = '$university_id')";
$result = mysqli_query($link, $sql);
$events = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo $university_name; ?> - Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="university_page.css">
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

    <div id="create-rso-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create RSO</h2>
            <form action="" method="post">
                <label for="rso-name">RSO Name:</label>
                <input type="text" id="rso-name" name="rso-name" required>
                <input type="submit" value="Create RSO" name="createRSO">
            </form>
        </div>
    </div>

    <!-- Event creation modal -->
    <div id="create-event-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create Event</h2>
            <form action="" method="post">
                <label for="event-name">Event Name:</label>
                <input type="text" id="event-name" name="event-name" required>
                <label for="event-location-name">Location Name:</label>
                <input type="text" id="event-location-name" name="event-location-name" required>
                <label for="event-address">Address:</label>
                <input type="text" id="event-location-address" name="event-location-address" required>
                <label for="event-type">Event Type:</label>
                <input type="text" id="event-type" name="event-type" required>
                <label for="event-phone">Contact Phone:</label>
                <input type="text" id="event-phone" name="event-phone" required>
                <label for="event-email">Contact Email:</label>
                <input type="text" id="event-email" name="event-email" required>
                <label for="event-description">Event Description:</label>
                <textarea id="event-description" name="event-description"></textarea>
                <label for="visibility">Visibility:</label>
                <select id="visibility" name="visibility">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                    <option value="rso">RSO</option>
                </select>
                <label for="event-date">Event Date:</label>
                <input type="date" id="event-date" name="event-date" required>
                <label for="event-time">Event Time:</label>
                <input type="time" id="event-time" name="event-time" required>
                <input type="submit" value="Create Event" name="createEvent">
            </form>
        </div>
    </div>

    <h1><?php echo $university_name; ?> - Events</h1>
    <?php
    echo $create_rso_button;
    echo $create_event_button;
    echo $scr;
    ?>
    <?php if (count($events) > 0) : ?>
        <table class='university-table'>
            <thead class='table-header'>
                <tr>
                    <th class='table-header'>Name</th>
                    <th class='table-header'>Date</th>
                    <th class='table-header'>Time</th>
                    <th class='table-header'>Location</th>
                    <th class='table-header'>RSO</th>
                    <th class='table-header'>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event) : ?>
                    <?php
                    $shouldcontinue = false;
                    switch ($event['visibility']) {
                        default:
                            break;
                        case 'private':
                            if ($_SESSION['admin_level'] == 'super_admin')
                                break;
                            if (
                                !isset($_SESSION['university_id']) ||
                                $_SESSION['university_id'] != $university_id
                            ) {
                                $shouldcontinue = true;
                            }
                            break;
                        case 'rso':
                            if ($_SESSION['admin_level'] == 'super_admin')
                                break;
                            if (
                                !isset($_SESSION['rso_id']) ||
                                $_SESSION['rso_id'] != $event['rso_id']
                            ) {
                                $shouldcontinue = true;
                            }
                            break;
                    }
                    if ($shouldcontinue)
                        continue;
                    ?>

                    <div id="event-modal-<?php echo $event['event_id'] ?>" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2 class="event-name"><?php echo $event['name']; ?></h2>
                            <h3><?php echo get_name_by_rso_id($event['rso_id']) ?></h3>
                            <div class="event-details">
                                <p class="event-category">Type: <?php echo $event['event_category']; ?></p>
                                <p class="event-date">Date: <?php echo $event['date']; ?></p>
                                <p class="event-time">Time: <?php echo $event['time']; ?></p>
                                <p class="event-lname">Location: <?php echo $event['lname']; ?></p>
                                <p class="event-location">Address: <?php echo $event['location']; ?></p>
                                <p class="event-contact-phone">Phone: <?php echo $event['contact_phone']; ?></p>
                                <p class="event-contact-email">Email: <?php echo $event['contact_email']; ?></p>
                                <p class="event-visibility">Who can come? <?php echo $event['visibility']; ?></p>
                            </div>
                            <div class="comments">
                                <form method="post">
                                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                    <label for="content">Comment:</label>
                                    <textarea id="content" name="content"></textarea>
                                    <label for="rating">Rating:</label>
                                    <select id="rating" name="rating">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                    <button type="submit" name="createComment">Submit</button>
                                </form>

                                <?php
                                $eid = $event['event_id'];
                                $sql = "SELECT comment_id, user_id, content, rating FROM comments WHERE event_id = '$eid'";
                                $result = mysqli_query($link, $sql);
                                $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

                                foreach ($comments as $comment) :   ?>
                                    <div class="comment">
                                        <p>User ID: <?php echo $comment['user_id']; ?></p>
                                        <p><?php echo $comment['content']; ?></p>
                                        <p>Rating: <?php echo $comment['rating']; ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <tr>
                            <td><?php echo $event['name']; ?></td>
                            <td><?php echo $event['date']; ?></td>
                            <td><?php echo $event['time']; ?></td>
                            <td><?php echo $event['location']; ?></td>
                            <td><?php echo get_name_by_rso_id($event['rso_id']) ?></td>
                            <td><button class='details-button' onclick="openModal(<?php echo $event['event_id'] ?>)">View Event</button></td>
                        </tr>

                        <script>
                            var modal = document.getElementById("event-modal-" + <?php echo $event['event_id'] ?>);

                            var btn = document.querySelector(".details-button");

                            var span = document.getElementsByClassName("close")[0];

                            window.onclick = function(event) {
                                for (var i = 0; i <= <?php echo $event['event_id'] ?>; i++) {
                                    var m2 = document.getElementById("event-modal-" + i);
                                    if (m2 != null)
                                        if (event.target == m2)
                                            m2.style.display = "none";
                                }
                            }

                            function openModal(id) {
                                var modal = document.getElementById("event-modal-" + id);
                                modal.style.display = "block";
                            }

                            function closeModal() {
                                for (var i = 0; i <= <?php echo $event['event_id'] ?>; i++) {
                                    console.log(i);
                                    var modal = document.getElementById("event-modal-" + i);
                                    if (modal != null)
                                        modal.style.display = "none";
                                }
                            }
                        </script>
                    <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No events to display.</p>
    <?php endif; ?>

</body>

</html>