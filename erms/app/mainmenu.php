<?php
    session_start();

    if(!isset($_SESSION['login_user'])) {
        header("Location: logout.php");
    }

    $loginName = $_SESSION['login_username'];
    $loginUserDetail = $_SESSION['login_userdetail'];
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

<div class="container" align = "center" style="width: 75%">
    <div style="min-width: 100px"><br><br><br><br></div>
    <div class="panel panel-default" style="width: inherit">
        <div class="panel-heading" align="center">
            <div class="container-fluid panel-container">
                <div class="col-xs-4 text-left">
                    <h4 class="panel-title"></h4>
                </div>
                <div class="col-xs-4 text-center">
                    <h4 class="panel-title">ERMS Main Menu</h4>
                </div>
                <div class="col-xs-4 text-right">
                    <h2 class="panel-title"><?=$loginName ?></h2 class="panel-title">
                    <h6><?=$loginUserDetail?></h6>
                </div>
            </div>

        </div>
        <div class="panel-body" align = "center">
            <p><a href = "addresource.php">Add Resource</p>
            <p><a href = "addincident.php">Add Emergency Incident</p>
            <p><a href = "searchresources.php">Search Resources</p>
            <p><a href = "resourcestatus.php">Resource Status</p>
            <p><a href = "resourcereport.php">Resource Report</p>
            <p><a href = "logout.php">Exit</a></p>
        </div>
    </div>
</div>
</body>

</html>