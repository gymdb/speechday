<?php
require_once('Util.php');
require_once('dao/UserDAO.php');
require_once('dao/LogDAO.php');

SessionContext::create();

class AuthenticationManager {
	public static function authenticate($userName, $password) {
		$user = UserDAO::getUserForUserName($userName);
        if ($user != null) {
            LogDAO::log($user->getId(), LogDAO::LOG_ACTION_LOGIN, $userName);
        }

	if ($user != null &&  password_verify($password,$user->getPasswordHash())) {
            $_SESSION['userId'] = $user->getId();
            $_SESSION['user'] = $user;
  			return true;
		}

		return false;
	}

	public static function signOut() {
	    if (self::isAuthenticated()) {
	        $user = self::getAuthenticatedUser();
            LogDAO::log($user->getId(), LogDAO::LOG_ACTION_LOGOUT);
        }

		unset($_SESSION['userId']);
        unset($_SESSION['user']);
	}

	public static function isAuthenticated() {
		return isset($_SESSION['userId']);
	}

	public static function getAuthenticatedUser() {
		return self::isAuthenticated() ? $_SESSION['user'] : null;
	}

	public static function checkPrivilege($role) {
	    if ((!self::isAuthenticated()) || (self::getAuthenticatedUser()->getRole() != $role)) {
            redirect('home.php');
        }
    }
}
