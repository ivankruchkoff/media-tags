<?php
/*
Plugin Name: Media Tags
Plugin URI: http://www.codehooligans.com/2009/08/17/media-tags-2-2-plugin-for-wordpress-released/
Description: Provides ability to tag media via Media Management screens
Author: Paul Menard
Version: 2.2.5
Author URI: http://www.codehooligans.com
*/

include_once ( dirname(__FILE__) . "/mediatags_config.php");
include_once ( dirname(__FILE__) . '/mediatags_admin.php' );
include_once ( dirname(__FILE__) . "/mediatags_rewrite.php");
include_once ( dirname(__FILE__) . "/mediatags_template_functions.php");
include_once ( dirname(__FILE__) . "/mediatags_shortcodes.php");
include_once ( dirname(__FILE__) . "/mediatags_settings.php");
include_once ( dirname(__FILE__) . "/mediatags_thirdparty.php");

class MediaTags {

	var $plugindir_url;
	
	function MediaTags()
	{
		global $wp_version;
		
		$plugindir_node 						= dirname(plugin_basename(__FILE__));	
		$this->plugindir_url 					= get_bloginfo('wpurl') . "/wp-content/plugins/". $plugindir_node;
	
		add_filter('attachment_fields_to_edit', 'mediatags_show_fields_to_edit', 11, 2);
		add_filter('attachment_fields_to_save', 'meditags_process_attachment_fields_to_save', 11, 2);
		add_filter( 'manage_media_columns', 'mediatags_library_column_header' );
		add_action( 'manage_media_custom_column', 'mediatags_library_column_row', 10, 2 );

		add_action('delete_attachment', 'mediatags_delete_attachment_proc');

		add_action('admin_head', array(&$this,'admin_head_proc'));

		add_action( 'init', array(&$this, 'init') );
		add_action( 'admin_init', array(&$this, 'admin_init') );
		
		add_filter('query_vars', 'mediatags_addQueryVar');
		add_action('parse_query','mediatags_parseQuery');

		add_filter('media_upload_tabs', 'mediatag_upload_tab');
		add_action('media_upload_mediatags', 'media_upload_mediatags');

		// This MAY not be needed. This was a safety catch for the non-Permalink URLs.
		add_filter('term_link', 'mediatags_term_link', 20, 2);

		if (function_exists('add_shortcode'))
			add_shortcode('media-tags', 'mediatags_shortcode_handler');

		// Add our sub-panel to the Media section. But only if WP 2.7 or higher!
		if ($wp_version >= "2.7")
		{
			add_action('admin_menu', 'mediatags_admin_panels');
		}

		$this->register_taxonomy();

		if ((isset($_REQUEST['page']))
		 && ($_REQUEST['page'] == ADMIN_MENU_KEY))
		{
			mediatags_process_actions();
		}		

		// Support for the Google Sitemap XML plugin
		add_action("sm_buildmap", 'mediatags_google_sitemap_pages');				
	}

	function init() {
		mediatags_init_rewrite();
			
		// Checks ths plugin version again the legacy data
		if ((isset($_REQUEST['activate'])) || ($_REQUEST['activate'] == true))
		{
			$this->mediatags_activate_plugin();
		}
	}

	function admin_init()
	{
		wp_enqueue_script('jquery-form'); 
		if (function_exists('mediatags_settings_api_init'))
			mediatags_settings_api_init();		
	}
		
	function register_taxonomy() {
		$args = array();					
		$args['rewrite'] = array('slug' => MEDIA_TAGS_TAXONOMY);
		$args['query_var'] = '?'.MEDIA_TAGS_TAXONOMY;		
		register_taxonomy( MEDIA_TAGS_TAXONOMY, MEDIA_TAGS_TAXONOMY, $args );		
	}

	function admin_head_proc()
	{
		?>
		<link rel="stylesheet" href="<?php echo $this->plugindir_url ?>/mediatags_style_admin.css" 
			type="text/css" media="screen" />
			
		<?php if ((isset($_REQUEST['page']))
		 	&& ($_REQUEST['page'] == ADMIN_MENU_KEY))
		{	?><script type="text/javascript" src="<?php echo $this->plugindir_url ?>/mediatags_inline_edit.js"></script><?php }
		
		?>
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready(function(){

			jQuery('div#media-tags-list-used').show();
			jQuery('div#media-tags-list-common').hide();
			jQuery('div#media-tags-list-uncommon').hide();

			jQuery("a#media-tags-show-hide-used").click(function () {
				jQuery("div#media-tags-list-used").slideToggle('slow');
				jQuery(this).text(jQuery(this).text() == 'Show Media Tags for this attachment' ? 'Media Tags for this attachment' : 'Show Media Tags for this attachment');
				return false;
			});

			jQuery("a#media-tags-show-hide-common").click(function () {
				jQuery("div#media-tags-list-common").slideToggle('slow');
				jQuery(this).text(jQuery(this).text() == 'Show Common Media Tags' ? 'Hide Common Media Tags' : 'Show Common Media Tags');
				return false;
			});

			jQuery("a#media-tags-show-hide-uncommon").click(function () {
				jQuery("div#media-tags-list-uncommon").slideToggle('slow');
				jQuery(this).text(jQuery(this).text() == 'Show Uncommon Media Tags' ? 'Hide Uncommon Media Tags' : 'Show Uncommon Media Tags');
				return false;
			});


/*

$("li").toggle(
      function () {
        $(this).css({"list-style-type":"disc", "color":"blue"});
      },
      function () {
        $(this).css({"list-style-type":"disc", "color":"red"});
      },
      function () {
        $(this).css({"list-style-type":"", "color":""});
      }
    );


			jQuery('a#media-tags-show-hide-common').click(function () {	
				jQuery('div#media-tags-list-common').toggle(
					function () {
						jQuery('a#media-tags-show-hide-common').text('Hide');
						jQuery('div#media-tags-list-common').show();
					},
					function () {
						jQuery('div#media-tags-list-common a').text('Show');
						jQuery('div#media-tags-list-common').hide();
					}
				);
				return false;
			});
			*/
		});
		//]]>
		</script>
		
		<?php
		
	}
		
	function mediatags_activate_plugin()
	{
		// First see if we need to convert the data. This really only applied to pre-Taxonomy versions
		include_once ( dirname (__FILE__) . '/mediatags_legacy_convert.php' );
		mediatags_plugin_version_check();

		mediatags_reconcile_counts();
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
			'search_by' => 'slug',
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

		// Force 'OR' on compare if searching by name (not slug). This is because the name search will return multiple
		// values per each 'media_tags' searched item.
		if ($r['search_by'] != 'slug')
			$r['tags_compare'] = 'OR';

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

				if ($r['search_by'] != "slug")
					$get_terms_args['search'] = $search_term;
				else
					$get_terms_args['slug'] = $search_term;
					
				$terms_item = get_terms( MEDIA_TAGS_TAXONOMY, $get_terms_args );
				if ($terms_item)
					$search_terms_array[$search_term] = $terms_item;
			}
		}

		$objects_ids_array = array();
		if (count($search_terms_array))
		{
			foreach($search_terms_array as $search_term_items)
			{
				if ($search_term_items) {
					foreach($search_term_items as $search_term_item)
					{				
						$objects_ids = get_objects_in_term($search_term_item->term_id, MEDIA_TAGS_TAXONOMY);
						if ($objects_ids)
							$objects_ids_array[] = $objects_ids;
					}
				}
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