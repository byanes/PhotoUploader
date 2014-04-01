<!DOCTYPE html>
<?php
session_start();
$qurl = $_SESSION['queueurl'];
$rhandle = $_SESSION['rhandle'];
$bucket = $_SESSION['bucket'];
$url2 = $_SESSION['url2'];
$url = $_SESSION['url'];
$topicArn = $_SESSION['topicArn'];


// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';

use Aws\SimpleDb\SimpleDbClient;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;
use Aws\Common\Aws;
use Aws\SimpleDb\Exception\InvalidQueryExpressionException;


//aws factory
$aws = Aws::factory('/var/www/vendor/aws/aws-sdk-php/src/Aws/Common/Resources/custom-config.php');

// Instantiate the S3 client with your AWS credentials and desired AWS region
$client = $aws->get('S3');

$sdbclient = $aws->get('SimpleDb');

$sqsclient = $aws->get('Sqs');

$snsclient = $aws->get('Sns'); 

//removes the specified message from the specified queue
$result = $sqsclient->deleteMessage(array(
    // QueueUrl is required
    'QueueUrl' => $qurl,
    // ReceiptHandle is required
    'ReceiptHandle' => $rhandle,
));

//Sets lifecycle configuration for your bucket. If a lifecycle configuration exists, it replaces it.
$result = $client->putBucketLifecycle(array(
    'Bucket' => $bucket,
    'Rules' => array(
        array(
            'Expiration' => array(
                'Days' => 1,
            ),
            'Prefix' => '',
            'Status' => 'Enabled',
        ),
    ),
));


//uses the acl subresource to set the access control list (ACL) permissions 
//for an object that already exists in a bucket
$result = $client->putObjectAcl(array(
    'ACL' => 'public-read',
    'Bucket' => $bucket,
    'Key' => 'WaterMark_' . $_SESSION['uploadedfile'] ,
));

#####################################################
# SNS publishing of message to topic - which will be sent via SMS
#####################################################
$result = $snsclient->publish(array(
   'TopicArn' => $topicArn,
   'TargetArn' => $topicArn,
   'Message' => 'Your image has been successfully processed. Here you can take a look of it: ' . $url2,
   'Subject' => $url2,
));

$_SESSION = array();
session_destroy();

?> 

<html>
<head><title>Cleanup PHP</title></head>
<body bgcolor="#3399FF">
<p align="center"><b>CONGRATULATIONS! These are your images!</b></p>
<br /><table align="center">
<tr>
<td align="center">Before</td>
<td align="center">After</td>
</tr>
<tr>
<td><img src="<? echo $url ?>" height="400" width="400"/></td>
<td><img src="<? echo $url2 ?>" height="400" width="400"/></td>
</tr>
</table>
</body>
</html>
