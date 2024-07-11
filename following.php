<?php
$_GET['id'] && $user_id = $_GET['id'];

header("Location: /following/?id=$user_id");
exit();
?>