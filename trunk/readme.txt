=== Media Tags ===
Contributors: Paul Menard
Donate link: http://www.codehooligans.com
Tags: images, tags, media
Requires at least: 2.6.5
Tested up to: 2.7
Stable tag: 1.0.1

== Description ==

Adds an input to the media upload and management screens. This input field can be used to "tag" a media file. Works with images, documents anything.

[Plugin Homepage](http://www.codehooligans.com/2008/12/14/media-tags-plugin/ "Media-Tags Plugin")



== Installation ==

1. Upload the extracted plugin folder and contained files to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to any Post/Page where a media file has been attached. Click on the 'Add Media' option on the post content icon bar. Select a media item from the Gallery. Click the 'Show' link to show the media item details. You will notice the new 'Media Tags' input field just below the WordPress Size option. 
4. All used tags will be displayed as checkboxes below the new Media Tag input field. 

== Frequently Asked Questions ==

= Ok I can now add tags to my images. So what, what can I do with them =

Excellent question. By default if you are uploading an image to a post then inserting the media link into your post there is no need for you to use this plugin as it will not provide any benefit for you. But consider this scenario. You have 5 images you want to upload for a post. Two of these images will be inserted into the actual post content but the other three you want to display in the sidebar. How would you do this? Normally, in the past I would make the 3 images used in the sidebar follow a specific filename convention like sidebar1.jpg, sidebar2.jpg and sidebar3.jpg. This get ugly if you are managing lots of images. I would be better ( and one reason I wrote this plugin) to allow the tagging of the 3 images as 'sidebar'. 

To actually get to the images for display in the sidebar you will need to have a way of filtering the attachments that are associated with a post. Luckily I've also included a simple function: `get_media_by_tag()` This function works similar to most other WordPress functions. 

There are a number of arguments that can be passed into the function to filter the media:

image_tags: (Required) This is a comma separated list of tags you want to filter on. 
image_types: (Optional) This is a comma separated list of media types - gif, pdf, png to return
post_parent: (Optional) This is the post ID of the related media items. If this parameter is not provided then the current post from the loop is assumed.

Actual examples of the function call are 
`
	$media_items = $mediatags->get_media_by_tag('media_tags=banner&post_parent=6');
	This call will filter the tags for the tag 'banner' and the post ID of 6. 
	
	$media_items = $mediatags->get_media_by_tag('media_tags=sidebar,trees&media_types=pdf,xls&post_parent='.$post->ID);
	This is another example. Filtering media based on the tags sidebar and trees. Also filtering the media type to be pdf or xls only.

	Note the object $mediatags. This is the global instance of the plugin object. If you are calling the function from within a function you may need to add 'global $mediatags;' inside your function code.
`
== Screenshots ==

1. The Media Tags example via the Media popup from the Post editor screen. 
2. The Media Tags displayed via the Media Management screen. 


== Version Histroy == 
<p>
1.0 - 2008-12-13: Initial release<br />
1.0.1 - 2009-02-04: Found an issue with the returned attachment when calling get_posts. Changed this to get_children.<br />
</p>
