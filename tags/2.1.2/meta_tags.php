<?php
/*
Plugin Name: Media Tags
Plugin URI: http://www.codehooligans.com/2009/07/15/media-tags-20-released/
Description: Provides ability to tag media via Media Management screens
Author: Paul Menard
Version: 2.1.2
Author URI: http://www.codehooligans.com
*/

include_once ( dirname(__FILE__) . "/mediatags_config.php");
include_once ( dirname(__FILE__) . '/mediatags_admin.php' );
include_once ( dirname(__FILE__) . "/mediatags_rewrite.php");
//include_once ( dirname(__FILE__) . "/mediatags_tagcloud.php");
include_once ( dirname(__FILE__) . "/mediatags_template_functions.php");
include_once ( dirname(__FILE__) . "/mediatags_shortcodes.php");

class MediaTags {

	var $plugin_version;
	var $plugindir_url;
	
	var $taxonomy_name; 
	
	function MediaTags()
	{
		$this->plugin_version = "2.0";
		
		global $wp_version;
		
		$plugindir_node 						= dirname(plugin_basename(__FILE__));	
		$this->plugindir_url 					= get_bloginfo('wpurl') . "/wp-content/plugins/". $plugindir_node;
	
		add_filter('attachment_fields_to_edit', array(&$this,'show_media_tag_fields_to_edit'), 11, 2);
		add_filter('attachment_fields_to_save', array(&$this,'process_media_attachment_fields_to_save'), 11, 2);

		add_action('admin_head', array(&$this,'admin_head_proc'));

		add_action( 'init', array(&$this, 'init') );
		add_filter('query_vars', 'mediatags_addQueryVar');
		add_action('parse_query','mediatags_parseQuery');
		
		if (function_exists('add_shortcode'))
			add_shortcode('media-tags', 'mediatags_shortcode_handler');

		// Add our sub-panel to the Media section. But only if WP 2.7 or higher!
		if ($wp_version >= "2.7")
			add_action('admin_menu', array(&$this, 'admin_panels'));

		if ((isset($_REQUEST['page']))
		 && ($_REQUEST['page'] == "media-tags/meta_tags.php"))
		{
			$this->register_taxonomy();
			mediatags_process_actions();
		}		
	}

	function init() {
		wp_enqueue_script('jquery-form'); 
		$this->register_taxonomy();

		mediatags_init_rewrite();
		
		// Checks ths plugin version again the legacy data
		$this->plugin_version_check();
	}

	function plugin_version_check()
	{
		// We get the version from the main WP wp_options table. 
		$media_tags_version = get_option('media-tags-version');
		
		// If we don't find the setting we can assume the plugin version is either
		//	a) Never been installed
		//	b) An older version was installed which means we need to convert. 
		
		
		if (!$media_tags_version)
		{
			// Here we need to convert the existing legacy media tags into the terms table. 
			include_once ( dirname (__FILE__) . '/mediatags_legacy_convert.php' );
			$legacy_master_media_tags = legacy_load_master_media_tags();
			if ($legacy_master_media_tags)
			{
				foreach($legacy_master_media_tags as $legacy_slug => $legacy_name)
				{
					if ( ! ($id = is_term( $legacy_slug, MEDIA_TAGS_TAXONOMY ) ) )
						wp_insert_term($legacy_name, MEDIA_TAGS_TAXONOMY, array('slug' => $legacy_slug));
				}
				//$media_tags_tmp = (array) get_terms(MEDIA_TAGS_TAXONOMY, 'hide_empty=0');
				//echo "media_tags_tmp<pre>"; print_r($media_tags_tmp); echo "</pre>";				
			}
			
			// Now we need to grab all the attachments in the system. Then for each one grab the meta info
			// load the media tags then set the terms relationship
			$post_attachments = get_posts('post_type=attachment&numberposts=-1');
			if ($post_attachments)
			{
				foreach($post_attachments as $attachment)
				{
					$legacy_media_meta = wp_get_attachment_metadata($attachment->ID);
					if (isset($legacy_media_meta['image_meta']['media_tags']))
						$legacy_post_media_tags_str = $legacy_media_meta['image_meta']['media_tags'];
					else
						$legacy_post_media_tags_str = "";
					
					$legacy_post_media_tags_array = legacy_get_post_media_tags($attachment->ID, $legacy_post_media_tags_str);
					if ($legacy_post_media_tags_array)
					{
						wp_set_object_terms($attachment->ID, $legacy_post_media_tags_array, MEDIA_TAGS_TAXONOMY);
					}
				}				

				foreach($post_attachments as $attachment)
				{
					$media_tags_tmp 	= (array)wp_get_object_terms($attachment->ID, MEDIA_TAGS_TAXONOMY);
				}
			}
			
			// Then insert/update the options table with the current plugin version so we don't have to check each time. 
			update_option('media-tags-version', $this->plugin_version);
		}
		else if ($media_tags_version < $this->plugin_version)
		{
			// Here we might need to do something for other variations. 
		}
	}

