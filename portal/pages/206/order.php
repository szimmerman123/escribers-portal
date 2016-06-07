<?php 
$rates = $db->getDefaultRates(206); // 206 = City of Phoenix
$turnarounds = array('Daily' => 'Next Working Day', '3-Day' => '3 Working Days', '5-Day' => '5 Working Days', '10-Day' => '10 Working Days', '20-Day' => ' 20 Working Days');
$payparties = array('private-non-indigent' => 'Private - Non-indigent (Enter party name)', 
                    'private-indigent' => 'Private - Indigent (Enter party name)', 
                    'court-appointed' => 'Court Appointed (Enter attorney name)', 
                    'prosecutor-states-appeal' => 'Prosecutor - State\'s Appeal (Enter attorney name)', 
                    'other' => 'Other (Enter details)');
$clerks = array('Katie Cruz', 'Jackie Diaz', 'Alexander Pompa', 'Gabriel Nunez', 'Rachel Ramirez');
$judges = array(
    'Full time Judges' => array(
        'Judge Lori Metcalf',
        'Judge Francisca Cota',
        'Judge James Hernandez',
        'Judge Michael Hintze',
        'Judge Cynthia Gonzales',
        'Judge Deborah Griffith',
        'Judge Marianne Bayardi',
        'Judge Kevin Kane',
        'Judge James Sampanes',
        'Judge Monyette Nyquist',
        'Judge Eric Jeffery',
        'Judge Robert Doyle',
        'Judge Laura Lowery',
        'Judge Walter Jackson',
        'Judge Hercules Dellas',
        'Judge Cynthia Certa',
        'Judge Carol Scott-Berry',
        'Judge Carrie Withey',
        'Judge Chris McBride',
        'Judge Wil Hudson'
    ),
    'Full time Hearing Officers' => array(
        'Judge Rosemarie Gavin',
        'Judge Basil Diamos',
        'Judge Alisha Villa'
    ),
    'Pro Tem Judges' => array(
        'Judge Charles Adornetto',
        'Judge Martha Ashburn',
        'Judge James Carter',
        'Judge Robert Colosi',
        'Judge Edwin Cook',
        'Judge B Robert Dorfman',
        'Judge Gerald Eastman',
        'Judge Patrick Eldridge',
        'Judge Kenneth Everett',
        'Judge Danielle Harris',
        'Judge Robert Hungerford',
        'Judge Lance Jacobs',
        'Judge Kathleen Kelly', 
        'Judge Alicia Lawler',
        'Judge Nancy Lewis',
        'Judge Denise Lightford',
        'Judge Jess Lorona',
        'Judge Harold Merkow',
        'Judge Thomas Scarduzio',
        'Judge James Scorza',
        'Judge Richard Smith',
        'Judge Malcolm Strohson',
        'Judge Elliot Talenfeld',
        'Judge Rick Tosto',
        'Judge John Wiehn',
        'Judge Alice Wright',
        'Judge Henry Zalut'
    )
);
//exit('<pre>' . print_r($rates, true));
?>

