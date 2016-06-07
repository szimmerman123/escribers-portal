<?php
require('../library/session.class.php');
$session = new session();
$session->start_session('_esc', false);

// save form data for repopulating on error
$_SESSION['formdata'] = serialize($_POST); 

// API key
$key = 'sedconguevelituttristiquehasellusegetduinecsemamet';  
 
// Find client's IP address
$ip_address = '';
if (!empty($_SERVER['HTTP_CLIENT_IP']))
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    foreach ($ips as $ip)
        if (preg_match('/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $ip, $matches))
            if (!preg_match('/(^127\.0\.0\.1)|(^10\.)|(^172\.1[6-9]\.)|(^172\.2[0-9]\.)|(^172\.3[0-1]\.)|(^192\.168\.)/', $matches[0])) {
                $ip_address = $matches[0];
                break;
            }
}
if (empty($ip_address)) $ip_address = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $ch = curl_init('localhost/tabtrunk/api');
    $redirect = 'http://localhost/escribers/portal/?page=result';
} else {
    $ch = curl_init('https://tabula.escribers.net/api');
    $redirect = 'https://escribers.net/portal/?page=result';
}

$cmds = array(206 => 'phoenix');
$jobtypeid = $_POST['jobtypeid'];

$postdata = file_get_contents("php://input");
$postdata .= '&cmd=' . $cmds[$jobtypeid] . '&ip=' . $ip_address;
$postdata .= '&hsh=' . hash_hmac('sha256', $postdata, $key);

//exit('<pre>' . print_r($_POST, true));
//exit($postdata); 

curl_setopt($ch, CURLOPT_HEADER, 0); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); 
$error = '';
if (!$res = curl_exec($ch)) {
    $error = curl_error($ch);
};
curl_close($ch);
//echo $res; exit;

if (!$data = json_decode($res, true))
    $data['php_error'] = $res; 
if ($error)
    $data['curl_error'] = $error;

$_SESSION['formresult'] = serialize($data);

//echo '<pre>' . print_r($_SESSION, true); exit;

header('Location: ' . $redirect); 
