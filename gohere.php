<?php

session_start();
$useremail=$_SESSION['useremail'];
$username=$_SESSION['firstname'];
$phone=$_SESSION['phone'];
#echo $useremail;
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$resultrds = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'ITMO-544-Database'
   
));
$endpoint = $resultrds['DBInstances'][0]['Endpoint']['Address'];
    echo "============\n". $endpoint . "================";
$link = mysqli_connect($endpoint,"controller","ilovebunnies","CloudProject") or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
else {
#echo "Success";
}
#create sns client
$sns = new Aws\Sns\SnsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$result1 = $result->listTopics(array(
    
));
#print_r($result1);

foreach ($result1['Topics'] as $key => $value){
if(preg_match("/SnsImageTopicName/", $result1['Topics'][$key]['TopicArn'])){
$topicARN =$result['Topics'][$key]['TopicArn'];
}
}
$result = $sns->subscribe(array(
    
    'TopicArn' => $topicARN,
    'Protocol' => 'email',
    'Endpoint' => $useremail,
));
#echo  "Sub-test".$result;
#print_r($result);
if (!($stmt = $link->prepare("INSERT INTO ITMO-544-Table (uName,email,phone,rawS3Url,finishedS3Url,jpgFileName,state) VALUES (?,?,?,?,?,?,?)"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}
$stmt->bind_param("sss",$username,$useremail,$phone);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno0 . ") " . $stmt->error;
}
#printf("%d Row inserted.\n", $stmt->affected_rows);
$stmt->close();
echo "Please check your email to confirm subscription, then click <a href='index.php'/>redirect!</a>";
?>