	function register_taxonomy() {
		$args = array();					
		register_taxonomy( MEDIA_TAGS_TAXONOMY, MEDIA_TAGS_TAXONOMY, $args );		
	}
	
	function admin_panels()
	{
		add_media_page( "Media Tags", "Media Tags", 8, __FILE__, 'mediatags_admin_panel');
	}
	
	function admin_head_proc()
	{
		?>
		<link rel="stylesheet" href="<?php echo $this->plugindir_url ?>/mediatags_style_admin.css" 
			type="text/css" media="screen" />
			
		<?php if ((isset($_REQUEST['page']))
		 	&& ($_REQUEST['page'] == "media-tags/meta_tags.php"))
		{	?><script type="text/javascript" src="<?php echo $this->plugindir_url ?>/mediatags_inline_edit.js"></script><?php }
	}
	
	function show_media_tag_fields_to_edit($form_fields, $post) 
	{	
		$post_media_tags_fields = $this->get_media_fields($post->ID);
		if (strlen($post_media_tags_fields))
			$post_media_tags_fields = "<br />Enter media tags in the space above. Enter multiple tags 
				separated with comma. Or select from the tag(s) below" . $post_media_tags_fields;
		else
			$post_media_tags_fields = "<br />Enter media tags in the space above. Enter multiple tags separated with comma.";
		
        $form_fields['media-meta'] = array(
           	'label' => __('Media tags:'),
	   		'input' => 'html',
	   		'html' => "<input type='text' name='attachments[$post->ID][media_tags_input]' 
				id='attachments[$post->ID][media_tags_input]'
	       		size='50' value='' />
			$post_media_tags_fields "
		);
		//echo "form_fields<pre>"; print_r($form_fields); echo "</pre>";
	    return $form_fields;
	}


	function get_media_fields($post_id)
	{
		$media_tags_tmp 	= (array)wp_get_object_terms($post_id, MEDIA_TAGS_TAXONOMY);
		//echo "media_tags_tmp<pre>"; print_r($media_tags_tmp); echo "</pre>";
		
		$post_media_tags = array();
		if ($media_tags_tmp)
		{
			$post_media_tags = array(); 
			foreach($media_tags_tmp as $p_media_tag)
			{
				$post_media_tags[$p_media_tag->slug] = $p_media_tag;
			}
			//echo "post_media_tags<pre>"; print_r($post_media_tags); echo "</pre>";
		}

		$master_media_tags_array = $this->load_master_media_tags();	
		if ($master_media_tags_array)
		{
			//echo "master_media_tags_array<pre>"; print_r($master_media_tags_array); echo "</pre>";
			foreach($master_media_tags_array  as $idx => $tag_item)
			{
				//if (!$post_media_tags) continue;
				
				if (array_key_exists($idx, $post_media_tags) !== false)
				{
					$selected_tag = ' checked="checked" ';
				}
				else
					$selected_tag = '';
			
				$master_media_tag_fields .= "<li><input type='checkbox' id='label-$post_id-".$idx."'
					name='attachments[$post_id][media_tags_checkbox][$idx]' " .$selected_tag. " />
					<label for='label-".$post_id."-".$idx."'>" . __($tag_item->name) . "</label></li>";
			}
			if (strlen($master_media_tag_fields))
				$master_media_tag_fields = '<ul id="media-tags-list">'. $master_media_tag_fields . '</ul>';
		}
		return $master_media_tag_fields;
	}

