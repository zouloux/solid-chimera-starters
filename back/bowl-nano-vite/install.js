const path = require( "path" );
const crypto = require( "crypto" );

// Node dependencies from index.js script
let _d

let phpIsEnough = false
let dockerIsRunning = false

module.exports = {
	// ------------------------------------------------------------------------- INIT
	injectDependencies ( dependencies ) { _d = dependencies },

	// ------------------------------------------------------------------------- QUESTIONS
	beforeQuestions : async () => {
		// Get PHP version
		const phpVersion = await _d.getPHPVersion()
		dockerIsRunning = await _d.getDockerIsRunning()

		// We need PHP 8.0+ if docker is not running
		phpIsEnough = (
			phpVersion !== false // php is installed in CLI
			&&
			phpVersion[0] >= 8 // php 8+ is fine
		)

		// Check requirements
		if ( !dockerIsRunning && !phpIsEnough ) {
			_d.nicePrint(`{b/r}To continue you need PHP 8.0+ installed or Docker installed and running.`, { code: 1 })
		}

		// Show info
		_d.nicePrint(`
			➤  Installed PHP Version : ${!phpVersion ? 'not installed' : phpVersion.join('.')}
			➤  Docker is ${dockerIsRunning ? '' : 'not '}running
		`)
	},
	getQuestions : () => ({
		name : {
			input : 'Project name, lower case, no special chars (dashes and underscore allowed, ex: project-name)',
			notEmpty: true,
			filter: v => v.split(' ').join('').toLowerCase()
		},
		description : {
			input : 'Project description (Free text)'
		},
		author: {
			input : 'Author full name or company',
			save: true
		},
		uri : {
			input : 'Author or company URL ( https://... )',
			save: true
		},
		stagingHost : {
			input : 'Staging host (chimera root), without scheme, without port ( like : your-domain.com )',
			save: true
		},
		localDBPassword : {
			input : 'Local database password',
			save: true
		},
		stagingDBPassword : {
			input : 'Staging database password',
			save: true
		},
		localDBName : {
			input : 'Local database name',
			defaultValue: '$name'
		},
		stagingDBName : {
			input : 'Staging database name',
			defaultValue: '$name'
		},
		themeName : {
			input : 'Wordpress theme name',
			defaultValue: '$name'
		},
		apacheLogin : {
			input : 'Apache login on staging (keep empty to disable)',
		},
		apachePassword : {
			input : 'Apache password on staging (keep empty to disable)'
		},
		acfKey : {
			input : 'ACF Pro key (needed for composer install)',
			filter: k => encodeURIComponent(k),
			save: true,
			validate : async acfKey => {
				try {
					const resp = await _d.NodeFetch(`https://connect.advancedcustomfields.com/v2/plugins/download?p=pro&k=${acfKey}&t=5.10.1`)
					const text = await resp.text()
					// If this is a JSON, this is an error
					try {
						JSON.parse( text )
						return "Invalid ACF key";
					}
					// Invalid JSON, this is a raw zip file
					catch (e) {
						return true;
					}
				}
				catch ( e ) {
					return "Cannot connect to ACF servers.";
				}
			}
		}
	}),
	filterAnswers : async ( answers ) => {
		// Generate salt hashes
		const randomString = ( length = 64 ) => {
			return crypto.randomBytes( Math.ceil(length/2) ).toString('hex').slice( 0, length );
		}
		const keys = [
			"AUTH_KEY", "SECURE_AUTH_KEY", "LOGGED_IN_KEY", "NONCE_KEY",
			"AUTH_SALT", "SECURE_AUTH_SALT", "LOGGED_IN_SALT", "NONCE_SALT"
		];
		const saltLoader = _d.printLoaderLine(`Generating salt hashes`)
		const salt = keys.map( k => `${k}=${randomString()}`).join("\n")
		saltLoader(`Generated salt hashes`)
		return { ...answers, salt }
	},

	// ------------------------------------------------------------------------- TEMPLATE
	beforeTemplate : null,
	getFilesToTemplate : () => ([
		'.env',
		'.env.staging',
		'.env.production',
		'public/themes/theme/style.css',
		'package.json',
		'README.md',
		'.chimera.yml',
		'composer.json',
		"docker-compose.yaml"
	]),
	afterTemplate: async function ( answers )
	{
		// Rename theme directory with project name
		const themeDirectory = new _d.Directory(path.join(process.cwd(), 'public/themes/theme'))
		await themeDirectory.moveTo( answers.themeName )
	},

	// ------------------------------------------------------------------------- INSTALL
	install: async function ( answers )
	{
		// Try to install composer dependencies locally
		let composerInstalled = false
		if ( phpIsEnough ) {
			await _d.cliTask({
				command : `composer install`,
				title : `Installing composer dependencies with local PHP`,
				success: `Unable to install dependencies with local composer`,
			})
		}

		// Local PHP could not install and Docker is not available
		if ( !composerInstalled && !dockerIsRunning ) {
			_d.nicePrint(`{b/r}Please run Docker or install PHP 8.0+ locally to continue.`)
			process.exit(2);
		}

		// Install composer dependencies through composer
		else if ( !composerInstalled && dockerIsRunning) {
			phpIsEnough && _d.nicePrint(`{b/g}Trying through docker`)
			async function getCleanTask ( code = null ) {
				await _d.cliTask({
					command : "docker-compose down",
					title: "Stopping container",
					code
				})
			}
			await _d.cliTask({
				command : `docker-compose build`,
				title : `Building docker image (can be long)`,
				success: `Docker image built`,
				error : `Unable to build docker image`,
				code: 3
			})
			await _d.cliTask({
				command : `docker-compose down; docker-compose up -d`,
				title : `Starting docker image`,
				success: `Docker image started`,
				error : `Unable to start docker image`,
				code: 4,
				clean: getCleanTask
			})
			await _d.cliTask({
				command : `docker exec 'project_${answers.name}' composer install`,
				title : `Installing composer dependencies through docker`,
				success: `Composer dependencies installed`,
				error : `Unable to install composer dependencies`,
				code: 5,
				clean: getCleanTask
			})
			await getCleanTask( 6 )
			await _d.cliTask({
				command: `npm i`,
				title: `Installing NPM dependencies`,
				success: `NPM dependencies installed`,
				error: `Unable to install NPM dependencies, you can do it manually.`
			});
			_d.nicePrint(`{b/g}Success 🎉`);
			_d.nicePrint(`You can now create your database {b}${answers.localDBName}{/} locally and {b}${answers.stagingDBName}{/}on {b}${answers.stagingHost}{/}`);
		}
	}
}