<?php include_once 'inc/header.php'; ?>


<?php
$user = AuthenticationManager::getAuthenticatedUser();
if ($user->getRole() === "teacher"): ?>
    <script type='text/javascript' src='js/teacher.js'></script>
<?php endif ?>
<?php
function getRoleInGerman($role)
{
  switch ($role)
  {
    case 'admin':
      return 'Administrator';
      break;
    case 'student':
      return 'Schüler';
      break;
    case 'teacher':
      return 'Lehrer';
      break;
    default:
      return 'Unbekannt';
  }
}

?>


<div class='container'>
    <div class="message" id="message">
    </div>
    <h1>Benutzerprofil</h1>
    <table class='table table-striped'>
        <tr>
            <th>Benutzername</th>
            <td><?php echo escape($user->getUserName()); ?></td>
        </tr>
        <tr>
            <th>Vorname</th>
            <td><?php echo escape($user->getFirstName()); ?></td>
        </tr>
        <tr>
            <th>Nachname</th>
            <td><?php echo escape($user->getLastName()); ?></td>
        </tr>
        <tr>
            <th>Klasse</th>
            <td><?php echo escape($user->getClass()); ?></td>
        </tr>
        <tr>
            <th>Rolle</th>
            <td><?php echo escape(getRoleInGerman($user->getRole())); ?></td>
        </tr>
    </table>
    <br><br>

  <?php
  if ($user->getRole() === "teacher"): ?>
      <h2>Passwort ändern</h2>
      <table class='table table-striped'>
          <form id="changePasswordForm" method="POST" action="">
              <tr>
                  <th><label for="current_password">Aktuelles Passwort</label></th>
                  <td><input type="password" id="current_password" name="current_password" required></td>
              </tr>
              <tr>
                  <th><label for="new_password">Neues Passwort:</label></th>
                  <td><input type="password" id="new_password" name="new_password" required></td>
              </tr>
              <tr>
                  <th><label for="confirm_password">Bestätige Passwort:</label></th>
                  <td><input type="password" id="confirm_password" name="confirm_password" required></td>
              </tr>
              <tr>
                  <th></th>
                  <td><input id="btn-change-password" type="submit" value="Passwort ändern"></td>
              </tr>

          </form>
      </table>
  <?php endif ?>
</div>


<?php include_once 'inc/footer.php'; ?>

