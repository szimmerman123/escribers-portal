<?php

require_once 'awslib/sdk.class.php';

class awss3 {

    const BUCKET = 'escribers';
    private $S3;
    
    function __construct() {
	   $this->S3 = new AmazonS3();
    }
    
    public function saveFile($filename, $data, $contenttype) {
        $res = $this->S3->create_object(self::BUCKET, $filename, array('body' => $data, 'contentType' => $contenttype));
        return (substr($res->status, 0, 2) == '20');
    }
    
    public function readFile($filename) {
        $res = $this->S3->get_object(self::BUCKET, $filename);
        if($res->status == 200)
            return $res->body;
        else
            return false;
    }
    
    public function getAuthorisedUrl($filename, $expires, $secure = false, $bucket = self::BUCKET) {
        $parts = explode('/', $filename);
        $parts[count($parts) - 1] = rawurlencode($parts[count($parts) - 1]);
        $filename = implode('/', $parts);
        $stringtosign = "GET\n\n\n$expires\n/" . $bucket . '/' . $filename;
        $sig = urlencode(base64_encode(hash_hmac('sha1', utf8_encode($stringtosign), AWS_SECRET_KEY, true)));
        if ($secure)
            $url = 'https://';
        else
            $url = 'http://'; 
        $url .= $bucket . '.s3.amazonaws.com/' . $filename . '?AWSAccessKeyId=' . AWS_KEY . '&Expires=' . $expires . '&Signature=' . $sig;
        return $url;
    }
    
    public function deleteFile($filename) {
        $res = $this->S3->delete_object(self::BUCKET, $filename);
//echo '<pre>' . htmlspecialchars(print_r($res, true)); exit;
        return (substr($res->status, 0, 2) == '20');
    }
    
    public function fileExists($filename) {
        return $this->S3->if_object_exists(self::BUCKET, $filename);
    }
    
}