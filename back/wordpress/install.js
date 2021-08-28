
// step 0 : Check if php 7.4 is installed
// If not, check if docker is started
// If not, halt

// step 1 : npm install

// step 2 : Install docker image as submodule
// git submodule add https://github.com/zouloux/docker-debian-apache-php.git deploy/docker-debian-apache-php

// INFO : Update sub-modules
// git submodule update --init --recursive

// step 3
// Replace vars in files
const filesToTemplate = [
	'.env',
	'.env.chimera',
	'.env.production',
	'.public/themes/theme/style.css',
	'package.json',
	'README.md',
	'.chimera.yml'
]

const templateVars = {
	name: '',
	url: '',
	description: '',
	author: '',
}

// step 4 : Composer install if php5
// step 4 : execute docker project and composer install