	function process_media_attachment_fields_to_save($post, $attachment) 
	{
		$media_tags_array = array();

		if (isset($attachment['media_tags_checkbox']))
		{
			foreach($attachment['media_tags_checkbox']  as $tag_idx => $tag_val)
			{
				$media_tags_array[] = $tag_idx;
			}
		}

		if (strlen($attachment['media_tags_input']))
		{
			$tags_tmp_array = split(',', $attachment['media_tags_input']);
			if ($tags_tmp_array)
			{
				foreach($tags_tmp_array as $idx => $tag_val)
				{
					$tag_slug = sanitize_title_with_dashes($tag_val);
					
					if ( ! ($id = is_term( $tag_slug, MEDIA_TAGS_TAXONOMY ) ) )
						wp_insert_term($tag_val, MEDIA_TAGS_TAXONOMY, array('slug' => $tag_slug));
					
					$media_tags_array[] = $tag_slug;
				}
			}
		}

		if ($media_tags_array)
		{
			wp_set_object_terms($post['ID'], $media_tags_array, MEDIA_TAGS_TAXONOMY);			
		}
		else
		{
			wp_set_object_terms($post['ID'], "", MEDIA_TAGS_TAXONOMY);				
		}
	    return $post;
	}

	function load_master_media_tags()
	{
		$media_tags_tmp = (array) get_terms(MEDIA_TAGS_TAXONOMY, 'hide_empty=0');
		if ($media_tags_tmp)
		{
			$master_media_tags_array = array(); 
			foreach($media_tags_tmp as $m_media_tag)
			{
				$master_media_tags_array[$m_media_tag->slug] = $m_media_tag;
			}
			return $master_media_tags_array;
		}
	}
	
	// Still support the original legacy version of the function. 
	// Force use of the post_parent parameter. Users wanting to search globally across all media tags should
	// switch to using the get_attachments_by_media_tags() function.
	function get_media_by_tag($args='')
	{
		global $post;
		
		$r = wp_parse_args( $args, $defaults );
		if (!isset($r['post_parent']))
		{
			if ($post)
				$r['post_parent'] = $post->ID;
			else
				return;
		}	
		return $this->get_attachments_by_media_tags($args);
	}
	
