<?php

// ----------------------------------------------------------------------------- FLEXIBLE

function filter_flexible ( $flexible )
{
	$output = [];
	$debug = 0 && env('WP_DEBUG', false);

	if ( is_array($flexible) ) foreach ( $flexible as $item )
	{
		$debug && dump($item);
		$type = $item['acf_fc_layout'];
		$blockData = [];

		// --------------------------------------------------------------------- QUOTE
		if ( $type === 'quote' )
		{
			$blockData = [
				'content' => nl2br( get_translation($item, 'quote') ),
				'background' => $item['background'], 	// none / bright
				'alignment' => $item['alignment'], 	// center / left
			];
		}

		// --------------------------------------------------------------------- PORTFOLIO
		if ( $type === 'portfolio' )
		{
			// Associated portfolio post id
			$postID = $item['link']->ID;

			// Get embedded custom image
			if ( isset($item['parent-image']['image']) && !empty($item['parent-image']['image']) )
				$image = $item['parent-image']['image'];

			// If not defined, get portfolio's introduction image
			else {
				$intro = filter_introduction( field('portfolio-introduction', $postID) );
				$image = $intro['image'];
			}

			$blockData = [
				'image' => $image,
				// Get embedded title if defined, otherwise get portfolio page title
				'title' => html_entity_decode(
					get_translation($item, 'title', function () use ($postID) {
						return acf_get_post_title( $postID );
					})
				),
				// Get embedded sub-title if defined, otherwise get portfolio sidebar title
				'sub-title' => get_translation($item, 'sub-title', function () use ($postID) {
					$sidebar = field('portfolio-sidebar', $postID);
					return get_translation($sidebar, 'sub-title');
				}),
				'description' => get_translation($item, 'description'),
				'href' => get_permalink( $postID ),
				'position' => $item['position'], // left / right / compact
				'offset' => $item['offset'],
			];
		}

		// --------------------------------------------------------------------- ARGUMENT LIST
		if ( $type === 'argument-list' )
		{
			$list = get_translation($item, 'arguments');
			$blockData = [
				'image' => $item['image'],
				'title' => get_translation($item, 'title'),
				'list' => $list,
				'total' => count( $list )
			];
		}

		// --------------------------------------------------------------------- IMAGES
		if ( $type === 'images' )
		{
			$size = implode('', explode('-', $item['size']));
			$blockData = [
				'columns' => [
					filter_image( $item['columns'], 'parent-image-1', 'image-1' ),
					filter_image( $item['columns'], 'parent-image-2', 'image-2' ),
				],
				'size' => $size,
				'verticalAlign' => $item['vertical-align'],
				'horizontalAlign' => $item['horizontal-align'],
			];
			$blockData['oneImage'] = (
				!$blockData['columns'][1]
				&& $size != '1080' && $size != 'full'
			);
		}

		// --------------------------------------------------------------------- TESTIMONIALS
		if ( $type === 'testimonial' )
		{
			$blockData = [
				'title' => get_translation($item, 'title', ''),
				'subTitle' => nl2br( get_translation($item, 'sub-title', '') ),
				'description' => nl2br( get_translation($item, 'description', '') ),
				'image' => $item['image'],
				'position' => $item['position'], 	// left / right
				'format' => $item['format'], 		// slim / large
			];
		}

		// --------------------------------------------------------------------- RICH TEXT COLUMNS
		if ( $type === 'rich-text-columns' )
		{
			$blockData = [
				'columns' => [
					get_translation( $item['columns'], 'content-1' ),
					get_translation( $item['columns'], 'content-2' ),
				]
			];
			// Invert if first column is null and second is not null
			if ( is_null($blockData['columns'][0]) && !is_null($blockData['columns'][1]) )
				$blockData['columns'] = [
					$blockData['columns'][1], $blockData['columns'][0]
				];
			// Check if we have only one column
			if ( is_null($blockData['columns'][1]) )
				$blockData['lonelyColumn'] = true;
		}

		// --------------------------------------------------------------------- ARTICLE TITLE
		if ( $type === 'article-title' )
		{
			$blockData = [
				'content' => nl2br( get_translation($item, 'content', '') ),
				'size' => $item['size']
			];
		}

		// --------------------------------------------------------------------- ARTICLE RICH TEXT
		if ( $type === 'article-rich-text' )
		{
			$blockData = [
				'content' => get_translation($item, 'content', '')
			];
		}

		// --------------------------------------------------------------------- ARTICLE IMAGE
		if ( $type === 'article-image' )
		{
			$blockData = [
				'image' => $item['image'],
				'overflow' => $item['overflow']
			];
		}

		// --------------------------------------------------------------------- ARTICLE REGULAR TEXT
		if ( $type === 'article-regular-text' )
		{
			$blockData = [
				'content' => nl2br( get_translation($item, 'content', '') ),
				'rendering' => $item['rendering']
			];
		}

		// --------------------------------------------------------------------- TYPE ANCHOR
		if ( $type === 'anchors')
		{
			$blockData = [
				'list' => []
			];
			$previousAnchor = &$blockData['list'];
		}

		// --------------------------------------------------------------------- TYPE BLOC
		if ( $type === 'type-bloc')
		{
			//$typeBlockID = isset($typeBlockID) ? $typeBlockID + 1 : 0;
			$anchor = get_translation( $item, 'anchor' );
			$typeBlockID = sanitize_title( $anchor );
			$calling = get_translation( $item, 'calling' );
			$blockData = [
				'id' => $typeBlockID,
				'title' => get_translation( $item, 'title' ),
				'content' => get_translation( $item, 'content' ),
				'image' => $item['image'],
				'calling' => (
					( is_null($calling) || empty($calling) )
					? []
					: explode("\n", $calling)
				)
			];
			if ( isset($previousAnchor) )
				$previousAnchor[] = [
					'text' => $anchor,
					'id' => $typeBlockID
				];
		}

		// --------------------------------------------------------------------- GALLERY
		if ( $type === 'gallery' )
		{
			if ( is_null($item['images']) || empty($item['images']) ) continue;
			$images = [];
			foreach ( $item['images'] as $image ) {
				if ( !$image['image'] ) continue;
				$images[] = $image['image'];
			}
			$blockData = ['images' => $images];
		}
		// --------------------------------------------------------------------- VIDEO
		if ( $type === 'video' )
		{
			if ( !$item['video'] ) continue;
			$blockData = [
				'video' => $item['video'],
				'poster' => $item['poster']
			];
		}

		// --------------------------------------------------------------------- LINKS
		if ( isset($item['link']) && !!$item['link'] )
			$blockData['link'] = [
				'href' => $item['link'],
				'text' => get_translation($item, 'link-text', get_dictionary('flexible.knowMore')),
				'type' => isset($item['link-type']) ? $item['link-type'] : 'default'
			];


		// Add to clean output list
		$output[] = array_merge( [ 'type' => $type ], $blockData );
	}

	$debug && dump($output);

    return $output;
}

