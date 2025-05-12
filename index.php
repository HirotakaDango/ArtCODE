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
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <?php include('bootstrapcss.php'); ?>
    <script>
      function getDisplayType() {
        return window.innerWidth <= 768 ? 'mobile' : 'desktop'; // Example condition
      }

      function getOption() {
        return localStorage.getItem('homepage_option') || 'simple'; // Default to 'simple'
      }

      function redirectToPage() {
        const displayType = getDisplayType();
        const option = getOption();
        const urlParams = new URLSearchParams(window.location.search);
        const urlOption = urlParams.get('option');
      
        // Update the 'option' parameter if not set
        if (urlOption !== option) {
          urlParams.set('option', option);
        }

        // Always update the 'display' parameter
        urlParams.set('display', displayType);
      
        let baseUrl = '<?php echo isset($_SESSION['email']) ? '/home/' : '/preview/home/'; ?>';

        // Construct the new URL
        const targetUrl = baseUrl + '?' + urlParams.toString();

        // Redirect to the target URL
        window.location.href = targetUrl;
      }

      // Execute redirect on page load
      document.addEventListener('DOMContentLoaded', redirectToPage);
    </script>
  </head>
  <body>
    <div class="d-flex justify-content-center align-items-center vh-100">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>