<?=\Lightning\Tools\SocialDrivers\Google::loginButton(true);?>
<br>
<?=\Lightning\Tools\SocialDrivers\Facebook::loginButton(true);?>
<br>
<?=\Lightning\Tools\SocialDrivers\Twitter::loginButton(true);?>

    <table>
        <thead>
        <tr>
            <td>
                Network
            </td>
            <td>Name</td>
            <td>Screen Name</td>
        </tr>
        </thead>
        <?php foreach ($authorizations as $auth): ?>
        <tr>
            <td>            <?= $auth['network']; ?>
            </td>
            <td><?=$auth['name'];?></td>
            <td><?=$auth['screen_name'];?></td>
        </tr>
        <?php endforeach; ?>
    </table>
