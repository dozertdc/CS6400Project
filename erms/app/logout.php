<?php
   include_once "config.php";
   session_start();
   
   if(session_destroy()) {
   		$mysqli->close();
       header("Location: index.php");
       exit;
   }
?>