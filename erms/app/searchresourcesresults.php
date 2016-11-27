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

    $loginName = $_SESSION['login_username'];
    $loginUser = $_SESSION['login_user'];

    $keywordSearch = $_SESSION['keywordSearch'];
    $selectedESFId = $_SESSION['selectESF'];
    $selectedIncidentId = $_SESSION['selectedIncidentId'];
    $selectedIncidentLat = $_SESSION['selectedIncidentLat'];
    $selectedIncidentLong = $_SESSION['selectedIncidentLong'];
    $selectedIncidentDesc = $_SESSION['selectedIncidentDesc'];
    $incDistance = $_SESSION['incDistance'];


    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }


    if(isset($_GET['resourceId']) and isset($_GET['action'])){
        $resourceId = test_input($_GET['resourceId']);
        $action = test_input($_GET['action']);
        switch ($action) {
            case 'deploy':
                $currentDate = date('Y-m-d');
                $returnDate = date('Y-m-d',(strtotime (RESOURCE_REQUEST_DURATION, strtotime ($currentDate))));
                $deployResourceStmt1 = updateResourceStmt($resourceId, 'In Use');
                $deployResourceStmt2 = requestResourceStmt($loginUser, $resourceId, $selectedIncidentId, $currentDate, $returnDate, $action);
                //echo $deployResourceStmt1;
                $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
                if(!$mysqli->query($deployResourceStmt1)){
                        $dataError = "Update to Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                }
                else{
                    if(!$mysqli->query($deployResourceStmt2)){
                        $dataError = "Insert to UserRequestsResourcesForIncident failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                        $mysqli->rollback();
                    }
                    else{
                        //only commit if all  statements have been evaluated..happens only if we reach here!
                        $mysqli->commit();
                    }
                }
                break;
            case 'request':
                $currentDate = date('Y-m-d');
                $returnDate = date('Y-m-d',(strtotime (RESOURCE_REQUEST_DURATION, strtotime (date('Y-m-d')))));
                $requestResourceStmt = requestResourceStmt($loginUser, $resourceId, $selectedIncidentId, $currentDate, $returnDate, $action);
                //echo $requestResourceStmt;

                if(!$mysqli->query($requestResourceStmt)){
                        $dataError = "Insert to UserRequestsResourcesForIncident failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                }
                break;
            case 'repair':
                if(isset($_GET['returnDate'])){
                    $startOnDate = (empty($_GET['returnDate'])) ? date('Y-m-d'): $_GET['returnDate'];
                }
                else{
                    $startOnDate = date('Y-m-d');
                }
                $readyBy = date('Y-m-d',(strtotime (RESOURCE_REPAIR_DURATION, strtotime ($startOnDate))));
                $repairResourceStmt1 = updateResourceStmt($resourceId, 'In Repair');
                $repairResourceStmt2 = repairResourceStmt($resourceId, $startOnDate, $readyBy);

                //echo $repairResourceStmt2;
                $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
                if(!$mysqli->query($repairResourceStmt1)){
                        $dataError = "Update to Resources failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                }
                else{
                    if(!$mysqli->query($repairResourceStmt2)){
                        $dataError = "Insert to Repairs failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
                    }
                    else{
                        //only commit if all  statements have been evaluated..happens only if we reach here!
                        $mysqli->commit();
                    }
                }
                break;
        }
    }  


    $queryResourcesStmt = queryResourcesStmt($selectedIncidentLat, $selectedIncidentLong, $keywordSearch, $selectedESFId, $incDistance);

    //echo $queryResourcesStmt;

    $resResources = $mysqli->query($queryResourcesStmt);
    if(!$resResources){
        $dataError = "Query Resources failed: (" . $mysqli->errno . ") "; //. $mysqli->error;
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
        function rowButtonClick(action, resourceId, returnDate) {
            //alert(action + ":" + resourceId + ":" + returnDate);
            window.location.href = "searchresourcesresults.php?resourceId=" + resourceId + "&action=" + action + "&returnDate=" + returnDate;
        }
    </script>
</head>

<body>

<div class="container" align="center">
    <div style="min-width: 100px"><br><br><br><br></div>
    <div class="panel panel-default" style="width: inherit">
        <div class="panel-heading" align="center">
            <h4 class="panel-title">Search Results</h4>
        </div>
        <div class="panel-body">
            <form class="form-horizontal" method= "post" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <fieldset>

                    <!-- Form Name -->
                    <!--<legend align="left">Search Results...</legend>-->

                    <!-- Incident Description input-->
                    <?php if(!empty($selectedIncidentDesc)){?>
                    <div class="form-group">
                        <label for="incName">Incident:  <?=$selectedIncidentDesc?></label>
                    </div>
                    <?php }?>

                    <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>ResourceId</th>
                            <th>ResourceName</th>
                            <th>Owner</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Next Available</th>
                            <?php if(!empty($selectedIncidentDesc)){?>
                                <th>Distance in Km</th>
                                <th>Action</th>
                            <?php }?>
                          </tr>
                        </thead>
                        <tbody>
                            <?php
                                $currentDate = date('Y-m-d');
                                $resResources->data_seek(0);
                                while ($currentResource = $resResources->fetch_assoc()) {?>
                                    <tr>
                                        <td><?=$currentResource['ResourceId']?></td>
                                        <td><?=$currentResource['ResourceName']?></td>
                                        <td><?=$currentResource['ResourceOwner']?></td>
                                        <td><?='$' . $currentResource['CostAmount']. '/' . $currentResource['CostUnit']?></td>
                                        <?php if($currentResource['ReadyBy'] != NULL){?>
                                            <td><?='In Repair'?></td>
                                            <td><?=$currentResource['ReadyBy']?></td>
                                        <?php } elseif($currentResource['ReturnDate'] != NULL){?>
                                            <td><?='In Use'?></td>
                                            <td><?=$currentResource['ReturnDate']?></td>
                                        <?php } else {?>
                                            <td><?='Available'?></td>
                                            <td>NOW</td>
                                        <?php }?>
                                        <?php if(!empty($selectedIncidentDesc)){?>
                                            <td><?=$currentResource['Distance']?></td>
                                            <td>
                                                <?php if(!isset($currentResource['ReturnDate']) and !isset($currentResource['ReadyBy']) 
                                                    and $loginName === $currentResource['ResourceOwner']){?>
                                                <button id="deployButton" name="deployButton" type="button" 
                                                 onclick="rowButtonClick('deploy', <?=$currentResource['ResourceId']?>, null)" class="btn btn-info">Deploy
                                                </button>
                                                <?php }?>
                                                <?php if(!isset($currentResource['ReadyBy']) 
                                                    and $loginName === $currentResource['ResourceOwner']){?>
                                                    <button id="repairButton" name="repairButton" type="button" 
                                                    onclick="rowButtonClick('repair', <?=$currentResource['ResourceId']?>, 
                                                    <?="'". $currentResource['ReturnDate'] . "'"?>)" class="btn btn-info">Repair
                                                    </button>
                                                <?php }?>
                                                <?php if($loginName != $currentResource['ResourceOwner'] 
                                                    and !isset($currentResource['ReadyBy'])){?>
                                                    <button id="requestButton" name="requestButton" type="button" 
                                                    onclick="rowButtonClick('request', <?=$currentResource['ResourceId']?>, null)"class="btn btn-info">Request
                                                    </button>
                                                <?php }?>
                                            </td>
                                        <?php }?>
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