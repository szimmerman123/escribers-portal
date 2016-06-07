<?php 
$jobtype = $db->getJobType($user['jobtypeid']);
$currentjobs = $db->getJobs($user['jobtypeid']);
$completejobs = $db->getJobs($user['jobtypeid'], true);
$jobs = $currentjobs + $completejobs;
?>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css">

<style>
li span {padding: 2px 10px;}
li span.bg-info {border: 1px solid lightblue;}
</style>

<div class="col-lg-12">

    <div class="row">
        
        <div class="col-md-12 alert alert-info text-center"> 
            
            <h3><strong><?php echo $jobtype['name']; ?></strong></h3>
    
            <h4>Current Transcript Orders</h4>

        </div>
        
        <div class="col-md-12 alert alert-info"> 

            <h4>Help:</h4>
            <ul>
                <li>Click on the column titles to sort the table on that column.</li>
                <li>You can use the search box to find data in any column.</li>
                <li>
                    The status of each job is indicated by the following row colors:
                    <span class="bg-info">Received</span>
                    <span class="bg-warning">In progress</span>
                    <span class="bg-success">Complete</span>
                </li>
            </ul>

        </div>
        
        <div class="col-md-12 alert alert-info"> 

<?php // echo '<pre>'; print_r($jobs); echo '</pre>'; ?>

            <table id="joblist" class="table table-hover table-condensed" data-order='[[ 0, "desc" ]]'>
            
                <thead>
                    <tr><th>Order Date</th><th>Title (Click to upload audio)</th><th>Complaint Number</th><th>Pages</th><th>Due Date</th><th>Invoice</th></tr>
                </thead>
                
                <tbody>
                    <?php 
                    foreach ($jobs as $job) : 
                        if (isset($job['invoice']))
                            $class = 'success';
                        else if ($job['status'] == 'In Progress')
                            $class = 'warning';
                        else 
                            $class = 'info';
                    ?>
                    <tr class="<?php echo $class; ?>">
                        <td><?php echo date('M j, Y', strtotime($job['received'])); ?></td>
                        <td>
                            <?php 
                            echo '<a href="?page=upload&jobref=' . urlencode($job['reference']) . '&caption=' . urlencode($job['caption']) . '">';
                            echo substr(array_shift(explode(';', $job['caption'])), 0, 40);
                            echo '</a>'; 
                            ?>
                        </td>
                        <td><?php echo $job['docketno']; ?></td>    
                        <td><?php echo $job['pagecount']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($job['clientdue'])); ?></td>
                        <td>
                            <?php if (isset($job['invoice'])) : ?>
                            <a href="<?php echo getAuthorisedUrl('uploads/' . $job['id'] . '/' . $job['invoice'], strtotime('+2 hours'), true, $job['invoicebucket']); ?>" target="_blank">
                                <span class="glyphicon glyphicon-cloud-download aria-hidden="true"></span> Download
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            
            </table>
            
        </div>
        
    </div>
        
</div>

<script type="text/javascript" charset="utf8" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" charset="utf8" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#joblist').dataTable({
        'lengthMenu' : [[-1, 10, 25, 50], ['All', 10, 25, 50]]
    });
});
</script>

<?php
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
