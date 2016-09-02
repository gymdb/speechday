$(document).ready(function () {
    loadTimeTable(1);

    $('#selectType').change(function () {
        var typeSelect = $('#selectType').find('option:selected');
        var typeId = typeSelect.val();

        loadTimeTable(typeId);
    });
});

$(document).on('click', '.btn-delete', function (event) {
    var postData = $.parseJSON(this.value);
    var typeId = postData.typeId;
    var errorText = '<h3>Beim Laden der Termine ist ein Fehler aufgetreten!<br>Bitte versuche es später erneut!</h3>';
    postData.action = 'deleteSlot';

    $.ajax({
        url: 'controller.php',
        type: 'POST',
        data: postData,
        success: function (data, textStatus, jqXHR) {
            if (data.indexOf('success') > -1) {
                loadTimeTable(typeId);
            } else {
                $('#timeTable').html(errorText);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#timeTable').html(errorText);
        }
    });
});

function loadTimeTable(typeId) {
    var timeTable = $('#timeTable');
    $.ajax({
        url: 'viewController.php?action=getMySlotsTable&typeId=' + typeId,
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