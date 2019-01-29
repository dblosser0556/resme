<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Resme
 * @subpackage Resme/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php

if (!current_user_can('manage_options')) {
    wp_die();
}

global $wpdb;
$table_name = $this->getTable('roles');

if (isset($_GET['roleID'])) {
    $roleID = (int)$_GET['roleID'];
}



if (isset($_POST['delete']) && isset($_POST['id']) && (int)$_POST['id'] > 0) { // delete
    $wpdb->delete($table_name, array('id' => (int)$_POST['id']));
    remove_role( $_POST['slug']);
}

if (isset($_POST['submit'])) {
    if (isset($_POST['id']) && (int)$_POST['id'] > 0) { // edit
        $wpdb->update(
            $table_name,
            array(
                'name' => $_POST['name'],
                'slug' => $_POST['slug'],
                'maxdays' => $_POST['maxdays'],
                'maxres' => $_POST['maxres'],
                'standardrole' => $_POST['standardrole'],
            ),
            array('id' => (int)$_POST['id']),
            array(
                '%s',
                '%s',
                '%d',
                '%d',
                '%s'
            )
        );
        $roleID = (int)$_POST['id'];
        $message = __('Successfully changed!', 'resme');
    } else { // create
        $wpdb->insert(
            $table_name,
            array(
                'name' => $_POST['name'],   //Display name
                'slug' => $_POST['slug'],   //Role Name
                'maxdays' => $_POST['maxdays'],
                'maxres' => $_POST['maxres'],
                'standardrole' => $_POST['standardrole'],
            ),
            array(
                '%s',
                '%s',
                '%d',
                '%d',
                '%s'
            )
        );

        $message = __('Successfully created!', 'resme');
        $roleID = $wpdb->insert_id;
    }
    
    // update the capabilities of the role
    // rather than compare before and after remvove the current role
    // use the current standard Wordpress role to add the capabilities
    remove_role($_POST['slug']);

    $srole = get_role($_POST['standardrole']);
    if ($_POST['standardrole'] !== 'none') {
        $caps = $srole->capabilities;
        $caps['place_reservation'] = true;
    } else 
        $caps = array('place_reservation' => true);
        
    
    
    

    PC::debug($caps);

    add_role($_POST['slug'], $_POST['name'], $caps);


}

if (isset($roleID) && $roleID > 0) {
    $role = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $roleID");
}


if (!isset($role)) {
    $role = new stdClass();
    $role->id = 0;
    $role->name = '';
    $role->slug = '';
    $role->maxdays = 5;
    $role->maxres = 5;
    $role->standardrole = 'none';
}
?>
<?php
  
?>


<div class="wrap">
  <a class="page-title-action" href="<?= admin_url("admin.php?page=resme-roles") ?>"><?= __('Back', 'resme'); ?></a>
  <h1 class="wp-heading-inline"><?= (isset($role) && $role->id > 0) ? $role->name . __(' edit', 'resme') : __('Create Role', 'resme') ?></h1>
  <hr class="wp-header-end">
  <?php if (isset($message)) { ?>
    <div id="message" class="updated notice is-dismissible">
        <p><?= $message ?></p>
        <button type="button" class="notice-dismiss"></button>
    </div>
      <?php 
    } ?>
  <form method="post">
    <input type="hidden" name="id" value="<?= $role->id ?>" />
    <table>
      <tr>
        <td><?= __('Name', 'resme'); ?></td>
        <td><input type="text" name="name" maxlength="255" value="<?= $role->name ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Short Name', 'resme'); ?></td>
        <td><input type="text" name="slug" maxlength="255" value="<?= $role->slug ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Maximum Days', 'resme'); ?></td>
        <td><input type="number" name="maxdays" min="0" max="365" maxlength="3" value="<?= $role->maxres ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Maximum Reservations', 'resme'); ?></td>
        <td><input type="number" name="maxres" min="0"  value="<?= $role->maxres ?>" required /></td>
      </tr>
      <tr>
        <td><?= __('Standard Role', 'resme'); ?></td>
        <td> <select name="standardrole" value="<?= $role->standardrole ?>">
                <option value="none" <?= $role->standardrole == '' ? 'selected="selected"' : ''; ?> ><?= __('None', 'resme'); ?></option>
				<option value="subscriber" <?= $role->standardrole == 'subscriber' ? 'selected="selected"' : ''; ?> > <?= __('Subscriber', 'resme'); ?></option>
                <option value="contributor" <?= $role->standardrole == 'contributor' ? 'selected="selected"' : ''; ?> ><?= __('Contributor', 'resme'); ?></option>	
                <option value="author" <?= $role->standardrole == 'author' ? 'selected="selected"' : ''; ?> ><?= __('Author', 'resme'); ?></option>
                <option value="editor" <?= $role->standardrole == 'editor' ? 'selected="selected"' : ''; ?> ><?= __('Editor', 'resme'); ?></option>
                
			</select>
        </td>    
      </tr>
      <tr>
        <td></td>
        <td><input class="button" type="submit" name="submit" /></td>
      </tr>
      <?php if (isset($role) && $role->id > 0) { ?>
        <tr>
          <td colspan="2"><hr/></td>
        </tr>
        <tr>
          <td><?= __('Delete Role', 'resme'); ?></td>
          <td><input class="button" type="submit" name="delete" value=<?= __('Delete', 'resme'); ?> /></td>
        </tr>
      <?php 
    } ?>
  </form>
</div>

