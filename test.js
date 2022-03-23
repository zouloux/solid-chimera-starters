const fetch = require( "node-fetch" );

(async function ( acfKey ) {
	const fetch = require("node-fetch");
	try {
		const resp = await fetch(`https://connect.advancedcustomfields.com/v2/plugins/download?p=pro&k=${acfKey}&t=5.10.1`)
		const text = await resp.text()
		// If this is a JSON, this is an error
		try {
			JSON.parse( text )
			return "Invalid key";
		}
		// Invalid JSON, this is a raw zip file
		catch (e) {
			return true;
		}
	}
	catch ( e ) {
		return "Cannot connect";
	}
})();