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
    var statuses = document.getElementById('statuses');
    var div = document.createElement('div');
    var label = document.createElement('label');
    label.innerHTML = filename.replace('&', '-') + ':';
    div.appendChild(label);
    var status = document.createElement('span')
    status.setAttribute('id', 'status' + num);
    div.appendChild(status);
    var percent = document.createElement('span');
    percent.setAttribute('id', 'percent' + num);
    percent.className = 'percent';
    var pbar = document.createElement('span');
    pbar.setAttribute('id', 'progress_bar' + num);
    pbar.className = 'progress_bar';
    pbar.appendChild(percent);
    div.appendChild(pbar);
    var cancel = document.createElement('img');
    cancel.src = 'images/cancel.png';
    cancel.setAttribute('id', 'cancel' + num);
    div.appendChild(cancel);
    statuses.appendChild(div);
}

/**
 * Execute the given callback with the signed response.
 */
function executeOnSignedUrl(file, num, uid, callback) {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'signput.php?name=' + uid + '/' + file.name.replace(" ", "+").replace("&", "-") + '&type=' + (file.type ? file.type : 'application/octet-stream'), true);

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
        var postfields = {
            filename: uid + '/' + file.name.replace(" ", "+").replace("&", "-"),
            size: file.size,
            mimetype: (file.type ? file.type : 'application/octet-stream'),
            email: $('#owner').val(),
            caseinfo: $('#case').val(),
            notes: $('#notes').val(),
            bucket: 'escribers-us'
        };
        $.post('https://tabula.escribers.net/api/storefileinfo/0',
        //$.post('http://localhost/tab/trunk/api/storefileinfo/0',
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
        filessent.push(file);
        sentcount++;
    }
  }
}

function setProgress(num, percent, statusLabel, full) { 
  document.getElementById('progress_bar' + num).className = 'loading';
  var progress = document.getElementById('percent' + num);
  progress.style.width = percent + '%';
  progress.innerHTML = statusLabel + '&nbsp;' + percent + '%';
  if (full == 2) {
    progress.style.width = '100%';
    $('#percent' + num).addClass('error');
    progress.innerHTML = '&nbsp;' + statusLabel;
  } else if (full == 1) {
    $('#percent' + num).addClass('full');
    progress.innerHTML = '&nbsp;' + statusLabel;
  } else {
    progress.innerHTML = '&nbsp;' + statusLabel + '&nbsp;' + percent + '%';
  }
}

var completecount = 0;
var filessent = [];
var sentcount = 0;

function checkAllComplete() {
    completecount++;

    // generate a random "unique" identifier
    var uid = '';
    while (uid.length != 6)
        uid = Math.random().toString(36).substring(6, 12);
    var zipname = uid + '/files-' + uid + '.zip'

    if (completecount == sentcount) {
        $('#zipping').html('<h3>Finalizing, please wait <img style="vertical-align: baseline;" src="images/ajax-loader.gif" width="43" height="11" /></h3>');
        //$.ajax({url: 'http://localhost/tab/trunk/api/sendfilelist',
        //$.ajax({url: 'https://tabula.escribers.net/api/sendfilelist/vermont',
        $.ajax({url: 'https://worker-aws-us-east-1.iron.io/2/projects/51374a012267d80210001716/tasks/webhook?code_name=Zipper&oauth=6DX98LXWiuOBBqki4Po1Zr2QeXY',
                type: 'POST',
                data: JSON.stringify({
                    files: filessent, 
                    zipname: zipname,
                    court: 'vermont',
                    email: $('#owner').val(),
                    caseinfo: $('#case').val(),
                    notes: $('#notes').val(),
                    ping: 'https://tabula.escribers.net/api/zipcomplete'
                })
               }).done(function(ret){
                   console.log(ret);
                   if (ret.msg == 'Queued up.') {
                       $('#zipping').html('<h3>Uploaded files received OK.</h3><p><a href="#" onclick="newupload(false);">Click here to start a new upload</a></p>'); 
                   } else {
                       $('#zipping').html('<h3>Uploading files failed.</h3><p><a href="#" onclick="newupload(false);">Click here to start a new upload</a></p>');
                   } 
               }).fail(function(){
                   $('#zipping').html('<h3>Failed to complete upload.</h3><p><a href="#" onclick="newupload(false);">Click here to start a new upload</a></p>'); 
               });
    }
}

function newupload(keep) {
    var email = $('#owner')[0].value;
    //var ref = $('#case')[0].value;
    //var notes = $('#notes')[0].value;
    var url = 'https://escribers.net/vtupload.php?email=' + encodeURIComponent(email);
//    if (email || ref || notes) url = url + '?';
//    if (email) {
//        url = url + 'email=' + encodeURIComponent(email);
//        if (ref || notes) url = url + '&';
//    }
//    if (ref) {
//        url = url + 'ref=' + encodeURIComponent(ref);
//        if (notes) url = url + '&';
//    }
//    if (notes)  url = url + 'notes=' + encodeURIComponent(notes);
    //console.log(url);
    window.location.assign(url);
}

$('#submit').bind('click', function() {
    if (filecount == 0) {
        alert('You must select some files first.');
        return;
    }
    submitted = true;
    //$('#files').attr('disabled', true);
   //$('#filechooser').html('<h3 style="color: red;">3. No more files may be added once upload has begun.</h3>');
    //$('#clickhere').html('See upload progress below.');
    $('#forminfo').slideUp(800);
    $('#progresstitle').html('<h3>Please wait while uploads complete...</h3>');
    for (i = 0; i < filecount; i++) {
        if (xhrs[i] && (xhrs[i].readyState > 0)) {
            xhrs[i].send(filelist[i]);
            filessent.push({name:filelist[i]['name'].replace("&", "-"),type:filelist[i]['type'],size:filelist[i]['size'],uid:filelist[i]['uid']});
            sentcount++;
        }
    }
});    

$('#files').bind('change', handleFileSelect);
