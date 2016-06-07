var filecount = 0;
var xhrs = [];

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
  
  if ($('input[name="court"]:checked').length == 0) {
    alert('You must select the court or agency type.');
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
  var type = (file.type ? file.type : 'application/octet-stream');
  if (file.name.substring(file.name.lastIndexOf('.')+1) == 'trm') type = 'application/octet-stream';
  xhr.open('GET', 'signput.php?name=' + uid + '/' + file.name.replace(" ", "+").replace("&", "-") + '&type=' + type, true);

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
  var type = (file.type ? file.type : 'application/octet-stream');
  if (file.name.substring(file.name.lastIndexOf('.')+1) == 'trm') type = 'application/octet-stream';
  if (!xhr) { 
    setProgress(num, 0, 'CORS&nbsp;not&nbsp;supported', 2);
  } else { 
    xhr.onload = function() { 
      if(xhr.status == 200) { 
        // now call the server with the file info to store in the db.
        var postfields = {
            filename: uid + '/' + file.name.replace(" ", "+").replace("&", "-"),
            size: file.size,
            mimetype: type,
            email: $('#owner').val(),
            caseinfo: $('#case').val(),
            notes: $('#notes').val(),
            bucket: 'escribers-us'
        };
        var court = $('input[name="court"]:checked')[0].value;
        var url = 'https://tabula.escribers.net/api/storefileinfo';
        //var url = 'http://localhost/tab/trunk/api/storefileinfo';
        if (court != 'other')
            url = url + '/1/' + court; // 1 = uploadcomplete
        $.post(url,
               postfields,
               function(ret) {
                    if (ret == 'ok') {
                        setProgress(num, 100, 'Upload&nbsp;completed.&nbsp;', 1);
                        $('#cancel' + num).hide();
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

    xhr.setRequestHeader('Content-Type', type);
    xhr.setRequestHeader('x-amz-acl', 'private');
    
    xhrs[num] = xhr;
    var cancel = document.getElementById('cancel' + num)
    cancel.onclick = function() {
        num = this.getAttribute('id').substring(6);
        xhrs[num].abort();
        setProgress(num, 0, 'Cancelled.', 2);
    };

    xhr.send(file);
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

$('#files').bind('change', handleFileSelect);
