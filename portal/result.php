<?php 
//echo '<pre>'; print_r($_SESSION); echo '</pre>'; 

if (isset($_SESSION['curl_error'])) {
    echo '<h3>An error occurred:</h3>';
    echo '<pre>';
    print_r($_SESSION['curl_error']);
    echo '</pre>';
    echo '<p>If possible, please send a screenshot of this page to support@escribers.net.  Thank you.</p>';
    exit;
}

if (isset($_SESSION['php_error'])){
    echo '<h3>An error occurred:</h3>';
    echo '<pre>';
    print_r($_SESSION['php_error']);
    echo '</pre>';
    echo '<p>If possible, please send a screenshot of this page to support@escribers.net.  Thank you.</p>';
    exit;
}

if (isset($_SESSION['formresult'])) {
    $result = unserialize($_SESSION['formresult']);
    echo '<script type="text/javascript">console.log(\'' . print_r($_SESSION['formresult'], true) . '\');</script>';
} 
?>
<style>
html {overflow-y: scroll;}
</style>
<br />
<?php if (isset($result['jobref']) OR isset($_GET['jobref'])) : ?>
<div class="alert alert-info col-md-10 col-md-offset-1">
    <h4>Job Submitted</h4>
    <p>Your job was submitted successfully.  The reference for the job is: <?php echo isset($result['jobref']) ? $result['jobref'] : $_GET['jobref']; ?></p>
    <p>You can now view your order in the <a href="?page=filelist">List Orders</a> screen.  From there you can go to the file upload page for the job.</p>
</div>
<?php else : ?>
<div class="alert alert-danger col-md-10 col-md-offset-1">
    <h4>Job Submission Error</h4>
    <p>Unfortunately, there was a problem with your order.  Please try again.<br />We apologize for any inconvenience.</p>
</div>
<?php endif; ?>

