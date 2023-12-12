<?php
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/icon/forbidden.php';
header("Location: $referer");
exit();
?>
