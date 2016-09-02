/*
 Author: Pradeep Khodke
 URL: http://www.codingcage.com/
 */

$('document').ready(function () {
    /* validation */
    $('#login-form').validate({
        rules: {
            password: {
                required: true
            },
            userName: {
                required: true
            }
        },
        messages: {
            password: {
                required: 'Bitte gib dein Passwort ein.'
            },
            userName: 'Bitte gib deinen Benutzernamen ein.',
        },
        submitHandler: submitForm
    });
    /* validation */

    /* login submit */
    function submitForm() {
        var data = $('#login-form').serialize();

        $.ajax({
            type: 'POST',
            url: 'code/actions/login_process.php',
            data: data,
            beforeSend: function () {
                $('#error').fadeOut();
                $('#btn-login').html('<img src="img/btn-ajax-loader.gif" height="12px" width="14px" /> &nbsp; Einloggen');
            },
            success: function (response) {
                if (response == 'ok') {
                    $('#btn-login').html('<img src="img/btn-ajax-loader.gif" height="12px" width="14px" /> &nbsp; Einloggen');
                    setTimeout(' window.location.href = "home.php"; ', 4000);
                }
                else {
                    $('#error').fadeIn(1000, function () {
                        $('#error').html('<div class="alert alert-danger"> <span class="glyphicon glyphicon-info-sign"></span> &nbsp; ' + response + '</div>');
                        $('#btn-login').html('<span class="glyphicon glyphicon-log-in"></span> &nbsp; Einloggen');
                    });
                }
            }
        });
        return false;
    }
    /* login submit */

});