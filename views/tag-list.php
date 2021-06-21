<?php include_once(__DIR__ . "/header.php"); ?>


<span>
    <form class="form-inline" action="/tags" method="post">
        <div class="form-container>">
            <div class="form-group">
            <label class="label-group" for="tagname">tagname</label>
            <input class="input-group" type="text" id="tagname" name="tagname">
            </div>
            <div class="form-group">
            <label class="label-group" for="description">description</label>
            <input class="input-group" type="description" id="description" name="description">
            </div>
            <input class="input-group submit-button" type="submit" value="New tag">
        </div>
    </form>
</span>
<br><br>
<table>
    <tr>
        <th>
            tagname
        <th>
            description
        </th>
    </tr>
    <?php foreach ($data as $model) : ?>
        <tr>
            <td><?= $model->tagname ?></td>
            <td><?= $model->description ?></td>
            <td><a href="/tags/delete/<?= $model->id ?>">Delete</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include_once(__DIR__ . "/footer.php"); ?>