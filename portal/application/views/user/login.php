<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="../favicon.ico">

        <title>Customer Portal | eScribers/AVTranz</title>

        <!-- Bootstrap core CSS -->
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
            <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        
        <style>
        * {border-radius: 0 !important;}
        </style>

    </head>

    <body>
    
        <div class="container">    
            
            <?php if (isset($_SESSION['flash'])) : ?>

                <div class="alert alert-success" role="alert"><?php echo $_SESSION['flash']; ?></div>

            <?php unset($_SESSION['flash']); endif; ?>

<style>
body {
    padding-top: 40px; padding-bottom: 40px;
    background-image: url(../images/legalbackground2.jpg); 
    background-size: 100% 100%; background-repeat: no-repeat; background-attachment: fixed;
}
.container {background-color: transparent;}
.form-signin {max-width: 330px; padding: 15px; margin: 0 auto;}
.form-signin .form-signin-heading, .form-signin .checkbox {margin-bottom: 10px;}
.form-signin .checkbox {font-weight: normal;}
.form-signin .form-control {position: relative; height: auto; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; padding: 10px; font-size: 16px;}
.form-signin .form-control:focus {z-index: 2;}
.form-signin input[type="email"] {margin-bottom: -1px; border-bottom-right-radius: 0; border-bottom-left-radius: 0;}
.form-signin input[type="password"] {margin-bottom: 10px; border-top-left-radius: 0; border-top-right-radius: 0;}
</style>

<div class="alert alert-info col-md-8 col-md-offset-2">

    <h2 class="text-center">eScribers/AVTranz Customer Portal</h2>

    <h3 class="text-center">Secure Transcript Management System</h3>

    <div class="row">

        <div class="col-md-12">

            <form class="form-signin" method="post" action="checkLogin">
                <h4 class="form-signin-heading">Please sign in:</h4>
                <label for="inputUsername" class="sr-only">Username</label>
                <input type="text" name="inputUsername" class="form-control" placeholder="Username" required autofocus>
                <label for="inputPassword" class="sr-only">Password</label>
                <input type="password" name="inputPassword" class="form-control" placeholder="Password" required>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
            </form>
            
            <!--<p class="text-center"><br />For new logins or password reset, please contact eScribers.</p>-->
            <p class="text-center"><a href="<?php echo site_url("user/register"); ?>">Click here to register.</a></p>

        </div>

    </div>

</div>
