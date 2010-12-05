<?php
function mediatags_template_redirect() 
{
	global $wp_version;
	
	$template = '';
					
	$mediatag_var = get_query_var(MEDIA_TAGS_QUERYVAR);
//	echo __FUNCTION__ .": mediatag_var=[".$mediatag_var."]<br />";

	$mediatag_feed_var = get_query_var('feed');
//	echo __FUNCTION__ .": mediatag_feed_var=[".$mediatag_feed_var."]<br />";

	if ($mediatag_var)
	{	
		if ($wp_version < "3.0")
			$mediatag_term = is_term( $mediatag_var, MEDIA_TAGS_TAXONOMY );
		else
			$mediatag_term = term_exists( $mediatag_var, MEDIA_TAGS_TAXONOMY );
		//echo "mediatag_term<pre>"; print_r($mediatag_term); echo "</pre>";
		if ($mediatag_term)
		{					
			$mediatag_term = get_term( $mediatag_term['term_id'], MEDIA_TAGS_TAXONOMY );
			//echo "mediatag_term<pre>"; print_r($mediatag_term); echo "</pre>";
			
			if (($mediatag_feed_var == "rss")
			 || ($mediatag_feed_var == "rss2")
			 || ($mediatag_feed_var == "feed"))
			{
//				return;
				
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
						$fname_parts['filename'] . ".". $fname_parts['extension'];				
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;
					}						
					
					// IF none are used them return which will default to the WP term feed handling. 
					// We no longer support the Media-Tags RSS template. Sorry. 
/*
					$template_filename = "";
					$plugindir_node = dirname(__FILE__);	
					$template_filename = $plugindir_node ."/".MEDIA_TAGS_RSS_TEMPLATE;
					if ( file_exists($template_filename) )
					{
						load_template($template_filename);
						exit;
					}
*/
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

			add_filter( 'the_content_rss', 						'mediatags_the_content_filter' );
			add_filter( 'the_excerpt_rss', 						'mediatags_the_content_filter' );
			
			
		}
	}
	return $query;
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
?>