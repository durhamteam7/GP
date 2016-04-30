#MammalWeb [![Build Status](https://travis-ci.org/durhamteam7/GP.svg?branch=master)](https://travis-ci.org/durhamteam7/GP) [![Code Climate](https://codeclimate.com/github/durhamteam7/GP/badges/gpa.svg)](https://codeclimate.com/github/durhamteam7/GP) [![Test Coverage](https://codeclimate.com/github/durhamteam7/GP/badges/coverage.svg)](https://codeclimate.com/github/durhamteam7/GP/coverage)

This project is about adding functionality to the system to ensure reliability of user classifications and provide detailed analysis and reports on this data. On user end: increase user engagement, which will result in more classifications and therefore better data to work with.

#Features
## Swanson Algorithm
Each image was circulated to multiple users and retired after meeting the following criteria:

 - the first five classifications were “nothing here” (blank)
 - ten non-consecutive “nothing here” classifications (blank_consensus)
 - ten matching classifications of species or species - combination, not necessarily consecutive (consensus). 
 
If none of these criteria were  met, the image was circulated until accumulating 25 species classifications (complete).

## Administrator Dashboard
- **Filters** - Narrow down searches with filters. Includes support for searching by location.
- **Photo/Sequence selection** - Show only sequences to remove repeated data.
- **User Management**- Scrollable list of user ratings in a range of areas. Quickly find users who are causing problems.
- **Graphs** - Graph your current search using a range of variables. Uses [GoogleCharts](https://developers.google.com/chart/)
- **CSV** - Download any search in CSV format compatible with R or Python based analyses software

## User-facing Dashboard
- **Map** - Displays the locations of animals on a map using icons. Uses [Leaflet](http://leafletjs.com/)
- **Timeline** - Timeline of classifications, linked with map. 
- **Slideshow** - Slideshow of images, includes basic information.

# Usage
Run php

# Documentation
The project is documented using [Angular-JSDoc](https://github.com/allenhwkim/angular-jsdoc) and [phpDocumentor](https://www.phpdoc.org/)

A browsable HTML version of this documentation for all parts of the project can be created by running the ./createDocs.sh bash file. (Note: requires npm to get dependancies). Docs will be found in the /docs folder.

# License
>You can check out the full license [here](http://www.gnu.org/licenses/agpl-3.0.en.html)

This project is licensed under the terms of the **GNU AGPLv3** license.
