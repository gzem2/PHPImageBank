<?php include_once(__DIR__ . "/header.php"); ?>

<script type="application/javascript">
    function arrangeTags() {
        var tags = document.getElementById("tags");
        var tv = tags.value.split(",");
        var f = [];
        tv.forEach(function(e) {
            let parts = e.split(" ");
            parts.forEach(function(ee) {
                if (ee != "") {
                    f.push(ee);
                }
            });
        });
        tags.value = f.join(", ")
    }
</script>

<div class="thumb-edit">
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
</div>
<form action="/images/update" method="post" enctype="multipart/form-data">
    <div class="form-container form-edit">
        <div class="form-group form-group-hidden">
            <input class="input-group" type="text" id="id" name="id" readonly="readonly" value="<?= $model->id ?>">
        </div>
        <div class="form-group">
            <label class="label-group" for="Image Title">Image Title:</label>
            <input class="input-group" type="text" id="imagename" name="imagename" value="<?= $model->imagename ?>">
        </div>
        <div class="form-group form-group-hidden">
            <input class="input-group" type="text" id="filename" name="filename" readonly="readonly" value="<?= $model->filename ?>">
        </div>
        <div class="form-group">
            <label class="label-group" for="description">Description:</label>
            <input class="input-group" type="textarea" id="description" name="description">
        </div>
        <div class="form-group form-group-hidden">
            <input class="input-group" type="text" id="upload_date" name="upload_date" readonly="readonly" value="<?= $model->upload_date ?>">
        </div>
        <div class="form-group form-group-hidden">
            <input class="input-group" type="text" id="uploader_id" name="uploader_id" readonly="readonly" value="<?= $model->uploader_id ?>">
        </div>
        <div class="form-group">
            <label class="label-group" for="tags">Tags:</label>
            <input class="input-group" type="text" id="tags" name="tags" value="<?= implode(", ", $tags) ?>" onchange="arrangeTags()">
        </div>
        <input class="input-group submit-button" type="submit" value="Submit">
    </div>
</form>



<?php include_once(__DIR__ . "/footer.php"); ?>