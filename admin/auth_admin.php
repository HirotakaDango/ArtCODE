<?php
// admin/auth_admin.php
session_start();

function isAdmin() {
  return isset($_SESSION['admin']) && $_SESSION['admin']['status'] === 'admin';
}

function isSuperAdmin() {
  return isset($_SESSION['admin']) && $_SESSION['admin']['status'] === 'superadmin';
}

function isLoggedIn() {
  return isset($_SESSION['admin']);
}

function requireAdmin() {
  if (!isLoggedIn()) {
    header('Location: /admin/authentication/');
    exit();
  }
  
  if (!isAdmin() && !isSuperAdmin()) {
    $_SESSION['error'] = 'Access Denied!';
    header('Location: /admin/authentication/error.php');
    exit();
  }
}
?>