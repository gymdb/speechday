<?php
require_once('AuthenticationManager.php');
require_once('Controller.php');
require_once('dao/Entities.php');
require_once('dao/UserDAO.php');
require_once('dao/EventDAO.php');
require_once('dao/SlotDAO.php');

class ViewController extends Controller {

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new ViewController();
        }
        return self::$instance;
    }

    public function handleGetRequest() {
        //check request method
        if (($_SERVER['REQUEST_METHOD'] != 'GET') || (!isset($_REQUEST['action']))) {
            return;
        }

        //execute action
        $method = 'action_' . $_REQUEST['action'];
        $this->$method();
    }

    private function checkIfTeacherIsBooked($teacherId, $bookedSlots) {
        foreach ($bookedSlots as $slot) {
        $help = $slot;
        unset($help['eventId']);  //remove eventId, because teacherId could be equal to eventId
        if (in_array($teacherId, $help)) {
                return true;
            }
        }

        return false;
    }

    public function action_getChangeEventForm() {
        $events = EventDAO::getEvents();
        if (count($events) > 0) {
            ?>
            <form id='changeEventForm'>
                <div class='form-group'>
                    <?php

                    foreach ($events as $event) :
                        $display = escape($event->getName() . ' am ' . toDate($event->getDateFrom(), 'd.m.Y'));
                        $isActive = $event->isActive() == 1 ? ' checked' : '';
                        $id = escape($event->getId());
                    ?>
                        <div class='radio'>
                            <label id="event-label-<?php echo($id) ?>"><input type='radio' name='eventId' value="<?php echo($id . '"' . $isActive) ?>><?php echo($display) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type='submit' class='btn btn-primary btn-change-event' id='btn-change-active-event'>als aktiven Sprechtag setzen</button>
                <button type='submit' class='btn btn-primary btn-change-event' id='btn-delete-event'>Sprechtag löschen</button>
            </form>
            <?php
        } else {
            ?>
            <form id='changeEventForm'>
                <p>Es gibt momentan keinen Elternsprechtag!</p>
            </form>
            <?php
        }
    }

    public function action_getTimeTable() {
        $teacher = UserDAO::getUserForId($_REQUEST['teacherId']);
        $user = AuthenticationManager::getAuthenticatedUser();
        $activeEvent = EventDAO::getActiveEvent();

        $noSlotsFoundWarning = '<h3>Keine Termine vorhanden!</h3>';
        if ($teacher == null || $user == null || $activeEvent == null) {
            echo($noSlotsFoundWarning);
            return;
        }

        $slots = SlotDAO::getSlotsForTeacherId($activeEvent->getId(), $teacher->getId());
        $bookedSlots = SlotDAO::getBookedSlotsForStudent($activeEvent->getId(), $user->getId());
        $canBook = !$this->checkIfTeacherIsBooked($teacher->getId(), $bookedSlots);
        $room = RoomDAO::getRoomForTeacherId($teacher->getId());

        if (count($slots) <= 0) {
            echo($noSlotsFoundWarning);
            return;
        }

        ?>
        <h3>Termine für <?php echo(escape($teacher->getTitle().' ' .$teacher->getFirstName() . ' ' . $teacher->getLastName())) ?></h3>

        <?php if ($room != null): ?>
            <h4>Raum: <?php echo(escape($room->getRoomNumber()) . ' &ndash; ' . escape($room->getName())) ?></h4>
        <?php endif; ?>

        <table class='table table-hover es-time-table'>
            <thead>
            <tr>
                <th width='15%'>Uhrzeit</th>
                <th width='30%'>Zeitplan Lehrer/in</th>
                <th width='40%'>Mein Zeitplan</th>
                <th width='15%'>Aktion</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($slots as $slot):
                $fromDate = $slot->getDateFrom();
                $teacherAvailable = $slot->getStudentId() == '';
                $studentAvailable = array_key_exists($fromDate, $bookedSlots) ? false : true;
                $timeTd = escape(toDate($slot->getDateFrom(), 'H:i')) . optionalBreak() . escape(toDate($slot->getDateTo(), 'H:i'));
                $bookJson = escape(json_encode(array('slotId' => $slot->getId(), 'teacherId' => $teacher->getId(), 'userId' => $user->getId(), 'eventId' => $activeEvent->getId())));
                ?>

                <?php if ($slot->getType() == 2): ?>
                <tr class='es-time-table-break'>
                    <td><?php echo($timeTd) ?></td>
                    <td colspan='3'>PAUSE</td>
                </tr>
            <?php else: ?>
                <tr class='<?php echo($teacherAvailable && $studentAvailable ? 'es-time-table-available' : 'es-time-table-occupied') ?>'>
                    <td><?php echo($timeTd) ?></td>
                    <td><?php echo($teacherAvailable ? 'frei' : 'belegt') ?></td>
                    <td><?php echo($studentAvailable ? 'frei' : $bookedSlots[$fromDate]['teacherName']) ?></td>
                    <td>
                        <?php if ($teacherAvailable && $studentAvailable && $canBook): ?>
                            <button type='button' class='btn btn-primary btn-book'
                                    id='btn-book-<?php echo($slot->getId()) ?>' value='<?php echo($bookJson) ?>'>buchen
                            </button>
                        <?php endif; ?>
                    </td
                </tr>
            <?php endif; ?>

            <?php endforeach; ?>

            </tbody>
        </table>
        <?php
    }

    public function action_getMySlotsTable() {
        $typeId = $_REQUEST['typeId'];
        $isFullView = $typeId == 0;

        $user = AuthenticationManager::getAuthenticatedUser();
        $activeEvent = EventDAO::getActiveEvent();

        $noSlotsFoundWarning = '<h3>Keine Termine vorhanden!</h3>';
        if ($user == null || $activeEvent == null) {
            echo($noSlotsFoundWarning);
            return;
        }

        $bookedSlots = SlotDAO::getBookedSlotsForStudent($activeEvent->getId(), $user->getId());
        if (count($bookedSlots) <= 0) {
            echo($noSlotsFoundWarning);
            return;
        }

        $slots = SlotDAO::calculateSlots($activeEvent, true);
        $rooms = RoomDAO::getAllRooms();

        ?>
        <div id="printHeader">
            <h3>Meine Termine für den <?php echo(toDate($activeEvent->getDateFrom(), 'd.m.Y')) ?></h3>
        </div>

        <table class='table table-hover es-time-table'>
            <thead>
            <tr>
                <th width='15%'>Uhrzeit</th>
                <th width='15%'>Raum</th>
                <th width='50%'>Mein Zeitplan</th>
                <th width='20%' class='no-print'>Aktion</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($slots as $slot):
                $fromDate = $slot->getDateFrom();
                $studentAvailable = array_key_exists($fromDate, $bookedSlots) ? false : true;
                $timeTd = escape(toDate($slot->getDateFrom(), 'H:i')) . optionalBreak() . escape(toDate($slot->getDateTo(), 'H:i'));

                $roomTd = "";
                if (!$studentAvailable && array_key_exists($bookedSlots[$fromDate]['teacherId'], $rooms)) {
                    $room = $rooms[$bookedSlots[$fromDate]['teacherId']];
                    $roomTd = escape($room->getRoomNumber()) . optionalBreak() . escape($room->getName());
                }
                ?>

                <?php if ($isFullView || !$studentAvailable): ?>
                <?php if ($slot->getType() == 2): ?>
                    <tr class='es-time-table-break'>
                        <td><?php echo($timeTd) ?></td>
                        <td></td>
                        <td>PAUSE</td>
                        <td class='no-print'></td>
                    </tr>
                <?php else: ?>
                    <tr class='<?php echo($studentAvailable ? 'es-time-table-available' : 'es-time-table-occupied') ?>'>
                        <td><?php echo($timeTd) ?></td>
                        <td><?php echo($roomTd) ?></td>
                        <td><?php echo($studentAvailable ? 'frei' : $bookedSlots[$fromDate]['teacherName']) ?></td>
                        <td class='no-print'>
                            <?php if (!$studentAvailable):
                                $deleteJson = escape(json_encode(array('userId' => $user->getId(), 'slotId' => $bookedSlots[$fromDate]['id'], 'eventId' => $activeEvent->getId(), 'typeId' => $typeId)));
                                ?>
                                <button type='button' class='btn btn-primary btn-delete'
                                        id='btn-delete-<?php echo($bookedSlots[$fromDate]['id']) ?>'
                                        value='<?php echo($deleteJson) ?>'>Termin löschen
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

            <?php endforeach; ?>

            </tbody>
        </table>
        <?php
    }

    public function action_getTeacherTimeTable() {
        $typeId = $_REQUEST['typeId'];
        $isFullView = $typeId == 2;

        $teacher = AuthenticationManager::getAuthenticatedUser();

        $this->printTableForTeacher($teacher, $isFullView);
    }

    public function action_getAdminTimeTable() {
        $teachers = UserDAO::getUsersForRole('teacher');
        foreach ($teachers as $teacher) {
            $this->printTableForTeacher($teacher, true, true);
            ?>
            <div class="pageBreak"></div>
            <?php
        }
    }

    private function printTableForTeacher($teacher, $isFullView, $adminPrint = false) {
        $activeEvent = EventDAO::getActiveEvent();
        $headerText = "Meine Termine";
        if ($adminPrint) {
            $headerText = "Termine für " . $teacher->getTitle(). " ". $teacher->getFirstName() . " " . $teacher->getLastName();
            $room = RoomDAO::getRoomForTeacherId($teacher->getId());
            if ($room != null) {
                $headerText .= " (Raum: " . $room->getRoomNumber() . " | " . $room->getName() . ")";
            }
        }

        ?>
        <div id="printHeader">
            <h3><?php echo escape($headerText); ?></h3>
        </div>
        <?php

        $noSlotsFoundWarning = '<div id="printHeader"><h3>Keine Termine vorhanden!</h3></div>';
        if ($teacher == null || $activeEvent == null) {
            echo($noSlotsFoundWarning);
            return;
        }

        $bookedSlots = SlotDAO::getBookedSlotsForTeacher($activeEvent->getId(), $teacher->getId());
        if (!$adminPrint && (count($bookedSlots) <= 0)) {
            echo($noSlotsFoundWarning);
            return;
        }

        $slots = SlotDAO::getSlotsForTeacherId($activeEvent->getId(), $teacher->getId());

        ?>
        <table class='table table-hover es-time-table'>
            <thead>
            <tr>
                <th width='15%'>Uhrzeit</th>
                <th width='65%'>Schüler</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($slots as $slot):
                $fromDate = $slot->getDateFrom();
                $teacherAvailable = array_key_exists($fromDate, $bookedSlots) ? false : true;
                $timeTd = escape(toDate($slot->getDateFrom(), 'H:i')) . optionalBreak() . escape(toDate($slot->getDateTo(), 'H:i'));
                ?>

                <?php if ($isFullView || !$teacherAvailable): ?>
                    <?php if ($slot->getType() == 2): ?>
                        <tr class='es-time-table-break'>
                            <td><?php echo($timeTd) ?></td>
                            <td>PAUSE</td>
                        </tr>
                    <?php else: ?>
                        <tr class='<?php echo($teacherAvailable ? 'es-time-table-available' : 'es-time-table-occupied') ?>'>
                            <td><?php echo($timeTd) ?></td>
                            <td><?php echo($teacherAvailable ? 'frei' : $bookedSlots[$fromDate]['studentName']) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>

            <?php endforeach; ?>

            </tbody>
        </table>
        <?php
    }

    public function action_createUser() {
        ?>

        <?php include_once('inc/userForm.php') ?>

        <button type='submit' class='btn btn-primary' id='btn-create-user'>Benutzer erstellen</button>

        <?php
    }

    public function action_changeUser() {
        $users = UserDAO::getUsers();
        $rooms = RoomDAO::getAllRooms();
        ?>

        <div class='form-group'>
            <label for='selectUser'>Benutzer</label>
            <select class='form-control' id='selectUser' name='type'>
                <?php foreach ($users as $user) : ?>
                    <?php
                    $val = $user->__toString();
                    if (array_key_exists($user->getId(), $rooms)) {
                        $room = $rooms[$user->getId()];
                        $val = json_decode($user->__toString(), true);
                        $val['roomNumber'] = $room->getRoomNumber();
                        $val['roomName'] = $room->getName();
                        $val['absent'] = $user->isAbsent();
                        $val = json_encode($val);
                    }
                    ?>
                    <option value='<?php echo(escape($val)) ?>'>
                        <?php echo(escape($user->getLastName() . ' ' . $user->getFirstName())) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr>

        <?php include_once('inc/userForm.php') ?>

        <button type='submit' class='btn btn-primary' id='btn-edit-user'>Benutzer ändern</button>

        <button type='submit' class='btn btn-primary' id='btn-delete-user'>Benutzer löschen</button>

        <?php
    }

    public function action_stats() {
        $userId = $_REQUEST['userId'];
        $logs = LogDAO::getLogsForUser($userId);

        ?>
        <br>
        <form id='deleteStatisticsForm'>
            <button type='button' class='btn btn-primary' id='btn-delete-whole-statistics'>
                gesamte Statistik löschen
            </button>
            <button type='button' class='btn btn-primary' id='btn-delete-statistics-for-userId-<?php echo(escape($userId)) ?>'>
                Statistik für ausgewählten Benutzer löschen
            </button>
        </form>
        <br>

        <?php if (count($logs) > 0): ?>
        <table class='table table-hover'>
            <thead>
            <tr>
                <th width='16%'>BenutzerID</th>
                <th width='28%'>Aktion</th>
                <th width='28%'>Info</th>
                <th width='28%'>Uhrzeit</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($logs as $log):
                $logDate = escape(toDate($log->getDate(), 'd.m.Y H:i:s'));
                $logInfo = json_decode($log->getInfo(), true);

                $infoOutput = '';
                if ($logInfo != null) {
                    $event = EventDAO::getEventForId($logInfo['eventId']);
                    if ($event != null) {
                        if ($log->getAction() == LogDAO::LOG_ACTION_CHANGE_ATTENDANCE) {
                            $infoOutput = 'Sprechtag: ' . escape($event->getName()) .
                                          '<br>anwesend von: ' . escape(toDate($logInfo['fromTime'], 'H:i')) .
                                          '<br>anwesend bis: ' . escape(toDate($logInfo['toTime'], 'H:i'));
                        } else {
                            $slot = SlotDAO::getSlotForId($logInfo['slotId']);
                            $infoOutput = 'Sprechtag: ' . escape($event->getName()) . '<br>Termin: ' .
                                          escape(toDate($slot->getDateFrom(), 'H:i'));
                        }
                    }
                }
                ?>

                <tr>
                    <td><?php echo(escape($log->getUserId())) ?></td>
                    <td><?php echo(getActionString($log->getAction())) ?></td>
                    <td><?php echo($infoOutput) ?></td>
                    <td><?php echo($logDate) ?></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
        <?php else: ?>
            <p>Es sind keine Statistiken für den ausgewählten Benutzer vorhanden!</p>
        <?php endif;
    }

    public function action_getNewsletterForm() {
        ?>
        <form id='newsletterForm'>
            <?php
            $checkAccessData = UserDAO::checkAccessData();
            $activeEventExists = EventDAO::getActiveEvent() != null;
            $filename = 'uploads/newsletter_filled.odt';
            $fileExists = file_exists($filename);
            if ($checkAccessData) {
                if ($activeEventExists) { ?>
                    <input type='hidden' id='newsletterExists' value='<?php echo(escape($fileExists)) ?>'>
                    <button type='button' class='btn btn-primary' id='btn-create-newsletter'>
                        Rundbrief erzeugen
                    </button>
                <?php } else { ?>
                    <div class='alert alert-info'>
                        INFO: Es ist momentan kein Elternsprechtag als aktiv gesetzt!<br>
                        Setze einen Elternsprechtag als aktiv um einen Rundbrief erzeugen zu können!
                    </div>
                <?php }
            } elseif ($fileExists) { ?>
                <div class='alert alert-info'>
                    INFO: Um einen neuen Rundbrief zu erstellen, müssen zuerst wieder die Schüler importiert werden!<br>
                    (Falls gewünscht kann zuvor auch eine neue Rundbrief-Vorlage hochgeladen werden.)
                </div>
            <?php } else { ?>
                <div class='alert alert-danger'>
                    Keine Schüler-Zugangsdaten vorhanden! Es müssen zuerst die Schüler importiert werden!
                </div>
            <?php } ?>

            <?php if ($fileExists): ?>
                <button type='button' class='btn btn-primary' id='btn-delete-newsletter'>
                    Rundbrief löschen
                </button>
            <?php endif; ?>

            <?php if ($checkAccessData): ?>
                <button type='button' class='btn btn-primary' id='btn-delete-access-data'>
                    Schüler-Zugangsdaten löschen
                </button>
            <?php endif; ?>

            <div class='message' id='newsletterMessage'></div>

            <?php if ($fileExists): ?>
                <div class='newsletterDownload'>
                        <p>Rundbrief herunterladen: </p>
                        <a href='<?php echo($filename) ?>' type='application/vnd.oasis.opendocument.text' download>Rundbrief</a>
                </div>
            <?php endif; ?>
        </form>
        <?php
    }

    public function action_csvPreview() {
        $role = $_REQUEST['role'];
        $germanRole = $role == 'student' ? 'Schüler' : 'Lehrer';
        $users = UserDAO::getUsersForRole($role, 10);
        ?>
        <div>
            <h4><br>Die ersten 10 Einträge der importierten <?php echo(escape($germanRole)) ?>:</h4>
        </div>

        <table class='table table-striped'>
            <tr>
                <th>Benutzername</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Klasse</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo escape($user->getUserName()); ?></td>
                    <td><?php echo escape($user->getFirstName()); ?></td>
                    <td><?php echo escape($user->getLastName()); ?></td>
                    <td><?php echo escape($user->getClass()); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    public function action_templateDownloadAlert() {
        switch ($_REQUEST['type']) {
            case 'student':
                $typeText = 'Schüler Vorlage (CSV)';
                $mimeType = 'text/csv';
                $filePath = 'templates/students.csv';
                $infos = '<br><br>
                    <p><b>Infos:</b></p>
                    <p>
                    Ein Datensatz muss folgende Elemente besitzen:
                    <br>
                    Vorname;Nachname;Klasse;Benutzername;Passwort
                    <br><br>
                    Trennzeichen muss der Strichpunkt sein. Benutzername und Passwort sind optional.
                    <br><br>
                    Beispiele:
                    <ul>
                        <li>Angelika;Albers;8B;;</li>
                        <li>Britta;Bäcker;1D;baecker1;password1</li>
                    </ul>
                    </p>';
                break;

            case 'teacher':
                $typeText = 'Lehrer Vorlage (CSV)';
                $mimeType = 'text/csv';
                $filePath = 'templates/teachers.csv';
                $infos = '<br><br>
                    <p><b>Infos:</b></p>
                    <p>
                    Ein Datensatz muss folgende Elemente besitzen:
                    <br>
                    Vorname;Nachname;Klasse;Benutzername;Passwort;Titel;Raumnummer;Raumname
                    <br><br>
                    Trennzeichen muss der Strichpunkt sein. Raumnummer und Raumname sind optional.
                    <br><br>
                    Beispiele:
                    <ul>
                        <li>Otto;Normalverbraucher;1C;ottonormal;user987;Mag.;A001;Konferenzzimmer</li>
                        <li>John;Doe;2E;johnny456;some_pw!;BEd.;;</li>
                    </ul>
                    </p>';
                break;

            case 'newsletter':
            default:
                $typeText = 'Rundbrief Vorlage (ODT)';
                $mimeType = 'application/vnd.oasis.opendocument.text';
                $filePath = 'templates/newsletter_template.odt';
                $infos = '<br><br>
                    <p><b>Infos:</b></p>
                    <p>
                    In der Vorlage können folgende Platzhalter verwendet werden:
                    <ul>
                        <li>ESTODAY (heutiges Datum)</li> 
                        <li>ESDATE (Datum des Elternsprechtags)</li>
                        <li>ESFIRSTNAME (Vorname des Schülers)</li>
                        <li>ESLASTNAME (Nachname des Schülers)</li>
                        <li>ESCLASS (Klasse des Schülers)</li>
                        <li>ESUSERNAME (Benutzername des Schülers)</li>
                        <li>ESPASSWORD (Passwort des Schülers)</li>
                    </ul>
                    </p>';
        }

        ?>
        <div class='alert alert-info'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <h4>Tipp!</h4>
            <p><b>Vorlage herunterladen:</b></p>
            <a href='<?php echo($filePath) ?>' type='<?php echo($mimeType) ?>' download><?php echo escape($typeText); ?></a>
            <?php echo($infos) ?>
        </div>
        <?php
    }

    public function action_attendance() {
        $user = AuthenticationManager::getAuthenticatedUser();
        $event = EventDAO::getActiveEvent();

        return $this->getAttendance($user, $event);
    }

    public function action_attendanceParametrized() {
        $userId = $_REQUEST['userId'];
        $eventId = $_REQUEST['eventId'];
        $user = UserDAO::getUserForId($userId);
        $event = EventDAO::getEventForId($eventId);

        return $this->getAttendance($user, $event, true);
    }

    private function getAttendance($user, $event, $named = false) {
        $attendance = null;
        $salutation = 'Du bist am ';

        if ($user != null) {
            $attendance = SlotDAO::getAttendanceForUser($user->getId(), $event);
            if ($named) {
                $salutation = $user->getFirstName() . ' ' . $user->getLastName() . ' ist am ';
            }
        }

        if ($attendance != null) {
            if ($attendance['to'] - $attendance['from'] == 0) {
                $output = escape($salutation . date('d.m.Y', $attendance['date']) . ' nicht anwesend.');
            } else {
                $output = escape($salutation . date('d.m.Y', $attendance['date']) . ' von ' . date('H:i', $attendance['from']) . ' bis ' . date('H:i', $attendance['to']) . ' anwesend.');
            }
        } else {
            $output = escape('Es gibt momentan keinen aktuellen Elternsprechtag, für den eine Anwesenheit eingestellt werden könnte.');
        }

        echo $output . '<br><br>';
        return $attendance;
    }

    public function action_changeAttendance() {
        $userId = $_REQUEST['userId'];
        $eventId = $_REQUEST['eventId'];
        $user = UserDAO::getUserForId($userId);
        $event = EventDAO::getEventForId($eventId);
        ?>
        <h4>
        Aktuelle Anwesenheit
        </h4>
        <p id='attendance'>
            <?php $attendance = $this->getAttendance($user, $event, true); ?>
        </p>

        <?php if ($attendance != null): ?>
        <h4>
            Anwesenheit ändern
        </h4>
        <form id='changeAttendanceForm'>
            <input type='hidden' name='userId' value='<?php echo(escape($userId)) ?>'>
            <input type='hidden' name='eventId' value='<?php echo(escape($attendance['eventId'])) ?>'>
            <div class='form-group'>
                <label for='inputFromTime'>Von</label>
                <select class='form-control' id='inputSlotDuration' name='inputFromTime'>
                    <?php echo(getDateOptions($attendance, true)); ?>
                </select>
            </div>

            <div class='form-group'>
                <label for='inputToTime'>Bis</label>
                <select class='form-control' id='inputSlotDuration' name='inputToTime'>
                    <?php echo(getDateOptions($attendance, false)); ?>
                </select>
            </div>

            <button type='submit' class='btn btn-primary' id='btn-change-attendance'>
                Anwesenheit für <?php echo escape($user->getFirstName() . ' ' . $user->getLastName()); ?> ändern
            </button>
        </form>
        <?php endif;
    }

    public function action_getActiveEventContainer() {
        $event = EventDAO::getActiveEvent();
        $displayText = "kein aktiver Elternsrpechtag vorhanden!";
        $activeEventId = -1;
        if ($event != null) {
            $displayText = $event->getName() . ' am ' . toDate($event->getDateFrom(), 'd.m.Y') . ' (mit ' . $event->getSlotTime() . '-Minuten-Intervallen)';
            $activeEventId = $event->getId();
        }
        ?>
            <p id='activeSpeechdayText'><b>Aktiver Sprechtag:</b> <?php echo escape($displayText); ?></p>
            <input type='hidden' id='activeEventId' value='<?php echo escape($activeEventId); ?>'>
        <?php
    }
}
