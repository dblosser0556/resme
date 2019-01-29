<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://webmuehle.at
 * @since      1.0.3
 *
 * @package    Courtres
 * @subpackage Courtres/admin/partials
 */
?>

<?php

  if (!current_user_can('manage_options')) {
      wp_die();
  }

  global $wpdb;
  $table_name = $this->getTable('facilities');

  if(isset($_GET['courtID'])) {
    $courtID = (int)$_GET['courtID'];
  }

  if(isset($_POST['delete']) && isset($_POST['id']) && (int)$_POST['id']>0) { // delete
    $wpdb->delete($table_name, array( 'id' => (int)$_POST['id']));
  }

  if(isset($_POST['submit'])) {
    if(isset($_POST['id']) && (int)$_POST['id']>0) { // edit
      $wpdb->update($table_name,
        array(
     		'name' => $_POST['name'],
     		'open' => $_POST['open'],
        'close' => $_POST['close'],
        'days' => $_POST['days'],
        'history' => $_POST['history'],
        'allowedtypes' => $_POST['allowedtypes']
       	),
        array( 'id' => (int)$_POST['id'] ),
       	array(
       		'%s',
       		'%d',
           '%d',
           '%d',
           '%d',
           '%s'
       	));
      $courtID = (int)$_POST['id'];
      $message = __('Successfully changed!','resme');
    } else { // create
      $wpdb->insert($table_name,
	     array(
    		'name' => $_POST['name'],
    		'open' => $_POST['open'],
        'close' => $_POST['close'],
        'days' => $_POST['days'],
        'history' => $_POST['history'],
        'allowedtypes' => $_POST['allowedtypes']
    	),
    	array(
    		'%s',
    		'%d',
        '%d',
        '%d',
        '%d',
        '%s'
    	));
      $message = __('Successfully created!','resme');
      $courtID = $wpdb->insert_id;
    }
  }


  if(isset($courtID) && $courtID>0) {
    $court = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $courtID" );
  }

  if(!isset($court)) {
    $court = new stdClass();
    $court->id = 0;
    $court->name = '';
    $court->open = 8;
    $court->close = 22;
    $court->days = 60;
    $court->history = 30;
  }
?>

<div class="wrap">
  <a class="page-title-action" href="<?= admin_url("admin.php?page=resme-facilities") ?>"><?= __('Back','resme');?></a>
  <h1 class="wp-heading-inline"><?= (isset($court) && $court->id>0) ? $court->name.__(' edit','resme') : __('Create Facility','resme') ?></h1>
  <hr class="wp-header-end">
  <?php if (isset($message)) { ?>
    <div id="message" class="updated notice is-dismissible">
        <p><?= $message ?></p>
        <button type="button" class="notice-dismiss"></button>
    </div>
      <?php 
    } ?>
  <form method="post">
    <input type="hidden" name="id" value="<?= $court->id ?>" />
    <table>
      <tr>
        <td><?= __('Name','resme');?></td>
        <td><input type="text" name="name" maxlength="255" value="<?= $court->name ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Opens (hour)','resme');?></td>
        <td><input type="number" name="open" min="0" max="23" maxlength="2" value="<?= $court->open ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Closes (hour)','resme');?></td>
        <td><input type="number" name="close" min="0" max="23" maxlength="2" value="<?= $court->close ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Days','resme');?></td>
        <td><input type="number" name="days" min="0" max="365" maxlength="3" value="<?= $court->days ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Days History','resme');?></td>
        <td><input type="number" name="history" min="0" max="365" maxlength="3" value="<?= $court->history ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Allowed Types','resme');?></td>
        <td><input type="text" name="allowedtypes" maxlength="520" value="<?= $court->allowedtypes ?>" /></td>
      </tr>
      <tr>
        <td></td>
        <td><input class="button" type="submit" name="submit" /></td>
      </tr>
      <?php if(isset($court) && $court->id>0) { ?>
        <tr>
          <td colspan="2"><hr/></td>
        </tr>
        <tr>
          <td><?= __('Delete Facility','resme');?></td>
          <td><input class="button" type="submit" name="delete" value=<?= __('Delete','resme');?> /></td>
        </tr>
      <?php } ?>
  </form>
</div>
</div>
</div>
