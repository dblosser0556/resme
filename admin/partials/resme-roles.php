<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://webmuehle.at
 * @since      1.0.0
 *
 * @package    resme
 * @subpackage resme/admin/partials
 */
?>

<?php
  if (!current_user_can('manage_options')) {
      wp_die();
  }

  global $wpdb;
  $table_name = $this->getTable('roles');
  $roles = $wpdb->get_results("SELECT * FROM $table_name ORDER BY name");

  ?>

<div class="wrap">
  <h1 class="wp-heading-inline"><?= __('Manage Roles', 'resme');?></h1>
  <a class="page-title-action" href="<?= admin_url("admin.php?page=resme-role") ?>"><?= __('Create', 'resme');?></a>
  <hr class="wp-header-end">

  <table class="wp-list-table widefat fixed striped posts">
    <thead>
      <tr>
        <th class="manage-column column-title column-primary"><?= __('Name', 'resme');?></th>
        <th class="manage-column column-title column-primary"><?= __('Maximum Days Out', 'resme');?></th>
        <th class="manage-column column-title column-primary"><?= __('Maximum Reservations in Period', 'resme');?></th>
        <th class="manage-column column-title column-primary"><?= __('Wordpress Role', 'resme');?></th>
        <th class="manage-column column-title"><?= __('Action', 'resme');?></th>
      </tr>
    </thead>
    <tbody>
      <?php for($i=0;$i<sizeof($roles);$i++) { $item = $roles[$i]; ?>
        <tr>
          <td><?= $item->name ?></td>
          <td><?= $item->maxdays ?></td>
          <td><?= $item->maxres ?></td>
          <td><?= $item->standardrole ?></td>
          <td><a class="page-action" href="<?= admin_url("admin.php?page=resme-role&roleID={$item->id}") ?>"><?= __('Edit', 'resme');?></a>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <p></p>
</div>
