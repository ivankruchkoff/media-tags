<?php
require_once(ABSPATH . 'wp-includes/pluggable.php');

function mediatags_admin_panel() {
	media_tags_register_columns();

	//$can_manage = current_user_can('manage_media_tags');	
	if ( ! current_user_can( 'manage_categories' ) )
		return;
	
	
	$messages[1] = __('Media Tag added.');
	$messages[2] = __('Media Tag deleted.');
	$messages[3] = __('Media Tag updated.');
	$messages[4] = __('Media Tag not added.');
	$messages[5] = __('Media Tag not updated.');
	$messages[6] = __('Media Tags deleted.');
	
	$title = __('Media Tags');
	
	
	//echo "_REQUEST<pre>"; print_r($_REQUEST); echo "</pre>";
	
	?>
	<div class="wrap nosubsub">
		<?php screen_icon(); ?>
		<h2><?php echo $title; 
		if ( isset($_GET['s']) && $_GET['s'] )
			printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', wp_specialchars( stripslashes($_GET['s']) ) );
		?></h2>
		<?php if ( isset($_GET['message']) && ( $msg = (int) $_GET['message'] ) ) : ?>
		<div id="message" class="updated fade"><p><?php echo $messages[$msg]; ?></p></div>
		<?php $_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
		endif; ?>

		<?php
			if ((isset($_GET['action'])) && ($_GET['action'] == 'editmediatag'))
			{
				$mediatag_ID = (int) $_GET['mediatag_ID'];
				mediatags_process_edit($mediatag_ID);
			}
			else
			{
				?>
				<form class="search-form" method="get"
					action="<?php echo get_option('siteurl') ?>/wp-admin/upload.php">
				<p class="search-box">
					<input type="hidden" name="page" value="media-tags/meta_tags.php" />
					<input type="hidden" name="action" value="searchmediatag" />
					<label class="hidden" for="media-tags-search-input"><?php _e( 'Search Media Tags' ); ?>:</label>
					<input type="text" class="search-input" id="media-tags-search-input" name="s" value="<?php _admin_search_query(); ?>" />
					<input type="submit" value="<?php _e( 'Search Media Tags' ); ?>" class="button" />
				</p>
				</form>
				<br class="clear" />
		
				<div id="col-container">
					<div id="col-right">
						<div class="col-wrap">
							<form id="posts-filter" method="get"
								action="<?php echo get_option('siteurl') ?>/wp-admin/upload.php">
								<div class="tablenav">
									<?php
										$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 0;
										if ( empty($pagenum) )	$pagenum = 1;
								
										$tagsperpage = apply_filters("tagsperpage",20);

										$page_links = paginate_links( array(
											'base' => add_query_arg( 'pagenum', '%#%' ),
											'format' => '',
												'prev_text' => __('&laquo;'),
												'next_text' => __('&raquo;'),
												'total' => ceil(wp_count_terms(MEDIA_TAGS_TAXONOMY) / $tagsperpage),
												'current' => $pagenum
										));

										if ( $page_links )
											echo "<div class='tablenav-pages'>$page_links</div>";
									?>

									<div class="alignleft actions">
										<select name="action">
											<option value="" selected="selected"><?php _e('Bulk Actions'); ?></option>
											<option value="deletemediatagsbulk"><?php _e('Delete'); ?></option>
										</select>
										<input type="hidden" name="page" value="media-tags/meta_tags.php" />
										<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" 
											id="doaction" class="button-secondary action" />
										<?php wp_nonce_field('media-tags-bulk'); ?>
									</div>

									<br class="clear" />
								</div>

								<div class="clear"></div>

							<table class="widefat tag fixed" cellspacing="0">
							<thead>
								<tr><?php print_column_headers('edit-media-tags'); ?></tr>
							</thead>

							<tfoot>
							<tr><?php print_column_headers('edit-media-tags', false); ?></tr>
							</tfoot>

							<tbody id="the-list" class="list:tag">
							<?php
								$searchterms = isset( $_GET['s'] ) ? trim( $_GET['s'] ) : '';
								$count = media_tags_display_rows( MEDIA_TAGS_TAXONOMY, $pagenum, $tagsperpage, $searchterms );
							?>
							</tbody>
							</table>

							<div class="tablenav">
							<?php
								if ( $page_links )
									echo "<div class='tablenav-pages'>$page_links</div>"; ?>

								<div class="alignleft actions">
									<select name="action2">
										<option value="" selected="selected"><?php _e('Bulk Actions'); ?></option>
										<option value="deletemediatagsbulk"><?php _e('Delete'); ?></option>
									</select>
									<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2"
									 class="button-secondary action" />
								</div>

								<br class="clear" />
							</div>

							<br class="clear" />
						</form>
				</div>
			</div><!-- /col-right -->

			<div id="col-left">
				<div class="col-wrap">
					<?php 
						//if ( $can_manage ) 
						{
							do_action('add_tag_form_pre'); ?>

							<div class="form-wrap">
								<h3><?php _e('Add a New Media Tag'); ?></h3>
								<div id="ajax-response"></div>

								<form name="addmediatag" id="addmediatag" method="post" class="add:the-list: validate"
									action="<?php echo get_option('siteurl') ?>/wp-admin/upload.php?page=media-tags/meta_tags.php">
									<input type="hidden" name="action" value="addmediatag" />

									<div class="form-field form-required">
										<label for="name"><?php _e('Media Tag name') ?></label>
										<input name="name" id="name" type="text" value="" size="40" aria-required="true" />
									    <p><?php _e('The name is how the media tag appears on your site.'); ?></p>
									</div>

									<div class="form-field">
										<label for="slug"><?php _e('Media Tag slug') ?></label>
										<input name="slug" id="slug" type="text" value="" size="40" />
									    <p><?php _e('The &#8220;slug&#8221; is the URL-friendly version of the name. 
											It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?></p>
									</div>

									<p class="submit"><input type="submit" class="button" name="submit" 
											value="<?php _e('Add Media Tag'); ?>" /></p>
									<?php //do_action('add_tag_form'); ?>
								</form>
							</div>
							<?php 
						} 
					?>

				</div>
			</div><!-- /col-left -->
		</div><!-- /col-container -->
		<?php } ?>
	</div><!-- /wrap -->
	<?php inline_edit_mediatags_row('edit-media-tags'); ?>
	<?php
}

function media_tags_register_columns() {
	if (!function_exists('register_column_headers'))
		require_once(ABSPATH . 'wp-admin/includes/template.php');

	$media_tags_edit_columns = array('cb' => '<input type="checkbox" />',
														'name' => __('Name'),
														'slug' => __('Slug'),
														'posts' => __('Used'));

	// Register columns for Edit Media Tags listing
	register_column_headers('edit-media-tags', $media_tags_edit_columns );
}


function media_tags_display_rows( $taxonomy = '', $page = 1, $pagesize = 20, $searchterms = '' ) {

	// Get a page worth of tags
	$start = ($page - 1) * $pagesize;

	$args = array('offset' => $start, 'number' => $pagesize, 'hide_empty' => 0);

	if ( !empty( $searchterms ) ) {
		$args['search'] = $searchterms;
	}

	$tags = get_terms($taxonomy, $args );
	//echo "tags<pre>"; print_r($tags); echo "</pre>";

	// convert it to table rows
	$out = '';
	$count = 0;
	foreach( $tags as $tag )
		$out .= _media_tag_row( $tag, ++$count % 2 ? ' class="iedit alternate"' : ' class="iedit"' );

	// filter and send to screen
	echo $out;
	return $count;
}

function _media_tag_row( $tag, $class = '' ) {
	$base_url = get_option('siteurl')."/wp-admin/upload.php?page=media-tags/meta_tags.php";
	
	$count = number_format_i18n( $tag->count );
	$count = ( $count > 0 ) ? "<a href='".
		get_option('siteurl')."/wp-admin/upload.php?mediatag_id=$tag->term_id'>$count</a>" : $count;

	$name = apply_filters( 'term_name', $tag->name );
	$qe_data = get_term($tag->term_id, MEDIA_TAGS_TAXONOMY, object, 'edit');
	$edit_link = $base_url ."&action=editmediatag&amp;mediatag_ID=$tag->term_id";
		
		$out = '';
		$out .= '<tr id="tag-' . $tag->term_id . '"' . $class . '>';
		$columns = get_column_headers('edit-media-tags');
		$hidden = get_hidden_columns('edit-media-tags');
		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array($column_name, $hidden) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ($column_name) {
				case 'cb':
					$out .= '<th scope="row" class="check-column"> <input type="checkbox" name="delete_media_tags[]" value="' . $tag->term_id . '" /></th>';
					break;
				case 'name':
					$out .= '<td ' . $attributes . '><strong><a class="row-title" href="' . $edit_link . '" title="' . attribute_escape(sprintf(__('Edit "%s"'), $name)) . '">' . $name . '</a></strong><br />';
					$actions = array();
					$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
					$actions['inline hide-if-no-js'] = '<a href="#" class="editinline-mediatag">' . __('Quick&nbsp;Edit') . '</a>';
					$actions['delete'] = "<a class='submitdelete' href='" .
					 wp_nonce_url(get_option('siteurl')
					 ."/wp-admin/upload.php?page=media-tags/meta_tags.php&amp;action=deletemediatag&amp;mediatag_ID=$tag->term_id", 
					'delete-tag_' . $tag->term_id) . "' onclick=\"if ( confirm('" . js_escape(sprintf(__("You are about to delete this media tag '%s'\n 'Cancel' to stop, 'OK' to delete."), $name )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
					$action_count = count($actions);
					$i = 0;
					$out .= '<div class="row-actions">';
					foreach ( $actions as $action => $link ) {
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						$out .= "<span class='$action'>$link$sep</span>";
					}
					$out .= '</div>';
					$out .= '<div class="hidden" id="inline_' . $qe_data->term_id . '">';
					$out .= '<div class="name">' . $qe_data->name . '</div>';
					$out .= '<div class="slug">' . $qe_data->slug . '</div></div></td>';
					break;
				case 'slug':
					$out .= "<td $attributes>$tag->slug</td>";
					break;
				case 'posts':
					$attributes = 'class="posts column-posts num"' . $style;
					$out .= "<td $attributes>$count</td>";
					break;
			}
		}

		$out .= '</tr>';

		return $out;
}


function inline_edit_mediatags_row($type) {

//	if ( ! current_user_can( 'manage_categories' ) )
//		return;

	$is_tag = $type == 'edit-media-tags';
	$columns = get_column_headers($type);
	$hidden = array_intersect( array_keys( $columns ), array_filter( get_hidden_columns($type) ) );
	$col_count = count($columns) - count($hidden);
	?>

<form method="get" action=""><table style="display: none"><tbody id="inlineedit">
	<tr id="inline-edit-mediatag" class="inline-edit-row" style="display: none"><td colspan="<?php echo $col_count; ?>">

		<fieldset><div class="inline-edit-col">
			<h4><?php _e( 'Quick Edit' ); ?></h4>

			<label>
				<span class="title"><?php _e( 'Name' ); ?></span>
				<span class="input-text-wrap"><input type="text" name="name" class="ptitle" value="" /></span>
			</label>

			<label>
				<span class="title"><?php _e( 'Slug' ); ?></span>
				<span class="input-text-wrap"><input type="text" name="slug" class="ptitle" value="" /></span>
			</label>

<?php if ( 'category' == $type ) : ?>

			<label>
				<span class="title"><?php _e( 'Parent' ); ?></span>
				<?php wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('None'))); ?>
			</label>

