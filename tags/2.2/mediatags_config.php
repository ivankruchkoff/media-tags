<?php
define('MEDIA_TAGS_TAXONOMY', 'media-tags');

define('ADMIN_MENU_KEY', 'media-tags');
define('MEDIA_TAGS_REWRITERULES','1');

define('MEDIA_TAGS_URL_DEFAULT', MEDIA_TAGS_TAXONOMY);
$mediatag_base = get_option('mediatag_base');
// Need to come up with validation logic here.
if (!$mediatag_base)
	$mediatag_base = "media-tags";
define('MEDIA_TAGS_URL', $mediatag_base);

define('MEDIA_TAGS_QUERYVAR', 'media-tag');

define('MEDIA_TAGS_TEMPLATE', 'mediatag.php');

?>