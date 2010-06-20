<?php

function mediatags_init_rewrite()
{
	global $wp_rewrite;

	// Adding hooks for custom rewrite for '/media-tags/...'
	if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
		add_filter('rewrite_rules_array', 'mediatags_createRewriteRules');
	}
	if ((isset($_REQUEST['activate'])) && ($_REQUEST['activate'] == true))
	{	
		$wp_rewrite->flush_rules();
	}
}

function mediatags_createRewriteRules($rules) {
	global $wp_rewrite;

	$mediatags_token = '%' . MEDIA_TAGS_QUERYVAR . '%';
	$wp_rewrite->add_rewrite_tag($mediatags_token, '(.+)', MEDIA_TAGS_QUERYVAR . '=');

	//without trailing slash
	$mediatags_structure = $wp_rewrite->front . MEDIA_TAGS_URL . "/".$mediatags_token;	
	$rewrite = $wp_rewrite->generate_rewrite_rules($mediatags_structure);

	return ( $rewrite + $rules );
}

function mediatags_addQueryVar($wpvar_array) {
	$wpvar_array[] = MEDIA_TAGS_QUERYVAR;
	return($wpvar_array);
}

function mediatags_parseQuery() {
	//if this is a series query, then reset other is_x flags and add template redirect;
	
	if (is_MEDIA_TAGS_URL()) {
		global $wp_query;
			
		$wp_query->is_single = false;
		$wp_query->is_page = false;
		$wp_query->is_archive = false;
		$wp_query->is_search = false;
		$wp_query->is_home = false;
		$wp_query->is_404 = false;

		$wp_query->is_mediatags = true;

		//echo "wp_query<pre>"; print_r($wp_query); echo "</pre>";

		add_action('template_redirect', 'mediatags_includeTemplate');
	}	
	add_filter('posts_where', 'mediatags_postsWhere');
	add_filter('posts_join', 'mediatags_postsJoin');
}

function is_MEDIA_TAGS_URL() { 
	global $wp_version, $wp_query;

	//echo "get_query_var=[".get_query_var(MEDIA_TAGS_QUERYVAR)."]<br />";
	$MEDIA_TAGS_URL = ( isset($wp_version) 
		&& ($wp_version >= 2.0) ) ? get_query_var(MEDIA_TAGS_QUERYVAR) : $GLOBALS[MEDIA_TAGS_QUERYVAR];

	//$series = get_query_var(SERIES_QUERYVAR);
	if ( (!is_null($MEDIA_TAGS_URL) && ($MEDIA_TAGS_URL != '')) || ((isset($wp_query->is_mediatags)) && ($wp_query->is_mediatags == true)) )
		return true;
	else
		return false;
}

function mediatags_includeTemplate() {
	if (is_MEDIA_TAGS_URL()) {
		$template = '';
					
		$mediatag_var = get_query_var(MEDIA_TAGS_QUERYVAR);
		//echo "mediatag_var=[".$mediatag_var."]<br />";

		$mediatag_feed_var = get_query_var('feed');
		//echo "mediatag_feed_var=[".$mediatag_feed_var."]<br />";

		if ($mediatag_var)
		{	
			$mediatag_term = is_term( $mediatag_var, MEDIA_TAGS_TAXONOMY );
			if ($mediatag_term)
			{					
				if (($mediatag_feed_var == "rss")
 				 || ($mediatag_feed_var == "rss2")
				 || ($mediatag_feed_var == "feed"))
				{
					//load_template( ABSPATH . WPINC . '/feed-rss2.php' );					
					//load_template( dirname(__FILE__) . "/mediatags_rss2.php");

					$fname_parts = pathinfo(MEDIA_TAGS_RSS_TEMPLATE);
					if (strlen($fname_parts['filename']))
					{
						$template_filename = TEMPLATEPATH. "/" . 
							$fname_parts['filename'] . "-". $mediatag_term['term_id'] . 
							".". $fname_parts['extension'];
					
						if ( !file_exists($template_filename) )
						{
							$template_filename = "";
							$plugindir_node = dirname(__FILE__);	
							$template_filename = $plugindir_node ."/".MEDIA_TAGS_RSS_TEMPLATE;
						}
					}
					//echo "template_filename[".$template_filename."]<br />";
					//include($template_filename);
					load_template($template_filename);
					exit;
				}
				else
				{
					$fname_parts = pathinfo(MEDIA_TAGS_TEMPLATE);
					if (strlen($fname_parts['filename']))
					{
						$template_filename = TEMPLATEPATH. "/" . 
							$fname_parts['filename'] . "-". $mediatag_term['term_id'] . 
							".". $fname_parts['extension'];
					
						if ( !file_exists($template_filename) )
							$template_filename = "";						
					}
				}
			}
		}
		if (strlen($template_filename) == 0)
			$template_filename = TEMPLATEPATH. "/" . MEDIA_TAGS_TEMPLATE;

		if ( file_exists($template_filename) )
			$template = $template_filename;
		else
			$template = get_archive_template();

		if ($template) {
			load_template($template);
			exit;
		}
	}
	return;
}

function mediatags_postsWhere($where) 
{ 
	global $wpdb;
	
	$whichmediatags	= "";
	
	$mediatags_var = get_query_var(MEDIA_TAGS_QUERYVAR);	
	if ($mediatags_var)
	{
		//is the term (media-tag value valid)?
		$media_tags_chk = is_term( $mediatags_var, MEDIA_TAGS_TAXONOMY );
		if ($media_tags_chk)
		{
			// Dear Wordpress. I hate parsing SQL. Find a better interface for this crap!
			$where = str_replace("AND $wpdb->posts.post_type = 'post'", "AND $wpdb->posts.post_type = 'attachment'", $where);
			$where = str_replace("($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private')", 
								"($wpdb->posts.post_status = 'inherit')", $where);
			$where = str_replace("($wpdb->posts.post_status = 'publish')", 
								"($wpdb->posts.post_status = 'inherit')", $where);

			$whichmediatags .= " AND $wpdb->term_taxonomy.taxonomy = '".MEDIA_TAGS_TAXONOMY."'";
			$whichmediatags .= " AND $wpdb->term_taxonomy.term_id = ".$media_tags_chk['term_id'];
		}
	}
	else if (isset($_REQUEST['mediatag_id']))
	{
		$whichmediatags .= " AND $wpdb->term_taxonomy.taxonomy = '".MEDIA_TAGS_TAXONOMY."'";
		$whichmediatags .= " AND $wpdb->term_taxonomy.term_id = '".$_REQUEST['mediatag_id']."' ";		
	}
	$where .= $whichmediatags;
	return $where;
}

function mediatags_postsJoin($join) 
{
	global $wpdb;

	$mediatags_var = get_query_var(MEDIA_TAGS_QUERYVAR);
	$media_tags_chk = is_term( $mediatags_var, MEDIA_TAGS_TAXONOMY );
	if (($media_tags_chk) 
	 || (isset($_REQUEST['mediatag_id'])))
	{
		$join = " INNER JOIN $wpdb->term_relationships 
					ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) 
					INNER JOIN $wpdb->term_taxonomy 
					ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) ";
	}
	return $join;	
}

function mediatags_term_link($termlink, $term)
{
	if ($term->taxonomy == MEDIA_TAGS_TAXONOMY)
		$termlink = get_mediatag_link($term->term_id);
	
	return $termlink;
}
?>