<?php endif; // $type ?>

		</div></fieldset>

<?php

	$core_columns = array( 'cb' => true, 'description' => true, 'name' => true, 'slug' => true, 'posts' => true );

	foreach ( $columns as $column_name => $column_display_name ) {
		if ( isset( $core_columns[$column_name] ) )
			continue;
		do_action( 'quick_edit_custom_box', $column_name, $type );
	}

?>

	<p class="inline-edit-save submit">
		<a accesskey="c" href="#inline-edit-mediatag" title="<?php _e('Cancel'); ?>" class="cancel button-secondary alignleft"><?php _e('Cancel'); ?></a>
		<?php $update_text = ( $is_tag ) ? __( 'Update Tag' ) : __( 'Update Category' ); ?>
		<a accesskey="s" href="#inline-edit-mediatag" title="<?php echo attribute_escape( $update_text ); ?>" class="save button-primary alignright"><?php echo $update_text; ?></a>
		<img class="waiting" style="display:none;" src="images/loading.gif" alt="" />
		<span class="error" style="display:none;"></span>
		<?php wp_nonce_field( 'taxinlineeditnonce', '_inline_edit', false ); ?>
		<br class="clear" />
	</p>
	</td></tr>
	</tbody></table></form>
<?php
}


function mediatags_process_actions()
{
	if (!isset($_REQUEST['action']))
		return;

	if (strlen($_REQUEST['action']) == 0)
	{
		if ((isset($_REQUEST['action2'])) && (strlen($_REQUEST['action2']) > 0))
			$_REQUEST['action'] = $_REQUEST['action2'];
		else
			return;
	}
	//echo "_REQUEST<pre>"; print_r($_REQUEST); echo "</pre>";
	
	switch($_REQUEST['action'])
	{
		case 'inline-save-mediatag':
			mediatags_process_inline_save();
			exit;
			break;
			
		case 'updatemediatag':
			mediatags_process_update();
			break;
			
		case 'deletemediatag':
			mediatags_process_delete();
			break;

		case 'deletemediatagsbulk':
			mediatags_process_delete_bulk();
			break;
			
		case 'addmediatag':
			mediatags_process_add();
			break;

		default:
			break;
	}
}

