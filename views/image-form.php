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

<form action="/images" method="post" enctype="multipart/form-data">
    <div class="form-container">
        <div class="form-group">
            <label class="label-group" for="Image Title">Image Title:</label>
            <input class="input-group" type="text" id="imagename" name="imagename">
        </div>
        <div class="form-group">
            <label class="label-group" for="description">Description:</label>
            <input class="input-group" type="textarea" id="description" name="description">
        </div>
        <div class="form-group">
            <label class="label-group" for="tags">Tags:</label>
            <input class="input-group" type="text" id="tags" name="tags" onchange="arrangeTags()">
        </div>
        <div class="form-group">
            <label class="label-group" for="imagefile">Image:</label>
            <input class="input-group" type="file" id="imagefile" name="imagefile">
        </div>
        <input class="input-group submit-button" type="submit" value="Submit">
    </div>
</form>



<?php include_once(__DIR__ . "/footer.php"); ?>