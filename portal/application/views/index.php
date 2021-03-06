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
    
            <?php if ($user) if (file_exists('pages/' . $user['jobtypeid'] . '/nav.php')) include('pages/' . $user['jobtypeid'] . '/nav.php'); else include('nav.php'); ?>

            <?php if (isset($_SESSION['flash'])) : ?>

                <div class="alert alert-success" role="alert"><?php echo $_SESSION['flash']; ?></div>

            <?php unset($_SESSION['flash']); endif; ?>

            <?php include($page); ?>       
            
            <div class="text-center col-md-8 col-md-offset-2" style="background-color: white;">
                 <img src="../images/escribers-logo.png" height="108" /> &nbsp; &nbsp; &nbsp; <img src="../avtranz/img/avt_logo.jpg" height="75" /><br /> 
                <h5>&copy; <?php echo date('Y'); ?> eScribers, LLC.  All Worldwide Rights Reserved</h5>
            </div>              
    
        </div> <!-- /container -->

    </body>

</html>
