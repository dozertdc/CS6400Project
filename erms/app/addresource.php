<?php
    include_once "config.php";
    include "dbfunctions.php";
    session_start();

    if(!isset($_SESSION['login_user'])) {
        header("Location: index.php");
    }

    $loginName = $_SESSION['login_username'];
    $loginUser = $_SESSION['login_user'];
    $saveError = "";
    $resourceName = $homeLatitude = $homeLongitude = $costAmt = "";
    $resourceNameErr = $homeLatitudeErr = $homeLongitudeErr = $costAmtErr = "";

    if(isset($_POST['saveButton'])){


        //echo ($_POST['hiddenCapability']);
        /*
        if (empty($_POST["resourceName"])) {
            $resourceNameErr = "ResourceName is required";
        } else {
            $resourceName = test_input($_POST["resourceName"]);
            // check if name contains letters and whitespace only
            if (!preg_match("/^[a-zA-Z ]*$/",$resourceName)) {
                $resourceNameErr = "Only letters and white space are allowed";
            }
        }

        if (empty($_POST["homeLatitude"])) {
            $homeLatitudeErr = "Latitude is required. ";
        } else {
            $homeLatitude = test_input($_POST["homeLatitude"]);
            // check if latitude contains signed decimal only
            if (!preg_match("/^[+\-]?[0-9]{1,2}+(\.[0-9]{1,6})$/",$homeLatitude)) {
                $homeLatitudeErr = "Invalid Latitude. ";
            }
        }

        if (empty($_POST["homeLongitude"])) {
            $homeLongitudeErr = "Longitude is required.";
        } else {
            $homeLongitude = test_input($_POST["homeLongitude"]);
            // check if longitude contains signed decimal only
            if (!preg_match("/^[+\-]?[0-9]{1,3}+(\.[0-9]{1,6})$/",$homeLongitude)) {
                $homeLongitudeErr = "Invalid Longitude.";
            }
        }
        
        if (empty($_POST["costAmt"])) {
            $costAmtErr = "Cost is required";
        } else {
            $costAmt = test_input($_POST["costAmt"]);
            // check if amount contains signed decimal only
            if (!preg_match("/^[0-9]{1,8}+(\.[0-9]{1,2})$/",$costAmt)) {
                $costAmtErr = "Invalid Cost";
            }
        }
        */

        if(empty($resourceNameErr) and empty($homeLatitudeErr) and empty($homeLongitudeErr) and empty($costAmtErr)){

            $resourceName = htmlspecialchars($_POST['resourceName']);
            $resourceModel = htmlspecialchars($_POST['resourceModel']);
            $primaryESFId = $_POST['selectESFPrimary'];
            $resourceStatus = 'Available'; //default
            $homeLatitude = htmlspecialchars($_POST['homeLatitude']);
            $homeLongitude = htmlspecialchars($_POST['homeLongitude']);
            $costAmt = htmlspecialchars($_POST['costAmt']);
            $costPerUnit = htmlspecialchars($_POST['costPerUnit']);

            /*$insertResourceStmt =  "INSERT INTO Resources (ResourceName, Model, PrimaryESFId, Status, ResourceOwner, 
                                                Latitude, Longitude, CostAmount,  CostUnitId) " .
                                   "VALUES ('$resourceName', '$resourceModel', $primaryESFId, '$resourceStatus', '$loginUser', $homeLatitude, 
                                             $homeLongitude, $costAmt, $costPerUnit)";*/


            $insertResourceStmt =  insertResourceStmt($resourceName, $resourceModel, $primaryESFId, $resourceStatus, 
                                                         $loginUser, $homeLatitude, $homeLongitude, $costAmt, $costPerUnit);

            //echo $insertResourceStmt;

            //start a transaction to insert into Resources, Resource_AdditoinalESF and Capability
            $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
            if(!$mysqli->query($insertResourceStmt)){
                    $saveError = "Add Resource failed: (" . $mysqli->errno . ") "; //. $mysqli->error;
                    $mysqli->rollback();
            }
            else {
                if(!empty($_POST['selectESFSecondary']))
                {
                    $selectedSecondaryESFs = "";
                    $lastInsertedResourceId = $mysqli->insert_id;
                    foreach ($_POST['selectESFSecondary'] as $secondaryESF) {
                        $selectedSecondaryESFs .= "($lastInsertedResourceId,$secondaryESF),";

                    }

                    /*$insertSecondaryESFStmt = "INSERT INTO Resource_AdditionalESF (ResourceId, AdditionalESFId) 
                                               VALUES " . trim($selectedSecondaryESFs,',');*/
                    $insertSecondaryESFStmt = insertSecondaryESFStmt($selectedSecondaryESFs);
                    //echo $insertSecondaryESFStmt;
                    if(!$mysqli->query($insertSecondaryESFStmt)){
                        $saveError = "Add SecondaryESFs failed: (" . $mysqli->errno . ") "; //. $mysqli->error;
                        $mysqli->rollback();
                    }
                }
                
                if(!empty($_POST['hiddenCapability'])) 
                {
                    $selectedCapabilities = "";
                    //only insert unique capabilities added...
                    $hiddenCapabilityArray = array_unique(explode('::', $_POST['hiddenCapability']));
                    foreach ($hiddenCapabilityArray as $newCapability) {
                        $selectedCapabilities .= "($lastInsertedResourceId,'$newCapability'),";

                    }

                    /*$insertCapabilityStmt = "INSERT INTO Capability (ResourceId, Capability) 
                                               VALUES " . trim($selectedCapabilities,',');*/

                    $insertCapabilityStmt = insertCapabilityStmt($selectedCapabilities);

                    //echo $insertCapabilityStmt;
                    if(!$mysqli->query($insertCapabilityStmt)){
                        $saveError = "Add Capabilities failed: (" . $mysqli->errno . ") "; //. $mysqli->error;
                        $mysqli->rollback();
                    }
                }

                if(empty($saveError))
                {
                    //only commit if all insert statements have been evaluated..happens only if we reach here!
                    $mysqli->commit();
                    header("Location: mainmenu.php");
                    exit;

                }

            }

        }

    }

    if (isset($_POST['cancelButton'])){
        header("Location: mainmenu.php");
        exit;
    }
  

    $selectESFStmt = selectESFStmt();
    $res = $mysqli->query($selectESFStmt);

    if(!$res OR $res->num_rows === 0)
    {
        $loginError = "No ESFs present. Contact system administrator...";
        $res->free();
        header("Location: index.php?err=$loginError");
        exit;
    }

    $selectCostUnitStmt = selectCostUnitStmt();
    $resCostUnit = $mysqli->query($selectCostUnitStmt);

    if(!$resCostUnit OR $resCostUnit->num_rows === 0)
    {
        $loginError = "No Cost Units present. Contact system administrator...";
        $res->free();
        header("Location: index.php?err=$loginError");
        exit;
    }


    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
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
        function addToCapability() {
            var regexp1= /^[a-zA-Z0-9 ]+$/;
            document.getElementById("valCapability").innerHTML = "";
            if(regexp1.test(document.getElementById("addCapability").value) == false){
                document.getElementById("valCapability").innerHTML = "Alphanumeric only";
                return false;
            } else {
                var capability = document.getElementById("capabilityList");
                var hiddenCapability = document.getElementById("hiddenCapability");
                var newoption = document.createElement("option");
                newoption.text = newoption.value = document.getElementById("addCapability").value;
                if(hiddenCapability.value == "") {
                    hiddenCapability.value = newoption.text;
                } else {
                    hiddenCapability.value = hiddenCapability.value + "::" + newoption.text;
                }
                capability.add(newoption);
                document.getElementById("addCapability").value = "";
            }
        }
        function updateSecondaryESFs() {
            var selPrimary = document.getElementById("selectESFPrimary");
            var selSecondary = document.getElementById("selectESFSecondary[]");

            //remove previous options...
            for(var i = selSecondary.options.length - 1 ; i >= 0 ; i--){
                selSecondary.remove(i);
            }

            //add new options
            for(var i = 0; i <  selPrimary.options.length; i++){
                if(i != selPrimary.selectedIndex){
                    var secESFOption = document.createElement("option");
                    secESFOption.value = selPrimary.options[i].value;
                    secESFOption.text = selPrimary.options[i].text;
                    selSecondary.add(secESFOption);
                }
            }
        }
    </script>
