<?php
	

function swr_admin_menu() {
	add_submenu_page( 'users.php', __("Rewards", 'rewards'), __("Rewards", 'rewards'), 'manage_options', 'rewards-details', 'swr_user_rewards_details' );
}
add_action('admin_menu', 'swr_admin_menu');

function swr_admin_users_scripts(){
	global $woocommerce;
	if($_GET['page'] == 'rewards-details'){
		wp_enqueue_script( 'chosen' );
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
	}
}
add_action('admin_enqueue_scripts', 'swr_admin_users_scripts');

function swr_user_rewards_details(){
	$blogusers = get_users();
?>
<script type="text/javascript">
	jQuery(function(){
		jQuery("select.chosen_select").chosen();

		jQuery("select.chosen_select_nostd").chosen({
			allow_single_deselect: 'true'
		});
	});
</script>
<div class="wrap">
	<div id="icon-users" class="icon32"><br></div>
	<h2><?= __("Rewards", 'rewards') ?> <a href="user-new.php" class="add-new-h2">Ajouter</a></h2>

<!--
	<ul class="subsubsub">
		<li class="all"><a href="users.php" class="current">Tous <span class="count">(3)</span></a> |</li>
		<li class="administrator"><a href="users.php?role=administrator">Administrateur <span class="count">(1)</span></a> |</li>
		<li class="customer"><a href="users.php?role=customer">Client <span class="count">(2)</span></a></li>
	</ul>
-->
	
	<form action="" method="get">
		
		<div class="tablenav top">

			<div class="alignleft actions">
				<select name="user_id" class="chosen_select">
					<option value="0"<?= !isset($_GET['user_id'])||$_GET['user_id']==0?' selected="selected"':'' ?>><?php _e("Show all customers", 'rewards'); ?></option>
					<?php if(count($blogusers) > 0){ ?>
					<?php foreach($blogusers as $user){
							$first_name = get_user_meta($user->ID, 'first_name', true);
							$last_name = get_user_meta($user->ID, 'last_name', true);
					?>
					<option value="<?= $user->ID ?>"<?= $_GET['user_id']==$user->ID?' selected="selected"':'' ?>><?= $user->user_nicename.(!empty($first_name)||!empty($last_name)?' '.(!empty($first_name)?$first_name:'').' '.(!empty($last_name)?$last_name:''):'') ?></option>
					<?php } ?>
					<?php } ?>
				</select>
				<select name="type_id" class="chosen_select">
					<option value="0"<?= !isset($_GET['type_id'])||$_GET['type_id']==0?' selected="selected"':'' ?>><?php _e("Show all types", 'rewards'); ?></option>
					<option value="1"<?= $_GET['type']==1?' selected="selected"':'' ?>><?php _e("Orders", 'rewards'); ?></option>
					<option value="2"<?= $_GET['type']==2?' selected="selected"':'' ?>><?php _e("Comments", 'rewards'); ?></option>
				</select>
				<input type="submit" name="" id="post-query-submit" class="button-secondary" value="Filtrer">
			</div>
			<div class="tablenav-pages one-page">
				<span class="displaying-num">3 éléments</span>
				<span class="pagination-links">
					<a class="first-page disabled" title="Aller à la première page" href="http://rewards.synmedia.ca/wp-admin/users.php">«</a>
					<a class="prev-page disabled" title="Aller à la page précédente" href="http://rewards.synmedia.ca/wp-admin/users.php?paged=1">‹</a>
					<span class="paging-input"><input class="current-page" title="Page actuelle" type="text" name="paged" value="1" size="1"> sur <span class="total-pages">1</span></span>
					<a class="next-page disabled" title="Aller à la page suivante" href="http://rewards.synmedia.ca/wp-admin/users.php?paged=1">›</a>
					<a class="last-page disabled" title="Aller à la dernière page" href="http://rewards.synmedia.ca/wp-admin/users.php?paged=1">»</a>
				</span>
			</div>
			<br class="clear">
		</div>
		
		<table class="wp-list-table widefat fixed pages" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="title" class="manage-column column-author sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=title&amp;order=asc"><span><?php _e("Order", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="author" class="manage-column column-author sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=author&amp;order=asc"><span><?php _e("Customer", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Earned", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Used", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Status", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Type", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="date" class="manage-column column-comments num sortable asc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=date&amp;order=desc"><span><?php _e("Date", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="col" id="title" class="manage-column column-author sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=title&amp;order=asc"><span><?php _e("Order", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="author" class="manage-column column-author sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=author&amp;order=asc"><span><?php _e("Customer", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Earned", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Used", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Status", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="comments" class="manage-column column-comments num sortable desc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=comment_count&amp;order=asc"><span><?php _e("Type", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
					<th scope="col" id="date" class="manage-column column-comments num sortable asc" style=""><a href="http://rewards.synmedia.ca/wp-admin/edit.php?post_type=page&amp;orderby=date&amp;order=desc"><span><?php _e("Date", 'rewards'); ?></span><span class="sorting-indicator"></span></a></th>
				</tr>
			</tfoot>

			<tbody id="the-list">
				<tr id="post-4" class="post-4 page type-page status-publish hentry alternate iedit author-self" valign="top">
					<td class="post-title page-title column-title">
						<strong><a class="row-title" href="http://rewards.synmedia.ca/wp-admin/post.php?post=4&amp;action=edit" title="Modifier avec «&nbsp;Boutique&nbsp;»">#57</a></strong>
						<div class="row-actions">
							<span class="edit"><a href="http://rewards.synmedia.ca/wp-admin/post.php?post=4&amp;action=edit" title="Modifier cette entrée">Modifier</a> | </span>
							<span class="view"><a href="http://rewards.synmedia.ca/?page_id=4" title="Afficher «&nbsp;Boutique&nbsp;»" rel="permalink">Afficher</a></span>
						</div>
					</td>
					<td class="author column-author"><a href="edit.php?post_type=page&amp;author=1">synmedia</a></td>
					<td class="comments column-comments">4</td>
					<td class="comments column-comments">0</td>
					<td class="comments column-comments">Completed</td>
					<td class="comments column-comments">Order</td>
					<td class="comments column-comments">26/07/2012</td>
				</tr>
			</tbody>
</table>
	<div class="tablenav bottom">

		<div class="alignleft actions">
			<select name="action2">
<option value="-1" selected="selected">Actions groupées</option>
	<option value="delete">Supprimer </option>
</select>
<input type="submit" name="" id="doaction2" class="button-secondary action" value="Appliquer">
		</div>
<div class="tablenav-pages one-page"><span class="displaying-num">3 éléments</span>
<span class="pagination-links"><a class="first-page disabled" title="Aller à la première page" href="http://rewards.synmedia.ca/wp-admin/users.php">«</a>
<a class="prev-page disabled" title="Aller à la page précédente" href="http://rewards.synmedia.ca/wp-admin/users.php?paged=1">‹</a>
<span class="paging-input">1 sur <span class="total-pages">1</span></span>
<a class="next-page disabled" title="Aller à la page suivante" href="http://rewards.synmedia.ca/wp-admin/users.php?paged=1">›</a>
<a class="last-page disabled" title="Aller à la dernière page" href="http://rewards.synmedia.ca/wp-admin/users.php?paged=1">»</a></span></div>
		<br class="clear">
	</div>
</form>

<br class="clear">
</div>
<?php
}
	
?>