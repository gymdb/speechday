<?php
require_once('AuthenticationManager.php');
require_once('dao/UserDAO.php');
require_once('dao/EventDAO.php');
require_once('dao/SlotDAO.php');
require_once('dao/LogDAO.php');
require_once('dao/RoomDAO.php');

class Controller {
    // request wide singleton
    protected static $instance = false;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Controller();
        }
        return self::$instance;
    }

    public function handlePostRequest() {
        //check request method
        if (($_SERVER['REQUEST_METHOD'] != 'POST') || (!isset($_REQUEST['action']))) {
            redirect('home.php');
        }

        //execute action
        $method = 'action_' . $_REQUEST['action'];
        $this->$method();
    }

    protected function forward($errors = null, $target = null) {
        if ($target == null) {
            if (!isset($_REQUEST['page'])) {
                throw new Exception('Missing target for forward!');
            }
            $target = strtok($_REQUEST['page'], '?');
        }
        // forward request to target
        require($_SERVER['DOCUMENT_ROOT'] . $target);
        exit(0); // --> successful termination of script
    }

    //=== USER ACTIONS ===
    protected function action_createEvent() {
        $name = $_REQUEST['name'];
        $date = $_REQUEST['date'];
        $beginTime = $_REQUEST['beginTime'];
        $endTime = $_REQUEST['endTime'];
        $slotDuration = $_REQUEST['slotDuration'];
        $setActive = $_REQUEST['setActive'] == 'true' ? true : false;

        $unixTimeFrom = strtotime($date . ' ' . $beginTime);
        $unixTimeTo = strtotime($date . ' ' . $endTime);

        if (!$unixTimeFrom || !$unixTimeTo) {
            return;
        }

        $eventId = EventDAO::createEvent($name, $unixTimeFrom, $unixTimeTo, $slotDuration, $setActive);
        if ($eventId > 0) {
            echo 'success';
        }
    }

    protected function action_changeAttendance() {
        $fromTime = $_REQUEST['inputFromTime'];
        $toTime = $_REQUEST['inputToTime'];
        $userId = $_REQUEST['userId'];
        $eventId = $_REQUEST['eventId'];

        if ($toTime < $fromTime) {
            echo 'failure';
            return;
        }

        SlotDAO::changeAttendanceForUser($userId, $eventId, $fromTime, $toTime);

        echo 'success';
    }

    protected function action_uploadFile() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Content-Type: text/html; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!array_key_exists('file-0', $_FILES)) {
                echo 'Es wurde keine Datei ausgewählt!';
                return;
            }

            $name = $_FILES['file-0']['name'];
            $tmpName = $_FILES['file-0']['tmp_name'];
            $error = $_FILES['file-0']['error'];
            $size = $_FILES['file-0']['size'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            switch ($error) {
                case UPLOAD_ERR_OK:
                    //validate file size
                    if ($size / 1024 / 1024 > 2) {
                        echo 'Die Datei überschreitet die Maximalgröße!';
                        return;
                    }

                    //upload file
                    $type = $_REQUEST['uploadType'];
                    if (in_array($type, array('student', 'teacher', 'subject'))) {
                        if (!$this->validateFileExtension($ext, array('csv'))) {
                            echo 'Ungültiges Dateiformat!';
                            return;
                        }
                        $targetPath = $this->uploadFileAs($name, $tmpName);
                        $importCSVResult = $this->importCSV($type, $targetPath);
                        echo $importCSVResult['success'] ? 'success' : $importCSVResult['message'];
                        return;

                    } else if ($type == 'newsletter') {
                        if (!$this->validateFileExtension($ext, array('odt'))) {
                            echo 'Ungültiges Dateiformat!';
                            return;
                        }
                        $this->uploadFileAs('newsletter.' . $ext, $tmpName);
                        echo 'success';
                        return;

                    } else {
                        echo 'Ungültiger Typ!';
                        return;
                    }

                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo 'Die Datei überschreitet die Maximalgröße!';
                    return;

                case UPLOAD_ERR_PARTIAL:
                    echo 'Die Datei konnte nicht vollständig hochgeladen werden!';
                    return;

                case UPLOAD_ERR_NO_FILE:
                    echo 'Es wurde keine Datei ausgewählt!';
                    return;

                case UPLOAD_ERR_NO_TMP_DIR:
                    echo 'Kein Ordner für den Dateiupload verfügbar!';
                    return;

                case UPLOAD_ERR_CANT_WRITE:
                    echo 'Die Datei konnte nicht auf den Server geschrieben werden!';
                    return;

                case UPLOAD_ERR_EXTENSION:
                    echo 'Der Dateiupload wurde durch eine Erweiterung abgebrochen!';
                    return;

                default:
                    echo 'Die Datei konnte nicht hochgeladen werden!';
                    return;
            }
        }
    }

    private function checkCSVHeader($type, $row) {
        $constraints['teacher'] = array('Vorname', 'Nachname', 'Klasse', 'Benutzername', 'Passwort', 'Titel', 'Raumnummer', 'Raumname');
        $constraints['student'] = array('Vorname', 'Nachname', 'Klasse', 'Benutzername', 'Passwort');
        $constraints['subject'] = array('ToDo');

        $constraintPart = implode('', $constraints[$type]);
        $length = strlen($constraintPart);
        if (substr(implode('', $row), 0 - $length) == substr($constraintPart, 0 - $length)) {
            return true;
        } else {
            return false;
        }
    }

    private function removeSpecials($string) {
        $search  = array('ç', 'æ',  'œ',  'á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ì', 'ò', 'ù', 'ä', 'ë', 'ï', 'ö', 'ü', 'ÿ', 'â', 'ê', 'î', 'ô', 'û', 'å', 'ø', 'ß', 'Ä', 'Ö', 'Ü');
        $replace = array('c', 'ae', 'oe', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'ae', 'e', 'i', 'oe', 'ue', 'y', 'a', 'e', 'i', 'o', 'u', 'a', 'o', 'ss', 'Ae', 'Oe', 'Ue');
        return str_replace($search, $replace, $string);
    }

    private function generateUserName($firstName, $lastName, $digits = 3) {
        $randomDigit = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        $firstName = strtolower($this->removeSpecials(preg_replace('/\s/', '', $firstName)));
        $lastName = strtolower($this->removeSpecials(preg_replace('/\s/', '', $lastName)));

        return substr($lastName, 0, 3) . substr($firstName, 0, 3) . $randomDigit;
    }

    private function generateRandomPassword($length = 10) {
        $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789!@#$%&*()_-=+,.?';
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    protected function uploadFileAs($name, $tmpName) {
        $folder = 'uploads';
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $targetPath = $folder . DIRECTORY_SEPARATOR . $name;
        move_uploaded_file($tmpName, $targetPath);
        return $targetPath;
    }

    protected function importCSV($role, $targetPath) {
        // import into database
        $filename = $targetPath;
        $fp = fopen($filename, 'r');

        //parse the csv file row by row
        $firstRow = true;
        $users = array();
        $accessData = array();
        $rooms = array();
        $userNames = array();

        $duplicateUserError = array(
            'success' => false,
            'message' => 'Die Benutzernamen sind nicht eindeutig! Bitte vergib eindeutige Benutzernamen!'
        );

        $csv = file_get_contents($filename);
        $isUTF8 = mb_detect_encoding($csv, mb_detect_order(), TRUE) == 'UTF-8';

        while (($row = fgetcsv($fp, 0, ';')) != FALSE) {
            if (!$isUTF8) {
                $row = array_map('utf8_encode', $row);
            }

            if ($firstRow) {
                if (!$this->checkCSVHeader($role, $row)) {
                    fclose($fp);
                    return array(
                        'success' => false,
                        'message' => 'Die Spalten der CSV Datei passen nicht zum gewählten Typ!'
                    );
                } else {
                    $firstRow = false;
                }
            } else {
                //insert csv data into mysql table
                $class = trim($row[2]) != '' ? trim($row[2]) : null;

                if ($role == 'teacher') {
                    $userName = trim($row[3]);
                    $password = trim($row[4]);

                    if (!$this->checkForUniqueUserName($userName, $userNames)) {
                        fclose($fp);
                        return $duplicateUserError;
                    }
                    $userNames[] = $userName;
                    $title = trim($row[5]);

                    $roomNumber = trim($row[6]);
                    $roomName = trim($row[7]);
                    if ($roomNumber != '' && $roomName != '') {
                        $rooms[$userName] = array($roomNumber, $roomName);
                    }
                } else {
                    $userName = trim($row[3]);

                    $tries = 0;
                    if ($userName == '') {
                        do {
                            $userName = $this->generateUserName(trim($row[0]), trim($row[1]));
                            $tries++;
                        } while ((!$this->checkForUniqueUserName($userName, $userNames)) && ($tries < 500));
                    }
                    if (!$this->checkForUniqueUserName($userName, $userNames)) {
                        fclose($fp);
                        return $duplicateUserError;
                    }
                    $userNames[] = $userName;
                    $title='';

                    $password = trim($row[4]) == '' ? $this->generateRandomPassword() : trim($row[4]);

                    $accessData[] = array($userName, $password);
                }

                $users[] = array($userName, createPasswordHash($password), trim($row[0]), trim($row[1]), $class, $role, $title);
            }
        }

        $deleteUserSuccess = UserDAO::deleteUsersByRole($role);
        $deleteEventSuccess = true;
        $deleteRoomSuccess = true;
        if ($role == 'teacher') {
            $deleteEventSuccess = EventDAO::deleteAllEvents();
            $deleteRoomSuccess = RoomDAO::deleteAllRooms();
        }

        if (!$deleteUserSuccess || !$deleteEventSuccess || !$deleteRoomSuccess) {
            fclose($fp);
            return array(
                'success' => false,
                'message' => 'Die bestehenden Einträge des gewählten Typs konnten nicht gelöscht werden!'
            );
        }

        UserDAO::bulkInsertUsers($users, $rooms);
        if (count($accessData) > 0) {
            UserDAO::bulkInsertAccessData($accessData);
        }

        fclose($fp);
        return array(
            'success' => true,
            'message' => 'Die CSV Datei wurde erfolgreich importiert!'
        );
    }

    private function checkForUniqueUserName($userName, $userNames) {
        return !in_array($userName, $userNames);
    }

    protected function validateFileExtension($ext, $allowed) {
        if (!in_array($ext, $allowed)) {
            return false;
        }

        return true;
    }

    protected function action_changeSlot() {
        $slotId = $_REQUEST['slotId'];
        $userId = $_REQUEST['userId'];
        $eventId = $_REQUEST['eventId'];

        $info = json_encode(array('eventId' => $eventId, 'slotId' => $slotId));
        LogDAO::log($userId, LogDAO::LOG_ACTION_BOOK_SLOT, $info);

        $result = SlotDAO::setStudentToSlot($eventId, $slotId, $userId);
        if ($result['success']) {
            if ($result['rowCount'] > 0) {
                echo('success');
            } else {
                echo('dirtyRead');
            }
        } else {
            echo('error');
        }
    }

    protected function action_deleteSlot() {
        $userId = $_REQUEST['userId'];
        $slotId = $_REQUEST['slotId'];
        $eventId = $_REQUEST['eventId'];

        $info = json_encode(array('eventId' => $eventId, 'slotId' => $slotId));
        LogDAO::log($userId, LogDAO::LOG_ACTION_DELETE_SLOT, $info);

        $success = SlotDAO::deleteStudentFromSlot($eventId, $slotId);
        if ($success) {
            echo('success');
        } else {
            echo('error');
        }
    }

    protected function action_setActiveEvent() {
        $eventId = $_REQUEST['eventId'];

        $success = EventDAO::setActiveEvent($eventId);

        if ($success) {
            echo('success');
        } else {
            echo('error');
        }
    }

    protected function action_deleteEvent() {
        $eventId = $_REQUEST['eventId'];

        $success = EventDAO::deleteEvent($eventId);

        if ($success) {
            echo('success');
        } else {
            echo('error');
        }
    }

    protected function action_createUser() {
        $userName = $_REQUEST['userName'];
        $password = $_REQUEST['password'];
        $firstName = $_REQUEST['firstName'];
        $lastName = $_REQUEST['lastName'];
        $class = $_REQUEST['class'];
        $type = $_REQUEST['type'];
        $roomNumber = $_REQUEST['roomNumber'];
        $roomName = $_REQUEST['roomName'];

        $userId = UserDAO::register($userName, $password, $firstName, $lastName, $class, $type);
        $updateRoomResult = true;
        if ($roomNumber != '' && $roomName != '') {
            $updateRoomResult = RoomDAO::update($roomNumber, $roomName, $userId)['success'];
        }

        if (($userId > 0) && $updateRoomResult) {
            echo('success');
        } else if ($userId == -1) {
            echo('Der Benutzer existiert bereits!');
        } else {
            echo('Das Passwort muss mindestens ' . UserDAO::MIN_PASSWORD_LENGTH . ' Zeichen lang sein!');
        }
    }

    protected function action_editUser() {
        $userId = $_REQUEST['userId'];
        $userName = $_REQUEST['userName'];
        $password = $_REQUEST['password'];
        $firstName = $_REQUEST['firstName'];
        $lastName = $_REQUEST['lastName'];
        $class = $_REQUEST['class'];
        $type = $_REQUEST['type'];
        $roomNumber = $_REQUEST['roomNumber'];
        $roomName = $_REQUEST['roomName'];

        $updateUserResult = UserDAO::update($userId, $userName, $password, $firstName, $lastName, $class, $type);
        $updateRoomResult = true;
        if ($roomNumber != '' && $roomName != '') {
            $updateRoomResult = RoomDAO::update($roomNumber, $roomName, $userId)['success'];
        }

        if ($updateUserResult && $updateRoomResult) {
            echo('success');
        } else {
            echo('error');
        }
    }

    protected function action_deleteUser() {
        $userId = $_REQUEST['userId'];

        $deleteUserResult = UserDAO::deleteUserById($userId);

        if ($deleteUserResult) {
            echo('success');
        } else {
            echo('error');
        }
    }

    protected function action_createNewsletter() {
        if (!UserDAO::checkAccessData()) {
            echo 'Keine Schüler-Zugangsdaten vorhanden! Es müssen zuerst die Schüler importiert werden!';
            return false;
        }

        $user = AuthenticationManager::getAuthenticatedUser();

        if (!file_exists('uploads/newsletter.odt')) {
            echo 'Keine Rundbrief-Vorlage vorhanden! Bitte lade zuerst eine hoch!';
            return false;
        }

        $newFileName = 'uploads/newsletter_filled.odt';
        copy('uploads/newsletter.odt', $newFileName);

        $zip = new ZipArchive;
        $fileToModify = 'content.xml';
        if ($zip->open($newFileName) === TRUE) {
            //Read contents into memory
            $oldContents = $zip->getFromName($fileToModify);
            //Modify contents:
            $newContents = $this->createNewsletter($oldContents);
            if ($newContents == null) {
                echo 'Der Rundbrief konnte nicht erstellt werden, da es keinen aktiven Elternsprechtag gibt!';
                $zip->close();
                unlink($newFileName);
                return false;
            }

            //Delete the old...
            $zip->deleteName($fileToModify);
            //Write the new...
            $zip->addFromString($fileToModify, $newContents);
            //And write back to the filesystem.
            $zip->close();

            echo 'success';
        } else {
            echo 'Der Rundbrief konnte nicht geöffnet werden!';
        }
    }

    protected function action_deleteNewsletter() {
        $newsletterPath = 'uploads/newsletter_filled.odt';

        if (!file_exists($newsletterPath)) {
            echo 'success';
            return true;
        }

        if (unlink($newsletterPath)) {
            echo 'success';
        } else {
            echo 'Der Rundbrief konnte nicht gelöscht werden!';
        }
    }

    protected function action_deleteAccessData() {
        $deleteSuccess = UserDAO::deleteAccessData();

        if ($deleteSuccess) {
            echo 'success';
        } else {
            echo 'Die Schüler-Zugangsdaten konnten nicht gelöscht werden!';
        }
    }

    private function createNewsletter($template) {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($template);
        $root = $doc->documentElement;

        $styles = $root->getElementsByTagName('automatic-styles')->item(0);
        $styleNode = $doc->createElement('style:style');
        $styleNode->setAttribute('style:name', 'NewsletterLineBreak');
        $styleNode->setAttribute('style:family', 'paragraph');
        $styleNodeChild = $doc->createElement('style:paragraph-properties');
        $styleNodeChild->setAttribute('fo:break-before', 'page');
        $styleNode->appendChild($styleNodeChild);
        $styles->appendChild($styleNode);

        $officeText = $root->getElementsByTagName('text')->item(0);
        $officeText->setAttribute("text:use-soft-page-breaks", "true");

        $breakNode = $doc->createElement('text:p');
        $breakNode->setAttribute('text:style-name', 'NewsletterLineBreak');
        $officeText->appendChild($breakNode);

        // --- student loop ---
        $copyNodeBackup = $officeText->cloneNode(true);
        $bodyNode = $root->getElementsByTagName('body')->item(0);

        $activeEvent = EventDAO::getActiveEvent();
        if ($activeEvent == null) {
            return null;
        }
        $students = UserDAO::getStudentsForNewsletter();

        foreach ($students as $studentInfo) {
            $student = $studentInfo['student'];
            $password = $studentInfo['password'];
            $trans = array(
                'ESTODAY' => toDate(time(), 'd.m.Y'),
                'ESDATE' => toDate($activeEvent->getDateFrom(), 'd.m.Y'),
                'ESFIRSTNAME' => escape($student->getFirstName()),
                'ESLASTNAME' => escape($student->getLastName()),
                'ESUSERNAME' => escape($student->getUserName()),
                'ESCLASS' => escape($student->getClass()),
                'ESPASSWORD' => escape($password)
            );

            $copyNode = $copyNodeBackup->cloneNode(true);

            $part = $doc->saveXML($copyNode);
            $part = strtr($part, $trans);

            $newPart = $doc->createDocumentFragment();
            $newPart->appendXML($part);

            $bodyNode->appendChild($newPart);
        }
        // --- student loop ---

        $doc->formatOutput = TRUE;
        $newFile = $doc->saveXML();

        return $newFile;
    }

    protected function action_deleteStats() {
        $userId = $_REQUEST['userId'];

        if ($userId != -1) {
            $success = LogDAO::deleteStatsForUser($userId);
        } else {
            $success = LogDAO::deleteAllStats();
        }

        if ($success) {
            echo 'success';
        } else {
            echo 'failure';
        }
    }
}
