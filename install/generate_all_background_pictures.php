<?php
// Check if the user is logged in
require_once('../auth.php');

// Connect to the SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Define the dimensions of the background image
$width = 2500;
$height = 1400;

// Function to generate a random color
function getRandomColor($image) {
  return imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
}

// Function to create a gradient image with random colors
function createGradientImage($width, $height) {
  // Create a blank image
  $image = imagecreatetruecolor($width, $height);

  // Generate two random colors for the gradient
  $color1 = getRandomColor($image);
  $color2 = getRandomColor($image);

  // Create a gradient background
  for ($y = 0; $y < $height; $y++) {
    $r1 = ($color1 >> 16) & 0xFF;
    $g1 = ($color1 >> 8) & 0xFF;
    $b1 = $color1 & 0xFF;

    $r2 = ($color2 >> 16) & 0xFF;
    $g2 = ($color2 >> 8) & 0xFF;
    $b2 = $color2 & 0xFF;

    $r = $r1 + ($r2 - $r1) * ($y / $height);
    $g = $g1 + ($g2 - $g1) * ($y / $height);
    $b = $b1 + ($b2 - $b1) * ($y / $height);

    $color = imagecolorallocate($image, $r, $g, $b);

    imageline($image, 0, $y, $width, $y, $color);
  }

  return $image;
}

// Prepare the query to get all users without a background picture
$stmt = $db->prepare('SELECT email FROM users WHERE bgpic IS NULL OR bgpic = ""');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
  $email = $user['email'];

  // Create a new gradient image
  $image = createGradientImage($width, $height);

  // Save the image
  $file_path = '../background_pictures/' . uniqid('bgpic_') . '.png';
  imagepng($image, $file_path);

  // Free up memory
  imagedestroy($image);

  // Update the user's background picture in the database
  $stmt = $db->prepare('UPDATE users SET bgpic = :bgpic WHERE email = :email');
  $stmt->bindParam(':bgpic', $file_path, PDO::PARAM_STR);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();
}

header('Location: /home/');
exit();

$db = null; // Close the PDO connection
?>