</head>

<body>

<div class="container" align = "center" style="width: 80%">
    <div style="min-width: 100px"><br><br><br><br></div>
    <div class="panel panel-default" style="width: inherit">
        <div class="panel-heading" align="center">
            <h4 class="panel-title">Add Resource</h4>
        </div>
        <div class="panel-body" align = "center">
            <form class="form-horizontal" method= "post" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <fieldset>

                    <!-- Form Name -->
                    <!--<legend align="left">New Resource Info...</legend>-->

                    <!-- Owner input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="ownerName">Owner</label>
                        <div class="col-md-4" align="left">
                            <label class="form-control input-md" for="OwnerName"><?=$loginName?></label>
                        </div>
                    </div>

                    <!-- Resource Name input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="resourceName">Resource Name</label>
                        <div class="col-md-4">
                            <input id="resourceName" name="resourceName" type="text" placeholder="enter resource name" class="form-control input-md" maxLength = "50" pattern="[0-9A-Za-z ]+" title = "Only alphabets, numbers and spaces allowed" value="<?php echo $resourceName;?>" required>
                            <span class="help-block">Helps identify resource</span>
                        </div>
                        <div class="col-md-4" style="color: red">
                            <?php echo $resourceNameErr;?>
                        </div>
                    </div>

                    <!-- Select Primary ESF -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="selectESFPrimary">Primary ESF</label>
                        <div class="col-md-4">
                            <select id="selectESFPrimary" name="selectESFPrimary" onchange="updateSecondaryESFs()" class="form-control">
                                <?php
                                $res->data_seek(0);
                                while ($pesf = $res->fetch_assoc()) {
                                    echo "<option value=" . $pesf['ESFId'] . ">" . $pesf['ESFName'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Select Multiple Secondary ESFs -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="selectESFSecondary">Secondary ESFs</label>
                        <div class="col-md-4">
                            <select id="selectESFSecondary[]" name="selectESFSecondary[]" class="form-control" multiple="multiple">
                                <?php
                                $res->data_seek(1);
                                while ($sesf = $res->fetch_assoc()) {
                                    echo "<option value=" . $sesf['ESFId'] . ">" . $sesf['ESFName'] . "</option>";
                                }
                                ?>
                            </select>
                            <span class="help-block">Select one or more Secondary ESFs</span>
                        </div>
                    </div>

                    <!-- Model input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="resourceModel">Model</label>
                        <div class="col-md-4">
                            <input id="resourceModel" name="resourceModel" type="text" placeholder="enter model" class="form-control input-md" maxLength = "50" pattern="[0-9A-Za-z ]+" title = "Only alphabets, numbers and spaces allowed">
                            <span class="help-block">Model Identifier</span>
                        </div>
                    </div>

                    <!-- List Capabilities -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="capabilityList">Capabilities</label>
                        <div class="col-md-4">
                            <select id="capabilityList" name="capabilityList[]" class="form-control" multiple="multiple">
                            </select>
                        </div>
                    </div>

                    <!-- Add Capability-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="addCapability">Capability</label>
                        <div class="col-md-3">
                            <input id="hiddenCapability" name="hiddenCapability" type="hidden">
                            <input id="addCapability" name="addCapability" type="text" placeholder="add capability" class="form-control input-md" maxLength = "50">
                            <span class="help-block">Add capability</span>
                        </div>
                        <div class="col-md-4" align="left">
                            <button id="capabilityButton" name="capabilityButton" type="button" onclick="addToCapability()" class="btn btn-info">Add</button>
                            <a id="valCapability" style="color: red"></a>
                        </div>
                    </div>

                    <!-- Home Location-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="homeLocation">Home location</label>
                        <div class="col-md-2">
                            <input id="homeLatitude" name="homeLatitude" type="number" placeholder="lat" class="form-control input-md" 
                            value="<?php echo $homeLatitude;?>" min="-90.000000" max = "90.000000" step= "0.000001" required>
                            <span class="help-block">Latitude</span>
                        </div>
                        <div class="col-md-2">
                            <input id="homeLongitude" name="homeLongitude" type="number" placeholder="long" class="form-control input-md" 
                            value="<?php echo $homeLongitude;?>" min="-180.000000" max = "180.000000" step= "0.000001" required>
                            <span class="help-block">Longitude</span>
                        </div>
                        <div class="col-md-4" style="color: red">
                            <?php echo $homeLatitudeErr;?>
                            <?php echo $homeLongitudeErr;?>
                        </div>
                    </div>

                    <!-- Cost-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="costPerOption">Cost</label>
                        <div class="col-md-2">
                            <input id="costAmt" name="costAmt" type="number" placeholder="$ Amount" class="form-control input-md"
                            value="<?php echo $costAmt;?>" min="0" max = "99999999.99" step= "0.01" required>
                            <span class="help-block">Dollars</span>
                        </div>
                        <div class="col-md-2">
                            <select id="costPerUnit" name="costPerUnit" placeholder="Select..." class="form-control">
                                <?php
                                $resCostUnit->data_seek(0);
                                while ($currCostUnit = $resCostUnit->fetch_assoc()) {
                                    echo "<option value=" . $currCostUnit['CostUnitId'] . ">" . $currCostUnit['CostUnit'] . "</option>";
                                }
                                ?>
                            </select>
                            <span class="help-block">Unit</span>
                        </div>
                        <div class="col-md-4" style="color: red">
                            <?php echo $costAmtErr;?>
                        </div>
                    </div>

                    <!-- Save Resource -->
                    <div class="form-group">
                        <div class="col-md-12">
                            <button id="saveButton" name="saveButton" type="submit"  class="btn btn-success">Save</button>
                            <button id="cancelButton" name="cancelButton" type="submit" class="btn btn-inverse" formnovalidate>Cancel</button>
                        </div>
                    </div>
                    <span class="error">
                    <?php
                    if (isset($saveError)){
                        echo $saveError;
                    }?>
                    </span>

                </fieldset>
            </form>
        </div>
    </div>
</div>

</body>

</html>