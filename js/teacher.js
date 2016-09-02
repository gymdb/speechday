$(document).ready(function () {
    loadTimeTable(1);

    $('#selectType').change(function () {
        var typeSelect = $('#selectType').find('option:selected');
        var typeId = typeSelect.val();

        loadTimeTable(typeId);
    });
});

$(document).on('click', '#btn-change-attendance', function () {
    $('#changeAttendanceForm').submit(function (e) {
        var postData = $(this).serializeArray();
        postData = postData.concat({name: 'action', value: 'changeAttendance'})

        var formURL = 'controller.php';
        $.ajax({
            url: formURL,
            type: 'POST',
            data: postData,
            success: function (data, textStatus, jqXHR) {
                var message = $('#message');
                if (data.indexOf('success') > -1) {
                    $('#attendance').load('viewController.php?action=attendance');

                    showMessage(message, 'success', 'Die Anwesenheit wurde erfolgreich geändert!');
                } else {
                    showMessage(message, 'danger', 'Die Anwesenheit konnte nicht geändert werden!');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showMessage(message, 'danger', 'Die Anwesenheit konnte nicht geändert werden!');
            }
        });
        e.preventDefault();
    });

    return true;
});

function loadTimeTable(typeId) {
    var timeTable = $('#timeTable');
    $.ajax({
        url: 'viewController.php?action=getTeacherTimeTable&typeId=' + typeId,
        dataType: 'html',
        type: 'GET',
        success: function (data, textStatus, jqXHR) {
            timeTable.html(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            timeTable.html('<h3>Es ist ein Fehler aufgetreten!<br>Bitte versuche es später erneut!</h3>');
        }
    });
}