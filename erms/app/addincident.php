<?php
    include_once "config.php";
    include "dbfunctions.php";
    session_start();

    if(!isset($_SESSION['login_user'])) {
        header("Location: index.php");
    }

    $loginName = $_SESSION['login_username'];
    $loginUser = $_SESSION['login_user'];
    $incName = $incLatitude = $incLongitude = "";
    $incDate = date('Y-m-d');
    $incNameErr = $incLatitudeErr = $incLongitudeErr = "";

    if(isset($_POST['saveButton'])){

        /*if (empty($_POST["incName"])) {
            $incNameErr = "Incident Description is required";
        } else {
            $incName = test_input($_POST["incName"]);
            // check if name contains letters and whitespace only
            if (!preg_match("/^[a-zA-Z ]*$/",$incName)) {
                $incNameErr = "Only letters and white space are allowed";
            }
        }

        if (empty($_POST["incLatitude"])) {
            $incLatitudeErr = "Latitude is required. ";
        } else {
            $incLatitude = test_input($_POST["incLatitude"]);
            // check if latitude contains signed decimal only
            if (!preg_match("/^[+\-]?[0-9]{1,2}+(\.[0-9]{1,6})$/",$incLatitude)) {
                $incLatitudeErr = "Invalid Latitude. ";
            }
        }

        if (empty($_POST["incLongitude"])) {
            $incLongitudeErr = "Longitude is required.";
        } else {
            $incLongitude = test_input($_POST["incLongitude"]);
            // check if latitude contains signed decimal only
            if (!preg_match("/^[+\-]?[0-9]{1,3}+(\.[0-9]{1,6})$/",$incLongitude)) {
                $incLongitudeErr = "Invalid Longitude.";
            }
        }
        */
        if(empty($resourceNameErr) and empty($homeLatitudeErr) and empty($homeLongitudeErr) and empty($costAmtErr)){

            $incName = htmlspecialchars($_POST['incName']);
            $incDate = htmlspecialchars($_POST['incDate']);
            $incLatitude = htmlspecialchars($_POST['incLatitude']);
            $incLongitude = htmlspecialchars($_POST['incLongitude']);

            /*$insertIncidentStmt =  "INSERT INTO Incidents (Description, IncidentDate, IncidentOwner, Latitude, Longitude) " .
                                   "VALUES ('$incName', '$incDate', '$loginUser', $incLatitude, $incLongitude)";*/

            $insertIncidentStmt =  insertIncidentStmt($incName, $incDate, $loginUser, $incLatitude, $incLongitude);

            //echo $insertIncidentStmt;

            if(!$mysqli->query($insertIncidentStmt)){
                    $saveError = "Add Incident failed: (" . $mysqli->errno . ") "; //. $mysqli->error;
            }
            else{
                header("Location: mainmenu.php");
                exit;
            }

        }

    }

    if (isset($_POST['cancelButton'])){
        header("Location: mainmenu.php");
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

                    <!-- Incident Description input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="incName">Incident</label>
                        <div class="col-md-4">
                            <input id="incName" name="incName" type="text" placeholder="enter incident" class="form-control input-md" maxLength = "50" value="<?php echo $incName;?>" maxLength = "50" pattern="[0-9A-Za-z ]+" title = "Only alphabets, numbers and spaces allowed" required>
                            <span class="help-block">Incident description</span>
                        </div>
                        <div class="col-md-4" style="color: red">
                            <?php echo $incNameErr;?>
                        </div>
                    </div>


                    <!-- Incident Date input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="incDate">Incident Date</label>
                        <div class="col-md-4">
                            <input id="incDate" name="incDate" type="date" class="form-control input-md" min=<?php echo date('Y-m-d',(strtotime ( '-2 day' , strtotime (date('Y-m-d')) ) ));?> max= <?php echo date('Y-m-d');?> value="<?php echo $incDate;?>" required>
                            <span class="help-block">Incident Date</span>
                        </div>
                    </div>

                    <!-- Incident Location-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="incLocation">Incident location</label>
                        <div class="col-md-2">
                            <input id="incLatitude" name="incLatitude" type="number" placeholder="lat" class="form-control input-md" 
                            value="<?php echo $incLatitude;?>" min="-90.000000" max = "90.000000" step= "0.000001" required>
                            <span class="help-block">Latitude</span>
                        </div>
                        <div class="col-md-2">
                            <input id="incLongitude" name="incLongitude" type="number" placeholder="long" class="form-control input-md" 
                            value="<?php echo $incLongitude;?>" min="-180.000000" max = "180.000000" step= "0.000001" required>
                            <span class="help-block">Longitude</span>
                        </div>
                        <div class="col-md-4" style="color: red">
                            <?php echo $incLatitudeErr;?>
                            <?php echo $incLongitudeErr;?>
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