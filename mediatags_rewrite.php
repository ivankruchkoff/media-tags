<?php
function mediatags_template_redirect() 
{
	global $wp_version;
	
	$template = '';
					
	$mediatag_var = get_query_var(MEDIA_TAGS_QUERYVAR);

	$mediatag_feed_var = get_query_var('feed');

	if ($mediatag_var)
	{	
		if ( version_compare( $wp_version, '3.0', '<' ) )
			$mediatag_term = is_term( $mediatag_var, MEDIA_TAGS_TAXONOMY );
		else
			$mediatag_term = term_exists( $mediatag_var, MEDIA_TAGS_TAXONOMY );
		if ($mediatag_term)
		{					
			$mediatag_term = get_term( $mediatag_term['term_id'], MEDIA_TAGS_TAXONOMY );

			if (($mediatag_feed_var == "rss")
			 || ($mediatag_feed_var == "rss2")
			 || ($mediatag_feed_var == "feed"))
			{
				$fname_parts = pathinfo(MEDIA_TAGS_RSS_TEMPLATE);
				if (strlen($fname_parts['filename']))
				{
					// First check if these is a template to handle this in the user's theme folder. 
					
					// Check for term slug first. 
					$template_filename = TEMPLATEPATH. "/" . 
						$fname_parts['filename'] . "-". $mediatag_term->slug . 
						".". $fname_parts['extension'];				
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;
					}						

					// Then check for term ID
					$template_filename = TEMPLATEPATH. "/" . 
						$fname_parts['filename'] . "-". $mediatag_term->term_id . 
						".". $fname_parts['extension'];				
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;
					}						
					
					// Then check for just mediatags_rss.php in the them folder
					$template_filename = TEMPLATEPATH. "/" . 
						$fname_parts['filename'] .".". $fname_parts['extension'];				
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;
					}						
					
				}
			}
			else
			{
				$fname_parts = pathinfo(MEDIA_TAGS_TEMPLATE);
				if (strlen($fname_parts['filename']))
				{
					// First check if these is a template to handle this specific term in the user's theme folder.
					$template_filename = TEMPLATEPATH. "/" . 
						$fname_parts['filename'] . "-". $mediatag_term->slug . 
						".". $fname_parts['extension'];
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;						
					}

					$template_filename = TEMPLATEPATH. "/" . 
						$fname_parts['filename'] . "-". $mediatag_term->term_id . 
						".". $fname_parts['extension'];
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;						
					}

					// Else try the generic mediatag.php template.

					$template_filename = TEMPLATEPATH ."/". $fname_parts['filename'] .".". $fname_parts['extension'];
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;						
					}					
				}
			}
		}
	}
	// If here we didn't find any Media-Tags specific template. So let WP figure out where to display the content. 
	return;
}

// Used to limit the categories displayed on the home page. Simple
function mediatags_pre_get_posts_filter($query) 
{
	global $wp_version;
	
	if (function_exists('get_current_screen'))
		$current_screen = get_current_screen();
	else
	{
		global $current_screen;			
	}

	if (
		( (isset($query->query_vars['taxonomy'])) && ($query->query_vars['taxonomy'] == MEDIA_TAGS_QUERYVAR) )
		|| (isset($query->query_vars[MEDIA_TAGS_QUERYVAR])) )
	{

		$query->set('post_type','attachment');
		$query->set('post_status','inherit');
		$query->set('is_mediatags','1');

		$mediatag_template_archive = get_option('mediatag_template_archive', 'yes'); 
		if ($mediatag_template_archive == "yes")
		{
			add_filter( 'the_content', 						'mediatags_the_content_filter' );
			add_filter( 'the_excerpt', 						'mediatags_the_content_filter' );

			add_filter( 'the_content_rss', 					'mediatags_the_content_filter' );
			add_filter( 'the_excerpt_rss', 					'mediatags_the_content_filter' );
		}
	}
	return $query;
}

function mediatags_postsWhere($where, $query) 
{ 
	global $wpdb, $wp_version;
		
	$mediatags_var = get_query_var(MEDIA_TAGS_QUERYVAR);	
	if ($mediatags_var)
	{

		// In WP 3.0 'is_term' was renamed to 'term_exists'
	    if ( version_compare( $wp_version, '3.0', '<' ) )
			$media_tags_chk = is_term( $mediatags_var, MEDIA_TAGS_TAXONOMY );
		else
			$media_tags_chk = term_exists( $mediatags_var, MEDIA_TAGS_TAXONOMY );

		if (($media_tags_chk) && ($query->is_search))
		{
			$where_mediatags	= "";
			$where_mediatags .= " AND $wpdb->term_taxonomy.taxonomy = '".MEDIA_TAGS_TAXONOMY."'";
			$where_mediatags .= " AND $wpdb->term_taxonomy.term_id = ".$media_tags_chk['term_id'];
			
			$where .= $where_mediatags;
		}
	}
	return $where;
}


function mediatags_postsJoin($join, $query) 
{
	global $wpdb, $wp_version;

	$mediatags_var = get_query_var(MEDIA_TAGS_QUERYVAR);
	if ($mediatags_var)
	{

		// In WP 3.0 'is_term' was renamed to 'term_exists'
	    if ( version_compare( $wp_version, '3.0', '<' ) )
			$media_tags_chk = is_term( $mediatags_var, MEDIA_TAGS_TAXONOMY );
		else
			$media_tags_chk = term_exists( $mediatags_var, MEDIA_TAGS_TAXONOMY );

		if (($media_tags_chk) && ($query->is_search))
		{
			$mediatags_join = " INNER JOIN $wpdb->term_relationships 
						ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) 
						INNER JOIN $wpdb->term_taxonomy 
						ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) ";
			$join .= $mediatags_join;
		}
	}
	return $join;	
}

function mediatags_the_content_filter($content)
{
	global $post;
	
	$mediatag_var = get_query_var(MEDIA_TAGS_QUERYVAR);
	$is_image = wp_attachment_is_image();

	if (($mediatag_var) && ($is_image == true)) 
	{
		$image_img_tag = wp_get_attachment_image( $post->ID, 'thumbnail' );
		if ($image_img_tag)
			$content .= '<a class="size-thumbnail" href="'. get_permalink($post->ID) .'">'. $image_img_tag .'</a>';
	}
	return $content;
}
