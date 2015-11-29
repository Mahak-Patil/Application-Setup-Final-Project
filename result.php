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
$bucket = uniqid("CharlieBucketsGallore",false);
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
   'Key' => $uploadfile,
'ContentType' => $_FILES['userfile']['type'],
'Body' => fopen($uploadfile,'r+')
]);
$url = $result['ObjectURL'];
echo $url;

##s3 and url for the thumbnailimage
$thumbimageobj = new Imagick($uploadfile);
$thumbimageobj->thumbnailImage(200, null);

$bucketfinished=uniquid("finishedimage",false);
$resultfinished = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $bucketfinished
]);
#print_r($resultfinished);
$resultfinished = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucketfinished,
   'Key' => $thumbimageobj,
'ContentType' => $_FILES['userfile']['type'],
'Body' => fopen($thumbimageobj,'r+')
]);
$finishedurl = $resultfinished['ObjectURL'];
echo $finishedurl;

//thumbnail code ends here

$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

$result = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'db1'
   
));
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
    echo "============\n". $endpoint . "================";

$link = mysqli_connect($endpoint,"testconnection1","testconnection1","Project1");

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

$result = $sns->listTopics(array(

));


foreach ($result['Topics'] as $key => $value){

if(preg_match("/ImageTopicSK/", $result['Topics'][$key]['TopicArn'])){
$topicARN =$result['Topics'][$key]['TopicArn'];
}
}

$uname=$_POST['username'];
$email = $_POST['useremail'];
$phoneforsms = $_POST['phone'];
$raws3url = $url; 
$finisheds3url =$finishedurl;
$jpegfilename = basename($_FILES['userfile']['name']);
$state=0;

$res = $link->query("SELECT * FROM MiniProject1 where email='$email'");

if($res->num_rows>0){

if (!($stmt = $link->prepare("INSERT INTO MiniProject1 (uname,email,phoneforsms,raws3url,finisheds3url,jpegfilename,state) VALUES (?,?,?,?,?,?,?)"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}

$stmt->bind_param("ssssssi",$uname,$email,$phoneforsms,$raws3url,$finisheds3url,$jpegfilename,$state);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno0 . ") " . $stmt->error;
}

printf("%d Row inserted.\n", $stmt->affected_rows);

$stmt->close();

$pub = $sns->publish(array(
    'TopicArn' => $topicARN,
    // Message is required
    'Subject' => 'Image Upload Notification',
    'Message' => 'Image is successfully uploaded and saved!',
    
    
));

$link->real_query("SELECT * FROM MiniProject1");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . " " . $row['email']. " " . $row['phoneforsms'];
}

$link->close();

$url	= "gallery.php";
   header('Location: ' . $url, true);
   die();
}
else 
{

$url	= "temp.php";
   header('Location: ' . $url, true);
   die();

}
?> 

     

 
