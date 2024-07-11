<?php
$_GET['id'] && $user_id = $_GET['id'];

header("Location: /follower/?id=$user_id");
exit();
?>