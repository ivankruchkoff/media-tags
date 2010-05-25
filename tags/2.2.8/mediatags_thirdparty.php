<?php
function mediatags_google_sitemap_pages()
{
	$mediatag_google_plugin = get_option('mediatag_google_plugin');
	if ((!$mediatag_google_plugin) || ($mediatag_google_plugin != "yes"))
		return;
		
	$generatorObject = &GoogleSitemapGenerator::GetInstance(); //Please note the "&" sign!
	if($generatorObject!=null) 
	{
		$mediatag_items = get_mediatags();
		if ($mediatag_items)
		{
			foreach($mediatag_items as $mediatag_item)
			{
				$mediatag_permalink = get_mediatag_link($mediatag_item->term_id);
				if (strlen($mediatag_permalink))
				{
					$generatorObject->AddUrl($mediatag_permalink, time(), "daily", 0.5);
				}				
			}
		}
	}	
}
?>