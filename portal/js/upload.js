var filecount = 0;
var xhrs = [];
var filelist = [];
var submitted = false;

function createCORSRequest(method, url) {
  var xhr = new XMLHttpRequest();
  if ("withCredentials" in xhr) {
    xhr.open(method, url, true);
  } else if (typeof XDomainRequest != "undefined") {
    xhr = new XDomainRequest();
    xhr.open(method, url);
  } else {
    xhr = null;
  }
  return xhr;
}

function handleFileSelect(evt) {
    
  if ($('#owner').val() == '') {
    alert('You must enter your email address.');
    evt.target.value = '';
    return;
  } 

  if ($('#case').val() == '') {
    alert('You must enter the case information.');
    evt.target.value = '';
    return;
  } 

  var files = evt.target.files; 

  for (var i = 0, f; f = files[i]; i++) {
    addFileHTML(filecount, f.name.replace('&', '-'));
    setProgress(filecount, 0, 'Queued.', 0);
    
    // generate a random "unique" identifier
    var uid = '';
    while (uid.length != 6)
        uid = Math.random().toString(36).substring(6, 12);
        
    uploadFile(f, filecount, uid);
    filecount++;
  }
}

function addFileHTML(num, filename) {
//    var statuses = $('#statuses');
//    var div = document.createElement('div');
//    var label = document.createElement('label');
//    label.setAttribute('class', 'filelabel');
//    label.innerHTML = filename.replace('&', '-') + ':';
//    div.appendChild(label);
//    var status = document.createElement('span')
//    status.setAttribute('id', 'status' + num);
//    div.appendChild(status);
//    var percent = document.createElement('span');
//    percent.setAttribute('id', 'percent' + num);
//    percent.className = 'percent';
//    var pbar = document.createElement('span');
//    pbar.setAttribute('id', 'progress_bar' + num);
//    pbar.className = 'progress_bar';
//    pbar.appendChild(percent);
//    $(div).append(pbar);
//    var cancel = document.createElement('img');
//    cancel.src = '../images/cancel.png';
//    cancel.setAttribute('id', 'cancel' + num);
//    div.appendChild(cancel);
    var div = $('<div class="col-md-5 text-right">' + filename.replace('&', '-') + ':</div>' +
                '<div class="col-md-6">' +
                    '<div class="progress">' +
                        '<div class="progress-bar progress-bar-striped active" id="progress_bar' + num + '" role="progressbar"' + 
                                'aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%; min-width: 11%;">' +
                            '<span id="status' + num + '">Queued</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-1">' +
                    '<img id="cancel' + num + '" src="../images/cancel.png" />' +
                '</div>');
    $('#statuses').append(div);
}

/**
 * Execute the given callback with the signed response.
 */
function executeOnSignedUrl(file, num, uid, callback) {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '../signput.php?name=' + uid + '/' + file.name.replace(" ", "+").replace("&", "-") + '&type=' + (file.type ? file.type : 'application/octet-stream'), true);

  // Hack to pass bytes through unprocessed.
  //xhr.overrideMimeType('text/plain; charset=x-user-defined'); 

  xhr.onreadystatechange = function(e) {
    if (this.readyState == 4 && this.status == 200) {
      callback(decodeURIComponent(this.responseText));
    } else if(this.readyState == 4 && this.status != 200) {
      setProgress(num, 0, 'Could&nbsp;not&nbsp;contact&nbsp;signing&nbsp;script.&nbsp;Status:&nbsp;' + this.status, 2);
    }
  };

  xhr.send();
}

function uploadFile(file, num, uid) {  
  executeOnSignedUrl(file, num, uid, function(signedURL) 
  {
    uploadToS3(file, num, uid, signedURL);
  });
}


/**
 * Use a CORS call to upload the given file to S3. Assumes the url
 * parameter has been signed and is accessible for upload.
 */
function uploadToS3(file, num, uid, url) { 
  var xhr = createCORSRequest('PUT', url);
  if (!xhr) { 
    setProgress(num, 0, 'CORS&nbsp;not&nbsp;supported', 2);
  } else { 
    xhr.onload = function() { 
      if(xhr.status == 200) { 
        // now call the server with the file info to store in the db.
        var caption = ($('#caption') ? $('#caption').html() : '');
        var jobref = ($('#jobref') ? $('#jobref').val() : '');
        var postfields = {
            filename: uid + '/' + file.name.replace(" ", "+").replace("&", "-"),
            size: file.size,
            mimetype: (file.type ? file.type : 'application/octet-stream'),
            email: 'Phoenix Municipal Court',
            caseinfo: jobref,
            notes: caption,
            bucket: 'escribers-us'
        };
        var ext = file.name.split('.').pop();
        var complete = ($.inArray(ext, ['doc', 'docx', 'pdf', 'fls', 'htm', 'html', 'txt']) > -1) ? 1 : 0;
        $.post('https://tabula.escribers.net/api/storefileinfo/' + complete,
        //$.post('http://localhost/tabtrunk/api/storefileinfo/' + complete,
               postfields,
               function(ret) {
                    if (ret == 'ok') {
                        setProgress(num, 100, 'File&nbsp;uploaded.&nbsp;', 1);
                        $('#cancel' + num).hide();
                        checkAllComplete();
                    } else {
                        setProgress(num, 100, 'Upload&nbsp;failed.&nbsp;ret&nbsp;=&nbsp;' + ret, 2);
                    }
               }).fail(function(){
                    setProgress(num, 100, 'Upload&nbsp;failed.', 2);
               });
      } else { 
        setProgress(num, 0, 'Upload&nbsp;error:&nbsp;' + xhr.status, 2);
      }
    };

    xhr.onerror = function(e) { 
      setProgress(num, 0, 'XHR&nbsp;error:&nbsp;' + e.type, 2);
    };

    xhr.upload.onprogress = function(e) { 
      if (e.lengthComputable) { 
        var percentLoaded = Math.round((e.loaded / e.total) * 100);
        setProgress(num, percentLoaded, (percentLoaded == 100 ? 'Saving.' : 'Uploading.'), 0);
      }
    };

    xhr.setRequestHeader('Content-Type', (file.type ? file.type : 'application/octet-stream'));
    xhr.setRequestHeader('x-amz-acl', 'private');
    
    xhrs[num] = xhr;
    var cancel = document.getElementById('cancel' + num)
    cancel.onclick = function() {
        cnum = this.getAttribute('id').substring(6);
        xhrs[cnum].abort();
        setProgress(cnum, 0, 'Cancelled.', 2);
    };

    filelist[num] = file;
    filelist[num].uid = uid;
    
    if (submitted) {
        xhr.send(file);
        var ext = file.name.split('.').pop();
        if ($.inArray(ext, ['doc', 'docx', 'pdf', 'fls', 'htm', 'html', 'txt']) == -1) {
            filessent.push({name:file.name, size:file.size, type:file.type, uid:file.uid});
        }
        sentcount++;
    }
  }
}

