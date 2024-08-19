<?php
// Check if the user is logged in
require_once('../auth.php');

// Get the user's artist name and current profile picture from the database
$db = new PDO('sqlite:../database.sqlite');
$email = $_SESSION['email'];
$stmt = $db->prepare('SELECT artist, pic FROM users WHERE email = :email');
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$current_pic = $row['pic'];
$artist = $row['artist'];

// Check if the user already has a profile picture
if (empty($current_pic)) {

  // Define the dimensions and font path
  $size = 400;
  $font_path = 'font/LEMONMILK-Bold.otf';

  // Extract the first letter from the artist name
  $first_letter = strtoupper(substr($artist, 0, 1));

  // Create a blank image
  $image = imagecreatetruecolor($size, $size);
  $background_color = imagecolorallocate($image, 255, 255, 255); // White background
  imagefill($image, 0, 0, $background_color);

  // Set the text color and size
  $text_color = imagecolorallocate($image, 0, 0, 0); // Black text
  $font_size = 150;

  // Calculate text box size
  $bbox = imagettfbbox($font_size, 0, $font_path, $first_letter);
  $text_width = $bbox[2] - $bbox[0];
  $text_height = $bbox[1] - $bbox[7];

  // Calculate coordinates to center the text
  $x = ($size - $text_width) / 2;
  $y = ($size + $text_height) / 2;

  // Add the text to the image
  imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_path, $first_letter);

  // Save the image
  $file_path = '../profile_pictures/' . uniqid('pic_') . '.png';
  imagepng($image, $file_path);

  // Free up memory
  imagedestroy($image);

  // Update the user's profile picture in the database
  $stmt = $db->prepare('UPDATE users SET pic = :pic WHERE email = :email');
  $stmt->bindParam(':pic', $file_path, PDO::PARAM_STR);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();
}

header('Location: generate_background.php');
exit();

$db = null; // Close the PDO connection
?>