<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Resme
 * @subpackage Resme/public/partials
 */
?>

<?php

$atts = array_change_key_case((array)$atts, CASE_LOWER);
if (!isset($atts['id'])) wp_die(__("Reservation ID not set.", 'resme'));
$facilityID = (int)$atts['id'];
if ($facilityID == 0) wp_die(__("Court Reservation ID invalid.", 'resme'));


$court = $this->getCourtByID($facilityID);
if ($court == null) wp_die(__("Court not found.", 'resme'));

$mayEdit = current_user_can('place_reservation');
if ($mayEdit) {
	$username = wp_get_current_user()->display_name;
} else {
	$username = '';
}

if (isset($atts['displaydays'])) {
	$displaydays = $atts['displaydays'];
} else {
	$displaydays = 5;
}

$reservationTypes = explode('|', $court->allowedtypes);

$curdate = new DateTimeImmutable();
$daysToAdd = new DateInterval('P' . $displaydays . 'D');
$enddate = $curdate->add($daysToAdd);

$maxdate = $curdate->add(new DateInterval('P' . $court->days . 'D'));
//$mindate = date_sub($curdate, new DateInterval('P' . $count->history . 'D'));
$mindate = $curdate->sub(new DateInterval('P30D'));
?>

<div id="message" class="alert alert-info alert-dismissible" style="display:none">
	<button id="messageAlert" type="button" class="close" data-dismiss="alert">&times;</button>
	<span id="messageText">Reached the end of the calendar.</span>
</div>

<div id="courtData" data-courtid='<?= $court->id ?>' data-mayedit='<?= $mayEdit ?>' 
	data-displaydays='<?= $displaydays ?>' 
	data-startdate='<?= date_format($curdate, 'Y-m-d') ?>'
	data-enddate='<?= $enddate->format('Y-m-d') ?>'  data-maxdate='<?= $maxdate->format('Y-m-d') ?>' 
	data-mindate='<?= $mindate->format('Y-m-d') ?>' >
</div>  
<!-- new reservation dialog -->
<div id="_dialog" title="<?= $court->name ?> <?= __('Reservation', 'resme'); ?>" style="display:none;">
  <form id="_resform" class="resform" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="add_reservation">
    <input type="hidden" name="courtid" value="<?= $court->id ?>" />
    <input type="hidden" name="day" />
    <input type="hidden" name="hour" />
	  <table class="table reservations">
		  <tr>
			  <td><?= __('Date', 'resme'); ?></td>
			  <td>
				  <span id="date">&ndash;</span>
				  <span>from</span>
				  <span id="time">&ndash;</span>
			  </td>
		  </tr>
		  <tr>
			  <td><?= __('Player', 'resme'); ?></td>
			  <td><?= $username ?></td>
		  </tr>
		 
		  <tr>
			  <td><?= __('Type', 'resme'); ?></td>
			  <td>
				  <select name="type">
						<?php 
					$html = "";
					foreach ($reservationTypes as $reservationType) {
						$html .= "<option value=" . $reservationType . ">" . $reservationType . "</option>";
					}
					echo $html
					?>
				  </select>
			  </td>
		  </tr>
	  </table>
  </form>
</div>

<!-- reservation form -->
<div class="modal" tabindex="-1" role="dialog" id="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= $court->name ?> <?= __('Reservation', 'resme'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
	  	<form id="resform">
		  	<input type="hidden" name="action" value="add_reservation">
    		<input type="hidden" name="courtid" value="<?= $court->id ?>" />
    		<input type="hidden" name="day" />
    		<input type="hidden" name="hour" />
			<div class="form-group">
				<label for="date"><?= __('Date', 'resme'); ?></label>
				<input type="text" class="form-control" id="date" readonly >
			</div>
			<div class="form-group">
				<label for="member"><?= __('Player', 'resme'); ?></label>
				<input type="text" class="form-control" id="member" value="<?= $username ?> " readonly>
			</div>
			<div class="form-group">
				<label for="type"><?= __('Type', 'resme'); ?></label>
				<select class="form-control" name="type" id="type">
					<?php 
						$html = "";
						foreach ($reservationTypes as $reservationType) {
							$html .= "<option value=" . $reservationType . ">" . $reservationType . "</option>";
						}
						echo $html
					?>
				  </select>
			</div>
			
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" id="submit" class="btn btn-primary" data-dismiss="modal">Save changes</button>
      </div>
    </div>
  </div>
</div> 

<!--
<div id="loader" class="spinner" style="display:none">
  <img src="<?= plugin_dir_url(__FILE__) . 'images/ajax-loader.gif' ?> "> 
</div> -->

<div  id="loader" class="smt-spinner-circle" style="display:none">
   <div class="smt-spinner"></div>
</div>
<!-- reservation page -->
<!-- toolbar -->
<div class="btn-toolbar mb-3" style="display: flex; justify-content: center; margin-bottom: 3px" role="toolbar" aria-label="Date pager with search">
  <div class="btn-group mr-2 datepager" role="group" aria-label="Pager group">
    <button type="button" class="btn btn-secondary" id="page-left"><i class="fas fa-angle-double-left"></i></button>
    <button type="button" class="btn btn-secondary" id="left"><i class="fas fa-angle-left"></i></button>
    <button type="button" class="btn btn-secondary" id="right"><i class="fas fa-angle-right"></i></button>
    <button type="button" class="btn btn-secondary" id="page-right"><i class="fas fa-angle-double-right"></i></button>
  </div>
  <div class="input-group mr-2">
		<div class="input-group-btn">
			<button class="btn" id="btnSearch"><i class="fas fa-search"></i></button>
		</div>
    <input type="date" id="searchDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" placeholder="Input search date" aria-label="Input search date" aria-describedby="btnGroupAddon">
	</div>
	<div class="input-group">
		<button type="button" id="today" class="btn btn-secondary" id="left">Today</i></button>
	</div>
</div>



<div class="table-responsive">
  <table class="table reservations" id="courtReservations">
  </table>
</div>

