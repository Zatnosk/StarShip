<?php namespace StarShip {

/* POSTs a signed json activity to someone's inbox URI */

function deliver($body, $inbox, $key) {
  // rfc standards require the timezone be declared as GMT and PHP's built-in formats all say UTC
  // so we gotta do it manually
  $date = date("D, d M Y H:i:s") . " GMT";
  // header information to sign
  $data = "(request-target): post ". parse_url($inbox,PHP_URL_PATH)
          ."\nhost: ". parse_url($inbox,PHP_URL_HOST)
          ."\ndate: $date";

  // signs $data and puts the signature in $signature
  // returns a 500 error if the function fails for some reason
  if (openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256) === FALSE) {
    header('HTTP/1.1 500 Internal Server Error');
    die('unable to sign');
  }

  // prepare data and signature for use in the POST headers
  $signature =  base64_encode($signature);
  $keyid =      'keyId="'. $_SESSION['actor'] .'#main-key"';
  $algo =       'algorithm="rsa-sha256"';
  $headers =    'headers="(request-target) host date"';
  $signature =  "signature=\"$signature\"";

  // define the list of POST headers
  // Content-Type and Accept must conform to ActivityPub specs
  $h = array(
        'Content-Type: application/activity+json',
        'Accept: application/activity+json,application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
        "Content-Length: ".strlen($body),
        "Host: ". parse_url($inbox,PHP_URL_HOST),
        "Date: $date",
        "Signature: $keyid,$algo,$headers,$signature"
      );

  // set up the curl request session
  $ch = curl_init($inbox);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
  // the user agent includes StarShip app attribution and the version number
  include "../useragent";
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  
  // actually post the thing
  $result = curl_exec($ch);
  file_put_contents("../logs/outpost.log",$result,FILE_APPEND);
  curl_close($ch);
  
  return $result;
}

}?>