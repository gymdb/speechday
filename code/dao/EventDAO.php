<?php

require_once('AbstractDAO.php');
require_once('Entities.php');
require_once('UserDAO.php');
require_once('SlotDAO.php');

class EventDAO extends AbstractDAO {
    public static function getEvents() {
        $events = array();
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, name, dateFrom, dateTo, slotTimeMin, isActive, finalPostDate, videoLink, breaks FROM event ORDER BY dateFrom;', array());

        while ($e = self::fetchObject($res)) {
            $events[] = new Event($e->id, $e->name, $e->dateFrom, $e->dateTo, $e->slotTimeMin, $e->isActive, $e->finalPostDate, $e->videoLink, $e->breaks);
        }
        self::close($res);

        return $events;
    }

    public static function createEvent($name, $dateFrom, $dateTo, $slotDuration, $setActive, $finalPostDate, $videoLink, $breaks) {
        $con = self::getConnection();

        if (($dateTo - $dateFrom < 0) || (count(EventDAO::getEventsForDate($dateFrom)) > 0)) {
            return -1;
        }

        self::getConnection()->beginTransaction();
        self::query($con, 'INSERT INTO event (name, dateFrom, dateTo, slotTimeMin, finalPostDate, videoLink, breaks) VALUES (?, ?, ?, ?, ?, ?, ?);', array($name, $dateFrom, $dateTo, $slotDuration, $finalPostDate, $videoLink, $breaks));
        $eventId = self::lastInsertId($con);

        $teachers = UserDAO::getUsersForRole('teacher');
        $slotsCreated = SlotDAO::createSlotsForEvent($eventId, $teachers);

        $setActiveSuccess = true;
        if ($setActive) {
            $setActiveSuccess = self::setActiveEvent($eventId, false);
        }

        if ($eventId > 0 && $slotsCreated && $setActiveSuccess) {
            self::getConnection()->commit();
        } else {
            self::getConnection()->rollBack();
        }

        return $eventId;
    }

    public static function getEventForId($eventId) {
        $event = null;
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, name, dateFrom, dateTo, slotTimeMin, isActive, finalPostDate, videoLink, breaks FROM event WHERE id = ?;', array($eventId));

        if ($e = self::fetchObject($res)) {
            $event = new Event($e->id, $e->name, $e->dateFrom, $e->dateTo, $e->slotTimeMin, $e->isActive, $e->finalPostDate, $e->videoLink, $e->breaks);
        }
        self::close($res);
        return $event;
    }

    public static function getActiveEvent() {
        $event = null;
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, name, dateFrom, dateTo, slotTimeMin, isActive,  finalPostDate, videoLink, breaks FROM event WHERE isActive = 1;', array());

        if ($e = self::fetchObject($res)) {
            $event = new Event($e->id, $e->name, $e->dateFrom, $e->dateTo, $e->slotTimeMin, $e->isActive, $e->finalPostDate, $e->videoLink, $e->breaks);
        }
        self::close($res);
        return $event;
    }

    public static function getEventsForDate($unixDate) {
        $from = $unixDate - ($unixDate % 86400);
        $to = $from + 86400;

        $events = array();
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, name, dateFrom, dateTo, slotTimeMin, isActive,finalPostDate, videoLink, breaks FROM event WHERE dateFrom BETWEEN ? AND ?;', array($from, $to));

        while ($e = self::fetchObject($res)) {
            $events[] = new Event($e->id, $e->name, $e->dateFrom, $e->dateTo, $e->slotTimeMin, $e->isActive, $e->finalPostDate, $e->videoLink, $e->breaks);
        }
        self::close($res);

        return $events;
    }

    public static function setActiveEvent($eventId, $createTransaction = true) {
        $con = self::getConnection();

        if ($createTransaction) {
            self::getConnection()->beginTransaction();
        }
        $s1 = self::query($con, 'UPDATE event SET isActive = 0;', array(), true);
        $s2 = self::query($con, 'UPDATE event SET isActive = 1 WHERE id = ?;', array($eventId), true);
        if ($createTransaction) {
            self::getConnection()->commit();
        }

        return $s1['success'] && $s2['success'];
    }

    public static function deleteAllEvents() {
        $con = self::getConnection();
        $s1 = self::query($con, 'DELETE FROM event;', array(), true);

        return $s1['success'];
    }

    public static function deleteEvent($eventId) {
        $con = self::getConnection();
        $s1 = self::query($con, 'DELETE FROM event WHERE id = ?;', array($eventId), true);

        return $s1['success'];
    }
}
