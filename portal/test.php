<?php 
           $username = 'barnabyd';
           $fullname = 'Deboarh Barnaby (MCJV)';
           $salt = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 32);
           $pass = 'escribers';
           $hash = hash('sha256', $salt . $pass . $salt);
          // $this->db->insert('clientuser', array(
//               'username' => $username,
//               'salt' => $salt,
//               'password' => $hash,
//               'fullname' => $fullname,
//               'jobtypeid' => 216,
//               'agencyid' => 0
//           ));
           echo $username . ' : ' . $pass . 'hash : ' . $hash . 'salt: ' . $salt .'<br />';
   
   
   
   ?>