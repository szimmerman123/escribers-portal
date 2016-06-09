<?php 

defined('BASEPATH') OR exit('No direct script access allowed'); 

class User extends MY_Controller {
    
    public function index() {
        // redirect index page to the login page
        redirect('user/login');
    }

    public function test() {
        $this->load->view('templates/header');  
        $this->load->view('welcome_message');
        $this->load->view('templates/footer'); 
    }

    public function login() {
        
        // get logged in user from session
        if (isset($_SESSION['user']))
            $user = $_SESSION['user'];
        else
            $user = false;
        
        $this->data = array(
            'user' => $user,
        );
    	
        $this->load->view('user/login', $this->data);
    }
    
    function checkLogin($data){
        // check login form data
        if (isset($_POST['inputUsername']) && isset($_POST['inputPassword'])) {
            $username = $_POST['inputUsername'];
            $password = $_POST['inputPassword'];
            $this->load->model('MUser');
            $user = $this->MUser->check_login($username, $password);
            //sess_regenerate(true); // removed as sess_time_to_update is set in config
            
            $redirectView = 'user/nav';
            // check if user is being referred from a page other than login and redirect there after logging in
            $this->load->library('user_agent');
            $referrerUrl = $this->agent->referrer();
            if (!strpos($referrerUrl, 'user/login')){
                $redirectView = $referrerUrl;
            }                
                
            if ($user) {
                $_SESSION['user'] = $user;
                $this->session->set_flashdata('Logged in OK.');
                redirect($redirectView);
               //redirect('user/nav');
            } else {
                unset($_SESSION['user']);
                $this->session->set_flashdata('Username or password incorrect.');
                redirect('user/login');
            }
        }
    } 
    
    public function register() {
        
        $this->load->view('templates/header');  
        $this->load->view('user/register');
        $this->load->view('templates/footer'); 
    }
    
    function signup() {
        echo 'sending email';
    }
    public function logout() {
        // manual logout 
        unset($_SESSION['user']);
        $this->session->set_flashdata('Username or password incorrect.');
        redirect('user/login');
    }
    
    public function nav() {         
        $view = 'nav';
        // determine which page to load
        $redirectPath = $this->getNavView($view); 
        $data = array(
          'user' => $_SESSION['user']
        );
            
        
        $this->load->view('templates/header');  
        $this->load->view($redirectPath, $data); 
        $this->load->view('templates/footer');        
    }
    
    public function filelist() {        
        // determine which page to load
        $navViewPath = $this->getPathToView('nav');          
        $fileListViewPath = $this->getPathToView('filelist');  
        $fileListFunc = $this->getFileListLogic();
                      
        $data = array(
          'user' => $_SESSION['user'],
          'jobtype' => $fileListFunc['jobtype'],
          'currentjobs' => $fileListFunc['currentjobs'],
          'completejobs' => $fileListFunc['completejobs'],
          'jobs' => $fileListFunc['jobs'],
          'jobTypeName'=>$fileListFunc['jobTypeName'],
          'class' => $fileListFunc['class'],
          'dateReceived' => $fileListFunc['dateReceived'],
          'url' => $fileListFunc['url'],
          'caption' => $fileListFunc['caption'],
          'docketNumber' => $fileListFunc['docketNumber'],
          'pageCount' => $fileListFunc['pageCount'],
          'clientDue' => $fileListFunc['clientDue'],
          'authorisedUrl' => $fileListFunc['authorisedUrl']
        );
            
        $this->load->view('templates/header');
        $this->load->view($navViewPath, $data);  
        $this->load->view($fileListViewPath, $data);  
        $this->load->view('templates/footer');    
    }
    
    /**
     * User::getFileListLogic()
     * 
     * @return
     */
    function getFileListLogic() {
        
        $user = $_SESSION['user'];
        $this->load->model('MUser');
        $jobtype = $this->MUser->getJobType($user['jobtypeid']);
        $currentjobs = $this->MUser->getJobs($user['jobtypeid']);
        $completejobs = $this->MUser->getJobs($user['jobtypeid'],true);
        $jobs = $currentjobs + $completejobs;
        
        $jobTypeName = $jobtype->name;
        foreach ($jobs as $job) {
            if (isset($job['invoice']))
                $class = 'success';
            else if ($job['status'] == 'In Progress')
                $class = 'warning';
            else 
                $class = 'info';
        
            $dateReceived = date('M j, Y', strtotime($job['received'])); 
            $url = 'users/upload/jobref=' . urlencode($job['reference']) . '/caption=' . urlencode($job['caption']);
            $caption =  substr(strtok(';', $job['caption']), 0, 40);               
            $docketNumber =  $job['docketno'];
            $pageCount = $job['pagecount'];
            $clientDue = date('M j, Y', strtotime($job['clientdue'])); 
            
            if (isset($job['invoice'])) {
                $authorisedUrl = $this->MUser->getAuthorisedUrl('uploads/' . $job['id'] . '/' . $job['invoice'], strtotime('+2 hours'), true, $job['invoicebucket']);
            } 
        }                
        
        $data = array(
          'user' => $_SESSION['user'],
          'jobtype' => $jobtype,
          'currentjobs' => $currentjobs,
          'completejobs' => $completejobs,
          'jobs' => $jobs,
          'jobTypeName'=>$jobTypeName,
          'class' => $class,
          'dateReceived' => $dateReceived,
          'url' => $url,
          'caption' => $caption,
          'docketNumber' => $docketNumber,
          'pageCount' => $pageCount,
          'clientDue' => $clientDue,
          'authorisedUrl' => $authorisedUrl
        );
        
        return $data;
    }
    
    public function order() {
        
        $view = 'order';
        // determine which page to load
        $redirectPath = $this->getPathToView($view);    
        $data = array(
          'user' => $_SESSION['user']
        );
            
        
        $this->load->view('templates/header'); 
        $this->load->view($redirectPath, $data);  
        $this->load->view('templates/footer');      
    }
    
     public function orderform() {
        $view = 'orderform';
        // determine which page to load
        $redirectPath = $this->getPathToView($view);        
        
        $data = array(
          'user' => $_SESSION['user']
        );
            
        
        $this->load->view('templates/header'); 
        $this->load->view($redirectPath, $data);  
        $this->load->view('templates/footer');      
    }
    
    function getPathToView($view) {
                
        // get user fom SESSION
        $user = $_SESSION['user'];
        $jobTypeID = $user['jobtypeid'];
        $redirectPath = 'user/'.$view;
        $fileName = APPPATH. 'views/user/pages/' . $jobTypeID . '/'.$view.'.php';
        
        // redirect to default nav page unless user has its special pages under a folder called by its jobtypeid
        if (file_exists($fileName)) {
            $redirectPath = 'user/pages/'.$jobTypeID.'/'.$view; 
        }
        else {
            // use default redirectPath
            $redirectPath = 'user/'.$view;
        }
        return $redirectPath;
    }
    
    // test function
    public function userPages() {
        
        $this->load->view('templates/header');
        $this->load->view('user/pages/206/nav');  
        $this->load->view('templates/footer'); 

    }
    
}