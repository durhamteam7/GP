# GP
[![Build Status](https://travis-ci.org/durhamteam7/GP.svg?branch=master)](https://travis-ci.org/durhamteam7/GP)
[![Test Coverage](https://codeclimate.com/github/durhamteam7/GP/badges/coverage.svg)](https://codeclimate.com/github/durhamteam7/GP/coverage)
<br>
Algorithm is in tests/ directory

## Main idea of algorithm
> Each image was circulated to multiple users and retired after meeting the following criteria:
> the first five classifications were “nothing here” (blank);
> ten non-consecutive “nothing here” classifications (blank_consensus);
> ten matching classifications of species or species - combination, not necessarily consecutive (consensus).
> If none of these criteria were met, the image was circulated until accumulating 25 species classifications (complete).
