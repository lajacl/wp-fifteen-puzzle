<?php
session_start();

if (!isset($_SESSION['puzzle']['user_id'])) {
    header('Location: login.php?redirect');
    exit;
}

/* Database connection */
$host = "localhost";
$user = "";
$pass = "";
$dbname = "";

$conn = new mysqli($host, $user, $pass, $dbname);
$username = $_SESSION['puzzle']['username'];

/* Get all active backgrounds */
$active_backgrounds = [];
$sql = "SELECT image_id, image_name, image_url FROM background_images WHERE is_active = TRUE ORDER BY image_id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bg = array('id' => $row['image_id'], 'name' => $row['image_name'], 'path' => $row['image_url']);
        $active_backgrounds[] = $bg;
    }
}

/* Get user preferences */
$pref_size;
$pref_bg_id;
$pref_sound;
$pref_anim;
$sql = "SELECT * from user_preferences where user_id = {$_SESSION['puzzle']['user_id']} LIMIT 1 ";

if ($result = $conn->query($sql)) {
    $row = $result->fetch_assoc();
    if (!empty($row)) {
        $pref_size = $row['default_puzzle_size'];
        $pref_bg_id = $row['preferred_background_image_id'];
        $pref_sound = $row['sound_enabled'];
        $pref_anim = $row['animations_enabled'];
    }
}

/* Update user preferences */
if (isset($_POST['submit']) && $_POST['submit'] == 'prefs') {
    unset($_POST['submit']);

    $sql = "INSERT INTO user_preferences (user_id, default_puzzle_size, preferred_background_image_id, sound_enabled, animations_enabled) 
        VALUES('{$_SESSION['puzzle']['user_id']}', '4x4', '{$_POST['pref-bg-id']}', '{$_POST['pref-sound']}', '{$_POST['pref-anim']}') ON DUPLICATE KEY UPDATE
        user_id = VALUES(user_id),
        default_puzzle_size = VALUES(default_puzzle_size), 
        preferred_background_image_id = VALUES(preferred_background_image_id), 
        sound_enabled = VALUES(sound_enabled), 
        animations_enabled = VALUES(animations_enabled)";

    $conn->query($sql);
}

/* Update user game stats */
if (isset($_POST['submit']) && $_POST['submit'] == 'stats') {
    unset($_POST['submit']);

    $time = $_POST['time'];
    $moves = $_POST['moves'];
    $bg_id = json_decode($_POST['current_bg'], true)['id'];
    date_default_timezone_set("America/New_York");
    $date = date("Y-m-d");
    $games_won = null;

    $sql = "INSERT INTO game_stats (user_id, puzzle_size, time_taken_seconds, moves_count, background_image_id, win_status, game_date)
        VALUES ('{$_SESSION['puzzle']['user_id']}', '4x4', '$time', '$moves', '$bg_id', true, '$date')";

    if ($conn->query($sql)) {
        $sql = "SELECT count(*) as games_won from game_stats where user_id = {$_SESSION['puzzle']['user_id']} and win_status = true";
        $games_won = $conn->query($sql)->fetch_assoc()['games_won'];
    }

    header("Location: puzzle.php?action=stats&time=$time&moves=$moves&bg={$_POST['current_bg']}&wins=$games_won");
    exit;
}

/* Get Admin Data if Admin User*/
if ($_SESSION['puzzle']['role'] == 'admin') {
    /* Get all users data */
    $user_list = [];
    $sql = "SELECT user_id, username, email, `role`, registration_date, last_login FROM users";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user_list[] = $row;
        }
    }

    /* Get all backgrounds */
    $background_list = [];
    $sql = "SELECT * FROM background_images ORDER BY is_active DESC, image_id DESC";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $background_list[] = $row;
        }
    }

    /* Get game stats */
    $tot_games;
    $avg_time;
    $avg_moves;
    $bg_freq_list = [];
    $sql = "SELECT count(*) as tot_games, ROUND(AVG(time_taken_seconds)) as avg_time, ROUND(AVG(moves_count)) as avg_moves FROM game_stats";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tot_games = $row['tot_games'];
        $avg_time = $row['avg_time'];
        $avg_moves = $row['avg_moves'];
    }

    $sql = "SELECT background_image_id as id, COUNT(*) as frequency, image_name as name, image_url as path from game_stats
        LEFT JOIN background_images on background_image_id = image_id GROUP BY background_image_id ORDER BY frequency DESC LIMIT 5";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bg_freq_list[] = $row;
        }
    }
}

/* Handle image file upload */
if (isset($_POST['submit']) && $_POST['submit'] == 'Upload File' && isset($_FILES['fileToUpload'])) {
    unset($_POST['submit']);
    $file_upload_msg = "";
    $target_dir = "backgrounds/";
    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . $file_name;
    $formatted_file_name = ucwords(strtolower(str_replace(["'"], "\'", str_replace(['_', '.'], ' ', trim(pathinfo($file_name, PATHINFO_FILENAME))))));

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO background_images (image_name, image_url, uploaded_by_user_id) VALUES ('$formatted_file_name', '{$conn->real_escape_string($file_name)}', {$_SESSION['puzzle']['user_id']})";

        if ($conn->query($sql) === TRUE) {
            $file_upload_msg = 'Background uploaded';
        } else {
            $file_upload_msg = 'Background upload failed';
        }
    } else {
        $file_upload_msg = "There was an error uploading your file. Please try again.";
    }
    unset($_FILES["fileToUpload"]);
}

$conn->close();
?>