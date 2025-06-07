<?php
session_start(); // Ensure the session is started

// Connect to the database using PDO
$db = new PDO('sqlite:database.sqlite');

// Get the artworkid from the query string
$artworkid = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;

// Variable to hold the redirection URL
$redirectUrl = "";

// Fetch the current image information if the artwork ID is provided
$image = null;
if ($artworkid) {
  $stmt = $db->prepare("SELECT * FROM private_images WHERE id = :artworkid");
  $stmt->bindParam(':artworkid', $artworkid, PDO::PARAM_INT);
  $stmt->execute();
  $image = $stmt->fetch(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC to ensure proper data structure
}

// Determine thumbnail or fallback image
if ($image && !empty($image['filename'])) {
  $thumbnailPath = "private_thumbnails/" . $image['filename'];

  // Check if the thumbnail file exists, otherwise use the fallback image
  if (!file_exists($thumbnailPath)) {
    $thumbnailPath = "icon/bg.png";
  }
} else {
  // Fallback to default if no image data is found
  $thumbnailPath = "icon/bg.png";
}

// Construct the full image URL
$imageUrl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/" . $thumbnailPath;

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to preview page if not logged in
  $redirectUrl = "/preview/home/";
} else {
  // Retrieve the logged-in user's email
  $email = $_SESSION['email'];

  // Fetch the user's display name
  $stmt = $db->prepare("SELECT display FROM users WHERE email = :email");
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the user exists in the database
  if (!$user) {
    $redirectUrl = "error.php";
  } else {
    // Get the display name or set a default value if it's empty
    $display = empty($user['display']) ? 'view' : $user['display'];
    $redirectUrl = "/my-private/private_$display.php?artworkid=$artworkid";
  }
}

// If no redirect URL was determined, set a default fallback
if (!$redirectUrl) {
  $redirectUrl = "error.php";
}

// Detect protocol (http or https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$currentUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include('bootstrapcss.php'); ?>
    <title><?php echo $image['title'] ?? 'No Title'; ?></title>
    <meta name="description" content="<?php echo $image['imgdesc'] ?? 'No description.'; ?>">
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="<?php echo $currentUrl; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $image['title'] ?? 'No Title'; ?>">
    <meta property="og:description" content="<?php echo $image['imgdesc'] ?? 'No description.'; ?>">
    <meta property="og:image" content="<?php echo $imageUrl; ?>">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:domain" content="<?php echo $_SERVER['HTTP_HOST']; ?>">
    <meta property="twitter:url" content="<?php echo $currentUrl; ?>">
    <meta name="twitter:title" content="<?php echo $image['title'] ?? 'No Title'; ?>">
    <meta name="twitter:description" content="<?php echo $image['imgdesc'] ?? 'No description.'; ?>">
    <meta name="twitter:image" content="<?php echo $imageUrl; ?>">
  </head>
  <body class="bg-light text-dark d-flex flex-column justify-content-center align-items-center vh-100">
    <div class="text-center">
      <div class="spinner-border text-primary mb-3" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <h1 class="fs-3 text-secondary">Redirecting...</h1>
      <p class="text-muted">
        If you are not redirected, click 
        <a href="<?php echo $redirectUrl; ?>" class="text-decoration-none text-primary">here</a>.
      </p>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const isMobile = window.innerWidth <= 767;
        const mode = isMobile ? 'mobile' : 'desktop';

        // Append mode dynamically to the redirect URL
        const redirectUrl = new URL('<?php echo $redirectUrl; ?>', window.location.origin);
        redirectUrl.searchParams.set('mode', mode);

        setTimeout(function () {
          // Redirect without reloading the current page twice
          window.location.href = redirectUrl.toString();
        }, 3000); // Redirect after 3 seconds
      });
    </script>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>