<?php

session_start();
require 'vendor/autoload.php';
$internals=true;
$_SESSION['internals']=$internals;
echo "=========== $internals =======";

$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

$DBresult = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'ITM0-544-Database-Replica'
   
));
$DBendpoint = $DBresult['DBInstances'][0]['Endpoint']['Address'];
    echo "============\n". $DBendpoint . "================";

$DBlink = mysqli_connect($DBendpoint,"controller","ilovebunnies","CloudProject");

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

else {
echo "Connected to database";
}


$backupFile = '/tmp/ITMO544FinalProject'.date("Y-m-d-H-i-s").'.gz';
$command = "mysqldump --opt -h $DBendpoint -u controller -p ilovebunnies CloudProject | gzip > $backupFile";
exec($command);
echo "Success";


			$s3 = new Aws\S3\S3Client([
				'version' => 'latest',
				'region'  => 'us-east-1'
			]);

$bucket='MahakPatilFinalProject-'.rand().'-dbdump';
			if(!$s3->doesBucketExist($bucket)) {
				
				$result = $s3->createBucket([
					'ACL' => 'public-read',
					'Bucket' => $bucket,
				]);
	
				$s3->waitUntil('BucketExists', array('Bucket' => $bucket));
				echo "$bucket created ";
			}

$result = $s3->putObject([
'ACL' => 'public-read',
'Bucket' => $bucket,
'Key' => $backupFile,
'SourceFile'   => $backupFile,
'Body' => fopen($backupFile,'r+'),
]);
echo "Database backed up successfully.";
$url = $result['ObjectURL'];
echo $url;


$urlintro	= "index.php";
   header('Location: ' . $urlintro, true);
   die();

$DBlink->close();
?>