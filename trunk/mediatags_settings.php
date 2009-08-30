<?php
function mediatags_settings_api_init() {
	
	if (isset($_POST['mediatag_base']))
	{
		update_option( 'mediatag_base', $_POST['mediatag_base'] );
	}
	
	if (function_exists('add_settings_field'))
	{
		// Add a new field to the Permalinks Options section to allow override of the default 'media-tags' slug.
		add_settings_field('mediatag_base', 'Media-Tags', 'mediatags_setting_permalink_proc', 'permalink', 'optional');
	}
}
  
function mediatags_setting_permalink_proc() {

	$mediatag_base = get_option('mediatag_base');
	if (!$mediatag_base)
		$mediatag_base = "media-tags";
		
	?><input name="mediatag_base" id="mediatag_base" type="text" 
	value="<?php echo $mediatag_base; ?>" class="regular-text code" /> 
	(<i>default is '<?php echo MEDIA_TAGS_URL_DEFAULT ?>'</i> )<br />
	<strong>Note</strong> Be careful not to use a prefix that may conflict with other WordPress standard prefixes like 'category', 'tag', a Page slug, etc<?php
} 



?>