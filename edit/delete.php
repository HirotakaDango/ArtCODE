<?php
require_once('../auth.php');

// Check if the image ID is provided in the POST request
if (isset($_POST['id'])) {
  $id = $_POST['id'];

  // Retrieve the email of the logged-in user
  $email = $_SESSION['email'];

  // Connect to SQLite database
  $db = new SQLite3('../database.sqlite');

  // Start a transaction
  $db->exec('BEGIN');

  try {
    // Get the filename of the image to delete
    $stmt = $db->prepare("SELECT filename FROM images WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row) {
      $filename = $row['filename'];

      // Define the path to the images folder and thumbnails folder
      $imagesFolder = '../images/';
      $thumbnailsFolder = '../thumbnails/';

      // Delete records from the reply_comments table based on the comment ID (comment_id)
      $stmt = $db->prepare("
        DELETE FROM reply_comments 
        WHERE comment_id IN (
          SELECT id FROM comments WHERE imageid = :id
        )
      ");
      $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
      $stmt->execute();

      // Delete records from the comments table based on the image ID (image_id)
      $stmt = $db->prepare("DELETE FROM comments WHERE imageid = :id");
      $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
      $stmt->execute();

      // Delete corresponding records from the image_album table
      $stmt = $db->prepare("DELETE FROM image_album WHERE image_id = :id");
      $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
      $stmt->execute();

      // Delete corresponding records from the favorites table
      $stmt = $db->prepare("DELETE FROM favorites WHERE image_id = :id");
      $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
      $stmt->execute();

      // Get the IDs and filenames of child images associated with the deleted image from the "image_child" table
      $stmt = $db->prepare("SELECT id, filename FROM image_child WHERE image_id = :id");
      $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
      $child_image_ids = $stmt->execute();

      // Loop through each child image and delete it along with the records in the "image_child" table
      while ($child_image_id = $child_image_ids->fetchArray(SQLITE3_ASSOC)) {
        $child_id = $child_image_id['id'];
        $child_filename = $child_image_id['filename'];

        // Delete corresponding records from the "image_child" table
        $stmt = $db->prepare("DELETE FROM image_child WHERE id = :child_id");
        $stmt->bindValue(':child_id', $child_id, SQLITE3_INTEGER);
        $stmt->execute();

        // Delete the child image if it exists and is a file
        $child_image_path = $imagesFolder . $child_filename;
        if (file_exists($child_image_path) && is_file($child_image_path)) {
          unlink($child_image_path);
        }

        // Delete the child image's thumbnail if it exists and is a file
        $child_thumbnail_path = $thumbnailsFolder . $child_filename;
        if (file_exists($child_thumbnail_path) && is_file($child_thumbnail_path)) {
          unlink($child_thumbnail_path);
        }
      }

      // Delete the image file from the images folder
      $imagePath = $imagesFolder . $filename;
      if (file_exists($imagePath)) {
        unlink($imagePath);
      }

      // Delete the thumbnail file from the thumbnails folder
      $thumbnailPath = $thumbnailsFolder . $filename;
      if (file_exists($thumbnailPath)) {
        unlink($thumbnailPath);
      }

      // Delete the image from the images table
      $stmt = $db->prepare("DELETE FROM images WHERE id = :id");
      $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
      $stmt->execute();

      // Commit the transaction
      $db->exec('COMMIT');
    } else {
      throw new Exception('Image not found.');
    }
  } catch (Exception $e) {
    // Rollback the transaction if an error occurs
    $db->exec('ROLLBACK');
    // Log the exception message or handle it as needed
    error_log($e->getMessage());
  }

  // Redirect to a success page or wherever you'd like
  header('Location: ../profile.php');
  exit();
} else {
  // Redirect to an error page if image ID is not specified
  header('Location: ../profile.php');
  exit();
}
?>