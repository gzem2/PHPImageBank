<?php include_once(__DIR__ . "/header.php"); ?>

<form action="/users" method="post">
    <div class="form-container">
        <div class="form-group">
            <label class="label-group" for="username">username:</label>
            <input class="input-group" type="text" id="username" name="username"><br><br>
        </div>
        <div class="form-group">
            <label class="label-group" for="email">email:</label>
            <input class="input-group" type="text" id="email" name="email"><br><br>
        </div>
        <div class="form-group">
            <label class="label-group" for="password">password:</label>
            <input class="input-group" type="password" id="password" name="password"><br><br>
        </div>
        <input class="input-group submit-button" type="submit" value="Submit">
    </div>
</form>

<?php include_once(__DIR__ . "/footer.php"); ?>