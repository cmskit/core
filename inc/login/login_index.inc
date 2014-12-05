<?php
/**
 *
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>cmskit-login</title>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1" />
    <link rel="icon" type="image/png" href="inc/img/icon.png" />
    <style>
        body {
            margin: 50px 0px;
            padding: 0px;
            text-align: center;
            background: url('inc/img/logo.png') no-repeat;
            background-position: 5px 5px;
        }
        form {
            max-width: 500px;
            margin: 0px auto;
            text-align: left;
            padding: 15px;
            border: 1px solid #333;
            background-color: #eee;
            border-radius: 15px;
        }
        select, input {
            border: 1px solid #ccc;
            width: 97%;
            margin-top: 5px;
            padding: 5px;
            -ms-box-sizing:content-box;
            -moz-box-sizing:content-box;
            box-sizing:content-box;
            -webkit-box-sizing:content-box;
        }
    </style>
</head>
<body>
<form action="backend.php" method="post" id="form">
    <?php
    if(!empty($_GET['error'])) echo '<h3>'.str_replace('_',' ',$_GET['error']).'</h3>';
    ?>
    <select name="project" id="project">
<?php
foreach($projects as $project) {
    echo '      <option value="'.$project.'"'
        .($project==$_GET['project']?' selected="selected"':'')
        .'>'.$project.'</option>';
}
?>
    </select>

<?php
if(!$config['autolog']) {
    echo '        <br>
        <input type="password" name="pass" placeholder="password" />
        <br>';
    // no need for captchas on localhost
    if(!in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) {
        echo '
        <img src="inc/php/captcha.php?x=1" />
        <br>
        <input name="name" placeholder="captcha">
        <br>
        ';
    }


}
?>
    <input type="hidden" name="client" id="client" />
    <input type="hidden" name="lang" id="lang" value="en" />
    <input type="submit" value="login" />
</form>
<script src="inc/js/modernizr.js"></script>
<script>
    Modernizr.addTest('json', function () {
        return window.JSON
        && window.JSON.parse
        && typeof window.JSON.parse === 'function'
        && window.JSON.stringify
        && typeof window.JSON.stringify === 'function';
    });

    // add screen width/height to modernizr-object
    Modernizr.win = {};
    Modernizr.win['width'] = window.innerWidth;
    Modernizr.win['height'] = window.innerHeight;

    // calculate the time difference between client and server
    Modernizr.tdiff = Math.round(Date.now() / 1000) - <?php echo time();?>

    // transfer modernizr-detection to a hidden field
    document.getElementById('client').value = JSON.stringify(Modernizr);

    <?php
    // auto-login if no password is needed and only one project (nothing to select)
    if($config['autolog'] && count($projects)==1) {
        echo 'document.getElementById("form").submit();';
    }

    ?>



</script>
</body>
</html>
