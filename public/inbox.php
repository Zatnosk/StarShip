<?php
require_once "../utils.php";
require_once "../activities.php";

/*
  this function is just formatting notes so I could read things easier
  in other words, it's entirely for testing and should be handled client-side
*/
function parse_note($object_arr) {
  $content = '';
  $content .= "Link: ". $object_arr['id'] ."\n";
  if (isset($object_arr['inReplyTo'])) $content .= "Re: ". $object_arr['inReplyTo'] ."\n";
  $content .= $object_arr['content'] ."\n";
  return $content;
}

/*
  work-around for having JUST too old a version of PHP to use getallheaders()
  should probably be moved to utils.php
*/
function getRequestHeaders() {
  $headers = array();
  foreach($_SERVER as $key => $value) {
    if (substr($key, 0, 5) <> 'HTTP_') {
      continue;
    }
    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
    $headers[$header] = $value;
  }
  return $headers;
}

/* fetches the public key for a remote actor to verify their signed data */
function get_pubkey($user_agent, $keyId) {
  $key_uri = $keyId;

  // post request to obtain public key; later, cache public keys for 24h
  $ch = curl_init($key_uri);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // hopefully conforming my request STRICTLY to AP spec Shoulds will mean Mastodon gives me the damn key
  // p.s. it does
  curl_setopt($ch, CURLOPT_HTTPHEADER,
    array('Content-Type: application/activity+json',
          'Accept: application/activity+json,application/ld+json; profile="https://www.w3.org/ns/activitystreams"')
    );
  // useragent file contains StarShip app attribution and current version number
  include "../useragent";
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  $json = curl_exec($ch);
  $actor = json_decode($json, true);

  // make sure there IS a public key
  if (isset($actor['publicKey'])) {
    // check that the public key says it's the same key we're looking for
    if (!isset($actor['publicKey']['id']) || $actor['publicKey']['id'] !== $keyId) return false;
    // return the public key PEM, if there is one
    if (isset($actor['publicKey']['publicKeyPem'])) return $actor['publicKey']['publicKeyPem'];
    else return false;
  }
  else return false;
}

/* does what it says on the tin */
function verify_signature(&$headers) {
  $sig_array = [];
  $sig_s = '';

  // break apart comma-separated string of id=value items for the signature
  if ($arr = explode(',', $headers["Signature"])) {
    foreach ($arr as $item) {
      if ($pos = strpos($item, '=')) {
        $sig_array[trim(substr($item, 0, $pos))] = trim(substr($item, $pos + 1),'"');
      }
      else $sig_array["nullIDs"] .= $item;
    }
  }

  if (!isset($sig_array["keyId"])) return "no keyId found";

  // should add a check to cross-check actor/owner entries later
  if (($pubkey = get_pubkey($headers["User-Agent"],$sig_array["keyId"])) === false) return "public key not found";

  $signed_headers = explode(' ',$sig_array['headers']);
  $signed_data = '';

  //add a check for encryption algorithm and use the appropriately indicated one

  foreach ($signed_headers as $item) {
    if ($item == '(request-target)') $signed_data .= "(request-target): post /inbox\n";
    else if ($item == 'digest') $signed_data .= "digest: SHA-256=".
      base64_encode(hash("sha256",file_get_contents("php://input"),true)) ."\n";
    else $signed_data .= "$item: ". $headers[dashedCamelCase($item,true)] ."\n";
  }

  $verification = openssl_verify(
    trim($signed_data), //trimmed to avoid trailing newlines
    base64_decode($sig_array['signature']), //decoded signature string
    $pubkey, //use PEM text from actor
    OPENSSL_ALGO_SHA256 //algorithm flag should be set from headers
  );

  if ($verification === 1) return true;
  else if ($verification === 0) return false;
  else if ($verification == -1) return openssl_error_string();
}

/* main code */

/*
  The inbox is currently linked to client-side display but should be disconnected before too much additional progress has been made to prevent the front- and back-end becoming too deeply intertwined and thus not easily separable.
  Most of the current read-out aspects will need to be modified with the expectation that they are passing "raw" data to the client/front-end which the latter will be parsing.
*/

$headers = getRequestHeaders();

$verified = verify_signature($headers);
// return Forbidden with informative message if signature is wrong
if ($verified === false) {
  http_response_code(403);
  die('Invalid signature.');
}
// return Server Error with informative message if signature verification outright fails
if ($verified !== true) {
  http_response_code(500)
  die('Error verifying signature: '. $verified);
}
// signature is verified, continue

// parse json
$post_content = json_decode(file_get_contents("php://input"),true);
file_put_contents("../logs/post.log", "Activity by: ". $post_content['actor'] ."\n", FILE_APPEND);
$newActivity = null;

// currently only has special-case handling for Create and Follow Activities
// goal: expand this to all supported types, then write sufficiently flexible default case to handle all others
switch($post_content['type']) {
  // process a Create activity
  case "Create":
    try { $newActivity = new StarShip\Create(); $newActivity->fill($post_content); }
    catch(Exception $e) {
      file_put_contents("../logs/debug.log",$e ."\n",FILE_APPEND);
      break;
    }
    $printout = "Created a <a href='". $newActivity->id() ."'>". $newActivity->obj['type'] ."</a><br/>\n";
    $printout .= $newActivity->obj['content'] ."<br/>\n";
    break;
  // process a Follow activity
  case "Follow":
    try { $newActivity = new StarShip\Follow(); $newActivity->fill($post_content); }
    catch(Exception $e) {
      file_put_contents("../logs/debug.log",$e ."\n",FILE_APPEND);
      break;
    }
    $printout = "Sent a <a href='". $newActivity->id() ."'>". $newActivity->obj['type'] ."</a><br/>\n";
    $printout = "<a href=''>Accept</a><br/>\n"; // link to post an Accept activity will go here
    // should also have a Reject activity
    break;
  default:
    $printout = $post_content['type'] ."\n";
}

// bunch of logging and output stuff
file_put_contents("../logs/post.log", "$printout\n", FILE_APPEND);
file_put_contents("../logs/recent.html", "$printout<br/>", FILE_APPEND);
file_put_contents("../logs/recent.html", "Activity by: ". $post_content['actor'] ."<br/>", FILE_APPEND);
return '';
?>
