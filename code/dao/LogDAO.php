<?php

require_once('AbstractDAO.php');

class LogDAO extends AbstractDAO {

    const LOG_ACTION_LOGIN = 1;
    const LOG_ACTION_LOGOUT = 2;
    const LOG_ACTION_BOOK_SLOT = 3;
    const LOG_ACTION_DELETE_SLOT = 4;
    const LOG_ACTION_CHANGE_ATTENDANCE = 5;

    public static function log($userId, $actionId, $info = null) {
        $con = self::getConnection();

        $now = time();
        self::query($con, 'INSERT INTO log (userId, action, info, date) VALUES (?, ?, ?, ?);', array($userId, $actionId, $info, $now));
    }

    public static function getLogsForUser($userId) {
        $logs = array();
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, userId, action, info, date FROM log WHERE userId = ?;', array($userId));

        while ($l = self::fetchObject($res)) {
            $logs[] = new Log($l->id, $l->userId, $l->action, $l->info, $l->date);
        }
        self::close($res);
        return $logs;
    }

    public static function deleteAllStats() {
        $con = self::getConnection();
        $s1 = self::query($con, 'DELETE FROM log;', array(), true);

        return $s1['success'];
    }

    public static function deleteStatsForUser($userId) {
        $con = self::getConnection();
        $s1 = self::query($con, 'DELETE FROM log WHERE userId = ?;', array($userId), true);

        return $s1['success'];
    }
}