const { nicePrint, execAsync, printLoaderLine } = require( "@solid-js/cli" );
const { Directory } = require( "@solid-js/files" );
const path = require( "path" );

let phpIsEnough = false
let dockerIsRunning = false

module.exports = {
	beforeQuestions : async ( parent ) => {
		// Get PHP version
		const phpVersion = await parent.getPHPVersion()
		dockerIsRunning = await parent.getDockerIsRunning()

		// We need PHP 7.4+ if docker is not running
		phpIsEnough = (
			phpVersion !== false // php is installed in CLI
			&& (
				phpVersion[0] >= 8 // php 8+ is fine
				|| ( phpVersion[0] === 7 && phpVersion[1] >= 4 ) // php 7.4+ is fine
			)
		)
		if ( !dockerIsRunning && !phpIsEnough ) {
			nicePrint(`{b/r}To continue you need PHP 7.4+ installed or Docker installed and running.`, { code: 1 })
		}

		nicePrint(`
			Installed PHP Version : ${!phpVersion ? 'not installed' : phpVersion.join('.')}
			Docker is ${dockerIsRunning ? '' : 'not '}running 
		`)
	},
	getQuestions : () => ({
		name : {
			input : 'Project name, lower case, no special chars ( a-z 0-9 - _ )',
			notEmpty: true
		},
		description : {
			input : 'Project description (Free text)'
		},
		author: {
			input : 'Author full name or company'
		},
		uri : {
			input : 'Author or company URL ( https://... )'
		},
		dbPassword : {
			input : 'Local Chimera database password'
		},
		dbName : {
			input : 'Wordpress DB name in Chimera database.',
			defaultValue: '$name'
		},
		themeName : {
			input : 'Wordpress theme name',
			defaultValue: '$name'
		},
		acfKey : {
			input : 'ACF Pro key',
			filter: k => encodeURIComponent(k)
		}
	}),
	filterAnswers : null,
	beforeTemplate : null,
	getFilesToTemplate : () => ([
		'.env',
		'.env.chimera',
		'.env.production',
		'public/themes/theme/style.css',
		'package.json',
		'README.md',
		'.chimera.yml',
		'composer.json',
	]),
	afterTemplate: async function ( answers )
	{
		const themeDirectory = new Directory(path.join(process.cwd(), 'public/themes/theme'))
		await themeDirectory.renameAsync( path.join('public/themes', answers.themeName) )
	},
	install: async function ( parent, answers )
	{
		// Try to install composer dependencies locally
		let composerInstalled = false
		if ( phpIsEnough ) {
			try {
				await execAsync(`composer install`, 3)
				composerInstalled = true
			}
			catch (e) {
				console.error(e)
				nicePrint(`{b/r}Unable to install dependencies with local composer.`)
			}
		}

		// Local PHP could not install and Docker is not available
		if ( !composerInstalled && !dockerIsRunning ) {
			nicePrint(`{b/r}Please run Docker or install PHP 7.4+ locally to continue.`)
			process.exit(2);
		}

		// Install composer dependencies through composer
		else if ( !composerInstalled && dockerIsRunning) {
			phpIsEnough && nicePrint(`{b/g}Trying through docker`)
			await parent.cliTask({
				command : `docker-compose build`,
				title : `Building docker image (can be long)`,
				success: `Docker image built`,
				error : `Unable to build docker image`,
				code: 3
			})
			await execAsync(`docker-compose down`)
			await parent.cliTask({
				command : `docker-compose up -d`,
				title : `Starting docker image`,
				success: `Docker image started`,
				error : `Unable to start docker image`,
				code: 4
			})
			await parent.cliTask({
				command : `docker exec 'project_${answers.name}' composer install`,
				title : `Installing composer dependencies through docker`,
				success: `Composer dependencies installed`,
				error : `Unable to install composer dependencies`,
				code: 5
			})
			await parent.cliTask({
				command : `docker-compose down`,
				title : `Stopping container`,
				code: 6
			})
		}

		// step 1 : npm install
		// step 2 : Install docker image as submodule
		// git submodule add https://github.com/zouloux/docker-debian-apache-php.git deploy/docker-debian-apache-php
		// INFO : Update sub-modules
		// git submodule update --init --recursive
		// step 4 : Composer install if php7.4
		// step 4 : execute docker project and composer install
	}

}