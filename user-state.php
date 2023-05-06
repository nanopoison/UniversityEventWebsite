<?php
require_once 'config.php';

$is_rso_member = false;

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $admin_level = $_SESSION['admin_level'];
    
    // Check if user is in the students table
    $query = "SELECT student_id FROM students WHERE user_id = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $is_rso_member = true;
        }
        mysqli_stmt_close($stmt);
    }
    
    // Check if user is in the admins table
    $query = "SELECT admin_id FROM admins WHERE user_id = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $is_rso_member = true;
        }
        mysqli_stmt_close($stmt);
    }
}

function is_super_admin($user_id) {
    $attempt = false;
    global $link;
    $sql = "SELECT user_id FROM super_admins WHERE user_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if ($user_id = $stmt) {
            $attempt = true;
        }
        mysqli_stmt_close($stmt);
    }
    return $attempt;
}

function has_university($user_id) {
    global $link;

    $sql = "SELECT * FROM universities WHERE super_admin_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $num_rows = mysqli_stmt_num_rows($stmt);

        mysqli_stmt_close($stmt);

        return $num_rows > 0;
    } else {
        return false;
    }
}

function get_name_by_rso_id($rso_id) {
    global $link;

    $sql = "SELECT name FROM rsos WHERE rso_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('s', $rso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['name'];
}

function is_member_of_university($user_id) {
    global $link;

    $sql = "SELECT * FROM students WHERE user_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $num_rows = mysqli_stmt_num_rows($stmt);

        mysqli_stmt_close($stmt);

        return $num_rows > 0;
    } else {
        return false;
    }
}

function get_user_university_id($link, $user_id) {
    $sql = "SELECT university_id FROM 
            (SELECT user_id, university_id FROM students 
             UNION SELECT user_id, university_id FROM admins 
             UNION SELECT user_id, university_id FROM super_admins) AS u
            WHERE u.user_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return null;
    }
    $row = $result->fetch_assoc();
    return $row["university_id"];
}

function get_user_rso_id($link, $user_id) {
    $sql = "SELECT rso_id FROM 
            (SELECT user_id, rso_id FROM students 
             UNION SELECT user_id, rso_id FROM admins) AS u
            WHERE u.user_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return null;
    }
    $row = $result->fetch_assoc();
    return $row["rso_id"];
}
