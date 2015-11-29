<html>
<head><title>Gallery</title>
  <!-- jQuery -->
  <script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
  <!-- Fotorama -->
  <link href="fotorama.css" rel="stylesheet">
  <script src="fotorama.js"></script>
</head>
<body>
<div class="fotorama" data-width="700" data-ratio="700/467" data-max-width="100%">

<?php
// NOTE: code provided by Jeremy Hajek is modified.
session_start();
if(isset($_SESSION['firstname']){
$username=$_SESSION['firstname'];
}
else
{
$username="guest";
}
echo $email;
require 'vendor/autoload.php';
//create client for s3 bucket
//use Aws\Rds\RdsClient;
//$client = RdsClient::factory(array(
//'region'  => 'us-east-1'
//));

$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

$result = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'ITMO-544-Database'
   
));
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
    echo "============\n". $endpoint . "================";

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","ilovebunnies","CloudProject") or die("Error " . mysqli_error($link));

//below line is unsafe - $email is not checked for SQL injection -- don't do this in real life or use an ORM instead

if($username!="guest"){
$link->real_query("SELECT * FROM ITM0-544-Table where uName=$username");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {

    echo "<img src =\" " . $row['rawS3Url'] . "\" /><img src =\"" .$row['finishedS3Url'] . "\"/>";
echo $row['id'] . "Email: " . $row['email'];
}
}
else
{
$link->real_query("SELECT raws3url FROM ITM0-544-Table);
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {

    echo "<img src =\" " . $row['rawS3Url'] . "\" />";

}
}

$link->close();


?>

</div>
</body>
</html>