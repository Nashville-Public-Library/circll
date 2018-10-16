<!DOCTYPE html>
<html>
<head>
<title>circll</title>
<link rel="stylesheet" type="text/css" href="./circll.css">

<script type="text/javascript">
function printReceipt () {
	var oPrintDiv = document.getElementById("print");
	oPrintDiv.style.visibility = "visible";
	window.print();
//	oPrintDiv.style.visibility = "hidden";
};
</script>

</head>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$item = htmlspecialchars(stripslashes(trim($_POST["item"])));
	$nbduedate = htmlspecialchars(stripslashes(trim($_POST["nbduedate"])));
	$customNotes = htmlspecialchars(stripslashes(trim($_POST["customNotes"])));
	if (!empty($nbduedate)) {
		$nbduedate = strtotime($nbduedate);
	}
} else { // TESTING
//	$item = '35192038783290';
//	$nbduedate = strtotime('2019-01-31');
}

?>

<body>

<div id="screen">
  <header id="formHeader">Limitless Libraries</header>
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
    <div class="row"><label for="item">Item Barcode: </label><input type="text" id="item" name="item" autofocus></div>
    <div class="row"><label for="nbduedate">NB Due Date: </label><input type="date" id="nbduedate" name="nbduedate" value="<?php if(isset($nbduedate)){echo date('Y-m-d',$nbduedate);} ?>"></div>
    <div class="row"><label for="customNotes">Custom Notes: </label><input type="text" id="customNotes" name="customNotes" value="<?php if(isset($customNotes)){echo $customNotes;} ?>"></div>
    <div class="row"><label for="submit"> </label><input type="submit" id="submit" name="submit" value="Submit"></div>
  </form>
  <footer id="formFooter">Have a Nice Day</footer>
</div>

<?php

if (empty($patronApiWsdl)) {
		$configArray		= parse_ini_file('config.pwd.ini', true, INI_SCANNER_RAW);
		$patronApiWsdl		= $configArray['Catalog']['patronApiWsdl'];
		$patronApiDebugMode	= $configArray['Catalog']['patronApiDebugMode'];
		$patronApiReportMode	= $configArray['Catalog']['patronApiReportMode'];
		$catalogApiWsdl		= $configArray['Catalog']['catalogApiWsdl'];
		$catalogApiDebugMode	= $configArray['Catalog']['catalogApiDebugMode'];
		$catalogApiReportMode	= $configArray['Catalog']['catalogApiReportMode'];
}

initMemcache();
$receipt = checkout($item,$nbduedate,$customNotes);
$css = file_get_contents('./circll.css');
$receipt = "<style>$css</style>" . $receipt;
$receipt .= '<script>(function(){printReceipt();})();</script>';
echo $receipt;

//////////////////// FUNCTIONS ////////////////////

function callAPI($wsdl, $requestName, $request, $tag) {
	$connectionPassed = false;
	$numTries = 0;
	$result = new stdClass();
	$result->response = "";
	while (!$connectionPassed && $numTries < 3) {
		try {
			$client = new SOAPClient($wsdl, array('connection_timeout' => 3, 'features' => SOAP_WAIT_ONE_WAY_CALLS, 'trace' => 1));
			$result->response = $client->$requestName($request);
			$connectionPassed = true;
			if (is_null($result->response)) {$result->response = $client->__getLastResponse();}
			if (!empty($result->response)) {
				if (gettype($result->response) == 'object') {
					$ShortMessage[0] = $result->response->ResponseStatuses->ResponseStatus->ShortMessage;
					$result->success = $ShortMessage[0] == 'Successful operation';
				} else if (gettype($result->response) == 'string') {
					$result->success = stripos($result->response, '<ns2:ShortMessage>Successful operation</ns2:ShortMessage>') !== false;
					preg_match('/<ns2:LongMessage>(.+?)<\/ns2:LongMessage>/', $result->response, $longMessages);
					preg_match('/<ns2:ShortMessage>(.+?)<\/ns2:ShortMessage>/', $result->response, $shortMessages);
				}
				if(!$result->success) {
					$result->error = "ERROR: " . $tag . " : " . (isset($longMessages[1]) ? ' : ' . $longMessages[1] : (isset($shortMessages[0]) ? ' : ' . $shortMessages[0] : ''));
				}
			} else {
				$result->error = "ERROR: " . $tag . " : No SOAP response from API.";
			}
		} catch (SoapFault $e) {
			if ($numTries == 2) { $result->error = "EXCEPTION: " . $tag . " : " . $e->getMessage(); }
		}
		$numTries++;
	}
	if (isset($result->error)) {
		echo "$result->error\n";
	} else {
//		echo "SUCCESS: " . $tag . "\n";
	}
	return $result;
}

