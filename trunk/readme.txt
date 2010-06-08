=== Media Tags ===
Contributors: Paul Menard
Donate link: http://www.codehooligans.com/donations/
Tags: images, tags, media, shortcode, permalinks
Requires at least: 2.7.1
Tested up to: 2.9.2
Stable tag: 2.2.9

== Description ==

[Plugin Homepage](http://www.codehooligans.com/projects/wordpress/media-tags/ "Media-Tags Plugin for WordPress")

Adds an input to the media upload and management screens. This input field can be used to "tag" a media file. Works with images, documents or anything.

The Media-Tags plugin 2.0 has been completely rewritten to use to WordPress Taxonomy system for storing related media-tag information. As a benefit to this rewrite the user now has a new Media Tags management interface located under the Media section. Via this Media Tags management interface users can better manage the media tags used on the site. Deleting, renaming and adding new media tags is now quite simple. The Media Tags for the attachment are still display both under Media and the media popup on the editor screen as before. 

Because the media tags plugin now uses the WordPress Taxonomy system you can now access attachments via the new permalink '/media-tags/'. This is very powerful and one of the most requested features since the plugins initial release. 

Also included as part of the plugin rewrite are some handy template tags to use via your theme. Below are a list of the template functions available. These are located in the plugin file mediatags_template_functions.php. All these functions should seem very familiar since they were modeled after the built-in WordPress tags template functions. 

* is_mediatag() - Tests is we are displaying a media-tags archive. Much like is_category() function
* in_mediatag() - Tests is an attachment post marked in a certain mediatag_id.
* get_mediatags()
* list_mediatags() - Very handy for listing your media tags like list_tags() in the sidebar.php
* get_mediatag_link() - Given a mediatag_id this functon will return a link href value. 
* the_mediatags() - Very much like the post-level the_tags() to display a comma separated list of tags for a given post item. Used then displaying media tags archives. 
* single_mediatag_title() - Get the Title of the Archive.

Speaking of template tags you can now have even more control over the display of media tags archives. Much like the WordPress category template hierarchy you can now define a template file as part of your theme names 'mediatag.php' This is a special archive file tha can be used to display your loop of items. Alternately, you can also used ID specific template files like 'mediatag-25.php'. This is the same basic logic for category specific template file. It lets you use a special template file to display that specific media tag ID group. If the 'mediatag.php' template file is not provided then the default hierarchy or template files will be used starting with archive.php. Please not the template file when used will display attachments not the parent post. In WordPress all uploaded media is part of a parent/child association. Unlike the normal post related template the media tag template display the actual media files. 


Just want to display attachments in a post or page? Use the new media tags shortcodes.

 
[Plugin Homepage](http://www.codehooligans.com/2009/08/17/media-tags-2-2-plugin-for-wordpress-released/ "Media-Tags Plugin")


== Installation ==

1. Upload the extracted plugin folder and contained files to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. If you are upgrading from an earlier version of Media Tags the tags will be converted automatically when the plugin is activated.
4. Navigate to any Post/Page where a media file has been attached. Click on the 'Add Media' option on the post content icon bar. Select a media item from the Gallery. Click the 'Show' link to show the media item details. You will notice the new 'Media Tags' input field just below the WordPress Size option. 
4. All used tags will be displayed as checkboxes below the new Media Tag input field. 
5. Also all used media tags can be managed via the new Media Tags interface as part of the Media section in wp-admin. Management of Media tag is now part of a familiar interface like Tags and Categories. 
6. Tweak out your template to display media tags (optional).

== Frequently Asked Questions ==

= Ok I can now add tags to my images. So what, what can I do with them =

Excellent question. By default if you are uploading an image to a post then inserting the media link into your post there is no need for you to use this plugin as it will not provide any benefit for you. But consider this scenario. You have 5 images you want to upload for a post. Two of these images will be inserted into the actual post content but the other three you want to display in the sidebar. How would you do this? Normally, in the past I would make the 3 images used in the sidebar follow a specific filename convention like sidebar1.jpg, sidebar2.jpg and sidebar3.jpg. This get ugly if you are managing lots of images. I would be better ( and one reason I wrote this plugin) to allow the tagging of the 3 images as 'sidebar'. 

To actually get to the images for display in the sidebar you will need to have a way of filtering the attachments that are associated with a post. Luckily I've also included a simple function: `get_attachments_by_media_tags()` This function works similar to most other WordPress functions. 

There are a number of arguments that can be passed into the function to filter the media:

media_tags: (Required) This is a comma separated list of tags (slug) you want to filter on. 

media_types: (Optional) This is a comma separated list of media types - gif, pdf, png to return

search_by: (Optional) Default is 'slug'. Denotes what is passed in the 'media_tags' parameter. To search by media tag name pass the name into the 'media_tags' parameter and 'name' into the 'search_by' parameter. 

post_parent: (Optional) This is the post ID of the related media items. This item is no longer required. This allows you to query media_tag items across all posts

numberposts: (Optional) Default is all. Allows control over the number of items returned.

orderby: (Optional) Default menu_order. See get_posts() for full list of options

order: (Optional) Default 'DESC'. Controls the order of items. Other option is 'ASC'.

offset: (Offset) Default is 0. Allows control over the subset of items returned.

return_type: (Optional) Defaults to Array. Other option is 'li'. In the case of shortcodes the return type is 'li' by default. When using the 'li' return type each element is given a class of 'media_tag_list' and an id of 'media-tag-item-xxxx' where 'xxx' is the attachment id.

tags_compare: (Optional) Defaults to 'OR'. When requesting multiple media_tags elements allow a compare between the lists of items returned. When using the 'OR' option returned item will be in one or more of the requested media_tags values. Other value is 'AND'. When using 'AND' you are requesting only attachments which are in all requested media_tags. 

Actual examples of the function call are:
`
	$media_items = get_attachments_by_media_tags('media_tags=banner');
	This call will filter the tags for the tag 'banner'. 
	
	$media_items = get_attachments_by_media_tags('media_tags=sidebar,trees&media_types=pdf,xls&tags_compare=AND);
	This is another example. Filtering media based on the tags sidebar and trees. Also filtering the media type to be pdf or xls only. We are also specifying the 'AND' compare option to only return elements that are part of all media_tags.

	Note: in the earlier versions of the media tags plugin you had to call the function by way of the object like 
	
		$mediatags->get_media_by_tag(...);
		
	The use of the object as well as the 'get_media_by_tag' function has been deprecated. In the current version you can now access the function directly as in 'get_attachments_by_media_tags(...)'. The legacy $mediatags->get_media_by_tag() function is still supported for now. 
`

= I want to use the new shortcode feature to display attachments by media tag on a page =

Great. The media tags plugin now support the use of shortcodes. In its simplest form the media tag shortcode appears like 

	[media-tags media_tags="alt-views,page-full,thumb"]
	
Note you need to include quotes around the element value to ensure proper handling. The shortcode parameters available are pretty close to the direct function call. The media_tags parameter is the mediatag slug. The exception is you need to specify the parameter name and value pairs. 

	[media-tags media_tags="alt-views,page-full,thumb" tags_compare="AND" orderby="menu_order"]

When using the shortcode option the return type is automatically set the 'LI' which means list elements will be returned. When using shortcodes you can specific two parameters 'before_list=' and 'after_list=' to control the wrapper elements. Also, you can specify a new parameter 'display_item_callback='. This parameter will be a user defined function which allows you to control the output. The callback function will be called for each found element. Check the plugin file 'mediatags_shortcodes.php'. There is a function used 'default_item_callback()' which is the default callback actually used by the plugin. Remember when using shortcodes in WordPress you MUST return every output. Do not echo information.

Lastly, when using the shortcode feature you may have a time when you need to specify the post_parent for the media_tags queried. Now you could add the post_parent ID directly to the shortcode settings. But then you would be hard-coding the ID for the given post. A better way is to just set the post_parent value to 'this'. This will allow dynamic control over the shortcode processing. 

	[media-tags media_tags="alt-views,page-full,thumb" post_parent="this" before_list="<ul class='frank'>" after_list="</ul>"]

Note as of version 2.1.3 of the plugin the 'media_tags' parameter must be the slug. To search by name use the new 'search_by=' parameter. As follows:

	[media-tags media_tags="Italy" search_by="name" post_parent="this" before_list="<ul class='frank'>" after_list="</ul>"]

= I want to display the media tags in my sidebar =

You can use the new template function 'list_mediatags()'. This will provide a list of all media tags used in your system. Much like the WordPress list_tags() function. 

There are many more template functions available. Check out the functions listed in the 'mediatags_template_functions.php'. 


= I want to display listings of media tags like an archive. How can I do that? =

The new version (2.x) now support the use of a new permalink structure for displaying media tags archives. This is automatic when you activate the plugin. A simple way to see this in actual is to add the template function 'list_mediatags()' to your sidebar. This will list all media tags in your system complete with clickable links. When you click on one of the links you will see the URL is something like:

	http://www.yoursite.com/media-tags/<media tag slug>
	
By default the plugin will use the default WordPress templates in your theme directory. This means is you have a template file 'archive.php' it will be used to display the archive. If not then the 'index.php' template file will be used. Optionally, you can also use the new mediatags.php template file in your theme. This will let you control the display of media tags archives from other post archives. Also, you can create media tag specific template files like 'mediatag-xx.php' where 'xx' is the media tag ID. This follows similar convention for the built-in WordPress category template hierarchy. http://codex.wordpress.org/Category_Templates	


== Screenshots ==

1. The Media Tags example via the Media popup from the Post editor screen. 
2. The Media Tags displayed via the Media Management screen. 
3. The Media Tags Management screen (new!)

== Changelog == 
= 2.2.9 =
2010-06-08: Changes include:
* Corrected an taxonomy registration during the initializing of the plugin. Testing with WP 3.0 RC1. 

= 2.2.8 =
2010-04-22: Changes include:
* Some code tweaks to streamline the logic.
* Corrected an initializing issue with the plugin that effected the init process which in turn effected the rewrite setup and use of the mediatag.php template file. 

= 2.2.7 =
2010-04-22: Changes include:
* Some code tweaks to streamline the logic.

* Bug fix: Better Init method. Thanks to Mike Schinkel for pointing out the error of my ways on this. Also for suggesting using the WP_DEBUG to make sure I have all the holes on the dike plugged. 

* Bug fix: Erronious compare argument on the activate logic media_tags.php in the init function. Thanks to Tom for that note http://www.codehooligans.com/2009/08/17/media-tags-2-2-plugin-for-wordpress-released/#comment-48664

* Bug fix: Fixed some hard-coded table name prefixes. To all I apologize for this issue. For some reason early code I lifted from another plugin I didn't scan. In the mediatags_rewrite.php where the SQL WHERE is manipulated for matching the rewrite URL the queries had hard-coded prefixed as in 'wp_posts.', etc. This prevents the Media Tags plugin from working on non-standard database setup and also for WPMU. This should now be working. 

* Some initial testing with WordPress 3.0 Beta 1. Things seem to work fine with this plugin. But open for further testing. 

= 2.2.6 = 
2010-01-30: Changes include:
* Some code tweaks to streamline the logic.

* Added RSS output option to Media-Tag Settings page. When enabled will allow direct RSS for an item archive. for example given a Media-Tag archive like http://www.somesite.com/media-tags/my-tag where my-tag is a Media-Tag item you can access the RSS by accessing http://www.somesite.com/media-tags/my-tag/feed.

* Export/Import logic for Media Tags. I've utilized form action in WordPress that allow complete export and import of Media-Tags elements when using the WordPress export Tool. There is currently not a stand alone method to just export Media-Tags.

* Coming soon a few Media-Tags widgets. 

= 2.2.5 =
2009-08-30: Changes include:
* Bug fixes to Admin screens. Namely one but for the Permalink slug field. 

* Added a 'View' option on the Media Tags Management screen on the quite menu options. This will let you preview the media-tag item in your theme. 

* Added new function for mediatags_cloud(); This will generate the tag cloud or tags. Note this new function is a wrapper for the new WordPress core function wp_tg_cloud() added in 2.8. If used on an older version of WordPress there will be no output. http://codex.wordpress.org/Template_Tags/wp_tag_cloud

* Added a column to the Media Library view. This new column lists the item's media tags. The content in this column is much like the Tags column on the Posts listing. The Media Tag is linked so it will filter the Media Library display. Thanks to the many commentors for that simple item. 

* Added some logic to split the media tags into sections on both the Media Library item view as well as the Media Upload view. The thought here is to display the media tags in three sets. The first set is the items' select media-tags. The second set is the common media tags. The third set is the uncommon media tags.

* Code cleanup.

* Coming in some future release will be a bulk management option. This option will allow you to select item(s) from your Media Library and set the media tag. Still working on the interface logic. 

= 2.2 =
2009-08-16: Changes include:
* New Media Tags tab on the media upload popup. Now you can search by the Media Tag items. Functionality similar to Media section.

* Now you can control the permalink prefix which was previously hard-coded as '/media-tags/'. Go to Settings -> Permalinks. You should see a new input field below the Category and Tag fields. This lets you use something unique to your site.

* Integration with some other plugins. The first to be added is the famous Google XML Sitemaps plugin. Now when you build your XML Sitemap you can include Media Tag URLs just like WordPress Categories and Tags. Look for the options under Settings -> Media Tags (New menu).

* Some general code cleanup. Namely a conflict the Media Tags plugin was causing with Role Scoper and some other plugins. 

* Renamed the original plugin file from 'meta_tags.php' to 'media_tags.php'.

= 2.1.3 =
* 2009-08-06: Changes include a addition of new template function 'single_mediatag_title()' for use on the archive.php template or the new mediatag.php template file. Thanks to Carlos for the comment regarding this. http://www.codehooligans.com/2009/07/15/media-tags-20-released/comment-page-1/#comment-42956

Also included are some small changes to cleanup the core media tag search code. One change as suggested by Jozik http://www.codehooligans.com/2009/07/15/media-tags-20-released/comment-page-1/#comment-42927 to correct the get_terms argument to search by slug as default. To search by name you may use the 'search_by=name' function argument. This new 'search_by' parameter is also supported via the shortcodes. 

= 2.1.2 =
* 2009-07-24: Changes include a bug in the SQL 'Where' parsing to display media tags via the site. The bug prevented non-authenticated users from seeing the items. Correct SQL Where parsing. Thanks for Francisco Ernesto Teixeira for find this issue and reporting it http://www.codehooligans.com/2009/07/15/media-tags-20-released/#comment-42663

= 2.1.1 =
* 2009-07-23: Changes to fix relative paths when WordPress is not installed into the root. Thanks to Ilan Y. Cohen for the tip on this bug. 

= 2.1 =
* 2009-07-23: Changes include code cleanup and correcting link under new Media-Tags Management screen.

= 2.0 =
* 2009-07-15: Major update to the plugin. New Permalink, shortcodes, Media Tags management interface.

= 1.0.1 =
* 2009-02-04: Found an issue with the returned attachment when calling get_posts. Changed this to get_children.

= 1.0 =
* 2008-12-13: Initial release