function setProgress(num, percent, statusLabel, full) { 
  //document.getElementById('progress_bar' + num).className = 'loading';
  var progress = $('#progress_bar' + num);
  progress.width(percent + '%');
  progress.attr({'aria-valuenow' : percent});
  //progress.innerHTML = statusLabel + '&nbsp;' + percent + '%';
  $('#status' + num).html(num + '%');
  if (full == 2) {
    progress.width('100%');
    progress.addClass('progress-bar-danger');
    progress.removeClass('active');
    progress.html(statusLabel);
  } else if (full == 1) {
    progress.addClass('progress-bar-success');
    progress.removeClass('active');
    progress.html(statusLabel);
  } else {
    if (percent == 0)
        progress.html('Queued');
    else
        progress.html(percent + '%');
  }
}

var completecount = 0;
var filessent = [];
var sentcount = 0;
var hearings = 1;

function checkAllComplete() {
    completecount++;
    if (completecount == sentcount) {
        if (filessent.length == 0) {
            $('#progresstitle').html('Files received OK.  Click "Order Transcript" on the menu above to start a new order.')
                                .removeClass('alert-info')
                                .addClass('alert-success');
        } else {
            // generate a random "unique" identifier
            var uid = '';
            while (uid.length != 6)
                uid = Math.random().toString(36).substring(6, 12);
            var filedate = filessent[0].name.match(/_(.*?)-/);
            if (filedate)  filedate = filedate[1]; else filedate = uid;
            var zipname = uid + '/files-' + filedate + '.zip'
            
            var caption = ($('#caption') ? $('#caption').html() : '');
            var jobref = ($('#jobref') ? $('#jobref').val() : '');

            $('#progresstitle').html('Finalizing, please wait <img style="vertical-align: baseline;" src="../images/ajax-loader.gif" width="43" height="11" />');
            //$.ajax({url: 'http://localhost/tab/trunk/api/sendfilelist/maine',
            //$.ajax({url: 'https://tabula.escribers.net/api/sendfilelist/maine',
            $.ajax({url: 'https://worker-aws-us-east-1.iron.io/2/projects/51374a012267d80210001716/tasks/webhook?code_name=Zipper&oauth=6DX98LXWiuOBBqki4Po1Zr2QeXY',
                    type: 'POST',
                    //data: JSON.stringify(filessent) // ['name','type','size','uid']
                data: JSON.stringify({
                    files: filessent, 
                    zipname: zipname,
                    court: '',
                    email: 'Phoenix Municipal Court',
                    caseinfo: jobref,
                    notes: caption,
                    ping: 'https://tabula.escribers.net/api/zipcomplete'
                })
                   }).done(function(ret){
                       console.log(ret);
                       if (ret.msg == 'Queued up.') {
                           $('#progresstitle').html('Files received OK.  Click "Order Transcript" on the menu above to start a new order.')
                                .removeClass('alert-info')
                                .addClass('alert-success'); 
                       } else {
                           $('#progresstitle').html('Uploading files failed.')
                                .removeClass('alert-info')
                                .addClass('alert-danger');
                       } 
                   }).fail(function(){
                       $('#progresstitle').html('Failed to complete upload.')
                            .removeClass('alert-info')
                            .addClass('alert-danger'); 
                   });
        }
    }
}

$(document).ready(function() {
    
    $('#startupload').bind('click', function(e) {
        e.preventDefault();
        if (filecount == 0) {
            alert('You must select some files first.');
            return;
        }
        submitted = true;
        $('#selector').slideUp(500);
        $('#mytabs').slideUp(500);
        $('#progresstitle').html('Please wait while uploads complete...').addClass('alert alert-info');
        for (i = 0; i < filecount; i++) {
            if (xhrs[i] && (xhrs[i].readyState > 0)) {
                xhrs[i].send(filelist[i]);
                var ext = filelist[i].name.split('.').pop();
                if ($.inArray(ext, ['doc', 'docx', 'pdf', 'fls', 'htm', 'html', 'txt']) == -1) {
                    filessent.push({name:filelist[i].name, size:filelist[i].size, type:filelist[i].type, uid:filelist[i].uid});
                }
                sentcount++;
            }
        }
    });    
    
    $('#files').bind('change', handleFileSelect);
    $('#files')
        .on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#filechooser').css('background-color', '#DCD8C3');
        })
        .on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#filechooser').css('background-color', '#fcf8e3');
        })
        .on('drop', function(e) {
            $('#filechooser').css('background-color', '#fcf8e3');
        });
        
});