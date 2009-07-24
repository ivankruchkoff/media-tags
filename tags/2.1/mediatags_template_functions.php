<?php
function is_mediatag()
{
	global $wp_query;
	
	if ($wp_query->is_mediatags == true)
		return true;
	else
		return false;
}

function in_mediatag($mediatag_id = '')
{
	if (!$mediatag_id) return;
	
	$mediatag_var = get_query_var(MEDIA_TAGS_QUERYVAR);
	if ($mediatag_var)
	{	
		$mediatag_term = is_term( $mediatag_var, MEDIA_TAGS_TAXONOMY );
		if ($mediatag_id === $mediatag_term['term_id'])
			return true;
	}
	return false;	
}


function &get_mediatags( $args = '' ) {
	
	$media_tags = get_terms( MEDIA_TAGS_TAXONOMY, $args );

	if ( empty( $media_tags ) ) {
		$return = array();
		return $return;
	}

	$media_tags = apply_filters( 'get_mediatags', $media_tags, $args );
	return $media_tags;
}

function list_mediatags($args = '' ) {
	
	$defaults = array(
		'echo' => '1'		
	);
	$r = wp_parse_args( $args, $defaults );
	
	$media_tag_list = get_mediatags( $args );
	if (!$media_tag_list)
	{
		$return = array();
		return $return;
	}		
	
	$media_tag_list = apply_filters( 'list_mediatags', $media_tag_list, $args );
	if (!$media_tag_list)
	{
		$return = array();
		return $return;
	}		
	
	$media_tag_list_items = "";
	foreach($media_tag_list as $media_tag_item)
	{
		$media_tag_list_items .= '<li><a href="'. get_mediatag_link($media_tag_item->term_id). '">'. 
			$media_tag_item->name. '</a></li>';
	}
	
	if ($r['echo'] == 1)
		echo $media_tag_list_items;
	else
		return $media_tag_list_items;
}

// Return the href link value for a given tag_id
// modeled after WP get_tag_link() function
function get_mediatag_link( $mediatag_id ) {
	global $wp_rewrite;

	$mediatag_link = "";
	if ($wp_rewrite->using_permalinks())
	{
		$mediatags_token = '%' . MEDIA_TAGS_QUERYVAR . '%';
		$mediatag_link = $wp_rewrite->front . MEDIA_TAGS_URL . "/".$mediatags_token;	
	}

	$media_tag = &get_term( $mediatag_id, MEDIA_TAGS_TAXONOMY );
	if ( is_wp_error( $media_tag ) )
		return $media_tag;
	
	$mediatag_slug = $media_tag->slug;

	if ( empty( $mediatag_link ) ) {
		$file = get_option( 'home' ) . '/';
		$mediatag_link = $file . '?media-tag=' . $mediatag_slug;
	} 
	else {
		$mediatag_link = str_replace( '%media-tag%', $mediatag_slug, $mediatag_link );
		$mediatag_link = get_option( 'home' ) . user_trailingslashit( $mediatag_link, 'category' );
	}

	return apply_filters( 'get_mediatag_link', $mediatag_link, $mediatag_id );
}

// Stadnard template function modeled after WP the_tags function. Used to list tags for a given post. 
function the_mediatags( $before = 'Media-Tags: ', $sep = ', ', $after = '' ) {
	return the_terms( 0, MEDIA_TAGS_TAXONOMY, $before, $sep, $after );
}
function get_attachments_by_media_tags($args='')
{
	global $mediatags;
	
	return $mediatags->get_attachments_by_media_tags($args);
}
?>