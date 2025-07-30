<!-- Project Extra Key Features:
    1. End-of-game notification
    2. Animations and/or transitions
    3. Game time with some music file
-->
<?php
session_start();
if (!$_SESSION['puzzle']['user_id']) {
    header('Location: login.php?redirect');
    exit;
}

require 'database.php';

$username = $_SESSION['puzzle']['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="puzzle.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fifteen Puzzle</title>
</head>

<body>
    <div id="menu">
        <form action="login.php" method="post">
            <?php echo $username; ?>
            <button id="menu-btn" type="submit" id="btn" name="logout" value="true">Logout</button>
        </form>
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

    <audio id="bg-song" src="audio/bg-song.mp3"></audio>

    <script src="puzzle.js"></script>
</body>

</html>