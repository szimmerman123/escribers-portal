<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css">

<style>
li span {padding: 2px 10px;}
li span.bg-info {border: 1px solid lightblue;}
</style>

<div class="col-lg-12">

    <div class="row">
        
        <div class="col-md-12 alert alert-info text-center"> 
            
            <h3><strong><?php echo $jobTypeName; ?></strong></h3>
    
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
                    
                    <tr class="<?php echo $class; ?>">
                        <td><?php echo $dateReceived; ?></td>
                        <td>
                            <?php 
                            echo '<a href="'.$url.'">';
                            echo $caption;
                            echo '</a>'; 
                            ?>
                        </td>
                        <td><?php echo $docketNumber; ?></td>    
                        <td><?php echo $pageCount; ?></td>
                        <td><?php echo $clientDue; ?></td>
                        <td>
                            <?php if ($authorisedUrl) : ?>
                            <a href="<?php echo $authorisedUrl; ?>" target="_blank">
                                <span class="glyphicon glyphicon-cloud-download aria-hidden="true"></span> Download
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
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


