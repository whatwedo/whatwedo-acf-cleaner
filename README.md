# WWD ACF Cleanup
Developed with ♥ by whatwedo(https://whatwedo.ch) in Bern.

## What does it do

We analyze the post of the selected post types against the ACF groups in use. Afterwards we remove all data from deleted groups.
There's a dry run available (discovery) to see how many fields would get removed. On the actual cleanup you get prompted because it can't be undone (so make a backup first).

## Version

This plugin was created for our own usage to clean a big database from old ACF data.
It's not under active development but can be used (on your own risk).

There are some improvements to do:

#### Conditional fields
Conditional fields are not taken in account. This is probably also the issue why there's nothing like this baked into the ACF plugin itself. Down the road it gets pretty complex.

#### Output the names of the deleted fields
Analyzing the actual data (which fields get deleted). We know the name of the fields. Also returning from the server, but don't do something proper on the frontend since they're a lot of unordered data.
