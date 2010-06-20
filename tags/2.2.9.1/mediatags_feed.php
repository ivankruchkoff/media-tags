<?php
/* Functions here handle all needed feed/rss functionality */

function add_mediatags_alternate_link() {
	
	// 
	$mediatag_rss_feed = get_option('mediatag_rss_feed');
	if ((!$mediatag_rss_feed) || ($mediatag_rss_feed != "yes"))
		return;

	if (is_MEDIA_TAGS_URL()) 
	{
		$mediatag_var = get_query_var(MEDIA_TAGS_QUERYVAR);
		//echo "mediatag_var<pre>"; print_r($mediatag_var); echo "</pre>";
		if ($mediatag_var)
		{	
			$mediatag_term = is_term( $mediatag_var, MEDIA_TAGS_TAXONOMY );
			if ($mediatag_term)
			{
				$get_terms_args['hide_empty'] = 0;
				$get_terms_args['slug'] = $mediatag_var;
				$terms_item = get_terms( MEDIA_TAGS_TAXONOMY, $get_terms_args );
				if ($terms_item)
				{
					$terms_item = $terms_item[0];
					//echo "terms_item<pre>"; print_r($terms_item); echo "</pre>";
					$feed_title = get_bloginfo('name') . " &raquo; Media-Tags RSS Feed &raquo; " . $terms_item->name;
					//echo "feed_title=[".$feed_title."]<br />";
					?><link id="MediaRSS" rel="alternate" type="application/rss+xml"
					title="<?php echo $feed_title; ?>" 
					href="<?php echo get_mediatag_link($terms_item->term_id, true); ?>" />
					<?php
				}
			}
		}
	}
}
?>
