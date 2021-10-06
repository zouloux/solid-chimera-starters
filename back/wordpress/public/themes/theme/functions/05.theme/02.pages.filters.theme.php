<?php

// ----------------------------------------------------------------------------- ALL PAGES FILTERS

register_page_data_filter('*before', function ( $data ) {

	// Filter all flexible
	if ( isset($data['flexible']) && is_array($data['flexible']) )
		$data['flexible'] = filter_flexible( $data['flexible'] );

	return $data;
});
register_page_data_filter('*after', function ( $data ) {
	return $data;
});

// -----------------------------------------------------------------------------
