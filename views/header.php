<HTML>

<HEAD>
    <TITLE>PHPImageBank<?php echo isset($title) ? " - " . $title : "" ?></TITLE>
    <div class="page-title">
        <H3><a href="/">PHPImageBank</a><?php echo isset($title) ? " - " . $title : "" ?></H3>
    </div>
    <link rel="stylesheet" href="/css/styles.css">
</HEAD>

<BODY>
    <div class="header">
        <?php if ($title != "Register") : ?>
            <?php if (!isset($_SESSION['logged_in'])) : ?>
                <span><a href="/register">Register</a></span>
                <span> new account or sign in: </span>
                <span>
                    <form class="form-inline" action="/login" method="post">
                        <label for="username">username:</label>
                        <input type="text" id="username" name="username">
                        <label for="password">password:</label>
                        <input type="password" id="password" name="password">
                        <input type="submit" value="Login">
                    </form>
                </span>
            <?php else : ?>
                <span>
                    <a href="/users/<?php echo $_SESSION['logged_in']->id ?>">Profile(<?php echo $_SESSION['logged_in']->username ?>)</a>
                </span>
                <span>
                    <a href="/my-tags">My tags</a>
                </span>
                <span>
                    <a href="/upload">Upload image</a>
                </span>
                <?php if (strpos($_SERVER['REQUEST_URI'], "/images/") !== false && $model->uploader_id == $_SESSION['logged_in']->id) : ?>
                    <span>
                        <a href="/images/edit/<?= $model->id ?>">Edit image</a>
                    </span>
                    <span>
                        <a href="/images/delete/<?= $model->id ?>">Delete image</a>
                    </span>
                <?php endif ?>
                <span>
                    <a href="/logout">Logout</a>
                </span>
            <?php endif ?>
        <?php endif ?>
    </div>
    <div class="content">