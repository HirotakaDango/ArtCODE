<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

header("Location: upload/");
exit();
?>
