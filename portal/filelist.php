<?php 
$jobtype = $db->getJobType($user['jobtypeid']);
$jobs = $db->getJobs($user['jobtypeid']);
?>

<div class="col-lg-12">

    <div class="row">
        
        <div class="col-md-12 alert alert-info text-center"> 
            
            <h3><strong><?php echo $jobtype['name']; ?></strong></h3>
    
            <h4>Current Transcript Orders</h4>

        </div>
        
        <div class="col-md-12 alert alert-info"> 

<?php //echo '<pre>'; print_r($jobs); echo '</pre>'; ?>

            <table class="table table-striped table-hover table-condensed">
            
                <thead>
                    <tr><th>Order Date</th><th>Title</th><th>Case Number</th><th>Client</th><th>Pages</th><th>Due Date</th></tr>
                </thead>
                
                <tbody>
                    <?php foreach ($jobs as $job) : ?>
                    <tr>
                        <td><?php echo date('M j, Y', strtotime($job['received'])); ?></td>
                        <td><?php echo array_shift(explode(';', $job['caption'])); ?></td>
                        <td><?php echo $job['docketno']; ?></td>    
                        <td><?php echo $job['client']; ?></td>
                        <td><?php echo $job['pagecount']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($job['clientdue'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            
            </table>
            
        </div>
        
    </div>
        
</div>