function mediatags_process_add()
{	
	if (!isset($_REQUEST['name']))
		return;

	$media_tag_name = trim($_REQUEST['name']);

	if ((isset($_REQUEST['slug'])) && (strlen($_REQUEST['slug'])))
		$media_tag_slug = trim($_REQUEST['slug']);
	else
		$media_tag_slug = trim($_REQUEST['name']);
		
	//$media_tag_slug = sanitize_title($media_tag_slug);
	$media_tag_slug = sanitize_title_with_dashes($media_tag_slug);
	
	if ( '' === $media_tag_slug )
		return;

	if ( !is_term( $media_tag_name, MEDIA_TAGS_TAXONOMY ) ) 
	{
		$ret = wp_insert_term( $media_tag_name, MEDIA_TAGS_TAXONOMY, array('slug' => $media_tag_slug));
		if ( $ret && !is_wp_error( $ret ) ) {
			wp_redirect(get_option('siteurl') ."/wp-admin/upload.php?page=media-tags/meta_tags.php&message=1");
		} else {
			wp_redirect(get_option('siteurl') ."/wp-admin/upload.php?page=media-tags/meta_tags.php&message=4");
		}
		exit;
	}
}

function mediatags_process_delete()
{
	if (!isset($_REQUEST['mediatag_ID']))
		return;
		
	$mediatag_ID = intval($_REQUEST['mediatag_ID']);
	wp_delete_term( $mediatag_ID, MEDIA_TAGS_TAXONOMY);
	
	wp_redirect(get_option('siteurl') ."/wp-admin/upload.php?page=media-tags/meta_tags.php&message=2");
	exit;	
}

