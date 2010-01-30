<?php
// [media-tags media_tags="alt-views,page-full,thumb" tags_compare="AND" orderby="menu_order" display_item_callback=""]

function mediatags_shortcode_handler($atts, $content=null, $tableid=null) 
{
	global $post;
	
	if ((!isset($atts['return_type'])) || ($atts['return_type'] != "li"))
		$atts['return_type'] = "li";

	if (!isset($atts['before_list']))
		$atts['before_list'] = "<ul>";

	if (!isset($atts['after_list']))
		$atts['after_list'] = "<ul>";


	if ((!isset($atts['display_item_callback'])) || (strlen($atts['display_item_callback']) == 0))
		$atts['display_item_callback'] = 'default_item_callback';

	if ((isset($atts['post_parent'])) && ($atts['post_parent'] == "this"))
		$atts['post_parent'] = $post->ID;
		
	$atts['call_source'] = "shortcode";
		
	//echo "atts<pre>"; print_r($atts); echo "</pre>";
	
	if (!is_object($mediatags)) 
		$mediatags = new MediaTags();
		
	$output = $mediatags->get_attachments_by_media_tags($atts);
	if ($output)
	{
		if (isset($atts['before_list']))
		{
			$output = $atts['before_list'] . $output;
		}
		
		if (isset($atts['after_list']))
		{
			$output = $output .$atts['after_list'];			
		}
	}
	return $output;
}

function default_item_callback($post_item)
{
	//echo "post_item<pre>"; print_r($post_item); echo "</pre>";
	
	return '<li class="media-tag-list" id="media-tag-item-'.$post_item->ID.'"><img src="'.
	 	wp_get_attachment_url($post_item->ID).'" 
			title="'.$post_item->post_title.'" /></li>';
}
?>