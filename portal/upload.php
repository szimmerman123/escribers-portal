
<style>

#filechooser {
    position:  relative;
    padding: 15px 25px 25px;
    border: 1px solid #8a6d3b;
    margin: 15px 0 10px;
}
#filechooser input {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    /* IE 8 */
    -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
    /* IE 5-7 */
    filter: alpha(opacity=0);
}
#progress_bar {
  width: 400px;
  margin: 10px 0;
  padding: 3px;
  border: 1px solid #000;
  font-size: 14px;
  clear: both;
  opacity: 0;
  -moz-transition: opacity 1s linear;
  -o-transition: opacity 1s linear;
  -webkit-transition: opacity 1s linear;
}
.filelabel {
    display: inline-block;
    width: 400px;
    text-align: right;
    padding-right: 4px;
}
.progress {margin-bottom: 10px;} 
#statuses img {vertical-align: middle;}

</style>

<?php if (isset($_GET['jobid'])) echo '<input type="hidden" name="jobid" id="jobid" value="' . intval($_GET['jobid']) . '" />'; ?>
<?php if (isset($_GET['jobref'])) echo '<input type="hidden" name="jobref" id="jobref" value="' . $_GET['jobref'] . '" />'; ?>

<div class="well">

    <h4>File upload <?php if (isset($_GET['caption'])) echo 'for case: <span id="caption">' . $_GET['caption'] . '</span>'; ?></h4>
    
    <div id="selector">
        <div id="filechooser" class="alert alert-warning">
            <h4>Drop files in this area or click here to select files to be uploaded</h4>
            <span>Please upload log notes and audio/video files here.<br />No more files can be added once upload has begun.</span>
            <input type="file" id="files" name="files[]" multiple>
        </div>
        <div class="col-md-8 col-md-offset-2">
            <a id="startupload" href="#" class="btn btn-primary btn-lg btn-block">Start Upload</a><br />
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="well">
        <div id="statuses" class="row">
            <p class="col-md-12" id="progresstitle">Upload Progress:</p>
        </div>
    </div>
    

</div>

<script type="text/javascript" charset="utf8" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="js/upload.js"></script>
