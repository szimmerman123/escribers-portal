<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    
    protected $data;
    
    function __construct() {
        
        // extend parent
        parent::__construct();
        
         // check page was loaded using HTTPS and redirect if not
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'http')) {
            redirect('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
        }
        
        $this->load->library('session');
        $this->load->helper('url');
    }
}