function checkout($item,$nbduedate = '',$customNotes = '') {
	global $memcache;
	include('sip2.class.php');
	date_default_timezone_set('America/Chicago');
	$mysip			= new sip2;
	$result			= $mysip->connect();
	$mysip->patron		= '';

// ACS Status request
//	$in = $mysip->msgSCStatus();
//	$result = $mysip->parseACSStatusResponse( $mysip->get_message($in) );
//var_dump($result);

// ITEM CHECKIN
	$in 			= '';
	$in 			= $mysip->msgCheckin($item,'');
	$result 		= '';
	$result 		= $mysip->parseCheckinResponse( $mysip->get_message($in) );
	$alertType		= isset($result['variable']['CV']) ? implode($result['variable']['CV']) : '';
	$destinationBranch	= isset($result['variable']['CT']) ? implode($result['variable']['CT']) : '';
	$title			= isset($result['variable']['AJ']) ? implode($result['variable']['AJ']) : '';
	$checkinDateTime	= isset($result['fixed']['TransactionDate']) ? date_format(date_create_from_format('Ymd    His', $result['fixed']['TransactionDate']), 'm/d/Y  h:i:s A') : date('m/d/Y  h:i:s A');
	$mysip->patron		= isset($result['variable']['CY']) ? implode($result['variable']['CY']) : '';

// IF DESTINATION BRANCH IS NOT A SCHOOL OR THERE IS NO PATRON ON HOLD, PRINT NPL TRANSIT SLIP
	if (preg_match('/^\D/', $destinationBranch) === 1 || $mysip->patron == '') {
		$receipt = '<div id="print">';
// TO DO: FLESH OUT CHECKIN->IN TRANSIT RECEIPT
		$receipt .= "<div id='receipt' class='transit'>";
		if ($alertType == '02') {
			$receipt .= "<div id='hold'>HOLD</div>"; 
		} else {
			$receipt .= "<div id='hold'> </div>";
		}
		$receipt .= "<div id='destinationBranch' data-destinationBranch=$destinationBranch>$destinationBranch</div>"; 
		$receipt .= "<div id='item' data-item=$item>ITEM NUMBER: $item</div>"; 
		$receipt .= "<div id='title' data-title=$title>TITLE: $title</div>"; 
		$receipt .= "<div id='checkinDateTime' data-checkinDateTime=$checkinDateTime>$checkinDateTime</div>"; 
		$receipt .= "</div>";
		$receipt .= "</div>";
		return $receipt;
	}

// IF DESTINATION BRANCH IS A SCHOOL AND THERE IS A PATRON ON HOLD, CHECK OUT ITEM AND PRINT DUE SLIP
//	if (preg_match('/^\d/', $destinationBranch) === 1 && !empty($mysip->patron)) {
	$in 			= '';
	$in 			= $mysip->msgCheckout($item,$nbduedate,'N','','N','Y','N'); // no block = 'Y'
//	$in 			= $mysip->msgCheckout($item,1546300801,'N','','N','Y','N'); // no block = 'Y'
//var_dump($in);
// TO DO: SET 7 day DVD/Blu-Ray DUE DATE TO 10 DAYS FROM NOW[?] USING msgCheckout 2nd argv $nbDateDue, 18 char timestamp
	$result 		= '';
	$result 		= $mysip->parseCheckoutResponse( $mysip->get_message($in) );
//var_dump($result);
// TEST FOR NOT "AFItem checked out."
	if (implode($result['variable']['AF']) != 'Item checked out.') {
		$receipt	= "<div id='print' class='error'>";
		$receipt	.= "<div id='message'>" . implode($result['variable']['AF']) . "</div>";
		$receipt	.= "<div id='checkinDateTime' data-checkinDateTime=$checkinDateTime>$checkinDateTime</div>"; 
		$receipt	.= "</div>";
		$receipt	.= "</div>";
		return $receipt;
	}
	$dueDate		= isset($result['variable']['AH']) ? date_format(date_create_from_format('mdy', implode($result['variable']['AH'])), 'F d, Y') : '';
// ADDITIONAL PATRON INFORMATION VIA SOAP
	global $patronApiWsdl;
	global $patronApiDebugMode;
	global $patronApiReportMode;
	global $catalogApiWsdl;
	global $catalogApiDebugMode;
	global $catalogApiReportMode;
	$requestName				= 'getPatronInformation';
	$tag					= $mysip->patron . ' : getPatronInformation';
	$requestPatron				= new stdClass();
	$requestPatron->Modifiers		= new stdClass();
	$requestPatron->Modifiers->DebugMode	= $patronApiDebugMode;
	$requestPatron->Modifiers->ReportMode	= $patronApiReportMode;
	$requestPatron->SearchType		= 'Patron ID';
	$requestPatron->SearchID		= $mysip->patron; // Patron ID
	$requestPatron->Patron			= new stdClass();
	$resultPatron 				= '';
	$resultPatron				= callAPI($patronApiWsdl, $requestName, $requestPatron, $tag);
	if($resultPatron->response->ResponseStatuses->ResponseStatus->ShortMessage == 'Successful operation') {
		$borrowerTypeCode		= (int) $resultPatron->response->Patron->PatronType;
		if ($borrowerTypeCode == 35 || $borrowerTypeCode == 36 || borrowerTypeCode == 37) {
			$receipt	= "<div id='print' class='error'>";
			$receipt	.= "<div id='message'>NON-DELIVERY PATRON</div>";
			$receipt	.= "<div id='checkinDateTime' data-checkinDateTime=$checkinDateTime>$checkinDateTime</div>"; 
			$receipt	.= "</div>";
			$receipt	.= "</div>";
			return $receipt;
		}
		$patronNameLast			= $resultPatron->response->Patron->LastName;
		$patronNameFirst		= $resultPatron->response->Patron->FirstName;
		$patronNameMiddle		= $resultPatron->response->Patron->MiddleName;
		$patronName			= $patronNameLast . ', ' . $patronNameFirst . ' ' . $patronNameMiddle;
		$branchCode			= $resultPatron->response->Patron->DefaultBranch;
		if (!empty($branchCode)) {
			$branchName = $memcache->get('carlx_branchCode_' . $branchCode);
			if (!empty($branchName) and isset($branchName)) {
			} else {
				$requestName				= 'getBranchInformation';
				$tag					= $requestName;
				$requestBranch				= new stdClass();
				$requestBranch->BranchSearchType	= 'Branch Code';
				$requestBranch->BranchSearchValue	= $branchCode;
				$requestBranch->Modifiers		= '';
				$resultBranch				= callAPI($catalogApiWsdl, $requestName, $requestBranch, $tag);
				if ($resultBranch && $resultBranch->response->BranchInfo) {
					$branchName = $resultBranch->response->BranchInfo->BranchName;
					$memcache->add('carlx_branchCode_' . $branchCode, $branchName, false, 86400);
				}
			}
		}
		if (!empty($borrowerTypeCode)) {
			$borrowerClass = '';
			$borrowerGrade = '';
			switch (true) {
				case $borrowerTypeCode==13:
					$borrowerClass	= 'Staff';
					$borrowerGrade	= '';
					break;
				case $borrowerTypeCode==21:
					$borrowerClass	= 'Student';
					$borrowerGrade	= 'PK';
					break;
				case $borrowerTypeCode==22:
					$borrowerClass	= 'Student';
					$borrowerGrade	= 'K';
					break;
				case $borrowerTypeCode >= 23 && $borrowerTypeCode <= 34: 
					$borrowerClass	= 'Student';
					$borrowerGrade	= $borrowerTypeCode - 22;
					break;
				case $borrowerTypeCode==40:
					$borrowerClass	= 'MNPS Librarian';
					$borrowerGrade	= '';
					break;
				case $borrowerTypeCode==35:
					$borrowerClass	= 'Student';
					$borrowerGrade	= 'E';
					$branchName	= 'NON-DELIVERY';
					break;
				case $borrowerTypeCode==36:
					$borrowerClass	= 'Student';
					$borrowerGrade	= 'M';
					$branchName	= 'NON-DELIVERY';
					break;
				case $borrowerTypeCode==37:
					$borrowerClass	= 'Student';
					$borrowerGrade	= 'H';
					$branchName	= 'NON-DELIVERY';
					break;
				default:
					$borrowerClass	= 'Student';
					$borrowerGrade	= '';
					$branchName	= '';
					break;
			}
		}
		$sponsorName	= $resultPatron->response->Patron->SponsorName;
		$receipt 	= '<div id="print"><div id="receipt" class="dueSlip">';
		$receipt 	.= '<div id="header">Only to be removed by NPL</div>';
		$itemLastFour	= substr($item,-4);
		$receipt 	.= "<div id='item'>$itemLastFour</div>"; 
		$receipt 	.= '<div id="limitless">Limitless Libraries</div>';
		$receipt	.= "<dl>";
		$receipt 	.= "<dt>School</dt><dd>$branchName</dd>"; 
		if ($borrowerClass == 'Student') {
			$receipt	.= "<dt>Grade $borrowerGrade</dt><dd>$sponsorName</dd>"; 
		}
		$receipt 	.= "<dt>$borrowerClass</dt><dd>$patronName</dd>"; 
		$receipt 	.= "<dt>Due</dt><dd>$dueDate</dd>"; 
		$receipt	.= "</dl>";
		$receipt 	.= '<div id="footer"><p>Students and Educators: Return to your school library or any NPL location</p><p>School Librarians: Return to NPL</p><p>NPL Staff: Remove label and check in</p></div>';
		if (!empty($customNotes)) {
			$receipt	.= '<div id="customNotes">' . $customNotes . '</div>';
		}
		$receipt 	.= '</div></div>';
		return $receipt;
	}
}

function initMemcache(){
	//Connect to memcache
	/** @var Memcache $memcache */
	global $memcache;
	global $configArray;
	// Set defaults if nothing set in config file.
//	$host = isset($configArray['Caching']['memcache_host']) ? $configArray['Caching']['memcache_host'] : 'localhost';
//	$port = isset($configArray['Caching']['memcache_port']) ? $configArray['Caching']['memcache_port'] : 11211;
//	$timeout = isset($configArray['Caching']['memcache_connection_timeout']) ? $configArray['Caching']['memcache_connection_timeout'] : 1;
	$host = 'localhost';
	$port = 11211;
	$timeout = 1;
	// Connect to Memcache:
	$memcache = new Memcache;
	if (!@$memcache->pconnect($host, $port, $timeout)) {
		//Try again with a non-persistent connection
		if (!$memcache->connect($host, $port, $timeout)) {
			var_dump('\n\nmemcache did not connect!\n\n');
		}
	}
}

?>

</body>
</html>
