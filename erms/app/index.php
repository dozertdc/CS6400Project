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
<div class="container" style="width: 50%">
    <div><br><br><br><br></div>
    <div class="panel panel-default">
        <div class="panel-heading" align="center">
            <h3 class="panel-title">ERMS Login</h3>
        </div>
        <div class="panel-body">
            <form class="form-horizontal" method= "post" role="form" action="login.php">
                <fieldset>
                    <!-- Username input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="textinput">Username</label>
                        <div class="col-md-4">
                            <input id="inputUserName" name="inputUserName" type="text" placeholder="enter username" class="form-control input-md" required="">

                        </div>
                    </div>

                    <!-- Password input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="passwordinput">Password</label>
                        <div class="col-md-4">
                            <input id="inputPassword" name="inputPassword" type="password" placeholder="enter password" class="form-control input-md" required="">

                        </div>
                    </div>

                    <!-- Login Button -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="singlebutton"></label>
                        <div class="col-md-4">
                            <button id="login" type = "submit" name="login" class="btn btn-success">Login</button>
                        </div>
                    </div>

                    <!--Login error message -->
                    <?php
                        if (isset($_GET['err'])){?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="textarea"></label>
                            <div class="col-md-4">
                                <div class="alert alert-danger">
                                    <?php echo $_GET['err']; ?>
                                </div>
                            </div>
                        </div>
                    <?php }?>

                </fieldset>
            </form>
        </div>
    </div>
</div>
</body>
</html>