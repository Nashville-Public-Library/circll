<!DOCTYPE html>
<html>
<head>
<title>circll</title>
<link rel="stylesheet" type="text/css" href="circll.css">

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
	if (isset($_POST["item"])) {
		$item = strtoupper(htmlspecialchars(stripslashes(trim($_POST["item"]))));
	} else {
		$item = '';
	}
	if (isset($_POST["alias"])) {$alias = htmlspecialchars(stripslashes(trim($_POST["alias"])));} else {$alias = '';}
//	if (isset($_POST["nbduedate"])) {$nbduedate = htmlspecialchars(stripslashes(trim($_POST["nbduedate"])));} else {$nbduedate = '';}
//	if (isset($_POST["nbduedate07"])) {$nbduedate07 = htmlspecialchars(stripslashes(trim($_POST["nbduedate07"])));} else {$nbduedate07 = '';}
//	if (isset($_POST["nbduedate21"])) {$nbduedate21 = htmlspecialchars(stripslashes(trim($_POST["nbduedate21"])));} else {$nbduedate21 = '';}
//	if (isset($_POST["nbduedate42"])) {$nbduedate42 = htmlspecialchars(stripslashes(trim($_POST["nbduedate42"])));} else {$nbduedate42 = '';}
	if (isset($_POST["customNotes"])) {$customNotes = htmlspecialchars(stripslashes(trim($_POST["customNotes"])));} else {$customNotes = '';}
} else { 
// TESTING ITEM
//	$item = '35192038783290';
	$item = '';
	$alias = '';
}
	$today = new DateTime('today');
	$nbduedate = new DateTime('today');
	$nbduedate = $nbduedate->add(new DateInterval("P42D"));
	$maxduedate = new DateTime('today');
	$maxduedate = $maxduedate->add(new DateInterval("P2M")); 
	$minduedate = new DateTime('January 8, 2024'); // for setting a minimum due date for, e.g., winter break
	$nbduedate07 = new DateTime('today');
	$nbduedate07 = $nbduedate07->add(new DateInterval("P9D")); // default for 7 day loans should be 9 days for delivery buffer
	$nbduedate07 = min($nbduedate, $nbduedate07);
	$nbduedate07 = max($nbduedate07, $minduedate);
	$nbduedate21 = new DateTime('today');
	$nbduedate21 = $nbduedate21->add(new DateInterval("P21D"));
	$nbduedate21 = min($nbduedate, $nbduedate21);
	$nbduedate21 = max($nbduedate21, $minduedate);
	$nbduedate42 = new DateTime('today');
	$nbduedate42 = $nbduedate42->add(new DateInterval("P42D"));
	$nbduedate42 = min($nbduedate, $nbduedate42);
	$nbduedate42 = max($nbduedate42, $minduedate);
//	$nbduedate21 = '';
//	$nbduedate42 = '';
	$customNotes = '';
//}

?>

<body>

<div id="screen">
  <header id="formHeader">Limitless Libraries</header>
  <form id="circll" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
    <div class="row"><label for="item">Item Barcode: </label><input type="text" id="item" name="item" autofocus></div>
    <div class="row"><label for="alias">Staff initials: </label><input type="text" id="alias" name="alias" value="<?php if(isset($alias)){echo $alias;} ?>"></div>
    <div class="row">
        <label for="nbduedate">NB Due Date: </label>
        <input type="date" id="nbduedate" name="nbduedate" value="<?php if(!empty($nbduedate)){echo date_format($nbduedate, 'Y-m-d');} ?>" readonly >
    </div>
    <div class="row">
        <label for="nbduedate07">7-day DVD due: </label>
	<input type="date" id="nbduedate07" name="nbduedate07" value="<?php if(!empty($nbduedate07)){echo date_format($nbduedate07, 'Y-m-d');} ?>" min="<?php echo date_format($today, 'Y-m-d'); ?>" max="<?php echo date_format($maxduedate, 'Y-m-d'); ?>" >
    </div>
    <div class="row">
        <label for="nbduedate21">21-day due date: </label>
	<input type="date" id="nbduedate21" name="nbduedate21" value="<?php if(!empty($nbduedate21)){echo date_format($nbduedate21, 'Y-m-d');} ?>" min="<?php echo date_format($today, 'Y-m-d'); ?>" max="<?php echo date_format($maxduedate, 'Y-m-d'); ?>" readonly >
    </div>
    <div class="row">
        <label for="nbduedate42">42-day due date: </label>
	<input type="date" id="nbduedate42" name="nbduedate42" value="<?php if(!empty($nbduedate42)){echo date_format($nbduedate42, 'Y-m-d');} ?>" min="<?php echo date_format($today, 'Y-m-d'); ?>" max="<?php echo date_format($maxduedate, 'Y-m-d'); ?>" readonly >
    </div>
    <div class="row"><label for="customNotes">Custom Notes: </label><textarea id="customNotes" name="customNotes" form="circll" maxlength="150" rows="4"><?php if(!empty($customNotes)){echo $customNotes;} ?></textarea></div>
    <div class="row"><label for="submit"> </label><input type="submit" id="submit" name="submit" value="Submit"></div>
  </form>
  <footer id="formFooter">Have a Nice Day</footer>
