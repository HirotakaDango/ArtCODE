<?php
// admin/update/logs.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];
?>


<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Update Logs</title>
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="w-100">
      <button id="clearLogs" class="btn btn-sm btn-primary position-fixed top-0 end-0 m-3 fw-medium z-2">Clear Logs</button>
      <button id="scrollToBottom" class="btn btn-sm btn-secondary position-fixed top-0 start-0 m-3 fw-medium z-2">Scroll to Bottom</button>
      <div id="logContainer">
        <p>Loading logs...</p>
      </div>
    </div>

    <script>
      const logContainer = document.getElementById('logContainer');
      const clearLogsButton = document.getElementById('clearLogs');
      const scrollToBottomButton = document.getElementById('scrollToBottom');

      function fetchLogs() {
        fetch('update_logs.php')
          .then(response => response.json())
          .then(data => {
            if (data.length === 0) {
              logContainer.innerHTML = '<p class="text-center fw-bold mt-5">No logs available.</p>';
            } else {
              logContainer.innerHTML = data.map(log => `
                <div class="card p-3 border-0 bg-body-tertiary rounded-0" style="font-family: monospace; white-space: pre;">
                  <div class="timestamp">${log.timestamp}</div>
                  <div class="message">${log.message}</div>
                </div>
              `).join('');
            }
          })
          .catch(error => {
            logContainer.innerHTML = '<p>Error loading logs.</p>';
            console.error('Error fetching logs:', error);
          });
      }

      function clearLogs() {
        fetch('update_logs.php?clear=true', { method: 'GET' })
          .then(response => response.json())
          .then(data => {
            if (data.message === 'Logs cleared') {
              fetchLogs(); // Refresh the log display
            } else {
              console.error('Error clearing logs');
            }
          })
          .catch(error => {
            console.error('Error clearing logs:', error);
          });
      }

      function scrollToBottom() {
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
      }

      clearLogsButton.addEventListener('click', clearLogs);
      scrollToBottomButton.addEventListener('click', scrollToBottom);

      // Fetch logs initially and then every 5 seconds
      fetchLogs();
      setInterval(fetchLogs, 5000);
    </script>
  </body>
</html>