# Solid Chimera Starters

### Requirements

#### Local machine : 
- Linux based shell
- Node 14+
- Git
- Docker
- PHP 7.4, not mandatory
- Tested on Mac, should be working on Linux, never tested on Windows
- Chimera client installed (`npm i -g @zouloux/chimera-client`)

#### Chimera server
- A [Chimera server](https://github.com/zouloux/chimera) configured and running

## Create a new Solid / Chimera app

```shell
npx https://github.com/zouloux/solid-chimera-starters
```

Then follow instructions.

( If you do not have npx installed : `npm i -g npx` )

#### Clear npx cache
```shell
rm -rf ~/.npm/_npx
```

#### Clear saved preferences
```shell
npx https://github.com/zouloux/solid-chimera-starters clear-preferences
```

## Available back-ends

### Wordpress

- Wordplate
- Docker Image : Debian / Apache / PHP 7.4
- MariaDB as MySQL with Chimera service
- ACF Pro
- Pre-installed theme
- Some utility plugins

> You will need a valid ACF Pro key to procecess installation

### Grav
#### TODO ...

### Node (find name)
#### TODO ...

## Available front-ends
#### TODO ...