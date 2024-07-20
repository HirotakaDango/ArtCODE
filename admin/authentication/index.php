<?php
// admin/authentication/index.php
session_start(); // Ensure session is started
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');

if (isLoggedIn()) {
  header('Location: /admin/analytic/');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('login-form').addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          const xhr = new XMLHttpRequest();
          xhr.open('POST', 'request.php', true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

          xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
              try {
                const response = JSON.parse(xhr.responseText);
                console.log(response); // Log response to debug
                if (response.success) {
                  window.location.href = '/admin/analytic/';
                } else {
                  document.getElementById('error-message').textContent = response.message;
                  document.getElementById('error-message').style.display = 'block';
                }
              } catch (e) {
                console.error('Failed to parse JSON response:', e);
                document.getElementById('error-message').textContent = 'An unexpected error occurred.';
                document.getElementById('error-message').style.display = 'block';
              }
            } else {
              console.error('AJAX Error: ' + xhr.status + ' - ' + xhr.statusText);
              document.getElementById('error-message').textContent = 'An unexpected error occurred.';
              document.getElementById('error-message').style.display = 'block';
            }
          };

          xhr.onerror = function() {
            console.error('AJAX request failed');
            document.getElementById('error-message').textContent = 'An unexpected error occurred.';
            document.getElementById('error-message').style.display = 'block';
          };

          xhr.send(new URLSearchParams(formData).toString());
        });
      });
    </script>
  </head>
  <body>
    <div class="container">
      <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card p-5 rounded-4 bg-dark-subtle border-0">
          <h1 class="text-center mb-4">Admin Login</h1>
          <p id="error-message" class="text-danger text-center" style="display: none;"></p>
          <form id="login-form">
            <div class="form-floating">
              <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required>
              <label for="email">Email address</label>
            </div>
            <div class="form-floating my-2">
              <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
              <label for="password">Password</label>
            </div>
            <button type="submit" class="btn btn-primary fw-medium w-100">Login</button>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>