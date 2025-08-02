<!--
    Project Extra Key Features:
    1. End-of-game notification
    2. Animations and/or transitions
    3. Game time with some music file
-->
<?php
session_start();
if (!isset($_SESSION['puzzle']['user_id'])) {
    header('Location: login.php?redirect');
    exit;
}

require 'database.php';
require 'uploader.php';

$username = $_SESSION['puzzle']['username'];
$backgrounds = [];

$sql = "SELECT image_id, image_name, image_url FROM background_images WHERE is_active = TRUE ORDER BY image_id DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $bg = array('id' => $row['image_id'], 'name' => $row['image_name'], 'path' => $row['image_url']);
    $backgrounds[] = $bg;
}

if (isset($_POST['submit']) && $_POST['submit'] == 'stats') {
    unset($_POST['submit']);

    $time = $_POST['time'];
    $moves = $_POST['moves'];
    $bg_id = json_decode($_POST['current_bg'], true)['id'];
    date_default_timezone_set("America/New_York");
    $date = date("Y-m-d");

    $sql = "INSERT INTO game_stats (user_id, puzzle_size, time_taken_seconds, moves_count, background_image_id, win_status, game_date)
        VALUES ('{$_SESSION['puzzle']['user_id']}', '4x4', '$time', '$moves', '$bg_id', true, '$date')";

    if ($conn->query($sql)) {
        header("Location: puzzle.php?action=stats&time=$time&moves=$moves&bg={$_POST['current_bg']}");
        exit;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="puzzle.css">
    <link rel="stylesheet" type="text/css" href="uploader.css">
    <link rel="icon" type="image/x-icon" href="images/icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fifteen Puzzle</title>
</head>

<body>
    <div id="menu">
        <?php echo $username; ?>
        <button id="menu-btn">Menu</button>
        <div id="menu-opts">
            <form action="login.php" method="post">
                <a id="account-opt">View My Account</a>
                <a id="bg-opt">Change Background</a>
                <a><button id="logout-btn" type="submit" id="btn" name="logout" value="true">Logout</button></a>
            </form>
        </div>
    </div>

    <div id="main">
        <h1 id="title">Bob's Burgers Sliding Puzzle</h1>
        <div id="message">&nbsp;</div>
        <div id="grid-board"></div>
        <div id="shuffle"><button id="shuffle-btn">Shuffle</button></div>
    </div>

    <div id="footer">
        <a href="https://validator.w3.org/"><img src="images/w3c-xhtml.png"></a>
        <a href="https://jigsaw.w3.org/css-validator/"><img src="images/w3c-css.png"></a>
    </div>

    <div id="gallery-container">
        <div id="gallery-header">
            <h3>Choose a Puzzle Background Below<br>
                <form id="upload-form" action="puzzle.php" method="post" enctype="multipart/form-data">
                    or upload one
                    <input type="file" accept=".png, .jpg, .jpeg, .webp, .bmp" name="fileToUpload" required>
                    <input id="upload-btn" type="submit" value="Upload File" name="submit">
                </form>
            </h3>
            <span id="gallery-close">&times;</span>
            <div id="upload-msg"><?php if (!empty($file_upload_msg))
                echo $file_upload_msg; ?></div>
        </div>
        <div id="gallery">
            <?php if (!empty($backgrounds)) {
                foreach ($backgrounds as $bg) {
                    echo '<div class="gallery-item">
                    <img class="bg-img" src="backgrounds/' . $bg['path'] . '" data-bg-id="' . $bg['id'] . '" data-bg-name="' . $bg['name'] . '" data-bg-path="' . $bg['path'] . '" data-bg="' . htmlspecialchars(json_encode($bg)) . '">
                    <div class="bg-name">' . $bg['name'] . '</div>
                    </div>';
                }
            } ?>
        </div>
    </div>

    <form action="puzzle.php" method="post" hidden>
        <input id="game_time" name="time" type="hidden">
        <input id="game_moves" name="moves" type="hidden">
        <input id="current_bg" name="current_bg" type="hidden">
        <button id="stats-btn" name="submit" value="stats" type="submit">
    </form>

    <audio id="bg-song" src="audio/bg-song.mp3"></audio>
    <script src="puzzle.js"></script>
</body>

</html>