function mediatags_process_delete_bulk()
{
	if ((isset($_REQUEST['delete_media_tags'])) && (is_array($_REQUEST['delete_media_tags'])))
	{
		foreach($_REQUEST['delete_media_tags'] as $delete_media_tag)
		{
			$mediatag_ID = intval($delete_media_tag);
			wp_delete_term( $mediatag_ID, MEDIA_TAGS_TAXONOMY);
		}
		wp_redirect(get_option('siteurl') ."/wp-admin/upload.php?page=media-tags/meta_tags.php&message=6");
	}	
	else
		wp_redirect(get_option('siteurl') ."/wp-admin/upload.php?page=media-tags/meta_tags.php");
	exit;	
}

function mediatags_process_update()
{
	if (!isset($_REQUEST['mediatag_ID']))
		return;


	//echo "_REQUEST<pre>"; print_r($_REQUEST); echo "</pre>";

	$mediatag_ID = intval($_REQUEST['mediatag_ID']);

	$media_tag_name = trim($_REQUEST['name']);

	if ((isset($_REQUEST['slug'])) && (strlen($_REQUEST['slug'])))
		$media_tag_slug = trim($_REQUEST['slug']);
	else
		$media_tag_slug = trim($_REQUEST['name']);
		
	$media_tag_slug = sanitize_title_with_dashes($media_tag_slug);


	//echo "media_tag_name=[".$media_tag_name."]<br />";
	//echo "media_tag_slug=[".$media_tag_slug."]<br />";
	
	if ( '' === $media_tag_slug )
		return;

	$ret = wp_update_term($mediatag_ID, MEDIA_TAGS_TAXONOMY, array('slug' => $media_tag_slug, 'name' => $media_tag_name));
	if ( $ret && !is_wp_error( $ret ) ) {
		wp_redirect(get_option('siteurl') ."/wp-admin/upload.php?page=media-tags/meta_tags.php&message=3");
	} else {
		wp_redirect(get_option('siteurl') ."/wp-admin/upload.php?page=media-tags/meta_tags.php&message=5");
	}
	exit;
}

