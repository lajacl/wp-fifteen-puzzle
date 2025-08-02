<?php
session_start();
unset($_SESSION['puzzle']);

if (isset($_POST['logout'])) {
    unset($_POST['logout']);
}

if (isset($_GET['redirect']) && isset($_POST['play'])) {
    unset($_GET['redirect']);
}

/* Database connection */
$host = "localhost";
$user = "";
$pass = "";
$dbname = "";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo '<script>console.log("DATABASE Could not connect to server Connection failed:",' . json_encode($conn->connect_error) . ');</script>';
} else {
    echo '<script>console.log("DATABASE Connection established");</script>';
}
echo '<script>console.log("Server Info:",' . json_encode(mysqli_get_server_info($conn)) . ');</script>';

if (isset($_POST['play'])) {
    $input_type;
    $username = "";
    $password = "";
    $email = "";
    $error_msg = "";
    $default_role = 'player';

    date_default_timezone_set("America/New_York");
    $datetime = date("Y-m-d H:i:s");

    if (isset($_POST['input_type'])) {
        $input_type = $_POST['input_type'];
    }

    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    }

    if (isset($_POST['password'])) {
        $password = trim($_POST['password']);
    }

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    if (isset($_GET['redirect'])) {
        $error_msg = "Please login to play.";
    } elseif (!empty($input_type) && !empty($username) && !empty($password) && ($input_type == "login" || !empty($email))) {
        if ($input_type == "login") {
            $sql = "SELECT user_id, username, password_hash, role FROM users WHERE username = '{$username}'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row["password_hash"])) {
                    $_SESSION['puzzle']['user_id'] = $row["user_id"];
                    $_SESSION['puzzle']['username'] = $row["username"];
                    $_SESSION['puzzle']['role'] = $row["role"];
                    session_regenerate_id(true);

                    $sql = "UPDATE users SET last_login = '$datetime' WHERE user_id = '{$row["user_id"]}'";
                    $conn->query($sql);
                    $conn->close();
                    header('Location: puzzle.php');
                    exit;
                } else {
                    $error_msg = "Invalid login credentials.";
                }
            } else {
                $error_msg = "Invalid login credentials.";
            }
        } elseif ($input_type == "register") {
            $sql = "SELECT username FROM users WHERE username = '{$username}'";
            $result = $conn->query($sql);

            if ($result->num_rows == 0) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password_hash, email, role, registration_date, last_login)
                VALUES ('$username', '$hashed_password', '$email', '$default_role', '$datetime', '$datetime')";

                if ($conn->query($sql) === TRUE) {
                    $last_id = $conn->insert_id;
                    $_SESSION['puzzle']['user_id'] = $last_id;
                    $_SESSION['puzzle']['username'] = $username;
                    $_SESSION['puzzle']['role'] = $default_role;

                    session_regenerate_id(true);
                    $conn->close();
                    header('Location: puzzle.php');
                    exit;
                } else {
                    $error_msg = "Registration issue. Please try again later.";
                }
            } else {
                $error_msg = "Username is not available.";
            }
        }
    } else {
        $error_msg = "Please complete all fields.";
    }

    unset($_POST['play']);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="login.css">
    <link rel="icon" type="image/x-icon" href="images/icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fifteen Puzzle</title>
</head>

<body<?php echo (!isset($_POST['play']) && !isset($show_rules) ? ' class="fade-in"' : ''); ?>>
    <div id="main">
        <h1 id="title">Sliding Puzzle</h1>
        <form action="login.php" method="post">
            <table id="login">
                <?php
                if ((isset($_POST['play']) || isset($_GET['redirect'])) && !empty($error_msg)) {
                    echo '<tr id="error"><td>' . $error_msg . '</td></tr>';
                }
                ?>
                <tr>
                    <td>
                        <input id="username" type="text" name="username" placeholder="Username"
                            value="<?php echo $username; ?>" autocomplete="username">
                    </td>
                </tr>
                <tr>
                    <td>
                        <input id="password" type="password" name="password" placeholder="Password"
                            value="<?php echo $password; ?>">
                    </td>
                </tr>
                <tr id="email-container">
                    <td>
                        <input id="email" type="email" name="email" placeholder="Email" value="<?php echo $email; ?>"
                            autocomplete="email">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label><input type="radio" name="input_type" value="login" required <?php echo (empty($input_type) || $input_type) == "login" ? " checked" : ""; ?>>Login</label>
                        <label><input type="radio" name="input_type" value="register" required <?php echo (!empty($input_type) && $input_type == "register") ? " checked" : ""; ?>>Register</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <button id="btn-play" type="submit" name="play" value="true">PLAY NOW</button>
                    </td>
                </tr>
            </table>
        </form>
        <img id="demo" src="images/fifteen_puzzle.gif" alt="fifteen puzzle">
    </div>
    <script src="login.js"></script>
    </body>

</html>