<?php
echo "<h3>You are logged in</h3>". $_SESSION['actor'];
?>
<div id="postform">
<!--
  This will be an ajax request eventually
-->
<form method="post" action="outbox.php">
  <input type="hidden" id="attributedTo" name="attributedTo" value="<?php echo $_SESSION['actor'];?>"><br/>
  <label for="to">To: </label><br/>
  <input type="text" id="to" name="to" required><br/>
  <label for="content">Content: </label><br/>
  <textarea name="content" id="content"></textarea><br/>
  <input type="submit" value="Send" />
</form>

<p>This will be a form to post new notes.</p>

</div>
<h3>Most recent post to inbox</h3>
<?php include "logs/recent.html"; ?>
