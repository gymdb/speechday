<?php

require_once('AbstractDAO.php');

class UserDAO extends AbstractDAO {

    const MIN_PASSWORD_LENGTH = 8;

    public static function getUserForId($userId) {
        $user = null;
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, userName, passwordHash, firstName, lastName, class, role, title FROM user WHERE id = ?;', array($userId));
        if ($u = self::fetchObject($res)) {
            $user = new User($u->id, $u->userName, $u->passwordHash, $u->firstName, $u->lastName, $u->class, $u->role, $u->title);
        }
        self::close($res);
        return $user;
    }

    public static function getUserForUserName($userName) {
        $user = null;
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, userName, passwordHash, firstName, lastName, class, role, title  FROM user WHERE userName = ?;', array($userName));

        if ($u = self::fetchObject($res)) {
            $user = new User($u->id, $u->userName, $u->passwordHash, $u->firstName, $u->lastName, $u->class, $u->role, $u->title);
        }
        self::close($res);
        return $user;
    }

    public static function getUsersForRole($type, $limit = 0) {
        $users = array();
        $con = self::getConnection();
        $params = array($type);

        $orderPhrase = 'ORDER BY lastName';
        $query = sprintf('SELECT id, userName, passwordHash, firstName, lastName, class, role, title, absent FROM user WHERE role = ? %s;', $orderPhrase);
        if ($limit > 0) {
           $query = 'SELECT id, userName, passwordHash, firstName, lastName, class, role, title, absent FROM user WHERE role = ? ORDER BY lastName LIMIT 10';
        }
        $res = self::query($con, $query, $params);

        while ($u = self::fetchObject($res)) {
            $users[] = new User($u->id, $u->userName, $u->passwordHash, $u->firstName, $u->lastName, $u->class, $u->role, $u->title, $u->absent);
        }
        self::close($res);
        return $users;
    }

    public static function checkAccessData() {
        $con = self::getConnection();
        $res = self::query($con, 'SELECT * FROM accessdata;', array());

        $a = self::fetchObject($res);
        if ($a != null) {
            return true;
        } else {
            return false;
        }
    }

    public static function getStudentsForNewsletter() {
        $users = array();
        $con = self::getConnection();
        $res = self::query($con, 'SELECT u.id, u.userName, a.password, u.firstName, u.lastName, u.class, u.role FROM user AS u JOIN accessdata AS a ON u.userName = a.userName WHERE role = ?;', array('student'));

        while ($u = self::fetchObject($res)) {
            $users[] = array(
                'student' => new User($u->id, $u->userName, $u->password, $u->firstName, $u->lastName, $u->class, $u->role, ''),
                'password' => $u->password
            );
        }

        self::close($res);
        return $users;
    }

    public static function deleteAccessData() {
        $con = self::getConnection();
        return self::query($con, 'DELETE FROM accessdata;', array(), true)['success'];
    }

    public static function getUsers() {
        $users = array();
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, userName, passwordHash, firstName, lastName, class, role, title, absent FROM user ORDER BY LOWER(lastName), LOWER(firstName);', array());

        while ($u = self::fetchObject($res)) {
            $users[] = new User($u->id, $u->userName, $u->passwordHash, $u->firstName, $u->lastName, $u->class, $u->role, $u->title, $u->absent);
        }
        self::close($res);
        return $users;
    }

    public static function register($userName, $password, $firstName, $lastName, $class, $role) {
        $user = UserDAO::getUserForUserName($userName);
        if ($user != null) {
            return -1; // already registered
        } else if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return -2; // password too short
        }

        // register user in database
        $con = self::getConnection();
        $passwordHash = createPasswordHash($password);

        self::getConnection()->beginTransaction();
        self::query($con, 'INSERT INTO user (userName, passwordHash, firstName, lastName, class, role) VALUES (?, ?, ?, ?, ?, ?);', array($userName, $passwordHash, $firstName, $lastName, $class, $role));
        $userId = self::lastInsertId($con);

        if ($role == 'teacher') {
            $activeEvent = EventDAO::getActiveEvent();
            if ($activeEvent != null) {
                $teacher = UserDAO::getUserForId($userId);
                SlotDAO::createSlotsForEvent($activeEvent->getId(), array($teacher));
            }
        } else {
            if (UserDAO::checkAccessData()) {
                $accessData = array();
                $accessData[] = array($userName, $password);
                UserDAO::bulkInsertAccessData($accessData, false);
            }
        }

        self::getConnection()->commit();

