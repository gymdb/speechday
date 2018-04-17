<?php
require_once('code/dao/EventDAO.php');
include_once 'inc/header.php';
?>

<script type='text/javascript' src='js/book.js'></script>

<p id='pageName' hidden>Book</p>

<div class='container'>
    <div id='tabs-1'>
        <h1>Zeitübersicht</h1>
        <h3>Hier können Sie Termine beim gewünschten Lehrer/Lehrerin buchen!<br><br></h3>
    </div>
</div>

<?php $activeEvent = EventDAO::getActiveEvent(); ?>

<div class='container'>
    <div>
        <?php if ($activeEvent != null): ?>
            <?php if ($activeEvent->getFinalPostDate() > time()): ?>
               <form id='chooseTeacherForm'>
                   <div class='form-group'>
                       <label for='selectTeacher'>Lehrer / Lehrerin</label>
                       <select class='form-control' id='selectTeacher' name='teacher'>
                           <?php echo(getTeacherOptions()); ?>
                       </select>
                   </div>
               </form>

               <div id='timeTable'></div>
           <?php else: ?>
            <h3>Buchungen sind nicht mehr möglich!</h3>
           <?php endif; ?>
            
        <?php else: ?>
            <h3>Es gibt momentan keinen Elternsprechtag!</h3>
        <?php endif; ?>
    </div>
</div>


<?php include_once 'inc/footer.php'; ?>

