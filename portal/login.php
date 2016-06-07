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

            <form class="form-signin" method="post">
                <h4 class="form-signin-heading">Please sign in:</h4>
                <label for="inputUsername" class="sr-only">Username</label>
                <input type="text" name="inputUsername" class="form-control" placeholder="Username" required autofocus>
                <label for="inputPassword" class="sr-only">Password</label>
                <input type="password" name="inputPassword" class="form-control" placeholder="Password" required>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
            </form>
            
            <!--<p class="text-center"><br />For new logins or password reset, please contact eScribers.</p>-->
            <p class="text-center"><a href="?page=register.php">Click here to register.</a></p>

        </div>

    </div>

</div>
