<?php

class Database {
    
    const PAGESIZE = 25; // number of items to display on a page
    
    private $connection;
    
    public function __construct() {
        $this->connection = new mysqli(config::$dbhostname, config::$dbusername, config::$dbpassword, config::$dbdatabase);
        if ($this->connection->connect_errno) {
            echo "Failed to connect to MySQL: (" . $this->connection->connect_errno . ") " . $this->connection->connect_error;
            die();
        }
    }
    
    function updateJobInvoice($ref, $amt) {
        $invoiceid = 0;
        $jobid = 0;
        $clientid = 0;
        if (filter_var($ref, FILTER_VALIDATE_INT) !== false) {
            // integer: this is an invoice
            $res = $this->connection->query('select i.*, j.clientid, j.jobtypeid from invoice i join job j on j.invoiceid = i.id where i.id = ' . intval($ref));
            if ($res->num_rows == 1) {
                $invoice = $res->fetch_assoc();
                $clientid = $invoice['clientid'];
            }
        } else {
            // non-integer: this is a job ref
            $res = $this->connection->query('select * from job where reference like "' . mysqli_real_escape_string($this->connection, $ref) . '%"');
            if ($res->num_rows == 1) { // check there's only one match
                $job = $res->fetch_assoc();
                $clientid = $job['clientid'];
                if ($job['invoiceid'] > 0) {
                    // job already invoiced?
                    $res = $this->connection->query('select * from invoice where id = ' . $job['invoiceid']);
                    if ($res->num_rows == 1) {
                        $invoice = $res->fetch_assoc();
                    }
                }
            }
        }
        
        if (isset($invoice)) {
            // this is an invoice payment
            $invoiceid = $invoice['id'];
            
            // add a receipt to the job for this amount
            $this->connection->query('insert into receipts (jobid, invoiceid, amount, type, date_entered, reference) 
                                        values (' . $invoice['jobid'] .','. $invoice['id'] .','. $amt .',"Credit Card",now(),"CC")');
            
            // check the amount paid agrees with outstanding amount
            // comparing two floats using epsilon method - see "Comparing floats" here: http://php.net/manual/en/language.types.float.php
            if (abs($invoice['total'] - $invoice['amtpaid'] - $amt) < 0.001) 
                // paying outstanding amount - update invoice
                $this->connection->query('update invoice set amtpaid = total, paid = 1, paiddate = now(), checkno = "cc" where id = ' . $invoiceid);
            else
                // partial payment 
                $this->connection->query('update invoice set amtpaid = amtpaid + ' . floatval($amt) . ', paid = 0, paiddate = now(), checkno = "cc" where id = ' . $invoiceid);

            // if this is a Re-org research job, then ping the order status api to send the download link
            if ($invoice['jobtypeid'] == 308) {
                $opts = array(
                    'http' => array(
                        'method' => 'GET',
                        'header' => "X-Api-Key: g8SUowH1gPIoNI1Zi8hI7g1YxRowtz70\r\n"
                    )
                );
                $context = stream_context_create($opts);
                file_get_contents('http://tabula.escribers.net/orderapi/orderstatus/' . $invoice['jobid'], false, $context);
            }
            
            // if the update was successful, add a line to the chage log
            if ($this->connection->affected_rows == 1)
                $this->connection->query('insert into jobchangelog (jobid, userid, description, uri, postdata, marginchange) values (' .
                                         $invoice['jobid'] . ', 0, "Invoice payment by Credit Card: $' . floatval($amt) . '", "authorize.net", "", 0)');
        } elseif (isset($job)) {
            // deposit payment
            $jobid = $job['id'];
//echo '<pre>'; print_r($job); echo '</pre><br />';
            if ($job['jobtypeid'] == 103) { // Vermont
                switch ($job['status']) {
                    case 0 : // Job received, no deposit, audio not ordered
                        if ($job['audiorequestdate'] != 0)
                            // audio is ordered
                            $status = 10; // Deposit paid/exempt, awaiting audio
                        else
                            // audio is not ordered
                            $status = 5; // Deposit paid/exempt, audio not yet ordered
                        break;
                    case 20 : // Audio received, awaiting deposit
                        $status = 30; // Deposit paid, audio received, awaiting assignment
                }
            }
            $this->connection->query('insert into receipts (jobid, invoiceid, amount, type, date_entered, reference) values (' . $jobid .',0,'. $amt .',"Credit Card",now(),"Deposit")');
            $sql = 'update job set depositamt = ' . floatval($amt) . ', 
                    depositdate = now(), deposittype = "Credit Card", depositstatus = 50' .
                    (isset($status) ? ', status = ' . $status : '') .
                    ' where id = ' . $jobid;
            $this->connection->query($sql);
            if ($_SERVER['SERVER_NAME'] == 'localhost')
                file_get_contents('http://localhost/tab/trunk/api/recachejob/' . $jobid);
            else
                file_get_contents('http://tabula.escribers.net/api/recachejob/' . $jobid);
            if ($this->connection->affected_rows == 1) {
                $this->connection->query('insert into jobchangelog (jobid, userid, description, uri, postdata, marginchange) values (' .
                                         $jobid . ', 0, "Deposit paid by Credit Card: $' . floatval($amt) . '", "authorize.net", "", 0)');
            }
        }

        return array('invoiceid' => $invoiceid, 'jobid' => $jobid, 'clientid' => $clientid);
    }
    
    function saveAuthorizeNetTransaction($response) {
        $response_vars = get_object_vars($response);
        $txn_id = $response_vars['trans_id'];
        $invoiceid = 0;
        $jobid = 0;
        $result = array();
        $amount = $response_vars['amount'];
        if ($response_vars['approved']) {
            $payment_status = 'approved';
            // now update job/invoice with payment
            $ref = $response_vars['invoice_number']; // may be job ref or invoice number
            $result = $this->updateJobInvoice($ref, $amount);
            $invoiceid = $result['invoiceid'];
            $jobid = $result['jobid'];
        } else if ($response_vars['declined'])
            $payment_status = 'declined';
        else if ($response_vars['error'])
            $payment_status = 'error';
        else if ($response_vars['held'])
            $payment_status = 'held';
        else 
            $payment_status = 'unknown';
        
        // save the transaction to the DB
        $this->connection->query('insert into ppipn (txn_id, payment_status, postdata, invoiceid, jobid, amount) values ("' . 
                $txn_id . '", "' . $payment_status . '", "' . mysqli_real_escape_string($this->connection, serialize($response_vars)) . '", ' . 
                $invoiceid . ', ' . $jobid . ', ' . $amount . ')');
        
        return $result; // array of jobid, invoiceid and clientid
    }
    
    function saveAuthorizeNetProfile($clientid, $profileid, $paymentprofileid) {
        return $this->connection->query('insert into anetcim (clientid, customerprofileid, paymentprofileid) 
                                  values (' . intval($clientid) . ', 
                                          "' . mysqli_real_escape_string($this->connection, $profileid) . '", 
                                          "' . mysqli_real_escape_string($this->connection, $paymentprofileid) . '")' );
    }
    
    function check_login($username, $password) {
        $res = $this->connection->query('SELECT * fROM clientuser WHERE username = "' . mysqli_real_escape_string($this->connection, $username) . '" LIMIT 1');
        if ($res->num_rows == 0)
            return false;
        $user = $res->fetch_assoc();
        $hash = hash('sha256', $user['salt'] . $password . $user['salt']);
        if ($hash == $user['password']) 
            return $user;
        else
            return false;
    }
    
    function change_password($userid, $newpass) {
        return $this->connection->query('UPDATE clientuser SET password = "' . mysqli_real_escape_string($this->connection, $newpass) . '" WHERE id=' . intval($userid));
    }
    
    function checkEmailInClient($clientid){
        
    }
    
    function getFiles($query = '', $jobtypeid, $limit = self::PAGESIZE, $offset = 0) {
        $sql = '
            select j.docketno, j.re, j.reference, j.hearing, j.hearingtype, a.origfilename, a.jobid, a.id as attachid, i.idate, date(a.uploaded) as uploaded, a.bucket
            from jobattachment a join job j on j.id = a.jobid join invoice i on i.jobid = j.id
            where i.id = j.invoiceid and a.type = "transcript" and j.jobtypeid = ' . intval($jobtypeid) . 
            ($query ? ' and j.re like "%' . mysqli_real_escape_string($this->connection, $query) . '%"' : '') . 
           ' order by a.id desc limit ' . intval($offset) . ',' . intval($limit);
//echo $sql; exit;
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[] = $row;
        return $ret;
    }
    
    function getClientFiles($query = '', $clientid, $limit = self::PAGESIZE, $offset = 0) {
        $sql = '
            select j.docketno, j.re, j.reference, j.hearing, j.hearingtype, a.origfilename, a.jobid, a.id as attachid, i.idate, date(a.uploaded) as uploaded, a.bucket
            from jobattachment a join job j on j.id = a.jobid join invoice i on i.jobid = j.id
            where i.id = j.invoiceid and a.type = "transcript" and j.clientid = ' . intval($clientid) . 
            ($query ? ' and j.re like "%' . mysqli_real_escape_string($this->connection, $query) . '%"' : '') . 
           ' order by a.id desc limit ' . intval($offset) . ',' . intval($limit);
//echo $sql; exit;
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[] = $row;
        return $ret;
    }

    function getAgencyFiles($agencyid, $filetypes = '') {
        if (empty($filetypes))
            $typelist = '"audio"';
        else
            $typelist = '"' . implode('","', $filetypes) . '"';
        $sql = 'select j.reference, j.re, j.hearing, a.* from job j join jobattachment a on a.jobid = j.id 
                where j.cosboardid = ' . intval($agencyid) . ' and a.type in (' . $typelist . ') order by j.hearing desc';
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[] = $row;
        return $ret;
    }
    
    function getTotal($query, $jobtypeid) {
        $res = $this->connection->query('
            select count(*) as count
            from jobattachment a join job j on j.id = a.jobid join invoice i on i.jobid = j.id
            where i.id = (select max(id) from invoice where jobid = j.id) 
            and a.type = "transcript" and j.jobtypeid = ' . intval($jobtypeid) . 
            ($query ? ' and j.re like "%' . mysqli_real_escape_string($this->connection, $query) . '%"' : '')
        );
        return mysqli_fetch_object($res)->count;
    }
    
    function getClientTotal($query, $clientid) {
        $res = $this->connection->query('
            select count(*) as count
            from jobattachment a join job j on j.id = a.jobid join invoice i on i.jobid = j.id
            where i.id = (select max(id) from invoice where jobid = j.id) 
            and a.type = "transcript" and j.clientid = ' . intval($clientid) . 
            ($query ? ' and j.re like "%' . mysqli_real_escape_string($this->connection, $query) . '%"' : '')
        );
        return mysqli_fetch_object($res)->count;
    }

    function getFileMeta($ids) { // array of attachment ids
        $cleanids = array();
        foreach ($ids as $id) $cleanids[] = intval($id); // only use the integer value to prevent SQL injection
        $res = $this->connection->query('select * from jobattachment where id in (' . implode(',', $cleanids) . ')');
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[] = $row;
        return $ret;
    }
    
    function getFilesForDate($startdate, $enddate = false, $jobtypeid) {
        if (!$enddate)
            $enddate = $startdate;
        $sql = '
            select j.docketno, j.re, j.reference, j.hearing, a.origfilename, a.jobid, a.id as attachid, i.idate, date(a.uploaded) as uploaded, a.bucket
            from jobattachment a 
            join job j on j.id = a.jobid 
            join invoice i on i.jobid = j.id
            where i.id = j.invoiceid 
            and a.type = "transcript" 
            and j.jobtypeid = ' . intval($jobtypeid) . '
            and date(a.uploaded) >= "' . $startdate . '" and date(a.uploaded) <= "' . $enddate . '"
            order by a.id desc';
//echo '<pre>'; echo $sql; exit;            
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[] = $row;
        return $ret;
    }
    
    function getJobs($jobtypeids, $completed = false, $type = 'transcript') {
        if (is_array($jobtypeids)) $jobtypeids = implode(',', $jobtypeids);
        $sql = 'select j.id, received, j.reference, j.clientid, j.clientref, j.re as caption, c.name as client, j.pagecount, d.description as turnaround, j.clientdue, 
                    j.depositdate, j.depositstatus, j.audiodate, j.pending, j.docketno, j.orderdata, j.hearing, j.hearingtype,' . 
                	($completed ? '"Job Complete"' : 'if(count(*) = sum(a.transcribercomplete), "Transcript Done", if(count(a.id) > 0, "In Progress", "Received"))') . ' as status, 
                	j.ordername as assistant, count(uploads.id) as uploads, j.copy ' . 
                    ($completed ? ', max(idate) as invoicedate, i.pdffilename as invoice, i.bucket as invoicebucket, GROUP_CONCAT(f.origfilename) as transcript, GROUP_CONCAT(f.bucket) as buckets ' : '') . 
               'from job j
                join client c on j.clientid = c.id
                join deliverytype d on d.id = j.deliverytypeid ' .
                ($completed ? ' join invoice i on i.id = j.invoiceid ' : ' left outer join jobassignment a on a.jobid = j.id ') .
               '
                left outer join jobattachment f on (f.jobid = j.id) and (f.type = "' . $type . '")
                left outer join uploads on caseinfo = j.reference and uploadcomplete = 1
                where jobtypeid in (' . $jobtypeids . ') and cancelled = 0 ' . 
                (!$completed ? ' and j.id not in (select jobid from invoice) ' : '') .  
               'group by j.id';
//if (!$completed) exit($sql);

        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[] = $row;
        return $ret;
    }
    
    function getHearings($date, $jobtypeid) {
        $sql = 'select * from job where jobtypeid = ' . $jobtypeid . ' and hearing="' . $date . '" and cancelled = 0';
//exit($sql);
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[] = $row;
        return $ret;
    }
    
    function getJobFromRef($ref, $uid) {
        $sql = 'select * from job where reference like "' . $ref . '%" and uid="' . $uid . '"';
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            // there can only be one row
            return $res->fetch_assoc();
        else
            return $ret;
    }
    
    function getJobFromId($jobid) {
        $sql = 'select * from job where id="' . $jobid . '"';
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            // there can only be one row
            return $res->fetch_assoc();
        else
            return $ret;
    }
    
    function depositMailCheck($jobid) {
        $sql = 'update job set depositstatus="20" where id=' . intval($jobid); // 20 = check in mail
        $ret = $this->connection->query($sql);
        return $ret;
    }
    
    function getClientNames($jobtypeid) {
        $sql = 'select id, name from client where id in (select clientid from clientjobtype where jobtypeid = ' . intval($jobtypeid) . ')';
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[$row['id']] = $row['name'];
        return $ret;
    }
    
    function getJobType($jobtypeid) {
        $res = $this->connection->query('select * from jobtype where id = ' . intval($jobtypeid));
        if ($res)
            // there can only be one row
            return $res->fetch_assoc();
        else
            return false;
    }
    
    function getDeliveryTypes() {
        $sql = 'select id, description from deliverytype';
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            while ($row = $res->fetch_assoc())
                $ret[$row['id']] = $row['description'];
        return $ret;
    }
    
    function getDefaultRates($jobtypeid) {
        $sql = 'select `Same_Day`, `Daily`, `2-Day`, `3-Day`, `4-Day`, `5-Day`, `6-Day`, `7-Day`, `10-Day`, `14-Day`, `20-Day`, `30-Day`, `45-Day` 
                from clientrates where id = (select defaultrates from jobtype where id = ' . intval($jobtypeid) . ')';
        $res = $this->connection->query($sql);
        $ret = array();
        if ($res)
            if ($row = $res->fetch_assoc()) {
                foreach ($row as $col => $val)
                    if ($val != 0) $ret[$col] = $val;
                return $ret;
            }
        return array();
    }
    
    function unmark_transcript($filename) {
        $sql = 'update jobattachment set type="none" where origfilename="' . mysqli_real_escape_string($this->connection, $filename) . '"';
//echo $sql; exit;
        return $this->connection->query($sql);
    }
    
}