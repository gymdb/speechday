<?php

require_once('../AuthenticationManager.php');

SessionContext::create();

if (isset($_POST['btn-login'])) {
    $userName = trim($_POST['userName']);
    $password = trim($_POST['password']);

    try {
        if (AuthenticationManager::authenticate($userName, $password)) {
            echo 'ok'; // log in
        } else {
            echo 'Benutzername und Passwort stimmen nicht überein!'; // wrong details
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
