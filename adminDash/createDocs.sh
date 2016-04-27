#!/bin/bash

if [ "$(ls -A node_modules/jsdoc)" ]; then
   echo "Found jsDoc"
else
   echo "Installing jsDoc"
   `npm install jsdoc angular-jsdoc`
fi

echo "Creating Doc files"

for file in *.js
do
      echo $file
      `node_modules/jsdoc/jsdoc.js --configure node_modules/angular-jsdoc/common/conf.json --template node_modules/angular-jsdoc/angular-template --destination docs ${file}`
done
