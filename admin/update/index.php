<?php
// admin/update/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Update</title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../navbar.php'); ?>
          <div>
            <!-- Experimental Warning Section -->
            <div class="container mt-3">
              <div class="alert alert-warning">
                <h4 class="alert-heading">Experimental Feature</h4>
                <p>This feature is currently experimental and not recommended for general use. Please proceed with caution.</p>
                <p>Updating ArtCODE may cause problems on the server, such as deleting all previous files or folders. Ensure that you have backups and are aware of the risks.</p>
                <p>To update ArtCODE, you will need to manually check the GitHub repository for the latest changes and apply updates as needed.</p>
                <hr>
                <p class="mb-0">Thank you for your understanding.</p>
              </div>
            </div>
            
            <!-- Update Section -->
            <div class="container mt-2">
              <h1>Update ArtCODE</h1>
              <p>Click the button below to update ArtCODE from the GitHub repository.</p>
              <form id="updateForm">
                <button type="submit" class="btn btn-primary fw-medium w-100">Update Now</button>
              </form>
              <div id="message" class="text-center mt-2"></div>
              <div id="progressText" class="text-center mt-2 d-none">Start Updating: 0%</div>
              <iframe src="logs.php" class="border-0 rounded-4 w-100 mt-3" style="height: 85svh;"></iframe>
            </div>
            <div class="mt-5"></div>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.getElementById('updateForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Show the progress text when the update starts
        const progressText = document.getElementById('progressText');
        progressText.style.display = 'block';
        progressText.innerText = 'Start Updating: 0%';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
              document.getElementById('message').innerText = xhr.responseText;
            } else {
              document.getElementById('message').innerText = 'Error: ' + xhr.status;
            }

            // Hide the progress text after the update is complete
            progressText.style.display = 'none';
          }
        };

        xhr.upload.onprogress = function(event) {
          if (event.lengthComputable) {
            const percentComplete = (event.loaded / event.total) * 100;
            progressText.innerText = 'Updating: ' + Math.round(percentComplete) + '%';
          }
        };

        xhr.send('update=1');
      });
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>