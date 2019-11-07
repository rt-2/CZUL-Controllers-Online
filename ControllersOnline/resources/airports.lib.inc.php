<?php
$GLOBALS['ALL_AIRPORTS'] = array_map('str_getcsv', file(dirname(__FILE__).'/airports.lib.data.csv'));