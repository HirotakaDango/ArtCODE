<?php
// Check if the user is logged in
require_once('../auth.php');

// Define the dimensions and font path
$size = 400;
$font_path = 'font/LEMONMILK-Bold.otf';

// Connect to the SQLite database
$db = new PDO('sqlite:../database.sqlite');

// Retrieve all users
$stmt = $db->query('SELECT email, artist, pic FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
  $email = $user['email'];
  $current_pic = $user['pic'];
  $artist = $user['artist'];

  // Check if the user already has a profile picture
  if (empty($current_pic)) {

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
    $update_stmt = $db->prepare('UPDATE users SET pic = :pic WHERE email = :email');
    $update_stmt->bindParam(':pic', $file_path, PDO::PARAM_STR);
    $update_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $update_stmt->execute();
  }
}

// Redirect to set_region.php
header('Location: /install/generate_all_background_pictures.php');
exit();

$db = null; // Close the PDO connection
?>