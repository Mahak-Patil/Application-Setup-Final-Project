<html>
<head><title>Gallery</title>
  <!-- jQuery -->
  <script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
  <!-- Fotorama -->
  <link href="fotorama.css" rel="stylesheet">
  <script src="fotorama.js"></script>
</head>
<body>

<?php
// NOTE: code provided by Jeremy Hajek is modified.
session_start();

require 'vendor/autoload.php';

$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

$DBresult = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'ITMO-544-Database'
   
));
$DBendpoint = $DBresult['DBInstances'][0]['Endpoint']['Address'];
    echo "============\n". $endpoint . "================";

//echo "begin database";
$DBlink = mysqli_connect($DBendpoint,"controller","ilovebunnies","CloudProject") or die("Error " . mysqli_error($link));

if(isset($_SESSION['useremail'])){
$email=$_SESSION['useremail'];
//echo $email;
$DBlink->real_query("SELECT * FROM ITMO-544-Table where email='$email'");
$DBres = $DBlink->use_result();

//echo "Result set order...\n";
echo '<div align="left" class="fotorama" data-width="100" data-ratio="100/46" data-max-width="50%">';
while ($row = $DBres->fetch_assoc()) {

    echo "<img src =\"" .$row['finishedS3Url'] . "\"/>";

}
echo'</div';
}

else
{
echo "Error! No image entered";

$DBlink->real_query("SELECT rawS3Url FROM ITMO-544-Table");
$DBres = $DBlink->use_result();
//echo "Result set order...\n";
echo '<div align="right" class="fotorama" data-width="700" data-ratio="700/467" data-max-width="50%">';
while ($row = $DBres->fetch_assoc()) {

    echo "<img src =\" " . $row['rawS3Url'] . "\" />";
    
}

echo'</div>';

}

echo '<div class="errormsg">';
if((isset($_SESSION['alertmsg']))&&($_SESSION['alertmsg'])){
echo "YOU NEED TO SUBSCRIBE TO RECEIVE NOTIFICATIONS!";
}
echo '</div>';

$DBlink->close();
session_unset();
echo "<a href='index.php'/>Home</a>"
?>



</body>
</html>
