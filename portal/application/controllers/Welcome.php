<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
        // set timezone
        date_default_timezone_set('America/New_York'); 
        
        header('Content-Type: text/html; charset=utf-8');
        
        // check page was loaded using HTTPS and redirect if not
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'http')) {
            header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
            exit();
        }
        
        // set base url (don't use ssl on localhost)
        //$base_url = 'http' . (($_SERVER['SERVER_NAME'] == 'localhost') ? '://localhost/escribers' : 's://' . $_SERVER['SERVER_NAME']) . '/portal/';
        $script_url = 'http' . (($_SERVER['SERVER_NAME'] == 'localhost') ? '' : 's') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
        
        // start session using db session class
        require('../library/session.class.php');
        $session = new session();
        $session->start_session('_esc', false); // use '_esc' as the session cookie name
        
        // check if last request was more than 2 hours ago, make user re-login if timed out
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 7200)) {
            unset($_SESSION['LAST_ACTIVITY']);
            unset($_SESSION['user']);
            $_SESSION['flash'] = 'Session timed out. Please log in again.';
            header('Location: ' . $script_url);
            exit();
        }
        
        // update last activity time stamp
        $_SESSION['LAST_ACTIVITY'] = time(); 
        
        // manual logout 
        if (isset($_GET['cmd']) && ($_GET['cmd'] == 'logout')) {
            unset($_SESSION['user']);
            $_SESSION['flash'] = 'Logged out OK.';
            header('Location: ' . $base_url);
            exit();
        }
        
        // get logged in user from session
        if (isset($_SESSION['user']))
            $user = $_SESSION['user'];
        else
            $user = false;
        
        //load config class
        if ($_SERVER['SERVER_NAME'] == 'localhost')
            require('../library/config.localhost.php');
        else
            require('../library/config.php');
        
        // get DB connection
        require('../library/database.class.php');
        $db = new Database();
        
        
        // check login form data
        if (isset($_POST['inputUsername']) && isset($_POST['inputPassword'])) {
            $username = $_POST['inputUsername'];
            $password = $_POST['inputPassword'];
            $user = $db->check_login($username, $password);
            session_regenerate_id(true);
            if ($user) {
                $_SESSION['user'] = $user;
                $_SESSION['flash'] = 'Logged in OK.';
            } else {
                unset($_SESSION['user']);
                $_SESSION['flash'] = 'Username or password incorrect.';
                header('Location: ' . $script_url);
                exit();
            }
        }
        
        if ($user) {
            // user is logged in
            $page = 'order.php'; // default page
            // is there a request for a specific page?
            if (isset($_GET['page'])) 
                $page = $_GET['page'] . '.php';
            // see if there is a customized version of this page for this user
            if (file_exists('pages/' . $user['jobtypeid'] . '/' . $page))
                $page = 'pages/' . $user['jobtypeid'] . '/' . $page;
            else if (!file_exists($page)) 
                // if reqeusted page doesn't exist, go back to using the default
                $page = 'order.php';
        } 
        else if(isset($_GET['page'])) {
            // not logged in, but want to register or complete registration as new user
            $page = $_GET['page'] . '.php';
        }
        else {
            // no user logged in, show the login page
            $page = 'login.php';
        }
        header('Content-Type: text/html; charset=utf-8');
    		$this->load->view('index.php');
    	}
}
