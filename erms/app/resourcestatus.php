<?php
    include_once "config.php";
    include "dbfunctions.php";
    session_start();

    if(!isset($_SESSION['login_user'])) {
        header("Location: index.php");
    }

    if(isset($_POST['closeButton'])){
        header("Location: mainmenu.php");
        exit;
    }

    $loginUserName = $_SESSION['login_username'];
    $loginUser = $_SESSION['login_user'];


    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if(isset($_GET['resourceId']) and isset($_GET['action']) and isset($_GET['userName'])){
        $resourceId = test_input($_GET['resourceId']);
        $action = test_input($_GET['action']);
        $userName = test_input($_GET['userName']);
        switch ($action) {
            case 'return':
                $incidentId = test_input($_GET['incidentId']);
                $updateResourcesStmt = updateResourceStmt($resourceId, 'Available');
                $returnResourcesForIncident = updateRequestsResourcesForIncidentStmt($loginUser, $resourceId, $incidentId, $action);
                //echo $updateResourcesStmt;
                $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);                          
                if(!$mysqli->query($updateResourcesStmt)){
                        $dataError = "Update to Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                }
                else {
                    if(!$mysqli->query($returnResourcesForIncident)){
                        $dataError = "Return Requested Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                    }
                    else{
                        //only commit if all  statements have been evaluated..happens only if we reach here!
                        $mysqli->commit();
                    }
                }
                break;
            case 'cancel':
                $incidentId = test_input($_GET['incidentId']);
                $cancelRequestedResourceStmt = updateRequestsResourcesForIncidentStmt($loginUser, $resourceId, $incidentId, $action);
                //echo $cancelRequestedResourceStmt;                        
                if(!$mysqli->query($cancelRequestedResourceStmt)){
                        $dataError = "Cancel Requested Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                }
                break;
            case 'deploy':
                $incidentId = test_input($_GET['incidentId']);
                $updateResourcesStmt = updateResourceStmt($resourceId, 'In Use');
                $deployResourcesForIncident = updateRequestsResourcesForIncidentStmt($userName, $resourceId, $incidentId, $action);
                //echo $updateResourcesStmt;
                $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);                          
                if(!$mysqli->query($updateResourcesStmt)){
                        $dataError = "Update to Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                }
                else {
                    if(!$mysqli->query($deployResourcesForIncident)){
                        $dataError = "Deploy Requested Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                    }
                    else{
                        //only commit if all  statements have been evaluated..happens only if we reach here!
                        $mysqli->commit();
                    }
                }
                break;
            case 'reject':
                $incidentId = test_input($_GET['incidentId']);
                $rejectRequestedResourceStmt = updateRequestsResourcesForIncidentStmt($userName, $resourceId, $incidentId, $action);
                //echo $cancelRequestedResourceStmt;                        
                if(!$mysqli->query($rejectRequestedResourceStmt)){
                        $dataError = "Reject Requested Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                }
                break;
            case 'cancelRepair':
                $cancelResourceRepairRequestStmt = cancelResourceRepairRequestStmt($resourceId);
                $cancelRepairStmt = cancelRepairStmt($resourceId);
                        
                //echo $cancelResourceRepairRequestStmt;
                //echo $cancelRepairStmt;
                $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);                          
                if(!$mysqli->query($cancelResourceRepairRequestStmt)){
                        $dataError = "Update to Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                }
                else {
                    if(!$mysqli->query($cancelRepairStmt)){
                        $dataError = "Cancel Repair failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                    }
                    else{
                        //only commit if all  statements have been evaluated..happens only if we reach here!
                        $mysqli->commit();
                    }
                }
                break;
        }

    }

    //resources that are currently in use responding to any incidents owned by the current user 
    $queryUsedResStmt = queryUsedResStmt($loginUser);

    //echo $queryUsedResStmt;

    $resUsedResources = $mysqli->query($queryUsedResStmt);

    if(!$resUsedResources){
        $dataError = "Query Used Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
    }

    //resource requests that have been sent by the current user (to another user) but have not yet been responded to
    $queryRequestedResStmt = queryRequestedResStmt($loginUser);

    //echo $queryRequestedResStmt;

    $resRequestedResources = $mysqli->query($queryRequestedResStmt);

    if(!$resRequestedResources){
        $dataError = "Query Requested Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
    }

    //resource requests received by the current user that are awaiting the user’s response
    $queryReceivedReqStmt = queryReceivedReqStmt($loginUser);

    //echo $queryRequestedResStmt;

    $resReceivedReq = $mysqli->query($queryReceivedReqStmt);

    if(!$resReceivedReq){
        $dataError = "Query Received Requests failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
    }

    //repairs scheduled/in-progress

    $queryRepairsStmt = queryRepairsStmt($loginUser);

    //echo $queryRequestedResStmt;

    $resRepairs = $mysqli->query($queryRepairsStmt);

    if(!$resRepairs){
        $dataError = "Query Used Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
    }
?>

<html>

<head>
    <title>Emergency Resource Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script>
        function rowButtonClick(action, userName, resourceId, incidentId) {
            //alert(action + ":" + userName + ":" + resourceId + ":" + incidentId);
            window.location.href = "resourcestatus.php?resourceId=" + resourceId + "&action=" + action + "&incidentId=" + incidentId + "&userName=" + userName;
        }
    </script>
</head>

<body>

<div class="container" align="center">
    <div style="min-width: 100px"><br><br><br><br></div>
    <div class="panel panel-default" style="width: inherit">
        <div class="panel-heading" align="center">
            <h4 class="panel-title">Resource Status</h4>
        </div>
        <div class="panel-body">
            <form class="form-horizontal" method= "post" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <fieldset>

                    <!-- Form Name -->
                    <!--<legend align="left">Resource Status...</legend>-->

                    <!-- Resource In Use label-->
                    <div class="form-group">
                        <label for="incName">Resources In Use</label>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>ResourceId</th>
                            <th>ResourceName</th>
                            <th>Incident</th>
                            <th>Owner</th>
                            <th>StartDate</th>
                            <th>Return By</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                            <?php
                                $resUsedResources->data_seek(0);
                                while ($currentResource = $resUsedResources->fetch_assoc()) {?>
                                    <tr>
                                        <td><?=$currentResource['ResourceId']?></td>
                                        <td><?=$currentResource['ResourceName']?></td>
                                        <td><?=$currentResource['Description']?></td>
                                        <td><?=$currentResource['Name']?></td>
                                        <td><?=$currentResource['StartDate']?></td>
                                        <td><?=$currentResource['ReturnBy']?></td>
                                        <td>
                                            <button id="returnButton" name="returnButton" type="button" 
                                             onclick="rowButtonClick('return', <?="'". $currentResource['UserName'] . "'"?>, <?=$currentResource['ResourceId']?>, <?=$currentResource['IncidentId']?>)" class="btn btn-info">Return
                                            </button>
                                        </td>
                                    </tr>
                            <?php }?>
                        </tbody>
                    </table>

                    <!-- Resources requested by me label-->
                    <div class="form-group">
                        <label for="incName">Resources Requested by me</label>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>ResourceId</th>
                            <th>ResourceName</th>
                            <th>Incident</th>
                            <th>Owner</th>
                            <th>Return By</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                            <?php
                                $resRequestedResources->data_seek(0);
                                while ($currentResource = $resRequestedResources->fetch_assoc()) {?>
                                    <tr>
                                        <td><?=$currentResource['ResourceId']?></td>
                                        <td><?=$currentResource['ResourceName']?></td>
                                        <td><?=$currentResource['Description']?></td>
                                        <td><?=$currentResource['Name']?></td>
                                        <td><?=$currentResource['ReturnBy']?></td>
                                        <td>
                                            <button id="returnButton" name="returnButton" type="button" 
                                             onclick="rowButtonClick('cancel', <?="'". $currentResource['UserName'] . "'"?>, <?=$currentResource['ResourceId']?>, <?=$currentResource['IncidentId']?>)" class="btn btn-info">Cancel
                                            </button>
                                        </td>
                                    </tr>
                            <?php }?>
                        </tbody>
                    </table>

                    <!-- Resources requests received by me label-->
                    <div class="form-group">
                        <label for="incName">Resources Requests received by me</label>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>ResourceId</th>
                            <th>ResourceName</th>
                            <th>Incident</th>
                            <th>Requested By</th>
                            <th>Return By</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                            <?php
                                $resReceivedReq->data_seek(0);
                                while ($currentResource = $resReceivedReq->fetch_assoc()) {?>
                                    <tr>
                                        <td><?=$currentResource['ResourceId']?></td>
                                        <td><?=$currentResource['ResourceName']?></td>
                                        <td><?=$currentResource['Description']?></td>
                                        <td><?=$currentResource['Name']?></td>
                                        <td><?=$currentResource['ReturnBy']?></td>
                                        <td>
                                            <?php if($currentResource['Status'] === 'Available'){?>
                                            <button id="deployButton" name="deployButton" type="button" 
                                             onclick="rowButtonClick('deploy', <?="'". $currentResource['UserName'] . "'"?>, <?=$currentResource['ResourceId']?>, <?=$currentResource['IncidentId']?>)" class="btn btn-info">Deploy
                                            </button>
                                            <?php }?>
                                            <button id="rejectButton" name="rejectButton" type="button" 
                                             onclick="rowButtonClick('reject', <?="'". $currentResource['UserName'] . "'"?>, <?=$currentResource['ResourceId']?>, <?=$currentResource['IncidentId']?>)" class="btn btn-info">Reject
                                            </button>
                                        </td>
                                    </tr>
                            <?php }?>
                        </tbody>
                    </table>

                    <!-- Repairs Scheduled/In-progress label-->
                    <div class="form-group">
                        <label for="incName">Repairs Scheduled/In-progress</label>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>ResourceId</th>
                            <th>ResourceName</th>
                            <th>Start On</th>
                            <th>Ready By</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                            <?php
                                $resRepairs->data_seek(0);
                                while ($currentResource = $resRepairs->fetch_assoc()) {?>
                                    <tr>
                                        <td><?=$currentResource['ResourceId']?></td>
                                        <td><?=$currentResource['ResourceName']?></td>
                                        <td><?=$currentResource['StartOnDate']?></td>
                                        <td><?=$currentResource['ReadyBy']?></td>
                                        <td>
                                            <?php if(strtotime($currentResource['StartOnDate']) > strtotime(date('Y-m-d'))){?>
                                            <button id="cancelRepairButton" name="cancelRepairButton" type="button" 
                                             onclick="rowButtonClick('cancelRepair', null, 
                                             <?=$currentResource['ResourceId']?>, null)" class="btn btn-info">Cancel
                                            </button>
                                            <?php }?>
                                        </td>
                                    </tr>
                            <?php }?>
                        </tbody>
                    </table>


                    <!-- Save Resource -->
                    <div class="form-group">
                        <div class="col-md-12">
                            <button id="closeButton" name="closeButton" type="submit" class="btn btn-info" formnovalidate>Close</button>
                        </div>
                    </div>
                    <span class="error">
                        <?php
                        if (isset($dataError)){
                            echo $dataError;
                        }?>
                    </span>


                </fieldset>
            </form>
        </div>
    </div>
</div>

</body>

</html>