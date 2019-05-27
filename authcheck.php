<?php
if (!isset($_SESSION['actor']) || !isset($_SESSION['last_access']) || $_SESSION['authed'] === false ) {
  header('Location: https://'. $_SERVER['HTTP_HOST'] .'/login.php');
  exit();
}

else if (($_SERVER['REQUEST_TIME'] - $_SESSION['last_access']) > 3600) {
  $_SESSION['authed'] = false;
  header('Location: https://'. $_SERVER['HTTP_HOST'] .'/logout.php');
  exit();
}

$_SESSION['last_access'] = $_SERVER['REQUEST_TIME'];
?>