function mediatags_process_edit($mediatag_ID)
{
	if ( empty($mediatag_ID) ) { ?>
		<div id="message" class="updated fade"><p><strong><?php _e('A tag was not selected for editing.'); ?></strong></p></div>
	<?php
		return;
	}
	$tag = get_term($mediatag_ID, MEDIA_TAGS_TAXONOMY, OBJECT, 'edit');			

	do_action('edit_tag_form_pre', $tag); ?>

	<div class="wrap">
	<?php //screen_icon(); ?>
	<h2><?php _e('Edit Media Tag'); ?></h2>
	<div id="ajax-response"></div>
	<form name="edittag" id="edittag" method="post" class="validate"
			action="<?php echo get_option('siteurl') ?>/wp-admin/upload.php?page=media-tags/meta_tags.php">
		<input type="hidden" name="action" value="updatemediatag" />
		<input type="hidden" name="mediatag_ID" value="<?php echo $tag->term_id ?>" />
	<?php wp_original_referer_field(true, 'previous'); wp_nonce_field('update-tag_' . $mediatag_ID); ?>
		<table class="form-table">
			<tr class="form-field form-required">
				<th scope="row" valign="top"><label for="name"><?php _e('Tag name') ?></label></th>
				<td><input name="name" id="name" type="text" value="<?php if ( isset( $tag->name ) ) echo attribute_escape($tag->name); ?>" size="40" aria-required="true" />
	            <p><?php _e('The name is how the tag appears on your site.'); ?></p></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="slug"><?php _e('Tag slug') ?></label></th>
				<td><input name="slug" id="slug" type="text" value="<?php if ( isset( $tag->slug ) ) echo attribute_escape(apply_filters('editable_slug', $tag->slug)); ?>" size="40" />
	            <p><?php _e('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?></p></td>
			</tr>
		</table>
	<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e('Update Tag'); ?>" /></p>
	<?php do_action('edit_tag_form', $tag); ?>
	</form>
	</div>
	
	<?php
}

function mediatags_process_inline_save()
{
	//echo "_REQUEST<pre>"; print_r($_REQUEST); echo "</pre>";
	
	media_tags_register_columns();

	if ( ! isset($_POST['tax_ID']) || ! ( $id = (int) $_POST['tax_ID'] ) )
		die(-1);
		
	$updated = wp_update_term($id, MEDIA_TAGS_TAXONOMY, $_POST);
	if ( $updated && !is_wp_error($updated) ) 
	{
		$tag = get_term( $updated['term_id'], MEDIA_TAGS_TAXONOMY );
		if ( !$tag || is_wp_error( $tag ) )
			die( __('Tag not updated.') );

		echo _media_tag_row($tag);
	} 
	else {
		die( __('Tag not updated.') );
	}
}

?>