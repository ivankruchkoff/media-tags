<?php
/* Functions here handle all needed feed/rss functionality */

function add_mediatags_alternate_link() 
{
	global $wp_version;
	
	$mediatag_rss_feed = get_option('mediatag_rss_feed', 'yes');
	if ((!$mediatag_rss_feed) || ($mediatag_rss_feed != "yes"))
		return;

	$mediatag_var = get_query_var(MEDIA_TAGS_QUERYVAR);
	if ($mediatag_var)
	{	
		$mediatag_term = term_exists( $mediatag_var, MEDIA_TAGS_TAXONOMY );
		if ($mediatag_term)
		{
			$mediatag_term = get_term($mediatag_term['term_id'], MEDIA_TAGS_TAXONOMY);
			$feed_title = get_bloginfo('name') . " &raquo; ". __('Media-Tags RSS Feed', MEDIA_TAGS_I18N_DOMAIN) 
				." &raquo; " . $mediatag_term->name;

			$feed_link = get_mediatag_link( $mediatag_term->term_id, true );
			if ($feed_link)
			{
				?><link id="MediaTagsRSS" rel="alternate" type="application/rss+xml"
					title="<?php echo esc_attr( $feed_title ); ?>"
					href="<?php echo esc_attr( $feed_link ); ?>" />
				<?php
			}
		}
	}
}
