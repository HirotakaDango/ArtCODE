<?php
$toUrl = urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/');

header("Location: session/?tourl=" . $toUrl);
exit();
?>