        return $userId;
    }

    public static function bulkInsertUsers($users, $rooms = null) {
        $con = self::getConnection();
        $userSth = $con->prepare('INSERT INTO user (userName, passwordHash, firstName, lastName, class, role, title) VALUES (?, ?, ?, ?, ?, ?, ?);');
        $roomSth = self::getConnection()->prepare('INSERT IGNORE INTO room (roomNumber, name, teacherId) VALUES (?, ?, ?);');
        $isTeacherInsert = count($rooms) > 0;

        foreach ($users as $user) {
            for ($i = 0; $i < 7; $i++) {
                $userSth->bindValue($i + 1, $user[$i]);
            }
            $userSth->execute();

            if ($isTeacherInsert) {
                $userId = self::lastInsertId($con);
                $userName = $user[0];
                if (array_key_exists($userName, $rooms)) {
                    $room = $rooms[$userName];
                    $roomSth->bindValue(1, $room[0]);
                    $roomSth->bindValue(2, $room[1]);
                    $roomSth->bindValue(3, $userId);
                    $roomSth->execute();
                }
            }
        }
    }

    public static function bulkInsertAccessData($accessData, $delete = true) {
        $con = self::getConnection();
        if ($delete) {
            self::query($con, 'DELETE FROM accessdata;', array());
        }

        $sth = $con->prepare('INSERT INTO accessdata (userName, password) VALUES (?, ?);');

        foreach ($accessData as $access) {
            foreach ($access as $attr => $value) {
                $sth->bindValue($attr + 1, $value);
            }
            $sth->execute();
        }
    }

    public static function update($userId, $userName, $password, $firstName, $lastName, $class, $type) {
        $con = self::getConnection();
        $success = true;

        if ($userId == 1 && $type != 'admin') {
            return false;
        }

        if ($password != '') {
            if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
                return false;
            }

            $passwordHash = createPasswordHash($password);
            $query = 'UPDATE user SET passwordHash = ?, userName = ?, firstName = ?, lastName = ?, class = ?, role = ? WHERE id = ?';
            $params = array($passwordHash, $userName, $firstName, $lastName, $class, $type, $userId);
            $updateUserResult = self::query($con, $query, $params, true)['success'];

            $accessDataQuery = 'UPDATE accessdata SET password = ? WHERE userName = ?;';
            $accessDataParams = array($password, $userName);
            $accessDataResult = self::query($con, $accessDataQuery, $accessDataParams, true)['success'];

            $success = $updateUserResult && $accessDataResult;
        } else {
            $oldUserName = "";
            $oldUserQuery = self::query($con, 'SELECT userName FROM user WHERE id = ?;', array($userId));
            if ($u = self::fetchObject($oldUserQuery)) {
                $oldUserName = $u->userName;
            }

            if ($oldUserName != $userName) {
                return false;
            }

            $query = 'UPDATE user SET firstName = ?, lastName = ?, class = ?, role = ? WHERE id = ?';
            $params = array($firstName, $lastName, $class, $type, $userId);

            $success = self::query($con, $query, $params, true)['success'];
        }

        if ($userId == AuthenticationManager::getAuthenticatedUser()->getId()) {
            $_SESSION['user'] = UserDAO::getUserForId($userId);
        }

        return $success;

        // userName and password have to be set!!!


        /*
        // with userName as Salt and possibility to only change the userName
        $hashQueryPart = '';


        $updatePassword = ($userName != null) || ($password != null);
        if ($updatePassword) {
            $passwordHash = createPasswordHash($password);
            $hashQueryPart = ' passwordHash = ?,';
            $params = array_merge(array($passwordHash), $params);
        }

        $query = sprintf('UPDATE user SET%s userName = ?, firstName = ?, lastName = ?, class = ?, role = ? WHERE id = ?', $hashQueryPart);

        return self::query($con, $query, $params, true);
        */
    }

    public static function updateAbsent($userId,$absent) {
        $con = self::getConnection();
        $query = 'UPDATE user SET absent = ? WHERE Id = ?;';
        $params = array($absent, $userId);
        $result = self::query($con, $query, $params, true)['success'];
        return $result;
    }

    public static function deleteUsersByRole($role) {
        $con = self::getConnection();

        return self::query($con, 'DELETE FROM user WHERE role = ?', array($role), true)['success'];
    }

    public static function deleteUserById($userId) {
        $con = self::getConnection();

        if ($userId == 1) {
            return false;
        }

        return self::query($con, 'DELETE FROM user WHERE id = ?', array($userId), true)['success'];
    }
}
