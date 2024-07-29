<?php
session_start();

if (!isset($_SESSION['email'])) {
  header("Location: /preview/home/");
  exit();
} else {
  header("Location: /home/");
  exit();
}
?>