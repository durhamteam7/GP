#!/bin/bash
# Docs Generator
#
#   Script to genorate jsdocs for angularjs files
#   Installs dependancies if required
#   Requires node/npm installed


#Install dependancies if missing
if [ "$(ls -A node_modules/jsdoc)" ]; then
   echo "Found jsDoc"
else
   echo "Installing jsDoc"
   `npm install jsdoc angular-jsdoc`
fi
#Create doc files
echo "Creating Doc files"
`./composer.phar install`
`./vendor/bin/phpdoc.php -t docs/php/`
`node_modules/jsdoc/jsdoc.js --configure node_modules/angular-jsdoc/common/conf.json --template node_modules/angular-jsdoc/angular-template --readme README.md --destination docs/js commonDependancies adminDash userDash`
