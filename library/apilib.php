<?php

function apicall($postdata) {
    $key = 'sedconguevelituttristiquehasellusegetduinecsemamet'; 
    
    $postdata .= '&hsh=' . hash_hmac('sha256', $postdata, $key);
    
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        $ch = curl_init('localhost/tab/trunk/api');
    } else {
        $ch = curl_init('tabula.escribers.net/api');
    }
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); 
    $error = '';
    if (!$res = curl_exec($ch)) {
        return 0;
    };
    curl_close($ch);
//echo $res; exit;
    
    $data = json_decode($res, true);
    return $data;
}

