<?php
session_start();

// Define the path to the SQLite database
$dbPath = 'database.sqlite';

// Check if the database file exists
if (!file_exists($dbPath)) {
  // Redirect to the installation page if the database does not exist
  header("Location: /install/");
  exit();
}

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header("Location: /preview/home/");
  exit();
} else {
  header("Location: /home/");
  exit();
}
?>