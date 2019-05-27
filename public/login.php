<?php
session_start();
if (!isset($_SESSION['authed'])) $_SESSION['authed'] = false;

if ($_SESSION['authed'] && ($_SERVER['REQUEST_TIME'] - $_SESSION['last_access']) < 3600) {
  header('Location: index.php');
  exit();
}
function login_attempt() {
  // this is all dummy test data; will be db accesses later
  $data = json_decode(file_get_contents("../dummyauth.json"),true);
  if ($_POST['user'] === $data['login'] && password_verify($_POST["pass"], $data['hash'])) {
    $_SESSION['authed'] = true;
    $_SESSION['actor'] = "https://". $_SERVER['HTTP_HOST'] . $data['actor'];
    $_SESSION['last_access'] = $_SERVER['REQUEST_TIME'];
    header('Location: index.php');
    exit();
  }
}

if (isset($_POST["user"])) {
  login_attempt();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login | StarShip Testing</title>
</head>

<body>
<div id="main_content">
<?php
if (isset($_POST['user']) || isset($_POST['pass'])) {
  echo 'Invalid login.<br/>';
}
?>
<h3>Log In</h3>
<form method="post" action="">
Username: <input type="text" id="user" name='user'><br/>
Password: <input type="password" id="pass" name='pass'><br/>
  <input type="submit" value="Submit">
</form>
</div>
</body>
</html>
