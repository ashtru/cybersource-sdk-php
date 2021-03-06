<HTML>
	<HEAD>
       	<META HTTP-EQUIV="Content-Type" content="text/html; charset=iso-8859-1">
		<TITLE>Order Status</TITLE>
	</HEAD>
	<BODY>


<?php

// Before using this example, replace the generic values with your merchant ID and password.
define( 'MERCHANT_ID', 'YOUR MERCH ID' );
define( 'TRANSACTION_KEY', 'YOUR SECRET KEY' );
define( 'WSDL_URL', 'https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.78.wsdl' );


class ExtendedClient extends SoapClient {

   function __construct($wsdl, $options = null) {
     parent::__construct($wsdl, $options);
   }

// This section inserts the UsernameToken information in the outgoing SOAP message.
   function __doRequest($request, $location, $action, $version) {

     $user = MERCHANT_ID;
     $password = TRANSACTION_KEY;

     $soapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>$user</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">$password</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";

     $requestDOM = new DOMDocument('1.0');
     $soapHeaderDOM = new DOMDocument('1.0');

     try {

         $requestDOM->loadXML($request);
	 $soapHeaderDOM->loadXML($soapHeader);

	 $node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
	 $requestDOM->firstChild->insertBefore(
         	$node, $requestDOM->firstChild->firstChild);

         $request = $requestDOM->saveXML();

	 // printf( "Modified Request:\n*$request*\n" );

     } catch (DOMException $e) {
         die( 'Error adding UsernameToken: ' . $e->code);
     }

     return parent::__doRequest($request, $location, $action, $version);
   }
}

try {
	$soapClient = new ExtendedClient(WSDL_URL, array());

	/*
	To see the functions and types that the SOAP extension can automatically
    generate from the WSDL file, uncomment this section:
	$functions = $soapClient->__getFunctions();
	print_r($functions);
	$types = $soapClient->__getTypes();
	print_r($types);
	*/

        $request = new stdClass();

	$request->merchantID = MERCHANT_ID;

	// Before using this example, replace the generic value with your own.
	$request->merchantReferenceCode = "ABC123";

	// To help us troubleshoot any problems that you may encounter,
    // please include the following information about your PHP application.
	$request->clientLibrary = "PHP";
        $request->clientLibraryVersion = phpversion();
        $request->clientEnvironment = php_uname();

	// This section contains a sample transaction request for the authorization
    // service with complete billing, payment card, and purchase (two items) information.
	$ccAuthService = new stdClass();
	$ccAuthService->run = "true";
	$request->ccAuthService = $ccAuthService;

	$payerAuthEnrollService = new stdClass();
	$payerAuthEnrollService->run = "true";
	$request->payerAuthEnrollService = $payerAuthEnrollService;
	
	$billTo = new stdClass();
	$billTo->firstName = "John";
	$billTo->lastName = "Doe";
	$billTo->street1 = "1295 Charleston Road";
	$billTo->city = "Mountain View";
	$billTo->state = "CA";
	$billTo->postalCode = "94043";
	$billTo->country = "US";
	$billTo->email = "null@cybersource.com";
	$request->billTo = $billTo;

	$card = new stdClass();
	$card->accountNumber = "4000000000000002";
	$card->expirationMonth = "12";
	$card->expirationYear = "2020";
	$request->card = $card;

	$purchaseTotals = new stdClass();
	$purchaseTotals->currency = "USD";
	$request->purchaseTotals = $purchaseTotals;

	$item0 = new stdClass();
	$item0->unitPrice = "56.78";
	$item0->id = "1";

	$request->item = array($item0);

	$reply = $soapClient->runTransaction($request);

	// This section will show all the reply fields.
	 var_dump($reply);

	// To retrieve individual reply fields, follow these examples.
	printf( "decision = $reply->decision<br>" );
	printf( "reasonCode = $reply->reasonCode<br>" );
	printf( "requestID = $reply->requestID<br>" );
	printf( "requestToken = $reply->requestToken<br>" );
	
	foreach ($reply->payerAuthEnrollReply as $key => $value) {
			printf($key . " = ". $value . "<br>");
	}
	
	if ($reply->payerAuthEnrollReply->veresEnrolled == "Y") {
	
	echo "<form id=\"PAEnrollForm\" name=\"PAEnrollForm\" action=\"".$reply->payerAuthEnrollReply->acsURL."\" method=\"post\">";
	
	echo "<input type=\"hidden\" name=\"PaReq\" value=\"".$reply->payerAuthEnrollReply->paReq."\"/>";
	
	echo '<input type="hidden" name="TermUrl" value="http://localhost:8080/api_samples/auth_validation-soap.php"/>';
	 
	echo "<input type=\"hidden\" name=\"MD\" value=\"".$reply->payerAuthEnrollReply->xid."\"/>";
	
	echo '<input type="submit" name="rButton" value="Redirect to ACS"/>';
	
	echo "</form>";

	}
	
} catch (SoapFault $exception) {
	var_dump(get_class($exception));
	var_dump($exception);
}
?>




	</BODY>
</HTML>