<section class="header">
    <div class="menu"><a href="./index.php" class="menu-item">Todo list home</a></div>
    <div class="auth">
        <?php $viewName = $_SESSION['username'] ? $_SESSION['username'] : 'stranger'; ?>
        <span class="user">Hello, <?= $viewName ?></span>

        <?php if(!$_SESSION['userId']) { ?>
            <a class="login" href="./login.php">Login</a>
            <a class="register" href="./registration.php">Register</a>
        <?php } ?>

        <?php if($_SESSION['userId']) { ?>
            <a href="?logout=1" class="logout">Logout</a>
        <?php } ?>
    </div>
</section>
