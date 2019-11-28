<?php

require_once('code/Util.php');
require_once('code/dao/AbstractDAO.php');

SessionContext::create();

if (isset($_SESSION['userId']) != '') {
    header('Location: home.php');
}

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'
    'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>Elternsprechtagsverwaltung</title>
    <link href='libs/bootstrap/css/bootstrap.min.css' rel='stylesheet' media='screen'>
    <link href='libs/bootstrap/css/bootstrap-theme.min.css' rel='stylesheet' media='screen'>
    <link href='css/style.css' rel='stylesheet' type='text/css' media='screen'>
    <script type='text/javascript' src='js/jquery-1.11.3-jquery.min.js'></script>
    <script type='text/javascript' src='js/validation.min.js'></script>
    <script type='text/javascript' src='js/login-script.js'></script>

    <!-- favicon -->
    <?php include_once('inc/favicons.php') ?>
</head>

<body>

<div class='signin-form'>

    <div class='container'>

        <form class='form-signin' method='post' id='login-form'>

            <h2 class='form-signin-heading'>Elternsprechtag -  Login</h2>
            <hr/>

            <div id='error'>
                <!-- error will be shown here ! -->
            </div>

            <div class='form-group'>
                <input type='login' class='form-control' placeholder='Benutzername' name='userName' id='userName'/>
                <span id='check-e'></span>
            </div>

            <div class='form-group'>
                <input type='password' class='form-control' placeholder='Passwort' name='password' id='password'/>
            </div>

            <hr/>

            <div class='form-group'>
                <button type='submit' class='btn btn-default' name='btn-login' id='btn-login'>
                    <span class='glyphicon glyphicon-log-in'></span> &nbsp; Einloggen
                </button>
            </div>

        </form>

    </div>

</div>

<script src='libs/bootstrap/js/bootstrap.min.js'></script>

</body>
</html>