	function get_attachments_by_media_tags($args='')
	{
		global $post;

		$defaults = array(
			'call_source' => '',
			'display_item_callback' => 'default_item_callback',
			'media_tags' => '', 
			'media_types' => null,
			'numberposts' => '-1',
			'orderby' => 'menu_order',
			'order' => 'DESC',
			'offset' => '0',
			'return_type' => '',
			'tags_compare' => 'OR'			
		);
		$r = wp_parse_args( $args, $defaults );
		
		if ((!$r['media_tags']) || (strlen($r['media_tags']) == 0))
			return;
		
//		if ((!$r['post_parent']) || (strlen($r['post_parent']) == 0))
//		{
//			if ($post)
//				$r['post_parent'] = $post->ID;
//			else
//				return;
//		}
		
		// Future support for multiple post_parents --- Coming Soon!
//		if (strlen($r['post_parent']))
//		{
//			if (!is_array($r['post_parent']))
//			{
//				$r['post_parent'] = (array) $r['post_parent'];				
//			}			
//		}
//		echo "post_parent<pre>"; print_r($r['post_parent']); echo "</pre>";

		// First split the comma-seperated media-tags list into an array
		$r['media_tags_array'] = split(',', $r['media_tags']);
		if ($r['media_tags_array'])
		{
			foreach($r['media_tags_array'] as $idx => $val)
			{
				$r['media_tags_array'][$idx] = sanitize_title_with_dashes($val);
			}
		}

		// Next split the comma-seperated media-types list into an array
		if ($r['media_types'])
		{
			$r['media_types_array'] = split(',', $r['media_types']);
			if ($r['media_types_array'])
			{
				foreach($r['media_types_array'] as $idx => $val)
				{
					$r['media_types_array'][$idx] = sanitize_title_with_dashes($val);
				}
			}
		}
		//echo "r<pre>"; print_r($r); echo "</pre>";
		
		// Next lookup each term in the terms table. 
		$search_terms_array = array();
		if ($r['media_tags_array'])
		{
			foreach($r['media_tags_array'] as $search_term)
			{
				$get_terms_args['hide_empty'] = 0;
				$get_terms_args['search'] = $search_term;
				$terms_item = get_terms( MEDIA_TAGS_TAXONOMY, $get_terms_args );
				//echo "terms_item<pre>"; print_r($terms_item); echo "</pre>";

				if ($terms_item[0])
					$search_terms_array[$search_term] = $terms_item[0];
			}
		}


		$objects_ids_array = array();
		if (count($search_terms_array))
		{
			foreach($search_terms_array as $search_term_item)
			{
				$objects_ids = get_objects_in_term($search_term_item->term_id, MEDIA_TAGS_TAXONOMY);
				if ($objects_ids)
					$objects_ids_array[] = $objects_ids;				
			}
		}
		
		if (count($objects_ids_array) > 1)
		{
			foreach($objects_ids_array as $idx_ids => $object_ids_item)
			{
				if ((!isset($array_unique_ids)) && ($idx_ids == 0))
				{
					$array_unique_ids = $object_ids_item;
				}
				if (strtoupper($r['tags_compare']) == strtoupper("AND"))
				{
					$array_unique_ids = array_unique(array_intersect($array_unique_ids, $object_ids_item));
				}
				else
				{
					$array_unique_ids = array_unique(array_merge($array_unique_ids, $object_ids_item));
				}
			}			
			sort($array_unique_ids);
		}
		else if (count($objects_ids_array) == 1)		
		{
			$array_unique_ids = $objects_ids_array[0];
		}
		//echo "array_unique_ids<pre>"; print_r($array_unique_ids); echo "</pre>";
		
		$object_ids_str = "";
		if ($array_unique_ids)
		{
			$object_ids_str = implode(',', $array_unique_ids); 
		}

		if ($object_ids_str)
		{
			$query_str = 'post_type=attachment&numberposts=-1';
			if (isset($r['post_parent'])) $query_str = "post_parent=". $r['post_parent'] . "&". $query_str;
			//echo "query_str=[".$query_str."]<br />";
			$attachment_posts = get_posts($query_str);

			$attachment_posts_ids = array();
			if ($attachment_posts)
			{
				foreach($attachment_posts as $attachment_post)
				{
					$attachment_posts_ids[] = $attachment_post->ID;
				}
			}

			$result = array_intersect($array_unique_ids, $attachment_posts_ids);
			//echo "result<pre>"; print_r($result); echo "</pre>";
			if ($result)
			{				
				$get_post_args['post_type'] 	= "attachment";
				$get_post_args['numberposts'] 	= $r['numberposts'];
				$get_post_args['offset']		= $r['offset'];
				$get_post_args['orderby']		= $r['orderby'];
				$get_post_args['order']			= $r['order'];
				$get_post_args['include']		= implode(',', $result);
				//echo "get_post_args<pre>"; print_r($get_post_args); echo "</pre>";

				$attachment_posts = get_posts($get_post_args);
				
				// Now that we have the list of all matching posts we need to filter by the media type is provided
				if (count($r['media_types_array']))
				{
					foreach($attachment_posts as $attachment_idx => $attachment_post)
					{
						$ret_mime_match = wp_match_mime_types($r['media_types_array'], $attachment_post->post_mime_type);
						//echo "ret_mime_match<pre>"; print_r($ret_mime_match); echo "</pre>";
						if (!$ret_mime_match)
							unset($attachment_posts[$attachment_idx]);
					}
				}

				// If the calling system doesn't want the whole list.
				if (($r['offset'] > 0) || ($r['numberposts'] > 0))
					$attachment_posts = array_slice($attachment_posts, $r['offset'], $r['numberposts']);
				
				if ($r['return_type'] === "li")
				{
					$attachment_posts_list = "";
					foreach($attachment_posts as $attachment_idx => $attachment_post)
					{
						if ((strlen($r['display_item_callback']))
						 && (function_exists($r['display_item_callback'])))
							$attachment_posts_list .= call_user_func($r['display_item_callback'], $attachment_post);
					}
					return $attachment_posts_list;
				}
				else
					return $attachment_posts;
			}

		}
	}
}
$mediatags = new MediaTags();
?>