</div>

<?php

if (empty($patronApiWsdl)) {
	//Read default configuration file
	//$configFile = ROOT_DIR . '/../../sites/default/conf/config.ini';
	//$mainArray = parse_ini_file($configFile, true);

	global $fullServerName, $serverName, $instanceName;

	if (isset($_SERVER['circll_server'])) {
		//Override within the config file
		$fullServerName = $_SERVER['circll_server'];
		//echo("Server name is set as server var $fullServerName\r\n");
	} else {
	    die('No server name could be found to load configuration');
	}

    $configArray			= parse_ini_file('../../sites/' . $fullServerName . '/config.pwd.ini', true, INI_SCANNER_TYPED);
		$circulationApiLogin		= $configArray['Catalog']['circulationApiLogin'];
		$circulationApiPassword		= $configArray['Catalog']['circulationApiPassword'];
		$circulationApiWsdl		= $configArray['Catalog']['circulationApiWsdl'];
		$circulationApiDebugMode	= $configArray['Catalog']['circulationApiDebugMode'];
		$circulationApiReportMode	= $configArray['Catalog']['circulationApiReportMode'];
		$patronApiWsdl			= $configArray['Catalog']['patronApiWsdl'];
		$patronApiDebugMode		= $configArray['Catalog']['patronApiDebugMode'];
		$patronApiReportMode		= $configArray['Catalog']['patronApiReportMode'];
		$catalogApiWsdl			= $configArray['Catalog']['catalogApiWsdl'];
		$catalogApiDebugMode		= $configArray['Catalog']['catalogApiDebugMode'];
		$catalogApiReportMode		= $configArray['Catalog']['catalogApiReportMode'];
}

$receipt = checkout($item,$alias,$nbduedate07,$nbduedate21,$nbduedate42,$customNotes);
$css = file_get_contents('./circll.css');
$receipt = "<style>$css</style>" . $receipt;
$receipt .= '<script>(function(){printReceipt();})();</script>';
echo $receipt;

//////////////////// FUNCTIONS ////////////////////

