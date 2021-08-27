<?php

function rest_api_post_contact ( WP_REST_Request $request )
{
	// Get parameters from XHR request
	$params = $request->get_params();

	// Check if a bot used honeypot field
	$isSpam = ( isset($params['city']) && !empty($params['city']) );

	// Check if user missed a required field
	if ( !isset($params['name']) || !isset($params['email']) || !isset($params['message']) )
		return [
			'success' => false,
			'reason' => 'missing'
		];

	// Get site config options
	$adminEmail = get_option('admin_email');
	$siteURL = get_option('siteurl');
	$siteName = get_option('blogname');

	// Formal fields and limit max length
	$name = substr( $params['name'], 0, 255 );
	$email = substr( $params['email'], 0, 255 );
//	$phone = substr( $params['phone'] ?? ' - ', 0, 255 );
	$message = substr( $params['message'], 0, 10000 );
	$referrer = substr( $params['referrer'], 0, 255 );
	$locale = substr( $params['locale'], 0, 255 );
	$now = date('j/m/Y G:i:s');
	$spamValue = $isSpam ? '- OUI -' : 'non';

	// Generate email content
	$content = "
Site : ${siteURL}
Referrer : ${referrer}
Locale : ${locale}
Spam potentiel : ${spamValue}
Nom : ${name}
E-mail : ${email}
Le : ${now}

---

${message}";

	$headers = [];

	// Add reply-to header if not spam
	if (!$isSpam) {
		$nn = addslashes($name ?? '');
		$mm = addslashes($email ?? '');
		$headers[] = "Reply-To: $nn <$mm>";
	}

	// Send email
	$result = wp_mail($adminEmail, "${siteName} - ${name}", $content, $headers);

	// Save data into flamingo
	Flamingo_Inbound_Message::add([
		'spam' => $isSpam,
		'from' => $email,
		'from_name' => $name,
		'from_email' => $email,
		'subject' => substr($message, 0, 30).' ...',
		'fields' => [
			'site' => $siteURL,
			'page' => $referrer,
			'locale' => $locale,
			'honeypot' => $params['city'] ?? '',
			'spam' => $spamValue,
			'name' => htmlspecialchars( $name ),
			'email' => htmlspecialchars( $email ),
//			'phone' => htmlspecialchars( $phone ),
			'date' => $now,
			'message' => htmlspecialchars( $message )
		]
	]);

	// If mail could not be sent
	if ( !$result )
		return [
			'success' => false,
			'reason' => 'send'
		];

	// Email sent successfully
	return [ 'success' => true ];
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'yscorporate/v1', '/contact.json', [
		'methods' => 'POST',
		'callback' => 'rest_api_post_contact',
	]);
});