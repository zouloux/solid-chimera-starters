<?php

// ----------------------------------------------------------------------------- HOME PAGE

//const BOWL_DEBUG_PAGE_DATA = true;
register_page_data_filter('home', function ( $data ) {

	return $data;
});