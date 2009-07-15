<?php
/*
Plugin Name: Media Tags
Plugin URI: http://www.codehooligans.com/2008/12/14/media-tags-plugin/
Description: Provides ability to tag media via Media Management screens
Author: Paul Menard
Version: 1.0.1
Author URI: http://www.codehooligans.com
*/
class MediaTags {

	var $plugindir_url;
	
	function MediaTags()
	{
		$plugindir_node 						= dirname(plugin_basename(__FILE__));	
		$this->plugindir_url 					= get_bloginfo('wpurl') . "/wp-content/plugins/". $plugindir_node;
	
		add_filter('attachment_fields_to_edit', array(&$this,'show_media_tag_fields_to_edit'), 11, 2);
		add_filter('attachment_fields_to_save', array(&$this,'process_media_attachment_fields_to_save'), 11, 2);

		add_action('admin_head', array(&$this,'admin_head_proc'));
	}

	function admin_head_proc()
	{
		?>
		<link rel="stylesheet" href="<?php echo $this->plugindir_url ?>/style_admin.css" type="text/css" media="screen" />
		<?php
	}
	
	function show_media_tag_fields_to_edit($form_fields, $post) 
	{	
		$media_meta = wp_get_attachment_metadata($post->ID);
		
		if (isset($media_meta['image_meta']['media_tags']))
			$post_media_tags = $media_meta['image_meta']['media_tags'];
		else
			$post_media_tags = "";

		
		$post_media_tags_fields = $this->get_media_fields($post->ID, $post_media_tags);
		if (strlen($post_media_tags_fields))
			$post_media_tags_fields = "<br />Enter media tags in the space above. Enter multiple tags 
				separated with comma. Or select from the tag(s) below" . $post_media_tags_fields;
		else
			$post_media_tags_fields = "<br />Enter media tags in the space above. Enter multiple tags 
				separated with comma.";
		
        $form_fields['media-meta'] = array(
           	'label' => __('Media tags:'),
	   		'input' => 'html',
	   		'html' => "<input type='text' name='attachments[$post->ID][media_tags_input]' 
				id='attachments[$post->ID][media_tags_input]'
	       		size='50' value='' />
			$post_media_tags_fields "
		);
	    return $form_fields;
	}

	function get_media_fields($post_id, $post_media_tags_list)
	{
		$post_media_tags = array();

		$post_media_tags_tmp = split(',', $post_media_tags_list);
		if ($post_media_tags_tmp)
		{
			foreach($post_media_tags_tmp as $idx => $tag_val)
			{
				$tag_val_n = strtolower(trim($tag_val));
				$tag_val_n = str_replace(' ', '-', $tag_val_n);
			
				$post_media_tags[$tag_val_n] = $tag_val;
			}
			asort($post_media_tags);
		}
		
		$master_media_tag_fields = "";

		$master_media_tags_array = $this->load_master_media_tags();
		if ($master_media_tags_array)
		{
			foreach($master_media_tags_array  as $idx => $tag_val)
			{
				if (array_key_exists($idx, $post_media_tags) !== false)
				{
					$selected_tag = ' checked="checked" ';
				}
				else
					$selected_tag = '';
			
				$master_media_tag_fields .= "<li><input type='checkbox' id='label-$post_id-".$idx."'
					name='attachments[$post_id][media_tags_checkbox][$idx]' " .$selected_tag. " />
					<label for='label-".$post_id."-".$idx."'>" . __($tag_val) . "</label></li>";
			}
			if (strlen($master_media_tag_fields))
				$master_media_tag_fields = '<ul id="media-tags-list">'. $master_media_tag_fields . '</ul>';
		}
		return $master_media_tag_fields;
	}

	function process_media_attachment_fields_to_save($post, $attachment) 
	{
		$media_tags_array = array();

		$master_media_tags_array = $this->load_master_media_tags();
		if ($master_media_tags_array)
		{
			if (isset($attachment['media_tags_checkbox']))
			{
				foreach($attachment['media_tags_checkbox']  as $idx => $tag_val)
				{
					if (array_key_exists($idx, $master_media_tags_array) !== false)
						$media_tags_array[$idx] = $master_media_tags_array[$idx];
				}
			}
		}
	
		if (strlen($attachment['media_tags_input']))
		{
			$tags_tmp_array = split(',', $attachment['media_tags_input']);
			if ($tags_tmp_array)
			{
				foreach($tags_tmp_array as $idx => $tag_val)
				{
					$tag_val_n = strtolower(trim($tag_val));
					$tag_val_n = str_replace(' ', '-', $tag_val_n);
				
					if (array_key_exists($tag_val_n, $media_tags_array) === false)
					{
						$media_tags_array[$tag_val_n] = trim($tag_val);
					}					
				}
			}
		}
	
		if (count($media_tags_array))
		{
			$post_media_tag_list = "";
			foreach($media_tags_array as $idx => $tag_val)
			{
				if (strlen($post_media_tag_list))
					$post_media_tag_list .= ",";

				$post_media_tag_list .= $tag_val;
			}
			if (strlen($post_media_tag_list))
			{
	        	$media_meta = wp_get_attachment_metadata( $post['ID'] );
				$media_meta['image_meta']['media_tags'] = $post_media_tag_list;
				wp_update_attachment_metadata( $post['ID'],  $media_meta );
			
				update_post_meta( $post['ID'], 'post_media_tags', $post_media_tag_list);

				$this->merge_media_tags_to_master($media_tags_array);
			}        
		}
		else
		{
			$media_meta = wp_get_attachment_metadata( $post['ID'] );
			$media_meta['image_meta']['media_tags'] = "";
			wp_update_attachment_metadata( $post['ID'],  $media_meta );
			update_post_meta( $post['ID'], 'post_media_tags', "");
		}
	    return $post;
	}

