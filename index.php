<!DOCTYPE html>
<?php
session_start();
?>
<html>
<head>
<title>Welcome</title>
</head>

<body bgcolor="#3399FF">
<?php
// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';

use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Aws\Common\Aws;

//aws factory
$aws = Aws::factory('/var/www/vendor/aws/aws-sdk-php/src/Aws/Common/Resources/custom-config.php');
$snsclient = $aws->get('Sns'); 
$sqsclient = $aws->get('Sqs');

$topicName="mp1bygresize";

$snsresult = $snsclient->createTopic(array(
    // Name is required
    'Name' => $topicName,
));

$topicArn = $snsresult['TopicArn'];

#echo $topicArn ."\n";
#echo $phone ."\n";

$snsresult = $snsclient->setTopicAttributes(array(
    // TopicArn is required
    'TopicArn' => $topicArn,
    // AttributeName is required
    'AttributeName' => 'DisplayName',
    'AttributeValue' => 'aws544',
));

$sqsresult = $sqsclient->createQueue(array('QueueName' => 'photo_q2',));
$qurl=$sqsresult['QueueUrl'];
?>

<h2 align= "center">Picture Uploader</h2>
<div align="center">
<form action="process.php" method="post" enctype="multipart/form-data">
  Email: <input type="text" name="email" > <br />
  Cell Number: <input type="text" name="phone" > <br />
  Choose Image: <input type="file" name="uploaded_file" id="uploaded_file"> <br />  
 <input type="hidden" name="topicArn" value="<? echo $topicArn ?>" >
<input type="hidden" name="qurl" value="<? echo $qurl ?>" > 
 <br /><input type="submit"  value="submit it!" >
</form>
</div>
</body>

</html>
