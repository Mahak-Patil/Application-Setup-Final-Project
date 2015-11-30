<?php
// This script is the modified version of "submit.php" provided by Jeremy Hajek.

echo "Hello";
session_start();
var_dump($_POST);
if(!empty($_POST)){
echo $_POST['useremail'];
echo $_POST['phone'];
echo $_POST['firstname'];
$_SESSION['firstname']=$_POST['firstname'];
$_SESSION['phone']=$_POST['phone'];
$_SESSION['useremail']=$_POST['useremail'];
}

else
{
echo "post empty";
}

$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
print '<pre>';

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  echo "File is valid, and was successfully uploaded.\n";
}

else {
    echo "Possible file upload! \n";
}

echo 'Here is some more debugging info:';
print_r($_FILES);
print "</pre>";

require 'vendor/autoload.php';
#use Aws\S3\S3Client;
#$client = S3Client::factory();
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
#print_r($s3);
$bucket = uniqid("CharlieBucketsGallore3",false);
#$result = $s3->createBucket(array(
#    'Bucket' => $bucket
#));
#
## AWS PHP SDK version 3 create bucket
$result = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $bucket
]);
#print_r($result);
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
   'Key' => "RawURL".$uploadfile,
'ContentType' => $_FILES['userfile']['type'],
'Body' => fopen($uploadfile,'r+')
]);
$url = $result['ObjectURL'];
echo $url;

##new code for image and bucket
$thumbimageobj = new Imagick($uploadfile);
$thumbimageobj->thumbnailImage(80, 80);
$thumbimageobj->writeImage();

//$bucketfinished=uniquid("finishedimage",false);
//$resultfinished = $s3->createBucket([
//    'ACL' => 'public-read',
//    'Bucket' => $bucketfinished
//]);
#print_r($resultfinished);
$resultfinished = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
   'Key' => "Finished URL: ".$uploadfile,
'ContentType' => $_FILES['userfile']['type'],
'Body' => fopen($uploadfile,'r+')
]);
$finishedurl = $resultfinished['ObjectURL'];
echo $finishedurl;
$temporary_email = $_POST['useremail'];
//thumbnail code ends here

$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

$result = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'ITMO-544-Database'
));
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
    echo "============\n". $endpoint . "================";

$link = mysqli_connect($endpoint,"controller","ilovebunnies","CloudProject");

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

else {
echo "Success";
}

#create sns client

$sns = new Aws\Sns\SnsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

#print_r($result);
//echo "sns Topic";
//to list topics

$SNSresult = $sns->listTopics(array(

));


foreach ($SNSresult['Topics'] as $key => $value){

if(preg_match("/ImageTopicSK/", $SNSresult['Topics'][$key]['TopicArn'])){
$topicARN =$SNSresult['Topics'][$key]['TopicArn'];
}
}

#MORE NEW CODE
$SUBSresult = $sns->listSubscriptionsByTopic(array(
     //TopicArn is required
    'TopicArn' => $topicARN,
   
));
foreach ($SUBSresult['Subscriptions'] as $key => $value){

if((preg_match($temporary_email, $SUBSresult['Subscriptions'][$key]['endpoint']))&&(preg_match("PendingConfirmation", $SUBSresult['Subscriptions'][$key]['SubscriptionArn']))){
$alertmsg='true';
$_SESSION['alertmsg']=$alertmsg;
}
else{
$alertmsg='false';
$_SESSION['alertmsg']=$alertmsg;
}
}


$uName=$_POST['username'];
$email = $_POST['useremail'];
$phone = $_POST['phone'];
$rawS3Url = $url; 
$finishedS3Url =$finishedurl;
$jpgfilename = basename($_FILES['userfile']['name']);
$state=0;

$res = $link->query("SELECT * FROM ITMO-544-Table where email='$email'");

if($res->num_rows>0){

if (!($stmt = $link->prepare("INSERT INTO ITMO-544-Table (uName,email,phone,rawS3Url,finishedS3Url,jpgFileName,state) VALUES (?,?,?,?,?,?,?)"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}

$stmt->bind_param("ssssssi",$uName,$email,$phone,$rawS3Url,$finishedS3Url,$jpgFileName,$state);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno0 . ") " . $stmt->error;
}

printf("%d Row inserted.\n", $stmt->affected_rows);

$stmt->close();

$pub = $sns->publish(array(
    'TopicArn' => $topicARN,
    'Subject' => 'ITMO-544-Notification for image upload',
    'Message' => 'Imae uploaded successfully.',
    
    
));

$link->real_query("SELECT * FROM ITMO-544-Table");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . " " . $row['email']. " " . $row['phone'];
}

$link->close();

$url	= "gallery.php";
   header('Location: ' . $url, true);
   die();
}
else 
{

$url	= "gohere.php";
   header('Location: ' . $url, true);
   die();

}
?> 

     

 