function callAPI($wsdl, $requestName, $request, $tag) {
	global $circulationApiLogin;
	global $circulationApiPassword;
	$connectionPassed = false;
	$numTries = 0;
	$result = new stdClass();
	$result->response = "";
	while (!$connectionPassed && $numTries < 3) {
		try {
			$client = new SOAPClient($wsdl, array('connection_timeout' => 3, 'features' => SOAP_WAIT_ONE_WAY_CALLS, 'trace' => 1, 'login' => $circulationApiLogin, 'password' => $circulationApiPassword));
			$result->response = $client->$requestName($request);
			$connectionPassed = true;
			if (is_null($result->response)) {$result->response = $client->__getLastResponse();}
			if (!empty($result->response)) {
				if (gettype($result->response) == 'object') {
					$ShortMessage[0] = $result->response->ResponseStatuses->ResponseStatus->ShortMessage;
					if ($ShortMessage[0] == 'Successful operation') {
						$result->success = $ShortMessage[0];
					} else {
						$result->error = "ERROR: " . $tag . " : " . $ShortMessage[0];
					}
				} else if (gettype($result->response) == 'string') {
					$result->success = stripos($result->response, '<ns2:ShortMessage>Successful operation</ns2:ShortMessage>') !== false;
					preg_match('/<ns2:LongMessage>(.+?)<\/ns2:LongMessage>/', $result->response, $longMessages);
					preg_match('/<ns2:ShortMessage>(.+?)<\/ns2:ShortMessage>/', $result->response, $shortMessages);
					if (!empty($shortMessages)) {
 						$result->error  .= implode($shortMessages);
					} elseif (!empty($longMessages)) {
						$result->error  .= implode($longMessages);
					}
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
//		echo '<h1>result->error</h1>';
//		var_dump($result->error);
//		echo "\n\n";
	} else {
//		echo "SUCCESS: " . $tag . "\n";
	}
	return $result;
}

function checkout($item, $alias = '', $nbduedate07 = '', $nbduedate21 = '', $nbduedate42 = '', $customNotes = '') {
	include('sip2.class.php');
	date_default_timezone_set('America/Chicago');
	$mysip				= new sip2;
	$result				= $mysip->connect();
	$mysip->patron			= '';

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
	$mediaName		= isset($result['variable']['XF']) ? implode($result['variable']['XF']) : '';
//var_dump($mysip->patron);
// IF DESTINATION BRANCH IS NOT A SCHOOL OR THERE IS NO PATRON ON HOLD, PRINT TRANSIT SLIP
	if (preg_match('/^\D/', $destinationBranch) === 1 || $mysip->patron == '') {
		$receipt = '<div id="print">';
		$receipt .= "<div id='receipt' class='transit'>";
		if ($alertType == '02') {
			$receipt .= "<div id='hold'>HOLD</div>"; 
		} else {
			$receipt .= "<div id='hold'> </div>";
		}
		if (preg_match('/^\d/', $destinationBranch) === 1) {
            global $catalogApiWsdl;
            global $catalogApiDebugMode;
            global $catalogApiReportMode;
            $requestName				= 'getBranchInformation';
            $tag					= $requestName;
            $requestBranch				= new stdClass();
            $requestBranch->BranchSearchType	= 'Branch Code';
            $requestBranch->BranchSearchValue	= $destinationBranch;
            $requestBranch->Modifiers		= new stdClass();
            $requestBranch->Modifiers->DebugMode	= $catalogApiDebugMode;
            $requestBranch->Modifiers->ReportMode	= $catalogApiReportMode;
            $resultBranch				= callAPI($catalogApiWsdl, $requestName, $requestBranch, $tag);
            if ($resultBranch && $resultBranch->response->BranchInfo) {
                $destinationBranchName = $resultBranch->response->BranchInfo->BranchName;
            }
			$receipt .= "<div id='destinationBranch'>MNPS</div>";
			$receipt .= "<div id='destinationBranchName'>$destinationBranchName</div>";
		} else {
			$receipt .= "<div id='destinationBranch'>$destinationBranch</div>"; 
		}
		$receipt .= "<div id='item'>ITEM NUMBER: $item</div>"; 
		$receipt .= "<div id='title'>TITLE: $title</div>"; 
		if (preg_match('/^\d/', $destinationBranch) === 1) {
			$receipt .= "<div id='circNote'>MNPS LIBRARIAN: Check in this item and shelve it.</div>";
		}
		$receipt .= "<div id='checkinDateTime'>$checkinDateTime</div>"; 
		$receipt .= "</div>";
		$receipt .= "</div>";
		return $receipt;
	}

// IF DESTINATION BRANCH IS A SCHOOL AND THERE IS A PATRON ON HOLD, CHECK OUT ITEM AND PRINT DUE SLIP
//	if (preg_match('/^\d/', $destinationBranch) === 1 && !empty($mysip->patron)) {

/* SIP2 CHECKOUT NOT CURRENTLY USED. MODIFY CIRCULATION API CHECKOUT INSTEAD
// SIP2 CHECKOUT
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
*/

// CIRCULATION API CHECKOUT
	global $circulationApiLogin;
	global $circulationApiPassword;
	global $circulationApiWsdl;
	global $circulationApiDebugMode;
	global $circulationApiReportMode;
	$requestName					= 'CheckoutItem';
	$tag						= $requestName . ' ' . $item . ' to ' . $mysip->patron;
	$requestCheckoutItem				= new stdClass();
	$requestCheckoutItem->Modifiers			= new stdClass();
	$requestCheckoutItem->Modifiers->DebugMode	= $circulationApiDebugMode;
	$requestCheckoutItem->Modifiers->ReportMode	= $circulationApiReportMode;
	$requestCheckoutItem->Modifiers->EnvBranch	= 'LL'; // Checkout terminal = Limitless Libraries
	$requestCheckoutItem->PatronSearchType		= 'Patron ID';
	$requestCheckoutItem->PatronSearchID		= $mysip->patron; // Patron ID
	$requestCheckoutItem->ItemID			= $item; // Item Barcode
	$requestCheckoutItem->Alias			= $alias; // Staffer alias
	if (!empty($mediaName) && preg_match('/^(dvd|dvd, r-rated|blu-ray, circ 1-week|blu-ray, r-rated, circ 1-week)$/', $mediaName) === 1 && !empty($nbduedate07)) { // DUE DATE RECALCULATIONERATOR - HARD CODED TO RECOGNIZE 7-DAY MOVIE MEDIA
		$requestCheckoutItem->DueDate	= date_format($nbduedate07, 'Y-m-d');
	} elseif (!empty($mysip->patron) && (strlen($mysip->patron) == 6 || strlen($mysip->patron) == 7) && !empty($nbduedate42)) { // DUE DATE RECALCULATIONERATOR - HARD CODED TO RECOGNIZE 42-CHECKOUT FOR MNPS STAFF
		$requestCheckoutItem->DueDate	= date_format($nbduedate42, 'Y-m-d');
	} elseif (!empty($nbduedate21)) { // DUE DATE RECALCULATIONERATOR - HARD CODED BUCKET FOR 21-DAY CHECKOUT
		$requestCheckoutItem->DueDate	= date_format($nbduedate21, 'Y-m-d');
	}
//var_dump($requestCheckoutItem);
	$resultCheckoutItem				= '';
	$resultCheckoutItem				= callAPI($circulationApiWsdl, $requestName, $requestCheckoutItem, $tag);

// IF CIRCULATION API CHECKOUT ERROR, ABORT
	if (isset($resultCheckoutItem->error)) {
		$receipt	= "<div id='print' class='error'>";
		$receipt	.= "<div id='message'>" . $resultCheckoutItem->error . "</div>";
		$receipt	.= "<div id='checkinDateTime' data-checkinDateTime=$checkinDateTime>$checkinDateTime</div>"; 
		$receipt	.= "</div>";
		$receipt	.= "</div>";
		return $receipt;
	}
// DUE DATE
//	$dueDate	= date_create_from_format('Y-m-d-H:i', $resultCheckoutItem->response->DueDate);
//	$checkoutDate	= date_create();
//	$loanPeriod	= date_diff($dueDate, $checkoutDate);
//var_dump($loanPeriod->format('%R%a days'));
	$dueDate	= isset($resultCheckoutItem->response->DueDate) ? date_format(date_create_from_format('Y-m-d-H:i', $resultCheckoutItem->response->DueDate), 'F d, Y') : '';

// ADDITIONAL PATRON INFORMATION VIA SOAP
	global $circulationApiLogin;
	global $circulationApiPassword;
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
//var_dump($resultPatron);
	if($resultPatron->response->ResponseStatuses->ResponseStatus->ShortMessage == 'Successful operation') {
		$borrowerTypeCode		= (int) $resultPatron->response->Patron->PatronType;
		if ($borrowerTypeCode == 35 || $borrowerTypeCode == 36 || $borrowerTypeCode == 37) {
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
            $requestName				= 'getBranchInformation';
            $tag					= $requestName;
            $requestBranch				= new stdClass();
            $requestBranch->BranchSearchType	= 'Branch Code';
            $requestBranch->BranchSearchValue	= $branchCode;
            $requestBranch->Modifiers		= '';
            $resultBranch				= callAPI($catalogApiWsdl, $requestName, $requestBranch, $tag);
            if ($resultBranch && $resultBranch->response->BranchInfo) {
                $branchName = $resultBranch->response->BranchInfo->BranchName;
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
				case $borrowerTypeCode==46:
					$borrowerClass	= 'Student';
					$borrowerGrade	= 'H';
					break;
				case $borrowerTypeCode==40:
					$borrowerClass	= 'MNPS Librarian';
					$borrowerGrade	= '';
					break;
				case $borrowerTypeCode==42:
					$borrowerClass	= 'MNPS Library';
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
				case $borrowerTypeCode==47:
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
		$receipt 	.= "<dt>School</dt><dd class='emboldened'>$branchName</dd>"; 
		if ($borrowerClass == 'Student') {
			$receipt	.= "<dt>Grade $borrowerGrade</dt><dd>$sponsorName</dd>"; 
		}
		$receipt 	.= "<dt>$borrowerClass</dt><dd>$patronName</dd>"; 
		$receipt 	.= "<dt>Due</dt><dd class='emboldened'>$dueDate</dd>"; 
		$receipt	.= "</dl>";
		$receipt 	.= '<div id="footer"><p>Students and Educators: Return to your school library or any NPL location</p><p>School Librarians: Return to NPL</p><p>NPL Staff: Remove label and check in</p></div>';
		if (!empty($customNotes)) {
			$receipt	.= '<div id="customNotes">' . $customNotes . '</div>';
		}
		$receipt 	.= '</div></div>';
		return $receipt;
	}
}

?>

</body>
</html>
