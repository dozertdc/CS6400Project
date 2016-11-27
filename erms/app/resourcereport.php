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


    //resources that are currently in use responding to any incidents owned by the current user 
    /*$queryResourceReport = "SELECT PESF.ESFId, PESF.ESFName, 
                            COUNT(CASE WHEN R.PrimaryESFId = PESF.ESFId THEN 1 END) AS Total_Resources, 
                            COUNT(CASE WHEN R.Status = 'In Use' THEN 1 END) AS Resources_In_Use
                            FROM EmergencySupportFunctions AS PESF
                                LEFT OUTER JOIN Resources AS R ON (R.PrimaryESFId = PESF.ESFId AND R.ResourceOwner = '$loginUser')
                            GROUP BY PESF.ESFId, PESF.ESFName WITH ROLLUP";*/

    $queryResourceReport =  queryResourceReport($loginUser);                     
    //echo $queryUsedResStmt;

    $resResourceReport = $mysqli->query($queryResourceReport);

    if(!$resResourceReport){
        $dataError = "Query Resource Report failed: (" . $mysqli->errno . "::" . $mysqli->error . ")";
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

<div class="container" align="center">
    <div style="min-width: 100px"><br><br><br><br></div>
    <div class="panel panel-default" style="width: inherit">
        <div class="panel-heading" align="center">
            <h4 class="panel-title">Resource Report</h4>
        </div>
        <div class="panel-body">
            <form class="form-horizontal" method= "post" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <fieldset>

                    <!-- Form Name -->
                    <!--<legend align="left">Resource Report</legend>-->

                    <!-- Resource Report-->
                    <div class="form-group">
                        <label for="incName">Resource Report by Primary Emergency Support Function</label>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Primary Emergency Support Function</th>
                            <th>Total Resources</th>
                            <th>Resources in Use</th>
                          </tr>
                        </thead>
                        <tbody>
                            <?php
                                $resResourceReport->data_seek(0);
                                while ($currentResource = $resResourceReport->fetch_assoc()) {?>
                                    <?php if(isset($currentResource['ESFName'])){?>
                                        <tr>
                                            <td><?=$currentResource['ESFId']?></td>
                                            <td><?=$currentResource['ESFName']?></td>
                                            <td><?=$currentResource['Total_Resources']?></td>
                                            <td><?=$currentResource['Resources_In_Use']?></td>
                                        </tr>
                                    <?php }?>
                            <?php }?>
                            <?php $resResourceReport->data_seek(($resResourceReport->num_rows) - 1);
                                  $currentResource = $resResourceReport->fetch_assoc()
                            ?>
                            <tr>
                                <td></td>
                                <td><b>TOTALS</b></td>
                                <td><?=$currentResource['Total_Resources']?></td>
                                <td><?=$currentResource['Resources_In_Use']?></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Close Report -->
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