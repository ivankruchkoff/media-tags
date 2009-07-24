<?php
function legacy_load_master_media_tags()
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

function legacy_get_post_media_tags($post_id, $post_media_tags_list)
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
	return $post_media_tags;
}

?>