<?php
require_once('code/AuthenticationManager.php');
require_once('code/ViewController.php');
AuthenticationManager::checkPrivilege('admin');

include_once 'inc/header.php';
?>

<script type='text/javascript' src='js/admin.js'></script>
<script type='text/javascript' src='js/validation.min.js'></script>

<link href='libs/bootstrap/css/bootstrap-datepicker3.min.css' rel='stylesheet'>
<link href='libs/bootstrap/css/bootstrap-datetimepicker.css' rel='stylesheet'>
<script src='libs/bootstrap/js/bootstrap-datepicker.min.js'></script>
<script src='libs/bootstrap/locales/bootstrap-datepicker.de.min.js'></script>
<script type="text/javascript" src="libs/bootstrap/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="libs/bootstrap/js/bootstrap-datetimepicker.de.js" charset="UTF-8"></script>

<p id='pageName' hidden>Admin</p>

<div class='container'>

    <h1>Administration</h1>

    <div class='panel-group' id='accordion'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapse1'>
                        Neuen Elternsprechtag anlegen
                    </a>
                </h4>
            </div>
            <div id='collapse1' class='panel-collapse collapse'>
                <div class='panel-body'>

                    <form id='createEventForm'>
                        <div class='form-group'>
                            <label for='inputName'>Name</label>
                            <input type='text' class='form-control' id='inputName' name='name'
                                   placeholder='Tragen Sie hier den Namen des Elternsrpechtags ein'>
                        </div>

                        <div class='form-group'>
                            <label for='inputDate'>Datum</label>
                            <div class='input-group input-append date' id='datePicker'>
                                <input type='text' class='form-control' id='inputDate' name='date'>
                                <span class='input-group-addon'><i class='glyphicon glyphicon-calendar'></i></span>
                            </div>
                        </div>

                        <script>
                            $('#datePicker').datepicker({
                                container: '#datePicker',
                                startDate: '0d',
                                autoclose: true,
                                format: 'dd.mm.yyyy',
                                language: 'de',
                                daysOfWeekDisabled: '0,6',
                                daysOfWeekHighlighted: '1,2,3,4,5',
                                calendarWeeks: true,
                                todayHighlight: true
                            });
                        </script>

                        <div class='form-group'>
                            <label for='inputStartTime'>Beginn</label>
                            <input type='text' class='form-control' id='inputStartTime' name='beginTime'
                                   placeholder='16:00'>
                        </div>

                        <div class='form-group'>
                            <label for='inputEndTime'>Ende</label>
                            <input type='text' class='form-control' id='inputEndTime' name='endTime'
                                   placeholder='20:00'>
                        </div>

                        <div class='form-group'>
                            <label for='inputSlotDuration'>Dauer einer Einheit</label>
                            <select class='form-control' id='inputSlotDuration' name='slotDuration'>
                                <option>5</option>
                                <option>10</option>
                                <option>15</option>
                                <option>20</option>
                            </select>
                        </div>


                        <div class='form-group'>
                            <label for='inputDate'>Buchungsende</label>
                            <div class='input-group input-append date' id='datePickerBooking'>
                                <input type='text' class='form-control' id='bookingDate' name='bookingDate'>
                                <span class='input-group-addon'><i class='glyphicon glyphicon-calendar'></i></span>
                            </div>
                        </div>