// -----------------------------------------------------------------------------

function filter_introduction ( $data, $defaultTitle = null, $otherData = [] )
{
	$output = array_merge(
		filter_image( $data, 'parent-image'), [
			'title' => nl2br( get_translation($data, 'custom-title', $defaultTitle) ),
		]
	);

	$introText = get_translation($data, 'intro-text');
	if (!is_null($introText))
		$output['introText'] = nl2br( $introText );

	$descriptionText = get_translation($data, 'description');
	if (!is_null($descriptionText))
		$output['descriptionText'] = nl2br( $descriptionText );

	if (isset($data['sidebar-align']) && !is_null($data['sidebar-align']))
		$output['sidebarAlign'] = !!$data['sidebar-align'];

	return array_merge($output, $otherData);
}

// -----------------------------------------------------------------------------

function filter_image ( $data, $key, $imageKey = 'image' )
{
	$output = [];
	if ( isset($data[$key]) && !is_null($data[$key]) && $data[$key] !== false && $data[$key][ $imageKey ] !== false )
	{
		$output = [
			'image' => $data[$key][ $imageKey ],
		];

		if ( isset($data[$key][$imageKey.'-width']) )
			$output['imageWidth'] = get_default_value($data[$key], 'image-width', 'regular'); // regular / thin

		$imageLabel = get_translation($data[$key], 'content' );
		if ( !is_null($imageLabel) )
			$output['label'] = [
				'content' => nl2br( $imageLabel ),
				'alignment' => $data[$key]['alignment'] // left / right
			];
	}
	return $output;
}

function filter_contact ( $data, $key )
{
	$contact = false;
	if ( isset($data[$key]) && $data[$key]['enabled'] ) {
		$portfolioContact = $data[$key];
		$contact = [
			'title' => nl2br( get_translation($portfolioContact, 'title', '') ),
			'mention' => get_translation($portfolioContact, 'mention'),
			'button' => [
				'href' => get_permalink( get_custom_page_id('contact') ),
				'text' => get_dictionary('contact.contactButton')
			]
		];
	}
	return $contact;
}

// ----------------------------------------------------------------------------- POST

function filter_post ( $post, $preview = true )
{
	$author = get_post_field('post_author', $post);
    $data =  [
    	'id' => $post->ID,
        'title' => $post->post_title,
        //'author' => [
        	//'collaborateur' => get_collaborateur_by_author($author),
	        //'name' => get_the_author_meta('display_name', $author)
        //],
        'image' => get_the_post_thumbnail_url($post, $preview ? 'large' : '1536x1536'),
        'categories' => array_map(
            function ( $categoryID ) {
                return [
                    'id' => $categoryID,
                    'name' => get_the_category_by_ID($categoryID),
                    'href' => get_category_link( $categoryID )
                ];
            }, wp_get_post_categories($post->ID)
        ),
        'postDate' => $post->post_date,
        'date' => parse_date( $post->post_date ),
        'href' => get_permalink( $post )
    ];

    if ( $preview )
    {
        $data['excerpt'] = get_the_excerpt( $post ) ?? $post->post_content;
        $data['highlight'] = field( 'highlight', $post ) ?? false;
    }
    else
    {
	    $content = $post->post_content;
	    // Spécial de son ancien bloc, saut de ligne ESPACE saut de ligne ...
	    $content = str_replace("\n&nbsp;\n", "<br>", $content);
	    // nl2br pour prendre en compte les jump jump dans le back
	    $content = str_replace("\r\n", "<br>", $content);
	    // Virer les doubles sauts de lignes
	    $content = str_replace("<br><br>", "<br>", $content);
    	$data['content'] = $content;
    }

    return $data;
}

function parse_date ( $date )
{
    // Convert string to date time object
    $dateTime = new DateTime( $date );

    // Get current locale object (with date format and iso code)
    $localeObject = get_current_locale_object();

    // Set locale for translations, only once
    global $localeHasBeenDefined;
    if ( !isset($localeHasBeenDefined) ) {
        setLocale(LC_TIME, $localeObject['locale']);
        $localeHasBeenDefined = true;
    }

    // Convert date time to string with configured date format from wp-admin
    $formattedDate = strftime( $localeObject['date'], $dateTime->getTimestamp() );

    return (
    	!!env('FORCE_UTF8', false)
        ? utf8_encode($formattedDate)
	    : $formattedDate
    );
}
