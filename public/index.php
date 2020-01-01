<?php session_start(); require_once "../authcheck.php"; ?>
<html>
<head>
<title>StarShip Testing</title>
</head>
<body>
<?php
if (isset($_GET['m'])) {
  if ($_GET['m'] === '1') echo "<p>Post successful!</p>";
  if ($_GET['m'] === '0') echo "<p>Post failed, check error logs.</p>";
}
if ($_SESSION['authed'] !== true) echo '<p>Nothing to see here yet, move along.</p>';

else include "ui/post.php";
?>
</body>
</html>
