<?php
  session_start();
  session_destroy();
  header("Location: session.php");
  exit;
?>
