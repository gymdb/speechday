<?php include_once 'inc/header.php'; ?>

<?php
$user = AuthenticationManager::getAuthenticatedUser();

function getRoleInGerman($role){
    switch ($role) {
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
</div>

<?php include_once 'inc/footer.php'; ?>

