<?php

use WordPlate\Acf\FieldGroup;
use WordPlate\Acf\Fields\Group;
use WordPlate\Acf\Location;

// ----------------------------------------------------------------------------- REGISTERING ACF

function remove_editor_for_post ( $postID, $postType = null ) {
	add_filter( 'admin_head', function () use ($postID, $postType) {
		global $post;
		if (
			!is_null($post) && (
				( !is_null($postID) && $post->ID == $postID )
				|| ( !is_null($postType) && $post->post_type == $postType )
			)
		) {
			remove_post_type_support( 'page', 'editor' );
		}
	});
}

/**
 * Register a custom screen. Types can be :
 * - post_type : for a new custom post type
 * - page : to add specific custom fields to a page id
 * - option_page : to add a unique option page (no collection like post_type)
 *
 *
 * === POST TYPE ===
 * - name : Key of the new post_type
 * - labels = ["unique", "multiple"] : Labels in menu and edit page.
 * - icon : dashicon used in menu
 *
 * Ex : register_custom_screen('post_type', [
 *  'name' => 'talents',
 *  'labels' => ['un talent', 'Talents'],
 *  'icon' => 'dashicons-groups'
 * ], ...);
 *
 *
 * === PAGE ===
 * - name : Key of the new page type
 * - page : id of page (you may need a function like get_custom_page_id which store specific custom pages id)
 * - editor : If false, remove classic WP editor
 * - restrictDeletion : If true, will disable admin ability to remove this page.
 *
 * Ex : register_custom_screen('page', [
 *  'name' => 'cabinet',
 *  'page' => get_custom_page_id('cabinet'),
 *  'editor' => false,
 *  'restrictDeletion' => true
 * ], ...);
 *
 *
 * === OPTION PAGE ===
 * - name : Key of the new post_type
 * - label : Label in menu
 * - icon : dashicon used in menu
 *
 * Ex : register_custom_screen('options_page', [
 *  'name' => 'site-options',
 *  'label' => "Options du site",
 *  'icon' => 'dashicons-admin-generic'
 * ], ...);
 *
 * @param string $type post_type / page / option_page
 * @param array $screen Screen options
 * @param array $groups Groups fields to add
 *
 * @return mixed
 * @throws Exception
 */
