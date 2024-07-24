<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <meta http-equiv="refresh" content="10;url=/easter-egg">
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var countdownElement = document.getElementById('countdown');
        var countdownTime = 10; // countdown time in seconds

        function updateCountdown() {
          if (countdownTime > 0) {
            countdownTime--;
            countdownElement.textContent = countdownTime + ' seconds';
          } else {
            countdownElement.textContent = 'Redirecting...';
          }
        }

        setInterval(updateCountdown, 1000);
      });
    </script>
  </head>
  <body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
          <div class="alert alert-danger error-message">
            <h1 class="display-3">403</h1>
            <h4 class="mt-3">Access Denied</h4>
            <p class="lead">You don't have permission to access this resource.</p>
            <p>Additionally, a 403 Forbidden error was encountered while trying to use an ErrorDocument to handle the request.</p>
            <p>You will be redirected to another page in <span id="countdown">10 seconds</span>. If you are not redirected, <a href="/easter-egg">click here</a>.</p>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>