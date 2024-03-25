<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "onlineNotes";
$link = @mysqli_connect($servername, $username, $password, $database);
if(mysqli_connect_error()){
   die("unable to connect to database"); 
}
?>


