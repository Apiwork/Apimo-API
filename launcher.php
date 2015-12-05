<?php
/* Load Referential */
require_once('lib/ApimoApiReferential.php');
$api = new Apimo_Api_Referential();
$report = $api->update();
print_r($report);

/* Load Agencies */
require_once('lib/ApimoApiAgencies.php');
$api = new Apimo_Api_Agencies();
$report = $api->update();
print_r($report);

/* Load Properties */
require_once('lib/ApimoApiProperties.php');
$api = new Apimo_Api_Properties();
$report = $api->update();
print_r($report);