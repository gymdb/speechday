<?php

require_once('../AuthenticationManager.php');

SessionContext::create();
AuthenticationManager::signOut();

if (session_destroy()) {
    header("Location: ../../index.php");
}
