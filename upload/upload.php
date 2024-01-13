<?php
require_once('../auth.php');

// Connect to the SQLite database
$db = new SQLite3('../database.sqlite');

// Create the "image_child" table if it doesn't exist
$db->exec("
  CREATE TABLE IF NOT EXISTS image_child (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL,
    image_id INTEGER NOT NULL,
    email TEXT NOT NULL,
    FOREIGN KEY (image_id) REFERENCES images (id)
  )
");

// Check if any images were uploaded
if (isset($_FILES['image'])) {

  ob_start(); // Start output buffering to prevent header errors

  $images = $_FILES['image'];

  // Generate a unique file name for the random image
  $ext = pathinfo($images['name'][0], PATHINFO_EXTENSION);
  $filename = uniqid() . '.' . $ext;

  // Save the random image
  move_uploaded_file($images['tmp_name'][0], '../images/' . $filename);

  // Determine the image type and generate the thumbnail
  $image_info = getimagesize('../images/' . $filename);
  $mime_type = $image_info['mime'];
  switch ($mime_type) {
    case 'image/jpeg':
      $source = imagecreatefromjpeg('../images/' . $filename);
      break;
    case 'image/png':
      $source = imagecreatefrompng('../images/' . $filename);
      break;
    case 'image/gif':
      $source = imagecreatefromgif('../images/' . $filename);
      break;
    case 'image/webp':
      $source = imagecreatefromwebp('../images/' . $filename);
      break;
    case 'image/avif':
      $source = imagecreatefromavif('../images/' . $filename);
      break;
    case 'image/bmp':
      $source = imagecreatefrombmp('../images/' . $filename);
      break;
    case 'image/wbmp':
      $source = imagecreatefromwbmp('../images/' . $filename);
      break; 
    default:
      echo "Error: Unsupported image format.";
      exit;
  }

  if ($source === false) {
    echo "Error: Failed to create image source.";
    exit;
  }

  $original_width = imagesx($source);
  $original_height = imagesy($source);
  $ratio = $original_width / $original_height;
  $thumbnail_width = 300;
  $thumbnail_height = intval(300 / $ratio); // Convert float to integer

  $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

  if ($thumbnail === false) {
    echo "Error: Failed to create thumbnail.";
    exit;
  }

  imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

  switch ($ext) {
    case 'jpg':
    case 'jpeg':
      imagejpeg($thumbnail, '../thumbnails/' . $filename);
      break;
    case 'png':
      imagepng($thumbnail, '../thumbnails/' . $filename);
      break;
    case 'gif':
      imagegif($thumbnail, '../thumbnails/' . $filename);
      break;
    case 'webp':
      imagewebp($thumbnail, '../thumbnails/' . $filename);
      break;
    case 'avif':
      imageavif($thumbnail, '../thumbnails/' . $filename);
      break;
    case 'bmp':
      imagebmp($thumbnail, '../thumbnails/' . $filename);
      break;
    case 'wbmp': 
      imagewbmp($thumbnail, '../thumbnails/' . $filename);
      break;
    default:
      echo "Error: Unsupported image format.";
      exit;
  }

  // Add the random image to the "images" table
  $email = $_SESSION['email'];
  $tags = filter_var($_POST['tags'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $tags = explode(",", $tags);
  $tags = array_map('trim', $tags); // Remove extra white space from each tag
  $tags = array_filter($tags); // Remove any empty tags
  $tags = array_values($tags); // Reset array indexes
  $tags = implode(",", $tags); // Join tags by comma
  $date = date('Y-m-d'); // Get the current date in YYYY-MM-DD format

  // Determine the episode_name based on whether it was selected or entered manually
  if (!empty($_POST['selected_episode_name'])) {
    // Use the selected episode_name from the dropdown
    $episodeName = filter_var($_POST['selected_episode_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  } else {
    // Use the manually entered episode_name
    $episodeName = filter_var($_POST['new_episode_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

    // Check if the episode_name already exists for the current user
    $checkStmt = $db->prepare('SELECT COUNT(*) FROM episode WHERE email = :email AND episode_name = :episode_name');
    $checkStmt->bindValue(':email', $email, SQLITE3_TEXT);
    $checkStmt->bindValue(':episode_name', $episodeName, SQLITE3_TEXT);
    $existingCount = $checkStmt->execute()->fetchArray()[0];

    if ($existingCount == 0 && !empty(trim($episodeName))) {
      // If episode_name doesn't exist, insert it into the "episode" table
      $insertStmt = $db->prepare('INSERT INTO episode (email, episode_name) VALUES (:email, :episode_name)');
      $insertStmt->bindValue(':email', $email, SQLITE3_TEXT);
      $insertStmt->bindValue(':episode_name', $episodeName, SQLITE3_TEXT);
      $insertStmt->execute();
    }
  }

  $stmt = $db->prepare("INSERT INTO images (email, filename, tags, title, imgdesc, link, date, type, episode_name) VALUES (:email, :filename, :tags, :title, :imgdesc, :link, :date, :type, :episode_name)");
  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':filename', $filename);
  $stmt->bindValue(':tags', $tags);
  $stmt->bindValue(':title', filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':imgdesc', nl2br(filter_var($_POST['imgdesc'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW)));
  $stmt->bindValue(':link', filter_var($_POST['link'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':date', $date);
  $stmt->bindValue(':type', filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW));
  $stmt->bindValue(':episode_name', $episodeName);
  $stmt->execute();

  // Retrieve the ID of the inserted image
  $image_id = $db->lastInsertRowID();

  // Loop through each uploaded image (except the first one)
  for ($i = 1; $i < count($images['name']); $i++) {
    $image = array(
      'name' => $images['name'][$i],
      'type' => $images['type'][$i],
      'tmp_name' => $images['tmp_name'][$i],
      'error' => $images['error'][$i],
      'size' => $images['size'][$i]
    );

    // Check if the image is valid
    if ($image['error'] == 0) {
      // Generate a unique file name
      $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
      $child_filename = uniqid() . '.' . $ext;

      // Save the child image
      move_uploaded_file($image['tmp_name'], '../images/' . $child_filename);

      // Add the child image to the "image_child" table, associating it with the random image's ID
      $stmt = $db->prepare("INSERT INTO image_child (filename, image_id, email) VALUES (:filename, :image_id, :email)");
      $stmt->bindValue(':filename', $child_filename);
      $stmt->bindValue(':image_id', $image_id);
      $stmt->bindValue(':email', $email);
      $stmt->execute();
    } else {
      echo "Error uploading image.";
    }
  }

  header("Location: index.php");
  exit;
}
?>
