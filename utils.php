<?php
function dashedCamelCase($string, $capitalizeFirstCharacter = false) {
  $str = str_replace('-', '-', ucwords($string, '-'));
  if (!$capitalizeFirstCharacter) {
    $str = lcfirst($str);
  }
  return $str;
}
?>
