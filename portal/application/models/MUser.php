<?php

defined('BASEPATH') OR exit('No direct script access allowed'); 

class MUser extends MY_Model {
    
    public function check_login($username, $password) {
        $sql = "SELECT * FROM clientuser WHERE username LIKE '%" . 
            $this->db->escape_like_str($username) ."%' LIMIT 1" ;
        $res = $this->db->query($sql);  
        if ($res->num_rows() > 0){
            foreach ($res->result_array() as $user)
            {
                $hash = hash('sha256', $user['salt'] . $password . $user['salt']);
                if ($hash == $user['password'])
                    return $user;         
                           
            }                   
        }               
            else 
                return false;     
       }
       
     public function getJobType($jobtypeid) {
        $sql = 'select * from jobtype where id = ' . intval($jobtypeid);
        $res = $this->db->query($sql); 
        $row = $res->row();
        if(isset($row)) {            
            // there can only be one row
            return $row;
        }
        else {
            return false;            
        }
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

        $res = $this->db->query($sql); 
        $ret = array();
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $ret[] = $row;
            }
        }
        return $ret;
    }
    
    function getAuthorisedUrl($filename, $expires, $secure = false, $bucket = 'escribers-us') {
        $key = 'AKIAJEBEOK4HMWPOMOKQ';
        $secret = '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';
        $stringtosign = "GET\n\n\n$expires\n/" . $bucket . '/' . $filename;
        $sig = urlencode(base64_encode(hash_hmac('sha1', utf8_encode($stringtosign), $secret, true)));
        if ($secure)
            $url = 'https://';
        else
            $url = 'http://'; 
        $url .= $bucket . '.s3.amazonaws.com/' . $filename . '?AWSAccessKeyId=' . $key . '&Expires=' . $expires . '&Signature=' . $sig;
        return $url;
    }
        
    
}
    
   