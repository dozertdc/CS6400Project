<?php
    include_once "config.php";
    include "dbfunctions.php";
    session_start();

    if(!isset($_SESSION['login_user'])) {
        header("Location: index.php");
    }

    $loginName = $_SESSION['login_username'];
    $loginUser = $_SESSION['login_user'];
    $keywordSearch = "";

    if(isset($_POST['cancelButton'])){
        header("Location: mainmenu.php");
        exit;
    }

    if(isset($_POST['searchButton'])){
        $_SESSION['keywordSearch'] = test_input($_POST['keywordSearch']);
        $_SESSION['selectESF'] = test_input($_POST['selectESF']);
        $selectIncidentDetailArray = explode(':', test_input($_POST['selectIncident']));
        //print_r ($_POST['selectIncident']);
        $_SESSION['selectedIncidentId'] = $selectIncidentDetailArray[0];
        $_SESSION['selectedIncidentLat'] = $selectIncidentDetailArray[1];
        $_SESSION['selectedIncidentLong'] = $selectIncidentDetailArray[2];
        $_SESSION['selectedIncidentDesc'] = $selectIncidentDetailArray[3];
        $_SESSION['incDistance'] = (empty($_POST['incDistance'])) ? 99999 : $_POST['incDistance'];
        header("Location: searchresourcesresults.php");
        exit;
    }

    $selectESFStmt = selectESFStmt();
    $resESF = $mysqli->query($selectESFStmt);

    if(!$resESF OR $resESF->num_rows === 0)
    {
        $loginError = "No ESFs present. Contact system administrator...";
        $resESF->free();
        header("Location: index.php?err=$loginError");
        exit;
    }

    $selectIncidentsStmt = selectIncidentsStmt($loginUser);
    $resInc = $mysqli->query($selectIncidentsStmt);

    if(!$resInc )
    {
        $loginError = "No Incidents present. Add incidents for login user...";
        $resInc->free();
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
</head>

<body>

<div class="container" align = "center" style="width: 80%">
    <div style="min-width: 100px"><br><br><br><br></div>
    <div class="panel panel-default" style="width: inherit">
        <div class="panel-heading" align="center">
            <h4 class="panel-title">Search Resources</h4>
        </div>
        <div class="panel-body" align = "center">
            <form class="form-horizontal" method= "post" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <fieldset>

                    <!-- Form Name -->
                    <!--<legend align="left">Search Resources...</legend>-->


                    <!-- Incident Description input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="keywordSearch">Keyword</label>
                        <div class="col-md-4">
                            <input id="keywordSearch" name="keywordSearch" type="text" placeholder="enter search keyword" class="form-control input-md" maxLength = "50" value="<?php echo $keywordSearch;?>" pattern="[A-Za-z0-9 ]+" title = "Only alphanumeric chars allowed">
                            <span class="help-block">Incident description</span>
                        </div>
                    </div>

                    <!-- Select ESF -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="selectESF">ESF</label>
                        <div class="col-md-4">
                            <select id="selectESF" name="selectESF" class="form-control">
                                <?php
                                $resESF->data_seek(0);
                                echo "<option value = 0></option>";
                                while ($pesf = $resESF->fetch_assoc()) {
                                    echo "<option value=" . $pesf['ESFId'] . ">" . $pesf['ESFName'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Select Incident -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="selectIncident">Incident</label>
                        <div class="col-md-4">
                            <select id="selectIncident" name="selectIncident" class="form-control">
                                <?php
                                $resInc->data_seek(0);
                                echo "<option value = 0:NULL:NULL:></option>";
                                while ($incRow = $resInc->fetch_assoc()) {
                                    echo '<option value="'. $incRow["IncidentId"] . ':'. $incRow["Latitude"] . ':'. $incRow["Longitude"] . ':'
                                    .$incRow["Description"] .'">' . $incRow['Description'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Incident Location-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="incLocation">Location</label>
                        <div class="col-md-4">
                            <input id="incDistance" name="incDistance" type="number" placeholder="distance in kilometres" class="form-control input-md" 
                            value="<?php $incDistance;?>" min=0 max=99999>
                            <span class="help-block">Within Kilometres of Incident</span>
                        </div>
                    </div>

                    <!-- Save Resource -->
                    <div class="form-group">
                        <div class="col-md-12">
                            <button id="searchButton" name="searchButton" type="submit"  class="btn btn-success">Search</button>
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