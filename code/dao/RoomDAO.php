<?php

require_once('AbstractDAO.php');

class RoomDAO extends AbstractDAO {
    public static function deleteAllRooms() {
        $con = self::getConnection();
        $s1 = self::query($con, 'DELETE FROM room;', array(), true);

        return $s1['success'];
    }

    public static function getAllRooms() {
        $rooms = array();
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, roomNumber, name, teacherId FROM room', array());

        while ($r = self::fetchObject($res)) {
            $rooms[$r->teacherId] = new Room($r->id, $r->roomNumber, $r->name, $r->teacherId);
        }
        self::close($res);
        return $rooms;
    }

    public static function getRoomForTeacherId($teacherId) {
        $room = null;
        $con = self::getConnection();
        $res = self::query($con, 'SELECT id, roomNumber, name, teacherId FROM room WHERE teacherId = ?;', array($teacherId));
        if ($r = self::fetchObject($res)) {
            $room = new Room($r->id, $r->roomNumber, $r->name, $r->teacherId);
        }
        self::close($res);
        return $room;
    }

    public static function update($roomNumber, $roomName, $teacherId) {
        $con = self::getConnection();

        if ($roomName != '' && $roomNumber != '') {
            $query = 'UPDATE room SET roomNumber = ?, name = ? WHERE teacherId = ?;';
            $params = array($roomNumber, $roomName, $teacherId);

            $res = self::query($con, $query, $params, true);
            $count = $res['statement']->rowCount();

            if ($count < 1) {
                $query = 'INSERT IGNORE INTO room (roomNumber, name, teacherId) VALUES (?, ?, ?);';
                $params = array($roomNumber, $roomName, $teacherId);

                return self::query($con, $query, $params, true);
            } else {
                return $res;
            }
        } else {
            $query = 'DELETE FROM room WHERE teacherId = ?';
            $params = array($teacherId);

            return self::query($con, $query, $params, true);
        }
    }
}