[![Build Status](https://travis-ci.org/durhamteam7/GP.svg?branch=master)](https://travis-ci.org/durhamteam7/GP)
[![Code Climate](https://codeclimate.com/github/durhamteam7/GP/badges/gpa.svg)](https://codeclimate.com/github/durhamteam7/GP)
[![Test Coverage](https://codeclimate.com/github/durhamteam7/GP/badges/coverage.svg)](https://codeclimate.com/github/durhamteam7/GP/coverage)

#Introduction

This project is about adding functionality to the system to ensure reliability of user classifications and provide detailed analysis and reports on this data. The client currently has data but not the ability to analyse and get meaningful insights about it. The proposed system will provide this. On user end: increase user engagement, which will result in more classifications and therefore better data to work with.

#Features
## Swanson Algorithm
Each image was circulated to multiple users and retired after meeting the following criteria:

 - the first five classifications were “nothing here” (blank)
 - ten non-consecutive “nothing here” classifications (blank_consensus)
 - ten matching classifications of species or species - combination, not necessarily consecutive (consensus). 
 
If none of these criteria were  met, the image was circulated until accumulating 25 species classifications (complete).
## Administrator Dashboard


## User-facing Dashboard

## License
>You can check out the full license [here](http://www.gnu.org/licenses/agpl-3.0.en.html)

This project is licensed under the terms of the **GNU AGPLv3** license.
