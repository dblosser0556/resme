<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://webmuehle.at
 * @since      1.0.3
 *
 * @package    resme
 * @subpackage resme/admin/partials
 */
?>

<?php
 
 if (!current_user_can('manage_options')) {
      wp_die();
  }

  # $this->cleanUpReservations();

  if(isset($_POST['id']) && isset($_POST['delete'])) {
    $this->deleteReservationByID($_POST['id']);
    $message = "Deleted Reservation";
  }
  
  $reservations = $this->getReservations();
 
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
  <h1 class="wp-heading-inline"><?= __('Upcoming Reservations', 'resme');?></h1>
  <hr class="wp-header-end">
  <?php if (isset($message)) { ?>
    <div id="message" class="updated notice is-dismissible">
        <p><?= $message ?></p>
        <button type="button" class="notice-dismiss"></button>
    </div>
      <?php 
    } ?>
  <table class="wp-list-table widefat fixed striped posts">
    <thead>
      <tr>
        <th class="manage-column column-title column-primary"><?= __('Court', 'resme');?></th>
        <th class="manage-column column-title column-primary"><?= __('Player', 'resme');?></th>
        <th class="manage-column column-title column-primary"><?= __('Type', 'resme');?></th>
        <th class="manage-column column-title column-primary"><?= __('Date', 'resme');?></th>
        <th class="manage-column column-title column-primary"><?= __('Time', 'resme');?></th>
        <th class="manage-column column-title"><?= __('Action', 'resme');?></th>
      </tr>
    </thead>
    <tbody>
      <?php for($i=0;$i<sizeof($reservations);$i++) { $item = $reservations[$i]; ?>
        <tr>
          <td><?= $item->courtname ?></td>
          <td><?= (new WP_User($item->userid))->display_name ?></td>
          <td><?= $item->type ?></td>
          <td>
            <?= date_i18n(get_option('date_format'), strtotime($item->date)) ?>
          </td>
          <td>
            <?= $item->time. '&ndash;'.($item->time+1) ?>
          </td>
          <td>
            <form method="POST">
              <input type="hidden" name="id" value="<?= $item->id ?>"/>
              <input class="button" type="submit" name="delete" value=<?= __('Delete', 'resme');?>/>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <p></p>
</div>
