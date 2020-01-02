<?php
session_start();
require_once "../authcheck.php";
require_once "../activities.php";
require_once "../deliver.php";

/* Check for sufficiently "fresh" actor information and fetch the inbox URI for that actor */
function fetchInbox($actor) {
  // I don't feel like writing the actor fetching and parsing etc. right now so I'm just hardcoding my sharedInbox
  return "https://tootplanet.space/inbox";
  
  // Check if $actor is a known actor in the database
  // If so, verify that the actor information in the database has been updated within the past [some period of time]
  // If actor information in the database is stale or $actor is unknown, fetch the actor remotely and store their information
  // Return the inbox URI
}

/*
  Currently this outbox only supports a single addressee
  TODO:
    - handle multiple addresses and CCs
    - validate content being created for format match and required properties
    - read private key of specific actor posting to the outbox
*/

$_POST['type'] = 'Note';
$_POST['published'] = date(DATE_W3C);

// validate this all later
try {
  $create = new StarShip\Create();
  $create->fill($_POST,false);
} catch(Exception $e) {
  file_put_contents("../logs/debug.log",$e ."\n",FILE_APPEND);
  header('Location: https://'. $_SERVER['HTTP_HOST'] .'/index.php?m=0');
  exit($e);
}

// TODO: make $inbox an array of all target inboxes
// make sure the "public" keyword(s) are accounted for correctly
$inbox = fetchInbox($create->address['to']);

$body = $create->toJSON();
file_put_contents("../logs/debug.log",$body,FILE_APPEND);

// needs to support sending-actor-specific key validation
$key = openssl_get_privatekey(file_get_contents('../private.pem'));
if ($key === FALSE) {
  error_log(openssl_error_string());
  return 'key option could not be parsed';
}

// TODO: loop through $inbox array for multiple addressees
$result = StarShip\deliver($body,$inbox,$key);

// later, the outbox will be posted to via ajax, API, or similar
// at that point, simply return the success status as an echo rather than doing a page redirect
if ($result !== false) header('Location: https://'. $_SERVER['HTTP_HOST'] .'/index.php?m=1');
else header('Location: https://'. $_SERVER['HTTP_HOST'] .'/index.php?m=0');
exit();
?>
