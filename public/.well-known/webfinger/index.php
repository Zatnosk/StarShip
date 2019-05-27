<?php
if (isset($_GET['resource'])) {

$account = $_GET['resource'];

$snip = strpos($account, ':');
$snop = strpos($account, '@');
$name = substr($account, $snip+1, $snop-$snip-1);
$host = substr($account, $snop+1);

header('Content-Type: application/json');
echo '{"subject":"'. $account .'",';
echo '"links":[{"rel":"self","type":"application/activity+json",';
echo '"href":"https://'. $host .'/acct/'. $name .'/actor.json"}]}';

}

else echo "I said FINGERPRINTS!";

?>
