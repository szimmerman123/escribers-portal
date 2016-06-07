<?php 

class commandHandler {
    // email address 
// check if inputEmailAddress was filled out
if (isset($_POST['inputEmailAddress'])) {
    $emailAddress = $_POST['inputEmailAddress'];
}
// check if preauthorized email address - ie clientid is in link
$clientID = $_GET['clientID'];
if ($clientID)
{
// check if client is in database
    $client = $db->check_client_db($clientID);
}

// check if email address exists in clientuser table for this client
$emailInClientUser = $db->check_email_in_client_users($emailAddress);
if ($emailInClientUser)
{
// if yes, assign this email address to this client
    $client['emailaddress'] = $emailAddress;    
// email will be confirming email address (and assigning the email address to the user)
// message
    $UUID = uniqid();
    $message = "
    <html>
    	<head>
    		<title>
    			New user registration for escriber
    		</title>
    	</head>
    	<body>
    		<p>
    			Please confirm your email address
    		</p>
    		<a href = ". $base_url. "/registerEmail?clientID=" .$clientID."&emailAddress=".$emailAddress. "></a>
    	</body>
    </html>
    ";
    $mail = new postmark();
    $mail->send('operations@escribers.com', 'Escribers', $emailAddress, null, 'New user registration', null, $message);
}
else{
    
}
// if not, send to registration form to collect information 
// send email "Waiting for Authorization"
// redirect to "waiting for authorization" page

// clicked on link from registration email
// if already authorized, will be logged into regular dashboard pages
// if not yet authorized, still show "waiting for authorization"

    
}