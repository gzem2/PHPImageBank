<?php include_once(__DIR__ . "/header.php"); ?>

<div class="tag-list">
    <ul>
        <?php foreach ($tags as $t) : ?>
            <li>
                <a href="/?tags=<?=$t[1]?>"><?=$t[1]?></a>
                <span><?=$t[0]?></span>
            </li>
        <?php endforeach ?>
    </ul>
</div>
<div class="image-list">
    <?php foreach ($data as $model) : ?>
        <span class="thumb">
            <a class="thumb-link" href="/images/<?= $model->id ?>">
                <img src="/thumbs/<?php
                                    $t = explode(".", $model->filename);
                                    array_pop($t);
                                    array_push($t, "_thumb.jpg");
                                    echo implode($t);
                                    ?>" class="preview">
            </a>
        </span>
    <?php endforeach ?>
</div>
<br>

<div class="pagination">
  <a href="<?=$pages["prev"]?>">&laquo;</a>
  <?php foreach ($pages["pages"] as $p) : ?>
  <a <?php echo isset($p["active"]) ? "class=\"active\"" : ""; ?>href="<?= $p["link"]?>"><?= $p["text"]?></a>
  <?php endforeach ?>
  <a href="<?=$pages["next"]?>">&raquo;</a>
</div>

<?php include_once(__DIR__ . "/footer.php"); ?>