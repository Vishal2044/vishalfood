<?php

$servername = "localhost";

$username = "root"; 

$password = ""; 

$dbname = "vishalfood1"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn){

    //echo "connection ok";

}
else{

    echo "connection failed".mysqli_connect_error();
}


?> 