function register_custom_screen ( $type, $screen, $groups )
{
	// Default options
	$options = isset($screen['options']) ? $screen['options'] : [];

	// If we got at least 1 translation in those fields (default is true)
	$isMultilang = !isset($screen['multilang']) || $screen['multilang'];

	// Set location to add groups to correct screen
	$location = [ Location::if( $type, $screen['name'] ) ];

	// For custom post types (multiton)
	if ( $type === 'post_type' )
	{
		$screen['id'] = $screen['name'];
		$orderHookName = $screen['name'];

		// Do not re-declare post as a post type
		if ( $screen['name'] != 'post' && $screen['name'] != 'page' )
		{
			// Register this post type
			global $_bowlAllRegisteredPostTypes;
			if (is_null($_bowlAllRegisteredPostTypes))
				$_bowlAllRegisteredPostTypes = [];
			$_bowlAllRegisteredPostTypes[] = $screen['id'];

			$options = array_merge([
				'label' => $screen['labels'][1],
				'public' => true,
				'has_archive' => false,
				'show_in_rest' => true,
				'supports' => ['title'],
				'menu_position' => 5,
				'menu_icon' => $screen['icon'],
			], $options);

			// Register this post type at wordpress init
			add_action( 'init', function () use ($screen, $options) {
				register_post_type( $screen['name'], $options);
			});

			// Register this custom post type as multi lang
			if ( $isMultilang )
				add_filter( 'wpm_post_'.$screen['id'].'_config', function () {
					return [];
				});

			// Disable sitemap on this post type
			if ( isset($screen['sitemap']) && $screen['sitemap'] === false ) {
				global $_bowlSitemapRemovedPostTypes;
				if (is_null($_bowlSitemapRemovedPostTypes))
					$_bowlSitemapRemovedPostTypes = [];
				$_bowlSitemapRemovedPostTypes[] = $screen['name'];
			}
		}

		// All pages
		else if ( $screen['name'] == 'page' )
		{
			// Do not execute on custom page IDs
			if ( isset($screen['not']) )
				foreach ($screen['not'] as $key => $name)
					$location[0]->and( 'page', '!=', $name );

			// FIXME : Faire en sorte que le remove_editor_for_post prenne en compte le "not"
			// FIXME : Car là ça vire pour toutes les pages

			// Remove Wysiwyg editor from options
			!$screen['editor'] && remove_editor_for_post( null, 'page' );

			// Add meta to fields
			if ( !isset($screen['noMeta']) || $screen['noMeta'] !== true )
				$groups['meta'] = create_meta_fields_group();
		}
	}

	// For options page (singleton)
	else if ( $type === 'options_page' )
	{
		$screen['id'] = 'toplevel_page_'.$screen['name'];

		// Register options page with ACF
		acf_add_options_page(array_merge([
			'menu_slug' => $screen['name'],
			'page_title' => $screen['label'],
			'icon_url' => $screen['icon'],
			'position' => $screen['position'] ?? 5
		], $options));

		// Register this options page type as multi lang
		if ( $isMultilang )
			add_filter('wpm_admin_pages', function ($config) use ($screen) {
				$config[] = $screen['id'];
				return $config;
			});
	}

	// For specific pages (with id)
	else if ( $type === 'page' )
	{
		$orderHookName = 'page';

		// Override location for specific pages (we look for page id and not name)
		$location = [];
		if ( !is_array($screen['page']) )
			$screen['page'] = [ $screen['page'] ];
		foreach ( $screen['page'] as $pageID )
			$location[] = Location::if( $type, $pageID );

		// Remove Wysiwyg editor from options
		!$screen['editor'] && remove_editor_for_post( $screen['name'] );

		// Disallow deletion for this page from options
		if ( $screen['restrictDeletion'] )
		{
			$restrict_post_deletion = function ( $postID ) use ( $screen ) {
				if ( in_array($postID, $screen['page']) )
					show_admin_error_message("Désolé, il n'est pas possible de supprimer cette page.");
			};
			add_action('wp_trash_post', $restrict_post_deletion, 10, 1);
			add_action('delete_post', $restrict_post_deletion, 10, 1);
		}

		// Add meta to fields
		if ( !isset($screen['noMeta']) || $screen['noMeta'] !== true )
			$groups['meta'] = create_meta_fields_group();
	}

	// Invalid custom screen type
	else throw new Exception("Invalid custom screen type $type");

	// Patch admin custom screen
	patch_admin_custom_screen( $screen );

	// Ordered IDs of field groups
	$fieldGroupsIDOrders = [];

	// Process all groups for this screen
	foreach ( $groups as $key => $group )
	{
		$groupOptions = isset($group['options']) ? $group['options'] : [];

		if ( isset($groups['key']) )
			$key = $groups['key'];

		// Set a key from screen and group name to avoid collisions across screens
		$key = acf_slugify($screen['name']).'-'.$key;

		// Create FieldGroup
		$fieldGroupObject = new FieldGroup(array_merge([
			'title' => $group['title'] ?? '',
			'key' => $key,
			// Set layout to non-null will show collapsible blocks
			'layout' => (isset($group['noBorders']) && $group['noBorders'] ? null : ''),
			'location' => $location,
			'fields' => (
				// If asGroup is disabled, directly show fields without parent group
				( isset($group['asGroup']) && !$group['asGroup'] )
				? $group['fields']
				// By default, show fields inside a nameless group
				: [
					// We use the unique key here to avoid collisions
					Group::make(' ', isset($group['key']) ? $group['key'] : $key )
						->layout('row')
						->instructions( $group['instructions'] ?? '' )
						->fields( $group['fields'] )
				]
			)
		], $groupOptions));

		// Convert to array and store key to order it later
		$fieldGroupArray = $fieldGroupObject->toArray();
		$fieldGroupsIDOrders[] = 'acf-'.$fieldGroupArray['key'];

		// Register this field group
		register_field_group($fieldGroupArray);
	}

	// If we have info on field group orders
	if ( isset($orderHookName) )
	{
		// Setup field group orders by type
		global $_bowlAllFieldGroupOrders;

		if (!isset($_bowlAllFieldGroupOrders))
			$_bowlAllFieldGroupOrders = [];

		if (!isset($_bowlAllFieldGroupOrders[$orderHookName]))
			$_bowlAllFieldGroupOrders[ $orderHookName ] = [];

		// Add them by custom post type
		// We do this because for the custom post type "page", we have only 1 hook
		// So we will just concat all field orders for every pages into the CPT "pages"
		// It works because WP admin will use only fields in current page
		$_bowlAllFieldGroupOrders[ $orderHookName ][] = $fieldGroupsIDOrders;
	}

	return $screen;
}

// We inject field group orders after all fields are declared
add_action('after_functions', function ()
{
	// Browse all field group orders
	global $_bowlAllFieldGroupOrders;
	if (is_null($_bowlAllFieldGroupOrders)) return;
	foreach ( $_bowlAllFieldGroupOrders as $orderHookName => $fieldGroupOrders )
	{
		// Concat all field groups orders for this custom post type
		$allFieldGroupOrdersForHook = [];
		foreach ( $fieldGroupOrders as $currentFieldGroupOrder )
			$allFieldGroupOrdersForHook = array_merge($allFieldGroupOrdersForHook, $currentFieldGroupOrder);

		// Hook meta box order for this custom post type
		$hookName = 'get_user_option_meta-box-order_'.$orderHookName;
		add_filter($hookName , function () use ($allFieldGroupOrdersForHook) {
			return [
				// Force order with Yoast on top
				'normal' => join(',', array_merge([
					'wpseo_meta',
				], $allFieldGroupOrdersForHook))
			];
		});
	}
});