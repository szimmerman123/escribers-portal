<?php if ($_GET['page'] != 'filelist') header('Location: ?page=filelist'); ?>
<nav class="navbar navbar-inverse navbar-static-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo $script_url; ?>">eScribers/AVtranz Customer Portal</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav navbar-right">
            <li><p class="navbar-text">Signed in as <?php echo $user['fullname']; ?></p></li>
            <li><a href="<?php echo $base_url . '?cmd=logout'; ?>">Logout</a></li>
          </ul>
        </div><!-- /.navbar-collapse -->
    </div>
</nav>
