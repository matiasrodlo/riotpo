<div class="userpro userpro-<?php echo $i; ?> userpro-<?php echo $layout; ?>" <?php userpro_args_to_data( $args ); ?>>

	<a href="#" class="userpro-close-popup"><?php _e('Close','userpro'); ?></a>
	
	<div class="userpro-head">
		<div class="userpro-left"><?php echo $args["{$template}_heading"]; ?></div>
		<?php if (isset($args["{$template}_side"])) { ?>
		<div class="userpro-right"><a href="#" data-template="<?php echo $args["{$template}_side_action"]; ?>"><?php echo $args["{$template}_side"]; ?></a></div>
		<?php } ?>
		<div class="userpro-clear"></div>
	</div>
	
	<div class="userpro-body">
	
		<?php do_action('userpro_pre_form_message'); ?>

		<form action="" method="post" data-action="<?php echo $template; ?>">
		
			<input type="hidden" name="user_id-<?php echo $i; ?>" id="user_id-<?php echo $i; ?>" value="<?php echo $user_id; ?>" />
			
			<?php // Hook into fields $args, $user_id
			if (!isset($user_id)) $user_id = 0;
			$hook_args = array_merge($args, array('user_id' => $user_id, 'unique_id' => $i));
			do_action('userpro_before_fields', $hook_args);
			?>
			
			<?php echo userpro_edit_field_misc( $i, 'post_title', $args, null, null, __('Enter post title here...','userpro') ); ?>
			
			<?php echo userpro_post_editor( $i, 'userpro_editor', $args ); ?>
			
			<?php echo userpro_edit_field_misc( $i, 'post_featured_image', $args ); ?>
			
			<?php if ( count(userpro_publish_types($args)) > 1 ) { echo userpro_edit_field_misc( $i, 'post_type', $args, __('Post Type','userpro') ); } ?>
			
			<?php
			if (isset($args['taxonomy']) && isset($args['category'])){
			?>
				<input type="hidden" name="taxonomy-<?php echo $i; ?>" id="taxonomy-<?php echo $i; ?>" value="<?php echo $args['taxonomy']; ?>" />
				<input type="hidden" name="category-<?php echo $i; ?>" id="category-<?php echo $i; ?>" value="<?php echo $args['category']; ?>" />
			<?php
			}else {
				echo userpro_edit_field_misc( $i, 'post_categories', $args, null, null, __('Select Categories','userpro') );
			}
			?>
			
			<?php if (isset($args['post_meta']) && isset($args['post_meta_labels']) ) {
				$post_meta = explode(',',$args['post_meta']);
				$post_meta = array_combine( $post_meta, explode(',', $args['post_meta_labels']) );
				foreach($post_meta as $meta_key => $meta_label) {
				
					echo userpro_edit_field_misc( $i, $meta_key, $args, $meta_label );
				
				}
			} ?>

			<?php // Hook into fields $args, $user_id
			if (!isset($user_id)) $user_id = 0;
			$hook_args = array_merge($args, array('user_id' => $user_id, 'unique_id' => $i));
			do_action('userpro_after_fields', $hook_args);
			?>
			
			<?php // Hook into fields $args, $user_id
			if (!isset($user_id)) $user_id = 0;
			$hook_args = array_merge($args, array('user_id' => $user_id, 'unique_id' => $i));
			do_action('userpro_before_form_submit', $hook_args);
			?>
			
			<?php if ( isset( $args["{$template}_button_primary"] ) || isset( $args["{$template}_button_secondary"] ) ) { ?>
			<div class="userpro-field userpro-submit userpro-column">
				
				<?php if (isset($args["{$template}_button_primary"]) ) { ?>
				<input type="submit" value="<?php echo $args["{$template}_button_primary"]; ?>" class="userpro-button" />
				<?php } ?>

				<img src="<?php echo $userpro->skin_url(); ?>loading.gif" alt="" class="userpro-loading" />
				<div class="userpro-clear"></div>
				
			</div>
			<?php } ?>
		
		</form>
	
	</div>

</div>