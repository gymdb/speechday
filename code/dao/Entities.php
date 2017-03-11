<?php

abstract class Entity {
    private $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }
}

class User extends Entity {
    private $userName;
    private $passwordHash;
    private $firstName;
    private $lastName;
    private $class;
    private $role;
    private $title;

    public function __construct($id, $userName, $passwordHash, $firstName, $lastName, $class, $role,$title) {
        parent::__construct($id);
        $this->userName = $userName;
        $this->passwordHash = $passwordHash;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->class = $class;
        $this->role = $role;
        $this->title = $title;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function getPasswordHash() {
        return $this->passwordHash;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getClass() {
        return $this->class;
    }

    public function getRole() {
        return $this->role;
    }

    public function getTitle() {
        return $this->title;
    }

    public function __toString() {
        return json_encode(array(
            'id' => $this->getId(),
            'userName' => $this->userName,
            'passwordHash' => $this->passwordHash,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'class' => $this->class,
            'role' => $this->role,
            'title' => $this->title
        ));
    }
}

class Event extends Entity {
    private $name;
    private $dateFrom;
    private $dateTo;
    private $slotTime;
    private $isActive;

    public function __construct($id, $name, $dateFrom, $dateTo, $slotTime, $isActive) {
        parent::__construct($id);
        $this->name = $name;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->slotTime = $slotTime;
        $this->isActive = $isActive;
    }

    public function getName() {
        return $this->name;
    }

    public function getDateFrom() {
        return $this->dateFrom;
    }

    public function getDateTo() {
        return $this->dateTo;
    }

    public function getSlotTime() {
        return $this->slotTime;
    }

    public function isActive() {
        return $this->isActive;
    }
}

class Slot extends Entity {
    private $eventId;
    private $teacherId;
    private $studentId;
    private $dateFrom;
    private $dateTo;
    private $type;
    private $available;

    public function __construct($id, $eventId, $teacherId, $studentId, $dateFrom, $dateTo, $type, $available) {
        parent::__construct($id);
        $this->eventId = $eventId;
        $this->teacherId = $teacherId;
        $this->studentId = $studentId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->type = $type;
        $this->available = $available;
    }

    public function getEventId() {
        return $this->eventId;
    }

    public function getTeacherId() {
        return $this->teacherId;
    }

    public function getStudentId() {
        return $this->studentId;
    }

    public function getDateFrom() {
        return $this->dateFrom;
    }

    public function getDateTo() {
        return $this->dateTo;
    }

    public function getType() {
        return $this->type;
    }

    public function getAvailable() {
        return $this->available;
    }
}

class Log extends Entity {
    private $userId;
    private $action;
    private $info;
    private $date;

    public function __construct($id, $userId, $action, $info, $date) {
        parent::__construct($id);
        $this->userId = $userId;
        $this->action = $action;
        $this->info = $info;
        $this->date = $date;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getAction() {
        return $this->action;
    }

    public function getInfo() {
        return $this->info;
    }

    public function getDate() {
        return $this->date;
    }
}

class Room extends Entity {
    private $roomNumber;
    private $name;
    private $teacherId;

    public function __construct($id, $roomNumber, $name, $teacherId) {
        parent::__construct($id);
        $this->roomNumber = $roomNumber;
        $this->name = $name;
        $this->teacherId = $teacherId;
    }

    public function getRoomNumber() {
        return $this->roomNumber;
    }

    public function getName() {
        return $this->name;
    }

    public function getTeacherId() {
        return $this->teacherId;
    }
}
