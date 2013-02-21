qmpsantsupc
===========

qMp wireless mesh network monitoring

This work is designed to monitor the topology and some parameters of a
wireless mesh network, as in the following example:

http://compnet.ac.upc.edu/~llorenc/qmpsantsupc/index.php

Usage: Install the files in a web server with php support and open index.php in a browser.

Customization: Generate the JSON files following the format of the example files in jsdata/*.js

Upon creating a new js file (e.g. jsdata/the-new-jsfile.js), use the perl
script build-flot-data.pl to create the corresponding file
jsdata/the-new-jsfile.flot and update flot_data.js as follows:

./build-flot-data.pl -g jsdata/the-new-jsfile.js -v 'flotdata,flotgraph' jsdata/the-new-jsfile.flot
./build-flot-data.pl flot_data.js

The files flot_data.js and jsdata/the-new-jsfile.flot are used to
generate the flot graphs at the bottom of the page. These are built
using the flot library.

You can modify jit/qmpsantsupc.js to customize the data shown in the
right-container of the page when a node is selected. This information
is taken from the "data" field of each node in the files jsdata/*.js

Find more info about the format of the files jsdata/*.js here:

http://philogb.github.com/jit/static/v20/Jit/Examples/Sunburst/example1.html
