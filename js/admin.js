$(document).ready(function () {

    loadChangeUserForm('createUser');

    $(document).on('click', '#btn-create-event', function () {
        validateForm();
        var createEventForm = $('#createEventForm');
        if (createEventForm.valid()) {
            createEventForm.submit(function (e) {
                var postData = $(this).serializeArray();
                var setActive = $('input[name="setActive[]"]:checked').length > 0;
                postData = postData.concat({name: 'action', value: 'createEvent'});

                if (setActive) {
                    postData = postData.concat({name: 'setActive', value: 'true'});
                } else {
                    postData = postData.concat({name: 'setActive', value: 'false'});
                }

                var formURL = 'controller.php';
                $.ajax({
                    url: formURL,
                    type: 'POST',
                    data: postData,
                    success: function (data, textStatus, jqXHR) {
                        var message = $('#createEventMessage');
                        if (data.indexOf('success') > -1) {
                            showMessage(message, 'success', 'Der Elternsprechtag wurde erfolgreich angelegt!');
                            loadChangeEventsForm();
                        } else {
                            showMessage(message, 'danger', 'Der Elternsprechtag konnte nicht angelegt werden!');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        showMessage(message, 'danger', 'Der Elternsprechtag konnte nicht angelegt werden!');
                    }
                });
                e.preventDefault();
                createEventForm.unbind('submit');
            });
        }

        return true;
    });

    $('input[type=radio][name=changeUserType]').change(function () {
        var type = this.value;
        loadChangeUserForm(type);
    });
});

function loadChangeUserForm(type) {
    $('#changeUserForm').load('viewController.php?action=' + type, function () {
        if (type == 'changeUser') {
            fillUserEditFields();
        }
    });
}

function validateForm() {
    $('#createEventForm').validate({
        rules: {
            name: {
                minlength: 3,
                required: true
            },
            date: {
                required: true
            },
            beginTime: {
                required: true
            },
            endTime: {
                required: true
            },
            slotDuration: {
                required: true
            }

        },
        messages: {
            name: 'Gib einen Namen für den Elternsprechtag ein!',
            date: 'Gib ein Datum ein!',
            beginTime: 'Gib eine Startzeit ein!',
            endTime: 'Gib eine Endzeit ein!',
            slotDuration: 'Gib eine Dauer für eine Einheit ein!'
        },
        highlight: function (element) {
            var id_attr = '#' + $(element).attr('id') + '1';
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(id_attr).removeClass('glyphicon-ok').addClass('glyphicon-remove');
        },
        unhighlight: function (element) {
            var id_attr = '#' + $(element).attr('id') + '1';
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
            $(id_attr).removeClass('glyphicon-remove').addClass('glyphicon-ok');
        },
        errorElement: 'span',
        errorClass: 'help-block',
        errorPlacement: function (error, element) {
            if (element.length) {
                error.insertAfter(element);
            } else {
                error.insertAfter(element);
            }
        }
    });
}

$(document).on('click', '#btn-upload-file', function (event) {
    var uploadFileForm = $('#uploadFileForm');
    uploadFileForm.submit(function (e) {
        var message = $('#uploadFileMessage');
        var data = new FormData();
        $.each($('#input-file')[0].files, function (i, file) {
            data.append('file-' + i, file);
        });
        data.append('action', 'uploadFile');

        var postData = $(this).serializeArray();
        var uploadType = postData[0].value;
        data.append('uploadType', uploadType);

        var successMessage = 'Die Rundbrief-Vorlage wurde erfolgreich hochgeladen!';
        if (uploadType == 'teacher') {
            successMessage = 'Die Lehrer wurden erfolgreich importiert!';
        } else if (uploadType == 'student') {
            successMessage = 'Die Schüler wurden erfolgreich importiert!';
        }

        var formURL = 'controller.php';
        $.ajax({
            url: formURL,
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function (data, textStatus, jqXHR) {
                if (data.indexOf('success') > -1) {
                    showMessage(message, 'success', successMessage);
                    if ($.inArray(uploadType, ['teacher', 'student']) > -1) {
                        $('#csv-preview').load('viewController.php?action=csvPreview&role=' + uploadType, function () {
                            $('#csv-preview').show();
                        });
                    }
                } else {
                    showMessage(message, 'danger', data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showMessage(message, 'danger', data);
            }
        });

        e.preventDefault();
        uploadFileForm.unbind('submit');
    });
});

$(document).on('click', '#btn-change-active-event', function (event) {
    var changeEventForm = $('#changeEventForm');
    changeEventForm.submit(function (e) {
        var postData = $(this).serializeArray();
        postData = postData.concat({name: 'action', value: 'setActiveEvent'});
        var message = $('#changeEventMessage');

        var formURL = 'controller.php';
        $.ajax({
            url: formURL,
            type: 'POST',
            data: postData,
            success: function (data, textStatus, jqXHR) {
                if (data.indexOf('success') > -1) {
                    showMessage(message, 'success', 'Der aktive Elternsprechtag wurde erfolgreich gesetzt!');
                } else {
                    showMessage(message, 'danger', 'Der aktive Elternsprechtag konnte nicht gesetzt werden!');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showMessage(message, 'danger', 'Der aktive Elternsprechtag konnte nicht gesetzt werden!');
            }
        });
        e.preventDefault();
        changeEventForm.unbind('submit');
    });
});

$(document).on('click', '#btn-delete-event', function (event) {
    var changeEventForm = $('#changeEventForm');
    changeEventForm.submit(function (e) {
        var postData = $(this).serializeArray();
        postData = postData.concat({name: 'action', value: 'deleteEvent'});
        var message = $('#changeEventMessage');

        var formURL = 'controller.php';
        $.ajax({
            url: formURL,
            type: 'POST',
            data: postData,
            success: function (data, textStatus, jqXHR) {
                if (data.indexOf('success') > -1) {
                    showMessage(message, 'success', 'Der Elternsprechtag wurde erfolgreich gelöscht!');
                    loadChangeEventsForm();
                } else {
                    showMessage(message, 'danger', 'Der Elternsprechtag konnte nicht gelöscht werden!');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showMessage(message, 'danger', 'Der Elternsprechtag konnte nicht gelöscht werden!');
            }
        });
        e.preventDefault();
        changeEventForm.unbind('submit');
    });
});

function loadChangeEventsForm() {
    var changeEventForm = $('#changeEventForm');
    $.ajax({
        url: 'viewController.php?action=getChangeEventForm',
        dataType: 'html',
        type: 'GET',
        success: function (data, textStatus, jqXHR) {
            changeEventForm.html(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            changeEventForm.html('<h1>Es ist ein Fehler aufgetreten!<br>Bitte versuche es später erneut!</h1>');
        }
    });
}

$(document).on('click', '#btn-create-user', function (event) {
    var createUserForm = $('#editUsersForm');
    createUserForm.submit(function (e) {
        var postData = $(this).serializeArray();
        postData = postData.concat({name: 'action', value: 'createUser'});
        var message = $('#changeUserMessage');

        var formURL = 'controller.php';
        $.ajax({
            url: formURL,
            type: 'POST',
            data: postData,
            success: function (data, textStatus, jqXHR) {
                if (data.indexOf('success') > -1) {
                    showMessage(message, 'success', 'Der Benutzer wurde erfolgreich erstellt!');
                } else {
                    showMessage(message, 'danger', data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showMessage(message, 'danger', 'Der Benutzer konnte nicht erstellt werden!');
            }
        });
        e.preventDefault();
        createUserForm.unbind('submit');
    });
});

$(document).on('click', '#btn-edit-user', function (event) {
    var editUsersForm = $('#editUsersForm');
    editUsersForm.submit(function (e) {
        var postData = $(this).serializeArray();
        postData = postData.concat({name: 'action', value: 'editUser'});
        var message = $('#changeUserMessage');

        var formURL = 'controller.php';
        $.ajax({
            url: formURL,
            type: 'POST',
            data: postData,
            success: function (data, textStatus, jqXHR) {
                if (data.indexOf('success') > -1) {
                    showMessage(message, 'success', 'Der Benutzer wurde erfolgreich geändert!');
                } else {
                    showMessage(message, 'danger', 'Der Benutzer konnte nicht geändert werden!');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showMessage(message, 'danger', 'Der Benutzer konnte nicht geändert werden!');
            }
        });
        e.preventDefault();
        editUsersForm.unbind('submit');
    });
});

$(document).on('click', '#btn-delete-user', function (event) {
    var editUsersForm = $('#editUsersForm');
    editUsersForm.submit(function (e) {
        var postData = $(this).serializeArray();
        postData = postData.concat({name: 'action', value: 'deleteUser'});
        var message = $('#changeUserMessage');

        var formURL = 'controller.php';
        $.ajax({
            url: formURL,
            type: 'POST',
            data: postData,
            success: function (data, textStatus, jqXHR) {
                if (data.indexOf('success') > -1) {
                    loadChangeUserForm('changeUser');
                    showMessage(message, 'success', 'Der Benutzer wurde erfolgreich gelöscht!');
                } else {
                    showMessage(message, 'danger', 'Der Benutzer konnte nicht gelöscht werden!');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showMessage(message, 'danger', 'Der Benutzer konnte nicht gelöscht werden!');
            }
        });
        e.preventDefault();
        editUsersForm.unbind('submit');
    });
});

function fillUserEditFields() {
    var userSelect = $('#selectUser').find('option:selected');
    var user = $.parseJSON(userSelect.val());

    $('#inputUserId').val(user.id);
    $('#inputUserName').val(user.userName);
    $('#inputPassword').val('');
    $('#inputFirstName').val(user.firstName);
    $('#inputLastName').val(user.lastName);
    $('#inputClass').val(user.class);
    $('#inputRoomNumber').val(user.roomNumber);
    $('#inputRoomName').val(user.roomName);

    var typeSelect = $('#selectType');
    typeSelect.find('option').removeAttr('selected');
    typeSelect.find("option[value='" + user.role + "']").prop('selected', true);
    changeRoomInputVisibility(user.role == 'teacher');
}

$(document).on('change', '#selectUser', function (event) {
    fillUserEditFields();
});

$(document).on('change', '#selectType', function (event) {
    var typeSelect = $('#selectType').find('option:selected');
    var type = typeSelect.val();
    changeRoomInputVisibility(type == 'teacher');
});

function changeRoomInputVisibility(condition) {
    if (condition) {
        $('#inputRoomNumber-div').removeClass('hidden');
        $('#inputRoomName-div').removeClass('hidden');
    } else {
        $('#inputRoomNumber-div').addClass('hidden');
        $('#inputRoomName-div').addClass('hidden');
    }
}

$(document).on('change', '#selectUserStats', function (event) {
    var userSelect = $('#selectUserStats');
    userSelect.find("option[value='-1']").remove();

    var selectedUser = userSelect.find('option:selected');
    var user = $.parseJSON(selectedUser.val());
    var userId = user.id;

    $('#statistics').load('viewController.php?action=stats&userId=' + userId);
});

$(document).on('click', '#newsletterForm .btn', function (event) {
    var message = $('#newsletterMessage');
    var id = $(this).attr('id');

    var successMessage = '';
    var postData;

    if (id === 'btn-create-newsletter') {
        postData = $.param({action: 'createNewsletter'});
        successMessage = 'Der Rundbrief wurde erfolgreich erstellt!';
    } else if (id === 'btn-delete-newsletter') {
        postData = $.param({action: 'deleteNewsletter'});
        successMessage = 'Der Rundbrief wurde erfolgreich gelöscht!';
    } else {
        postData = $.param({action: 'deleteAccessData'});
        successMessage = 'Die Schüler-Zugangsdaten wurden erfolgreich gelöscht!';
    }

    var formURL = 'controller.php';
    $.ajax({
        url: formURL,
        type: 'POST',
        data: postData,
        success: function (data, textStatus, jqXHR) {
            if (data.indexOf('success') > -1) {
                $('#newsletterForm').load('viewController.php?action=getNewsletterForm', function () {
                    var newMessage = $('#newsletterMessage');
                    showMessage(newMessage, 'success', successMessage);
                });
            } else {
                showMessage(message, 'danger', data);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showMessage(message, 'danger', 'Der Rundbrief konnte nicht erstellt werden!');
        }
    });
});

$(document).on('click', '#deleteStatisticsForm .btn', function (event) {
    var message = $('#statisticsMessage');
    var id = $(this).attr('id').replace('btn-delete-statistics-for-userId-', '');

    var successMessage = 'Die Statistik wurde erfolgreich gelöscht!';
    var errorMessage = 'Die Statistik konnte nicht gelöscht werden!';
    var postData;

    var userId = 1;
    if (id === 'btn-delete-whole-statistics') {
        postData = $.param({action: 'deleteStats', userId: -1});
    } else {
        userId = id;
        postData = $.param({action: 'deleteStats', userId: userId});
        successMessage = 'Die Statistik für den ausgewählten Benutzer wurde erfolgreich gelöscht!';
    }

    var formURL = 'controller.php';
    $.ajax({
        url: formURL,
        type: 'POST',
        data: postData,
        success: function (data, textStatus, jqXHR) {
            if (data.indexOf('success') > -1) {
                $('#statistics').load('viewController.php?action=stats&userId=' + userId, function () {
                    var newMessage = $('#statisticsMessage');
                    showMessage(newMessage, 'success', successMessage);
                });
            } else {
                showMessage(message, 'danger', errorMessage);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showMessage(message, 'danger', errorMessage);
        }
    });
});