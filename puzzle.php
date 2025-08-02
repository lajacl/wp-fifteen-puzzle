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
    <!-- Menu -->
    <div id="menu">
        <?php echo $username; ?>
        <button id="menu-btn">Menu</button>
        <div id="menu-opts">
            <form action="login.php" method="post">
                <a id="bg-opt">Change Background</a>
                <a id="pref-opt">My Preferences</a>
                <?php if (isset($_SESSION['puzzle']['role']) && $_SESSION['puzzle']['role'] == 'admin')
                    echo '<a id="admin-opt" >Admin Dashboard</a>'; ?>
                <a><button id="logout-opt" type="submit" id="btn" name="logout" value="true">Logout</button></a>
            </form>
        </div>
    </div>

    <!-- Game Title & Board -->
    <div id="main">
        <h1 id="title">Fifteen Puzzle</h1>
        <div id="message">&nbsp;</div>
        <div id="grid-board"></div>
        <div id="shuffle"><button id="shuffle-btn">Shuffle</button></div>
    </div>

    <!-- Footer -->
    <div id="footer">
        <a href="https://validator.w3.org/"><img src="images/w3c-xhtml.png"></a>
        <a href="https://jigsaw.w3.org/css-validator/"><img src="images/w3c-css.png"></a>
    </div>

    <!-- Backgrounds Gallery & Upload -->
    <div id="gallery-container">
        <div id="gallery-header">
            <h3>Choose a Puzzle Background Below<br>
                <form id="upload-form" action="puzzle.php" method="post" enctype="multipart/form-data">
                    or upload one
                    <input type="file" accept="image/*" name="fileToUpload" required>
                    <input id="upload-btn" type="submit" value="Upload File" name="submit">
                </form>
            </h3>
            <span id="gallery-close">&times;</span>
            <div id="upload-msg"><?php if (!empty($file_upload_msg))
                echo $file_upload_msg; ?></div>
        </div>
        <div id="gallery">
            <?php if (!empty($active_backgrounds)) {
                foreach ($active_backgrounds as $active_bg) {
                    echo '<div class="gallery-item">
                    <img class="bg-img" src="backgrounds/' . $active_bg['path'] . '" data-bg-id="' . $active_bg['id'] . '" data-bg-name="' . $active_bg['name'] . '" data-bg-path="' . $active_bg['path'] . '" data-bg="' . htmlspecialchars(json_encode($active_bg)) . '">
                    <div class="bg-name">' . $active_bg['name'] . '</div>
                    </div>';
                }
            } ?>
        </div>
    </div>

    <!-- User Preferences -->
    <div id="pref-container">
        <div id="pref-header">
            <h3>Game Preferences</h3>
            <span id="pref-close">&times;</span>
        </div>
        <form method="post" action="puzzle.php">
            <div id="pref">
                <label for="pref-size">Puzzle Size:</label>
                <select id="pref-size" name="pref-size" required disabled>
                    <option value="4x4">4x4</option>
                </select>

                <label for="pref-bg">Background:</label>
                <?php
                $pref_bg_path;
                echo '<div>';
                echo '<select id="pref-bg" name="pref-bg-id">';
                echo '<option value="">None</option>';
                if (!empty($active_backgrounds)) {
                    foreach ($active_backgrounds as $bg) {
                        echo '<option value="' . $bg['id'] . '" data-bg="' . htmlspecialchars(json_encode($bg)) . '"' . ((!empty($pref_bg_id) && $bg['id'] === $pref_bg_id) ? ' selected' : '') . '>' . $bg['name'] . '</option>';
                        if ($bg['id'] == $pref_bg_id)
                            $pref_bg_path = $bg['path'];
                    }
                }
                echo '</select>';
                echo '<img id="pref-bg-img" ' . (!empty($pref_bg_path) ? 'src="backgrounds/' . $pref_bg_path . '"' : "hidden") . '>';
                echo '</div>';
                ?>

                <span>Sound:</span>
                <div>
                    <label for="sound-on"><input id="sound-on" type="radio" name="pref-sound" value="1" required <?php if (empty($pref_sound) || $pref_sound == 1)
                        echo ' checked'; ?>>On</label>
                    <label for="sound-off"><input id="sound-off" type="radio" name="pref-sound" value="0" required <?php if ($pref_sound == 0)
                        echo ' checked'; ?>>Off</label>
                </div>

                <span>Animations:</span>
                <div><label for="anim-on"><input id="anim-on" type="radio" name="pref-anim" value="1" required <?php if (empty($pref_anim) || $pref_anim == 1)
                    echo ' checked'; ?>>On</label>
                    <label for="anim-off"><input id="anim-off" type="radio" name="pref-anim" value="0" required <?php if ($pref_anim === 0)
                        echo ' checked'; ?>>Off</label>
                </div>

                <button id="prefs-btn" type="submit" name="submit" value="prefs">Save</button>
            </div>
        </form>
    </div>

    <!-- Admin Dashboard -->
    <div id="admin-dashboard" data-active="<?php echo ($_SESSION['puzzle']['role'] == 'admin') ? true : false; ?>">
        <div id="admin-header">
            <h3>Admin Dashboard</h3>
            <span id="admin-close">&times;</span>
        </div>
        <!-- Admin Tab Links -->
        <div class="tab">
            <button type="button" class="tablinks" onclick="openTab(event, 'admin-stats')" id="defaultOpen">Game
                Statistics</button>
            <button type="button" class="tablinks" onclick="openTab(event, 'admin-users')">Manage
                Users</button>
            <button type="button" class="tablinks" onclick="openTab(event, 'admin-content')">Manage
                Content</button>
            <button type="button" class="tablinks" onclick="openTab(event, 'admin-news')">Update Announcements /
                News</button>
        </div>

        <!-- Admin: Game Statistics -->
        <div id="admin-stats" class="tabcontent">
            <h3>Game Statistics</h3>
            <div id="stats-grid">
                <div id="general_stats">
                    <p>Total games played: <span class=stat><?php echo $tot_games; ?></p>
                    <p>Average time per game (s): <?php echo $avg_time; ?></p>
                    <p>Average moves per game: <?php echo $avg_moves; ?></p>
                </div>
                <div>
                    <h4>Most Frequent Backgrounds</h4>
                    <div id="bg-freq-chart">
                        <?php foreach ($bg_freq_list as $bg_freq) {
                            $bar_height_percentage = $bg_freq['frequency'] * 10;
                            echo '<div class="bar-wrapper">';
                            echo '<div class="bar" style="height: ' . $bar_height_percentage . '%;" title="' . $bg_freq['frequency'] . '"></div>';
                            echo '<span class="bar-label">' . $bg_freq['name'] . '</span>';
                            echo '</div>';
                        } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin: User Management -->
        <div id="admin-users" class="tabcontent">
            <h3>Manage Users</h3>
            <table id="user-table">
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Last Login</th>
                    <th>Register Date</th>
                    <th colspan="2">Actions</th>
                </tr>
                <?php foreach ($user_list as $user) {
                    echo '<tr data-user="' . htmlspecialchars(json_encode($user)) . '">';
                    echo '<td>' . $user['username'] . '</td><td>' . $user['role'] . '</td><td>' . $user['email'] . '</td><td>' . $user['last_login'] . '</td><td>' . $user['registration_date'] . '</td>';
                    echo '<td class="admin-action"><a href="#">Edit</a></td><td class="admin-action"><a href="#">Delete</a></td>';
                    echo '</tr>';
                } ?>
            </table>
        </div>

        <!-- Admin: Content Management  -->
        <div id="admin-content" class="tabcontent">
            <h3>Manage Content</h3>
            <form id="upload-form" action="puzzle.php" method="post" enctype="multipart/form-data">
                Upload a new background
                <input type="file" accept="image/*" name="fileToUpload" required>
                <input id="upload-btn" type="submit" value="Upload File" name="submit">
            </form>
            <table id="bg-table">
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Path</th>
                    <th>Is Active</th>
                    <th>Upload User id</th>
                    <th colspan="3">Actions</th>
                </tr>
                <?php foreach ($background_list as $bg) {
                    echo '<tr data-bg="' . htmlspecialchars(json_encode($bg)) . '">';
                    echo '<td><img class="bg-img" src="backgrounds/' . $bg['image_url'] . '"></td><td>' . $bg['image_name'] . '</td><td>' . $bg['image_url'] . '</td><td>' . ($bg['is_active'] ? 'yes' : 'no') . '</td><td>' . $bg['uploaded_by_user_id'] . '</td>';
                    echo '<td class="admin-action"><a href="#">Edit</a></td><td class="admin-action"><a href="#">' . ($bg['is_active'] ? 'Deactivate' : 'Activate') . '</a></td><td class="admin-action"><a href="#">Delete</a></td>';
                    echo '</tr>';
                } ?>
            </table>
        </div>

        <!-- Admin: News / Announcements -->
        <div id="admin-news" class="tabcontent">
            <h3>Announcement / News</h3>
            <div id="news-wrapper">
                <p>Add a game annoucement or news to be shown in a banner on the game page.</p>
                <label for="news-text">Update:</label>
                <textarea id="news-text"></textarea>
                <div><button id="news-btn" type="submit" name="submit" value="news">Save</button></div>
            </div>
        </div>

        <!-- Hidden Puzzle Data Form -->
        <form action="puzzle.php" method="post" hidden>
            <input id="game_time" name="time" type="hidden">
            <input id="game_moves" name="moves" type="hidden">
            <input id="current_bg" name="current_bg" type="hidden">
            <button id="stats-btn" name="submit" value="stats" type="submit">
        </form>

        <!-- External File Linking -->
        <audio id="bg-song" src="audio/bg-song.mp3"></audio>
        <script src="puzzle.js"></script>
</body>

</html>