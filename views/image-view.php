<?php include_once(__DIR__ . "/header.php"); ?>

<div class="tag-list">
    <ul>
        <?php foreach ($tags as $t) : ?>
            <li>
                <a href="/?tags=<?= $t[1] ?>"><?= $t[1] ?></a>
                <span><?= $t[0] ?></span>
            </li>
        <?php endforeach ?>
    </ul>
</div>
<div class="image-view">
    <img class="image-full" src="/images/<?= $model->filename ?>">
</div>
<br>
<?php if (isset($_SESSION['logged_in'])) : ?>
    <div class="comment-new">
        <form class="comment-form" action="/comments" method="post">
            <input type="hidden" name="image_id" value="<?= $model->id ?>">
            <label for="password">Comment:</label>
            <textarea rows="4" cols="50" class="comment-area" name="comment"></textarea><br>
            <input class="comment-button" type="submit" value="Submit">
        </form>
    </div>
<?php endif ?>
<div class="comments">
    <?php foreach ($comments as $c) : ?>
        <div class="comment">
            <div class="cm-sep"></div>
            <div class="cm-column cm-username">
                <a href="/users/<?= $c["user"]->id ?>"><?= $c["user"]->username ?>:</a>
            </div>
            <div class="cm-column cm-body"><?= $c["comment"]->comment ?></div>
        </div>
    <?php endforeach ?>
</div>

<?php include_once(__DIR__ . "/footer.php"); ?>