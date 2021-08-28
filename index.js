#!/usr/bin/env node

const { FileFinder, File } = require( "@solid-js/files" );
const { askList, nicePrint, printLoaderLine, askInput, execAsync } = require( "@solid-js/cli" );
const path = require( "path" );

// ----------------------------------------------------------------------------- UTILS

const version = require('./package.json').version
const printUsingVersion = () => nicePrint(`{d}Using Solid Chimera Starter {b/d}v${version}`);

async function getPHPVersion () {
	try {
		let phpVersion = await execAsync('php -v')
		return phpVersion.split("\n")[0].split(" ")[1].split('.').map( s => parseInt(s) )
	}
	catch (e) { return false }
}

async function getDockerIsRunning () {
	try {
		await execAsync(`docker ps`)
		return true
	}
	catch (e) { return false }
}

async function cliTask ( options ) {
	// console.log(options.command)
	const loader = printLoaderLine( options.title )
	try {
		await execAsync(options.command, 0)
	}
	catch (e) {
		loader( options.error ?? options.title, 'error' )
		e && console.error( e )
		options.error && process.exit( options.code )
	}
	loader( options.success )
}

module.exports = {
	getPHPVersion,
	getDockerIsRunning,
	cliTask
}

// ----------------------------------------------------------------------------- MAIN

;(async function ()
{
	printUsingVersion();

	// ------------------------------------------------------------------------- CHECK CWD

	// List files in current directory
	const currentFolderContent = FileFinder.list('*', { cwd: process.cwd() })

	// Ask confirm if we got some files
	if ( 0 && currentFolderContent.length > 0 ) {
		nicePrint(`
			{b/o}Warning, current directory is not empty, some file may be overridden.
			Found elements : {d}${currentFolderContent.filter((a, i) => i < 5).join(',')} ...
		`)
		const answer = await askList('Are you sure to continue ?', ['No', 'Yes'], { returnType: 'value' })
		if ( answer === 'No' ) process.exit(0)
	}

	// ------------------------------------------------------------------------- SELECT BACKEND

	// Ask which backend to use
	const backendList = FileFinder.find('directory', 'back/*', { cwd : __dirname }).map( fileEntity => fileEntity.name )
	// const selectedBackend = await askList(`Select backend to install`, backendList, { returnType: 'value' })
	const selectedBackend = 'wordpress'
	// console.log(selectedBackend)

	// Copy backend sources to current directory
	const backendLoader = printLoaderLine(`Copying ${selectedBackend} files`)
	const sourceFiles = FileFinder.find('all', '*', {
		cwd: path.join(__dirname, 'back', selectedBackend),
		dot: true
	})
	// console.log( sourceFiles );
	for ( const fileEntity of sourceFiles ) {
		console.log('Copying ' + fileEntity.fullName)
		await fileEntity.copyToAsync( process.cwd() )
	}
	backendLoader(`${selectedBackend} files copied`)

	// Execute install.js
	const installer = require( path.join(process.cwd(), 'install') )

	// Before questions
	installer.beforeQuestions && await installer.beforeQuestions( module.exports )

	// Ask all questions
	let answers = {}
	const questions = await installer.getQuestions();
	const keys = Object.keys( questions )
	for ( const key of keys ) {
		// Ask question
		const question = questions[ key ]
		let answer
		// As input
		if ( question.input )
			answer = await askInput( question.input, {
				isNumber: question.isNumber,
				notEmpty: question.notEmpty,
				defaultValue: (
					( question.defaultValue && question.defaultValue.indexOf('$') === 0 )
					? answers[ question.defaultValue.substr(1, question.defaultValue.length) ]
					: question.defaultValue
				)
			})
		// As list
		// else if ( question.list )
		// 	await askList()
		// Filter answer
		answers[ key ] = (
			question.filter
			? question.filter( answer )
			: answer
		)
	}

	// Filter answers
	answers = installer.filterAnswers ? await installer.filterAnswers( answers ) : answers

	// Template
	const templateLoader = printLoaderLine(`Templating files ...`)
	installer.beforeTemplate && await installer.beforeTemplate( answers )
	const templatedFiles = installer.getFilesToTemplate().map( filePath => {
		const fileToTemplate = new File( filePath )
		fileToTemplate.load()
		fileToTemplate.template( answers )
		fileToTemplate.save()
	})
	installer.afterTemplate && await installer.afterTemplate( answers )
	templateLoader(`${templatedFiles.length} file${templatedFiles.length > 1 ? 's' : ''} templated`)

	// Install
	installer.install && await installer.install( module.exports, answers )

	// ------------------------------------------------------------------------- SELECT FRONTEND
	// TODO

})()
