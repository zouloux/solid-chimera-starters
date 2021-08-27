<?php

function filter_javascript_app_data ( $data, $context )
{
	// Toujours virer la page sur ce projet
	unset($data['page']);

	// Virer les infos du layout
	unset($data['analytics']);
	unset($data['gtm']);
	unset($data['contact']);
	unset($data['contactButton']);
	unset($data['resources']);
	unset($data['footer']);
	unset($data['imageSizes']);
	unset($data['menu']);
	unset($data['referer']);
	unset($data['transition']);
	unset($data['dictionary']);

	return $data;
}