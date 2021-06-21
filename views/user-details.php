<?php include_once(__DIR__ . "/header.php"); ?>

<div class="user-details">
    <table>
        <tr>
            <th>Profile info</th>
            <th></th>
        </tr>
        <tr>
            <td>
                username:
            </td>
            <td>
                <?= $data->username ?>
            </td>
        </tr>
        <tr>
            <td>
                email:
            </td>
            <td>
                <?= ($data->email) ?: "(empty)" ?>
            </td>
        </tr>

    </table>
</div>

<?php include_once(__DIR__ . "/footer.php"); ?>