<?php

include('sip2.class.php');
date_default_timezone_set('America/Chicago');

$mysip = new sip2;
// connect to SIP server
$result = $mysip->connect();

$all_rows = array();
$fhnd = fopen("../data/circll_test.csv", "r");
if ($fhnd){
	$header = fgetcsv($fhnd);
	while ($row = fgetcsv($fhnd)) {
		$all_rows[] = array_combine($header, $row);
	}
	fclose($fhnd);
}
//print_r($all_rows);

$got_fhnd = fopen("../data/circll_test_result.csv", 'w');

foreach ($all_rows as $patron) {

// Get patron info without using PIN
	$mysip->patron		= $patron['patronid'];
	$mysip->patronpwd	= '';
	$in = '';
	$in = $mysip->msgPatronInformation('none');
// parse the raw response into an array
	$result = '';
	$result = $mysip->parsePatronInfoResponse( $mysip->get_message($in) );
// set new query with patronid and PIN
	$mysip->patron		= $patron['patronid'];
	$mysip->patronpwd	= $result['variable']['XQ'][0];
	$in = '';
	$in = $mysip->msgPatronInformation('none');
	$result = '';
	$result = $mysip->parsePatronInfoResponse( $mysip->get_message($in) );
	$search = [];
	$search = [
		'type'		=> 'patronid',
		'id'		=> $mysip->patron,
		'pin'		=> $mysip->patronpwd,
		'AA'		=> implode($result['variable']['AA']),
		'AF'		=> isset($result['variable']['AF']) ? implode($result['variable']['AF']) : '' ,
		'XQ'		=> implode($result['variable']['XQ']),
		'XV'		=> implode($result['variable']['XV'])
	];
//	var_dump($search);
	fputcsv($got_fhnd, $search);

// set new query with patronguid and PIN
	$mysip->patron		= $patron['patronguid'];
	$mysip->patronpwd	= $result['variable']['XQ'][0];
	$in = '';
	$in = $mysip->msgPatronInformation('none');
	$result = '';
	$result = $mysip->parsePatronInfoResponse( $mysip->get_message($in) );
	$search = [];
	$search = [
		'type'		=> 'patronguid',
		'id'		=> $mysip->patron,
		'pin'		=> $mysip->patronpwd,
		'AA'		=> implode($result['variable']['AA']),
		'AF'		=> isset($result['variable']['AF'][0]) ? implode($result['variable']['AF']) : '' ,
		'XQ'		=> implode($result['variable']['XQ']),
		'XV'		=> implode($result['variable']['XV'])
	];
//	var_dump($search);
	fputcsv($got_fhnd, $search);
}

fclose($got_fhnd);

?>
