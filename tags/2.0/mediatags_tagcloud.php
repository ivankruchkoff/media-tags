<?php

function mediatags_tag_cloud( $args = '' ) {
	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
		'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
		'exclude' => '', 'include' => '', 'link' => 'view'
	);
	$args = wp_parse_args( $args, $defaults );

	echo "args<pre>"; print_r($args); echo "</pre>";

	$mediatags = get_mediatags( array_merge( $args, array( 'orderby' => 'count', 'order' => 'DESC' ) ) ); // Always query top tags
	//echo "mediatags<pre>"; print_r($mediatags); echo "</pre>";

	if ( empty( $mediatags ) )
		return;

	$mediatags_structure = $wp_rewrite->front . MEDIA_TAGS_URL . "/".$mediatags_token;	
	foreach ( $mediatags as $key => $mediatag ) {
		$mediatag_link = mediatags_get_link( $mediatag->term_id );
		if ( is_wp_error( $mediatag_link ) )
			return false;

		$mediatags[ $key ]->link 	= $mediatag_link;
		$mediatags[ $key ]->id 		= $mediatag->term_id;
	}

	echo "mediatags<pre>"; print_r($mediatags); echo "</pre>";


//	$return = wp_generate_tag_cloud( $tags, $args ); // Here's where those top tags get sorted according to $args

//	$return = apply_filters( 'wp_tag_cloud', $return, $args );

	if ( 'array' == $args['format'] )
		return $return;

	echo $return;
}

?>