<script>
                            $('#datePickerBooking').datetimepicker({
                                format: 'dd.mm.yyyy hh:ii',
                                language: 'de',
});</script>

                      <!--  <script>
                            $('#datePickerBooking').datetimepicker({
    format: 'dd.mm.yyy hh:ii',
    defaultDate: new Date('2015-09-01 10:00'),
    disabledDates: [
    moment("2015-12-25 10:00")]
                            });
                        </script> -->


                        <div class='form-group'>
                            <label><input type='checkbox' name='setActive[]'> als aktiven Elternsrpechtag setzen</label>
                        </div>

                        <button type='submit' class='btn btn-primary' id='btn-create-event'>Anlegen</button>
                    </form>

                    <div class='message' id='createEventMessage'></div>
                </div>
            </div>
        </div>

        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapse2'>
                        Datei Upload / Import
                    </a>
                </h4>
            </div>
            <div id='collapse2' class='panel-collapse collapse'>
                <div class='panel-body'>
                    <form id='uploadFileForm'>
                        <div class='form-group'>
                            <label for='inputUploadType'>Typ</label>
                            <select class='form-control' id='inputUploadType' name='uploadType'>
                                <option value='teacher'>Lehrer</option>
                                <option value='student'>Schüler</option>
                                <!-- <option value='subject'>Fächer</option> -->
                                <option value='newsletter'>Rundbrief</option>
                            </select>
                        </div>

                        <div class='form-group'>
                            <label class='control-label'>Datei auswählen</label>
                            <input id='input-file' type='file' name='file' class='file' data-show-preview='false'
                                   accept='.csv,.odt'>
                            <p id="allowed-file-types" class='help-block'>Es sind nur CSV Dateien erlaubt.</p>

                            <div id='templateDownloadAlertContainer'></div>
                        </div>

                        <button type='submit' class='btn btn-primary' id='btn-upload-file'>Importieren</button>
                    </form>

                    <div class='message' id='uploadFileMessage'></div>

                    <div id="csv-preview"></div>
                </div>
            </div>
        </div>

        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapse3'>
                        Elternsprechtagsverwaltung
                    </a>
                </h4>
            </div>
            <div id='collapse3' class='panel-collapse collapse'>
                <div class='panel-body'>

                    <?php
                    $viewController = ViewController::getInstance();
                    echo($viewController->action_getChangeEventForm());
                    ?>

                    <div class='message' id='changeEventMessage'></div>
                </div>
            </div>
        </div>

        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapseTimeManagement'>
                        Anwesenheitszeitverwaltung
                    </a>
                </h4>
            </div>
            <div id='collapseTimeManagement' class='panel-collapse collapse'>
                <div class='panel-body'>
                    <div id="activeEventContainer"></div>
                    <hr>

                    <div class='form-group'>
                        <h4>Lehrer</h4>
                        <select class='form-control' id='selectTeacher'>
                            <?php
                            $teachers = UserDAO::getUsersForRole('teacher');
                            foreach ($teachers as $teacher) : ?>
                                <?php
                                $val = $teacher->__toString();
                                ?>
                                <option value='<?php echo(escape($val)) ?>'>
                                    <?php echo(escape($teacher->getLastName() . ' ' . $teacher->getFirstName())) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <hr>

                    <div id="changeAttendanceTime"></div>

                    <div class='message' id='changeTimeMessage'></div>
                </div>
            </div>
        </div>

        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapse4'>
                        Benutzerverwaltung
                    </a>
                </h4>
            </div>
            <div id='collapse4' class='panel-collapse collapse'>
                <div class='panel-body'>

                    <form id='editUsersForm'>
                        <div id='changeUserType'>
                            <div class='form-group'>
                                <div class='radio'>
                                    <label><input type='radio' name='changeUserType' value='createUser' checked>
                                        neuen Benutzer erstellen
                                    </label>
                                </div>
                                <div class='radio'>
                                    <label><input type='radio' name='changeUserType' value='changeUser'>
                                        bestehenden Benutzer bearbeiten
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id='changeUserForm'></div>
                    </form>

                    <div class='message' id='changeUserMessage'></div>
                </div>
            </div>
        </div>

        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapse5'>
                        Rundbriefverwaltung
                    </a>
                </h4>
            </div>
            <div id='collapse5' class='panel-collapse collapse'>
                <div class='panel-body'>
                    <?php
                    $viewController = ViewController::getInstance();
                    $viewController->action_getNewsletterForm();
                    ?>
                </div>
            </div>
        </div>

        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapse6'>
                        Statistik
                    </a>
                </h4>
            </div>
            <div id='collapse6' class='panel-collapse collapse'>
                <div class='panel-body'>

                    <form id='statisticsForm'>
                        <div class='form-group'>
                            <label for='selectUserStats'>Benutzer</label>
                            <select class='form-control' id='selectUserStats' name='type'>
                                <option value="-1">Bitte wähle einen Benutzer ...</option>
                                <?php $users = UserDAO::getUsers(); ?>
                                <?php foreach ($users as $user) : ?>
                                    <option value='<?php echo(escape($user->__toString())) ?>'>
                                        <?php echo(escape($user->getLastName() . ' ' . $user->getFirstName())) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <div class='message' id='statisticsMessage'></div>

                    <div id='statistics'></div>
                </div>
            </div>
        </div>

        <div id="print-panel" class='panel panel-default'>
            <div class='panel-heading'>
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-parent='#accordion' href='#collapse7'>
                        Zeitpläne ausdrucken
                    </a>
                </h4>
            </div>
            <div id='collapse7' class='panel-collapse collapse'>
                <div class='panel-body'>
                    <button class="btn btn-primary" onclick="PrintElem('#adminTimeTable', '<?php echo escape(getActiveSpeechdayText()); ?>')">
                        <span class='glyphicon glyphicon-print'></span>&nbsp;&nbsp;Zeitpläne ausdrucken
                    </button>

                    <div id='adminTimeTable' class="section-to-print only-print"></div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include_once 'inc/footer.php'; ?>

