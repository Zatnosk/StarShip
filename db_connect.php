<?php

$config = parse_ini("./config/db.ini");
$sql = new mysqli("localhost", $config['db_account'], $config['db_pass'], $config['db_name']);

if ($sql->connect_errno) {
  return "Failed to connect to MySQL: (" . $sql->connect_errno . ") " . $sql->connect_error;
}
return true;
?>