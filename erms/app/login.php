<?php
   include_once "config.php";
   include "dbfunctions.php";
   session_start();

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        // get the username and password sent from login form

        $inputUserName = htmlspecialchars($_POST['inputUserName']);
        $inputPassword = htmlspecialchars($_POST['inputPassword']);

        $selectUserStmt = selectUserStmt($inputUserName);
        $res = $mysqli->query($selectUserStmt);

        if(!$res or $res->num_rows == 0){
            $loginError = "Username entered is invalid. If problem persists contact administrator...";
            $res->free();
            header("Location: index.php?err=$loginError");
            exit;
        }

        // If result matched $inputUserName, table row must be 1 row
        $user = $res->fetch_assoc();

        if($user[Password] != $inputPassword)
        {
            $loginError = "Invalid password entered. If problem persists contact administrator...";
            $res->free();
            header("Location: index.php?err=$loginError");
            exit;
        }

        $_SESSION['login_user'] = $inputUserName;
        $_SESSION['login_username'] = $user['Name'];
        $_SESSION['login_usertype'] = $user['UserType'];
        $_SESSION['login_userdetail'] = '';

        switch ($user['UserType']) {
            case 'Company':
                $selectCompanyStmt = selectCompanyStmt($inputUserName);
                $res2 = $mysqli->query($selectCompanyStmt);

                if($res2->num_rows === 1){
                    $userdetail = $res2->fetch_assoc();
                    $_SESSION['login_userdetail'] =  'Headquarters: ' . $userdetail['Headquarters'];
                }
                break;
            case 'GovernmentAgency':
                $selectGovernmentStmt = selectGovernmentStmt($inputUserName);
                $res2 = $mysqli->query($selectGovernmentStmt);

                if($res2->num_rows === 1){
                    $userdetail = $res2->fetch_assoc();
                    $_SESSION['login_userdetail'] = 'Jurisdiction: ' . $userdetail['Jurisdiction'];
                }
                break;
            case 'Municipality':
                $selectMunicipalityStmt = selectMunicipalityStmt($inputUserName);
                $res2 = $mysqli->query($selectMunicipalityStmt);
                
                if($res2->num_rows === 1){
                    $userdetail = $res2->fetch_assoc();
                    $_SESSION['login_userdetail'] = 'Population: ' . number_format($userdetail['Population']);
                }
                break;
        }


        echo "Login Successful for user: " . $inputUserName . ", UserName: " . $user['Name'] . " of User Type: " . $user['UserType'];
        header("location: mainmenu.php");

        $res2->free();
        $res->free();
    }