	function load_master_media_tags()
	{
		//$master_images_tags_list = "One 1, Two 2, Three 3";
		$master_media_tags_list = get_option('media-tags');
		if ($master_media_tags_list)
		{
			$master_media_tags_tmp = split(',', $master_media_tags_list);
			if ($master_media_tags_tmp)
			{
				$master_media_tags = array();
				foreach($master_media_tags_tmp as $tag_idx => $tag_val)
				{
					if (!strlen($tag_val))
						continue;

					$tag_val_n 	= strtolower(trim($tag_val));
					$tag_val_n 	= str_replace(' ', '-', $tag_val_n);

					if (array_key_exists($tag_val_n, $master_media_tags) === false)
					{
						$master_media_tags[$tag_val_n] = trim($tag_val);
					}					
				}
				asort($master_media_tags, SORT_STRING);
				return $master_media_tags;
			}
		}
	}

	function merge_media_tags_to_master($post_media_tags_array)
	{
		$master_media_tags_array = $this->load_master_media_tags();
		if (!$master_media_tags_array)
			$master_media_tags_array = array();
		
		foreach($post_media_tags_array  as $idx => $tag_val)
		{
			if (array_key_exists($idx, $master_media_tags_array) === false)
				$master_media_tags_array[$idx] = $tag_val;
		}
		asort($master_media_tags_array, SORT_STRING);

		$master_media_tag_list = "";
		foreach($master_media_tags_array as $idx => $tag_val)
		{
			if (strlen($master_media_tag_list))
				$master_media_tag_list .= ",";

			$master_media_tag_list .= $tag_val;
		}
		if (strlen($master_media_tag_list))
			update_option('media-tags', $master_media_tag_list);
	}
	
	function get_media_by_tag($args='')
	{
		global $post;
		
		$defaults = array(
			'media_tags' => '', 
			'media_types' => ''
		);
		$r = wp_parse_args( $args, $defaults );
		
		if ((!$r['media_tags']) || (strlen($r['media_tags']) == 0))
			return;
			
		if ((!$r['post_parent']) || (strlen($r['post_parent']) == 0))
		{
			if ($post)
				$r['post_parent'] = $post->ID;
			else
				return;
		}	

		$search_media_tags_array = split(',', $r['media_tags']);
		if ($search_media_tags_array)
		{
			foreach($search_media_tags_array as $idx => $val)
			{
				$search_media_tags_array[$idx] = strtolower(trim($val));
			}
		}
		
		if ($r['media_types'])
		{
			$search_media_types_array = split(',', $r['media_types']);
			if ($search_media_types_array)
			{
				foreach($search_media_types_array as $idx => $val)
				{
					$search_media_types_array[$idx] = strtolower(trim($val));
				}
			}
		}

		$tag_attachents = array();

		//$post_attachments = get_posts('post_parent='. $r['post_parent'] .'&post_type=attachment&orderby=title&order=ASC');
		$post_attachments = get_children( array(	'post_parent' => $r['post_parent'], 
											'post_status' => 'inherit', 
											'post_type' => 'attachment', 
											'order' => 'asc', 
											'orderby' => 'title') );
		if ($post_attachments)
		{
			foreach ( $post_attachments as $attachment_item ) 
			{
				$media_meta = wp_get_attachment_metadata($attachment_item->ID);
				if ((isset($media_meta['image_meta']['media_tags']))
				 && (strlen($media_meta['image_meta']['media_tags'])))
				{
					$media_tags_array = split(',', $media_meta['image_meta']['media_tags']);
					if ($media_tags_array)
					{
						// Normalize the tags. Trim white space before and after, make all lowercase.
						foreach($media_tags_array as $idx => $val)
						{
							$media_tags_array[$idx] = strtolower(trim($val));
						}
					
						$array_of_match = array_intersect($search_media_tags_array, $media_tags_array);
						if ($array_of_match)
						{						
							if ($search_media_types_array)
							{
								list($image, $media_type) = split('/', $attachment_item->post_mime_type);								
								if (array_search(strtolower($media_type), $search_media_types_array) !== false)
								{
									$attachment_item->media_meta = $media_meta;
									$tag_attachents[] = $attachment_item;
								}
							}
							else
							{
								$attachment_item->media_meta = $media_meta;
								$tag_attachents[] = $attachment_item;
							}
						}
					}
				}
			}

			if (count($tag_attachents))
				return $tag_attachents;
		}
	}
}
$mediatags = new MediaTags();
?>