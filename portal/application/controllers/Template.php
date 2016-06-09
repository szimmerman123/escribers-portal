<?php 

class Template Extends MY_Controller {
 
    public function header() {
        $this->load->view('templates/header');
    }
    
    public function footer() {
        $this->load->view('templates/footer');
    }
}