<style>
.table thead, .table thead tr, .table thead tr th,
.table tbody, .table tbody tr, .table tbody tr td,
.table tfoot, .table tfoot tr, .table tfoot tr td {border: none;}
.error-msg {color: #CC0000; font-weight: normal; padding-left: 10px;}
</style>

<div class="panel panel-default">
    
    <div class="panel-body">

        <form id="orderform" class="form" role="form" action="<?php echo $base_url; ?>orderform.php" method="post">
    
            <div class="row">
                
                <div class="col-md-10 col-md-offset-1 alert alert-info text-center"> 
                    
                    <h3><strong>Transcript Order Form - City of Phoenix Municipal Court</strong></h3>
            
                    <h4>Please complete the following form to place a transcript order</h4>
        
                </div>
                
                <div class="col-md-10 col-md-offset-1 alert alert-info"> 

                    <h4>Case Information</h4>
                    
                    <input type="hidden" name="jobtypeid" value="206" />
                    
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="audioclerk" class="control-label">Audio Clerk</label><span class="error-msg" id="clerk-error-msg"></span>
                            <select class="form-control input-sm" name="audioclerk" id="audioclerk" data-error="Please select Assistant" data-error-msg="clerk-error-msg">
                                <option value="-">Please select...</option>
                                <?php foreach($clerks as $clerk) echo '<option value="' . $clerk . '">' . $clerk . '</option>'; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="judge" class="control-label">Judge</label><span class="error-msg" id="judge-error-msg"></span>
                            <select class="form-control input-sm" name="judge" id="judge" data-error="Please select Judge" data-error-msg="judge-error-msg">
                                <option value="-">Please select...</option>
                                <?php 
                                foreach($judges as $group => $list) {
                                    echo '<optgroup label="' . $group . '">';
                                    foreach ($list as $judge)
                                        echo '<option value="' . $judge . '">' . $judge . '</option>';
                                    echo '</optgroup>';
                                } 
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="courtroom" class="control-label">Courtroom</label><span class="error-msg" id="courtroom-error-msg"></span>
                            <input type="text" class="form-control input-sm" name="courtroom" id="courtroom" data-required data-error="Please select Courtroom" data-error-msg="courtroom-error-msg">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-8">
                            <label for="casecaption" class="control-label">Case Caption</label><span class="error-msg" id="caption-error-msg"></span>
                            <input type="text" class="form-control input-sm" name="casecaption" data-error="Please enter Case Caption" data-required data-error-msg="caption-error-msg"/>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="complaintnumber" class="control-label">Complaint #</label><span class="error-msg" id="caseno-error-msg"></span>
                            <input type="text" class="form-control input-sm" name="complaintnumber" data-error="Please enter Complaint #" data-required data-error-msg="caseno-error-msg"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="appeal" class="control-label">Indicate if this case is an appeal</label>
                            <div id="appealradio">
                                &nbsp;
                                <label class="radio-inline">
                                    <input type="radio" name="appeal" value="1" id="appealyes"> Yes
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="appeal" value="0" id="appealno"> No
                                </label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label for="appealname">Name of Appealing Party</label>
                            <input type="text" name="appealname" id="appealname" class="form-control input-sm" />
                        </div>
                    </div>
                    <br />
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <label for="turnaround" class="control-label">Requested Turnaround Time</label>
                                <select class="form-control input-sm" name="turnaround" id="turnaround" data-error="Please select Turnaround Time" data-error-msg="turnaround-error-msg">
                                    <option value="-">Please select...</option>
                                    <?php foreach($rates as $key => $rate) echo '<option value="' . $key . '">' . $turnarounds[$key] . ' ($' . $rate . ' per page)</option>'; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <br />
                                <span class="error-msg" id="turnaround-error-msg"></span>
                            </div>
                            <div class="col-md-4">
                                <label for="copycount" class="control-label">Number of Copies</label>
                                <select class="form-control input-sm" name="copycount" id="copycount">
                                    <option value="0">Original Only</option>
                                    <option value="1" selected="selected">Original + 1 Copy</option>
                                    <option value="2">Original + 2 Copies</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-10 col-md-offset-1 alert alert-info"> 
                    
                    <input type="hidden" name="hearings" id="hearings" value="1" />

                    <h4>Hearings Requested</h4>
                    
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th width="30%">Hearing Date<span class="error-msg" id="date-error-msg"></span></th>
                                <th width="40%">Type of Proceedings<span class="error-msg" id="type-error-msg"></span></th>
                                <th width="30%">Estimated Minutes<span class="error-msg" id="minutes-error-msg"></span></th>
                            </tr>
                        </thead>
                        <tbody id="hearingsbody">
                            <tr id="line1">
                                <td><input name="hdate[1]" type="date" placeholder="yyyy-mm-dd" class="form-control input-sm" data-error="Please enter Date" data-required data-error-msg="date-error-msg" /></td>
                                <td><input name="htype[1]" class="form-control input-sm" data-required data-error="Please enter Type" data-error-msg="type-error-msg" /></td>
                                <td><input name="hminutes[1]" type="number" class="form-control input-sm" data-required data-error="Enter Minutes" data-error-msg="minutes-error-msg" /></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">
                                    <button id="addhearing" class="btn btn-success">Add Another Date</button> 
                                    <button id="delhearing" class="btn btn-danger">Remove Last Date</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="appearances" class="control-label">Appearances</label>
                            <textarea class="form-control" name="appearances" rows="5"></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="witnesses" class="control-label">Witnesses</label>
                            <textarea class="form-control" name="witnesses" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        If any of the hearings requested is a Jury Trial, please also transcribe:<br />
                        <label class="checkbox-inline">
                            <input type="checkbox" name="voirdire" value="1" /> Jury Voir Dire &nbsp;
                        </label>
                        <label class="checkbox-inline"> 
                            <input type="checkbox" name="openingstatements" value="1" /> Opening Statements &nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="juryinstructions" value="1" /> Jury Instructions &nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="closingarguments" value="1" /> Closing Arguments &nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="audiovideo" value="1" /> Audio/Video Testimony
                        </label>
                    </div>
                    
                </div>
                
                <div class="col-md-10 col-md-offset-1 alert alert-info"> 
    
                    <h4>Payment Information</h4>
                    
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="paymentparty" class="control-label">Payment Party</label><span class="error-msg" id="payparty-error-msg"></span>
                            <select class="form-control input-sm" name="paymentparty" id="turnaround" data-error="Please select Party" data-error-msg="payparty-error-msg">
                                <option value="-">Please select...</option>
                                <?php foreach($payparties as $party => $desc) echo '<option value="' . $party . '">' . $desc . '</option>'; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-8">
                            <label for="otherpayparty" class="control-label">Payment Party Details</label><span class="error-msg" id="paydetails-error-msg"></span>
                            <input type="text" class="form-control" name="otherpayparty" data-required data-error="Please enter Details" data-error-msg="paydetails-error-msg" />
                        </div>
                    </div>
    
                </div>        

                <div class="col-md-10 col-md-offset-1 alert alert-info"> 
    
                    <h4>Additional Information and Special Instructions</h4>
                    <textarea name="specialnotes" id="specialnotes" class="form-control" rows="4"></textarea>

                </div>

                <div class="col-md-10 col-md-offset-1 alert alert-info">
                    <div class="col-md-3 col-md-offset-3 text-center">
                        <input type="submit" class="btn btn-primary btn-lg" id="form-submit" value="Place Order Now" />
                    </div>
                    <div class="col-md-6 text-danger">
                        <span id="form-errors"><strong>Form has errors - please correct</strong></span>
                    </div>
                </div>    
                
            </div>
    
        </form>
    
    </div>

</div>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script type="text/javascript" src="<?php echo $base_url; ?>pages/206/order.js"></script>
