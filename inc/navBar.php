<nav class='navbar navbar-default navbar-fixed-top'>
    <div class='container'>
        <div class='navbar-header'>
            <button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#navbar'
                    aria-expanded='false' aria-controls='navbar'>
                <span class='sr-only'>Toggle navigation</span>
                <span class='icon-bar'></span>
                <span class='icon-bar'></span>
                <span class='icon-bar'></span>
            </button>
            <a class='navbar-brand' href='home.php'>Eltern Sprechtags Verwaltung</a>
        </div>
        <div id='navbar' class='navbar-collapse collapse'>

            <ul class='nav navbar-nav'>
                <li id='navTabHome'><a href='home.php'>Gebuchte Zeiten</a></li>
                <li id='navTabBook'><a href='book.php'>Zeiten buchen</a></li>
                <?php if ($user->getRole() === 'teacher') { ?>
                    <li id='navTabTeacher'><a href='teacher.php'>Übersicht</a></li>
                <?php } ?>
                <?php if ($user->getRole() === 'admin') { ?>
                    <li id='navTabAdmin'><a href='admin.php'>Administration</a></li>
                <?php } ?>
            </ul>

            <ul class='nav navbar-nav navbar-right'>

                <li class='dropdown'>
                    <a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true'
                       aria-expanded='false'>
                        <span class='glyphicon glyphicon-user'></span>
                        &nbsp;eingeloggt als: <?php echo escape($user->getUserName()); ?>
                        &nbsp;<span class='caret'></span></a>
                    <ul class='dropdown-menu'>
                        <li><a href='profile.php'><span class='glyphicon glyphicon-user'></span>  Benutzerprofil</a></li>
                        <li><a href='code/actions/logout.php'><span class='glyphicon glyphicon-log-out'></span>  Ausloggen</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>