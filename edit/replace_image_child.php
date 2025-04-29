<?php
require_once('../auth.php');

// Connect to SQLite database
$db = new SQLite3('../database.sqlite');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  header("Location: ../index.php");
  exit;
}

// Function to delete previous image files (main and thumbnail) based on relative path
function deletePreviousImages($filename) {
  if (empty($filename)) {
    return;
  }

  $baseDirImages = '../images/';
  $baseDirThumbnails = '../thumbnails/';

  $previousImage = $baseDirImages . $filename;
  $previousThumbnail = $baseDirThumbnails . $filename;

  if (file_exists($previousImage)) {
    @unlink($previousImage);
  }
  if (file_exists($previousThumbnail)) {
    @unlink($previousThumbnail);
  }
}

// Retrieve user ID from users table
$user_email = $_SESSION['email'];
$userStmt = $db->prepare('SELECT id FROM users WHERE email = :email');
$userStmt->bindValue(':email', $user_email);
$userResult = $userStmt->execute();
$user = $userResult->fetchArray(SQLITE3_ASSOC);
$users_id = $user['id'] ?? null;

if (!$users_id) {
  error_log("Error: User ID not found for email: " . $user_email);
  echo "Error: User session is invalid or user not found. Please log in again.";
  exit;
}

// Retrieve image details with title from images table
if (isset($_GET['id']) && isset($_GET['child_id'])) {
  $id = $_GET['id']; // Parent image ID (from images.id)
  $child_id = $_GET['child_id']; // Child image ID (from image_child.id)

  $email = $_SESSION['email'];
  $stmt = $db->prepare('
    SELECT ic.*, i.title, i.id as image_id_parent
    FROM image_child ic
    JOIN images i ON ic.image_id = i.id
    WHERE ic.id = :child_id AND ic.email = :email AND i.id = :parent_id
  ');
  $stmt->bindValue(':child_id', $child_id, SQLITE3_INTEGER);
  $stmt->bindValue(':email', $email, SQLITE3_TEXT);
  $stmt->bindValue(':parent_id', $id, SQLITE3_INTEGER);
  $result = $stmt->execute();
  $image = $result->fetchArray(SQLITE3_ASSOC);

  if (!$image) {
    error_log("Access denied or image not found for user: {$email}, child_id: {$child_id}, parent_id: {$id}");
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <title>Error</title>
          <link rel="icon" type="image/png" href="../icon/favicon.png">';
           include('bootstrapcss.php');
    echo '</head><body>';
           include('../header.php');
    echo '<div class="container mt-3"><div class="alert alert-danger">Error: Image not found or you do not have permission to edit it.</div></div>';
           include('bootstrapjs.php');
    echo '</body></html>';
    exit();
  }

  // Get current image details
  $current_image_path = null;
  $current_image_res = 'N/A';
  $current_image_size = 'N/A';
  if (!empty($image['filename'])) {
    $current_image_path = '../images/' . $image['filename'];
    if (file_exists($current_image_path)) {
      $size_bytes = @filesize($current_image_path);
      if ($size_bytes !== false) {
        if ($size_bytes >= 1048576) {
          $current_image_size = round($size_bytes / 1048576, 2) . ' MB';
        } elseif ($size_bytes >= 1024) {
          $current_image_size = round($size_bytes / 1024, 2) . ' KB';
        } else {
          $current_image_size = $size_bytes . ' bytes';
        }
      }

      $image_info = @getimagesize($current_image_path);
      if ($image_info !== false) {
        $current_image_res = $image_info[0] . 'x' . $image_info[1];
      }
    }
  }

} else {
  // Redirect if id or child_id is missing
  $redirect_url = 'all.php?';
  if (isset($_GET['id'])) $redirect_url .= 'id=' . urlencode($_GET['id']);
  if (isset($_GET['page'])) $redirect_url .= '&page=' . urlencode($_GET['page']);
  if (!isset($_GET['id'])) {
      header('Location: ../index.php');
  } else {
      header('Location: ' . $redirect_url);
  }
  exit();
}

// Handle image update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

    $previous_db_filename = $image['filename'];
    $uid = $users_id;
    $image_parent_id = $image['image_id_parent'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $originalFilename = basename($_FILES['image']['name']);

    $imageassets_folder_name_part = '';
    $filename_base_part = '';
    $index_part = '_i0';

    // Determine path components, reusing unique ID if possible from previous path
    if (!empty($previous_db_filename) && preg_match('/^uid_\d+\/data\/imageid-\d+\/(imageassets_[a-f0-9]+)\/(.+)$/', $previous_db_filename, $path_matches)) {
      $imageassets_folder_name_part = $path_matches[1];
      $previous_basename = $path_matches[2];
      $previous_filename_no_ext = pathinfo($previous_basename, PATHINFO_FILENAME);

      if (preg_match('/^(.*?)((_i\d+)?)$/', $previous_filename_no_ext, $filename_matches)) {
        $filename_base_part = $filename_matches[1];
        if (!empty($filename_matches[3])) {
          $index_part = $filename_matches[3];
        } else {
          $index_part = '_i0';
        }
        $expected_base = str_replace('imageassets_', '', $imageassets_folder_name_part);
        $filename_base_part = 'imageassets_' . $expected_base;

      } else {
         $filename_base_part = $imageassets_folder_name_part;
         $index_part = '_i0';
      }

    } else {
      // Generate new unique parts if previous path was old format or empty
      $new_uniqid = uniqid();
      $imageassets_folder_name_part = 'imageassets_' . $new_uniqid;
      $filename_base_part = 'imageassets_' . $new_uniqid;

      if (!empty($previous_db_filename)) {
        $old_basename = basename($previous_db_filename);
        $old_filename_no_ext = pathinfo($old_basename, PATHINFO_FILENAME);
        if (preg_match('/(_i\d+)$/', $old_filename_no_ext, $old_index_matches)) {
          $index_part = $old_index_matches[1];
        } else {
          $index_part = '_i0';
        }
      } else {
         $index_part = '_i0';
      }
    }

    // Construct final paths
    $relative_base_dir = 'uid_' . $uid . '/data/imageid-' . $image_parent_id . '/' . $imageassets_folder_name_part . '/';
    $filename = $filename_base_part . $index_part . '.' . $ext;

    $uploadDir = '../images/' . $relative_base_dir;
    $thumbnailDir = '../thumbnails/' . $relative_base_dir;
    $uploadFile = $uploadDir . $filename;
    $thumbnailFile = $thumbnailDir . $filename;
    $new_db_filename = $relative_base_dir . $filename; // Path to store in DB

    // Delete previous images before creating new directories
    if (!empty($previous_db_filename)) {
      deletePreviousImages($previous_db_filename);
    }

    // Create directories
    if (!is_dir($uploadDir)) {
      if (!@mkdir($uploadDir, 0755, true)) {
        error_log("Failed to create directory: " . $uploadDir);
        echo "Error: Could not create image storage directory.";
        exit;
      }
    }
    if (!is_dir($thumbnailDir)) {
       if (!@mkdir($thumbnailDir, 0755, true)) {
        error_log("Failed to create directory: " . $thumbnailDir);
        echo "Error: Could not create thumbnail storage directory.";
        exit;
       }
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
      // Generate thumbnail
      try {
        $image_info = getimagesize($uploadFile);
        if (!$image_info) {
          throw new Exception("Could not get image size. Invalid image?");
        }
        $mime_type = $image_info['mime'];
        $source = null;

        $create_funcs = [
          'image/jpeg' => 'imagecreatefromjpeg',
          'image/png'  => 'imagecreatefrompng',
          'image/gif'  => 'imagecreatefromgif',
          'image/webp' => 'imagecreatefromwebp',
          'image/avif' => function_exists('imagecreatefromavif') ? 'imagecreatefromavif' : null,
          'image/bmp'  => 'imagecreatefrombmp',
          'image/wbmp' => 'imagecreatefromwbmp',
          'image/x-ms-bmp' => 'imagecreatefrombmp',
        ];

        if (isset($create_funcs[$mime_type]) && $create_funcs[$mime_type]) {
          $func = $create_funcs[$mime_type];
          $source = @$func($uploadFile);
        } else {
          throw new Exception("Unsupported image format: " . $mime_type);
        }

        if ($source === false) {
          throw new Exception("Failed to create image source from file.");
        }

        $original_width = imagesx($source);
        $original_height = imagesy($source);
        if ($original_width <= 0 || $original_height <= 0) {
          imagedestroy($source);
          throw new Exception("Invalid image dimensions.");
        }

        $ratio = $original_width / $original_height;
        $thumbnail_width = 300; // Keep thumbnail width consistent
        $thumbnail_height = max(1, intval($thumbnail_width / $ratio));

        $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        if ($thumbnail === false) {
          imagedestroy($source);
          throw new Exception("Failed to create thumbnail canvas.");
        }

        // Handle transparency
        if (in_array($mime_type, ['image/png', 'image/gif', 'image/webp', 'image/avif'])) {
          imagealphablending($thumbnail, false);
          imagesavealpha($thumbnail, true);
          $transparent_index = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
          imagefill($thumbnail, 0, 0, $transparent_index);
        }

        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $original_width, $original_height);

         $save_funcs = [
           'jpg'  => 'imagejpeg',
           'jpeg' => 'imagejpeg',
           'png'  => 'imagepng',
           'gif'  => 'imagegif',
           'webp' => 'imagewebp',
           'avif' => function_exists('imageavif') ? 'imageavif' : null,
           'bmp'  => 'imagebmp',
           'wbmp' => 'imagewbmp',
         ];

         // Save thumbnail
         if (isset($save_funcs[$ext]) && $save_funcs[$ext]) {
           $save_func = $save_funcs[$ext];
           if (!$save_func($thumbnail, $thumbnailFile)) {
             throw new Exception("Failed to save thumbnail file.");
           }
         } else {
           imagedestroy($source);
           imagedestroy($thumbnail);
           throw new Exception("Cannot save thumbnail: Unsupported extension '$ext'.");
         }

        imagedestroy($source);
        imagedestroy($thumbnail);

      } catch (Exception $e) {
        error_log("Thumbnail generation error: " . $e->getMessage() . " for file " . $uploadFile);
        @unlink($uploadFile); // Clean up uploaded file if thumbnail fails
        echo "Error generating thumbnail: " . htmlspecialchars($e->getMessage());
        exit;
      }

      // Update database
      $stmt = $db->prepare('UPDATE image_child SET filename = :filename, original_filename = :original_filename WHERE id = :child_id');
      $stmt->bindValue(':filename', $new_db_filename, SQLITE3_TEXT);
      $stmt->bindValue(':original_filename', $originalFilename, SQLITE3_TEXT);
      $stmt->bindValue(':child_id', $child_id, SQLITE3_INTEGER);

      if ($stmt->execute()) {
        // Redirect on success
        $redirect_url = 'all.php?id=' . urlencode($id);
        if (isset($_GET['page'])) {
            $redirect_url .= '&page=' . urlencode($_GET['page']);
        }
        header('Location: ' . $redirect_url);
        exit();
      } else {
         // Handle database update error
         error_log("Database update failed for child_id {$child_id}: " . $db->lastErrorMsg());
         echo 'Error updating image record in database.';
         @unlink($uploadFile); // Clean up files if DB fails
         @unlink($thumbnailFile);
         exit;
      }

    } else {
      // Handle move_uploaded_file error
      $error_code = $_FILES['image']['error'];
      $php_errors = [
          UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
          UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
          UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
          UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
          UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
          UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
          UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
      ];
      $error_message = $php_errors[$error_code] ?? 'Unknown upload error';
      error_log("Error uploading file: " . $error_message . " (code: $error_code)");
      echo 'Error uploading file: ' . htmlspecialchars($error_message);
      exit;
    }
  } elseif (isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      // Handle other upload errors
      $error_code = $_FILES['image']['error'];
      $php_errors = [ /* ... same as above ... */ ];
      $error_message = $php_errors[$error_code] ?? 'Unknown upload error';
      error_log("File upload failed: " . $error_message . " (code: $error_code)");
      $upload_error_message = 'File upload failed: ' . htmlspecialchars($error_message);
      // Let the form display again below with the error message
  } else {
    // No file was uploaded (or other non-error scenario like initial form load)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $upload_error_message = 'No file uploaded. Please select an image to replace the current one.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Replace Image - <?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="container mt-3">
      <?php include('nav.php'); ?>
      <h4 class="my-3">Replace Image for "<?php echo htmlspecialchars($image['title']); ?>"</h4>

      <?php if (isset($upload_error_message)): ?>
        <div class="alert alert-danger"><?php echo $upload_error_message; ?></div>
      <?php endif; ?>

      <div class="row">
        <!-- Current Image Column -->
        <div class="col-md-6 pe-md-1 mb-3">
          <p class="text-muted small mb-1">Current Image:</p>
          <!-- Current Image Preview Container -->
          <div class="border border-3 rounded-4 mb-1 overflow-hidden">
            <?php if (!empty($image['filename']) && $current_image_path): ?>
              <?php $thumbnail_path = '../thumbnails/' . $image['filename']; ?>
              <a href="#" data-bs-toggle="modal" data-bs-target="#originalImageModal" title="View original image" class="d-block w-100 h-100">
                <img src="<?php echo htmlspecialchars($thumbnail_path); ?>?t=<?php echo time(); // Cache buster ?>" alt="Current image thumbnail" class="w-100 h-100 object-fit-cover">
              </a>
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-light text-secondary">
                <div class="text-center">
                  <h6><i class="bi bi-image fs-1"></i></h6>
                  <h6>No current image</h6>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <!-- Current Image Details -->
          <div class="small text-muted">
            Resolution: <?php echo htmlspecialchars($current_image_res); ?> | Size: <?php echo htmlspecialchars($current_image_size); ?>
          </div>
        </div>

        <!-- Upload New Image Column -->
        <div class="col-md-6 ps-md-1 mb-3">
          <p class="text-muted small mb-1">Upload New Image:</p>
          <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <input type="hidden" name="child_id" value="<?php echo htmlspecialchars($child_id); ?>">

            <!-- File Input -->
            <div class="mb-2">
              <input class="form-control border border-secondary-subtle border-3 rounded-4" type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif, image/webp, image/avif, image/bmp" required onchange="showPreview(event)">
              <div class="form-text">Select new image. Max size: <?php echo ini_get('upload_max_filesize'); ?>.</div>
              <div class="form-text">Supported formats: JPG, PNG, GIF, WEBP, AVIF, BMP.</div>
            </div>

            <!-- New Image Preview Area (Initially Hidden) -->
            <div id="new-image-preview-area" class="d-none">
              <p class="text-muted small mb-1">New Image Preview:</p>
              <!-- New Image Preview Container -->
              <div class="border border-3 rounded-4 mb-1 overflow-hidden">
                 <a href="#" id="newImagePreviewLink" data-bs-toggle="modal" data-bs-target="#newImagePreviewModal" title="View new image preview" class="d-block w-100 h-100">
                   <img id="newImagePreview" src="#" alt="New image preview" class="w-100 h-100 object-fit-cover d-none" />
                 </a>
                 <div id="new-empty-state" class="d-flex align-items-center justify-content-center w-100 h-100 bg-light text-secondary">
                    <div class="text-center">
                      <h6><i class="bi bi-image-fill fs-1"></i></h6>
                      <h6>New image preview</h6>
                    </div>
                 </div>
              </div>
              <!-- New Image Details -->
              <div class="small text-muted">
                Resolution: <span id="new-image-resolution">N/A</span> | Size: <span id="new-image-size">N/A</span>
              </div>
            </div>

            <!-- Submit and Cancel Buttons -->
            <button class="btn btn-primary fw-bold w-100 mt-3 border border-primary-subtle border-3 rounded-4" type="submit">
              <i class="bi bi-save me-2"></i>Replace Image
            </button>
             <a href="all.php?id=<?php echo urlencode($id); ?><?php echo isset($_GET['page']) ? '&page='.urlencode($_GET['page']) : ''; ?>" class="btn btn-secondary w-100 mt-2 border border-secondary-subtle border-3 rounded-4">
               <i class="bi bi-arrow-left me-2"></i>Cancel
             </a>
          </form>
        </div>
      </div>
    </div>
    <div class="mt-5"></div>

    <!-- Modal for Original Image -->
    <?php if (!empty($image['filename']) && $current_image_path): ?>
    <div class="modal fade" id="originalImageModal" tabindex="-1" aria-labelledby="originalImageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain w-100 h-100 rounded" src="<?php echo htmlspecialchars($current_image_path); ?>?t=<?php echo time(); ?>" alt="Original Image Full">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Modal for New Image Preview -->
    <div class="modal fade" id="newImagePreviewModal" tabindex="-1" aria-labelledby="newImagePreviewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img id="newImageModalPreviewFull" class="object-fit-contain w-100 h-100 rounded" src="#" alt="New Image Preview Full">
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
          </div>
        </div>
      </div>
    </div>
    <?php include('bootstrapjs.php'); ?>
    <script>
      // Function to format bytes into KB/MB
      function formatBytes(bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes' // Use +bytes to handle non-numeric or zero input

        const k = 1024
        const dm = decimals < 0 ? 0 : decimals
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']

        const i = Math.floor(Math.log(bytes) / Math.log(k))

        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
      }

      // Function to show preview and details of the selected image
      function showPreview(event) {
        const fileInput = event.target;
        const newPreviewArea = document.getElementById("new-image-preview-area");
        const newImgPreview = document.getElementById("newImagePreview"); // The <img> tag for small preview
        const newEmptyState = document.getElementById("new-empty-state"); // The placeholder div
        const newImgModalPreview = document.getElementById("newImageModalPreviewFull"); // The <img> tag inside the modal
        const newResolutionSpan = document.getElementById("new-image-resolution");
        const newSizeSpan = document.getElementById("new-image-size");
        const newPreviewLink = document.getElementById("newImagePreviewLink"); // The <a> tag wrapping the small preview

        if (fileInput.files && fileInput.files[0]) {
          const file = fileInput.files[0];
          const reader = new FileReader();

          reader.onload = function (e) {
            const dataUrl = e.target.result;

            // Update small preview image source and make it visible
            newImgPreview.src = dataUrl;
            newImgPreview.classList.remove('d-none'); // Show image
            newImgPreview.classList.add('d-block');

            // Hide the placeholder empty state
            newEmptyState.classList.add('d-none');
            newEmptyState.classList.remove('d-flex');

            // Show the entire preview area container
            newPreviewArea.classList.remove('d-none');

            // Update modal preview image source
            newImgModalPreview.src = dataUrl;

            // Enable the link to the modal
            newPreviewLink.removeAttribute('data-bs-toggle'); // Temporarily remove toggle to avoid issues? Maybe not needed.
            newPreviewLink.setAttribute('data-bs-toggle', 'modal');
            newPreviewLink.style.cursor = 'pointer'; // Indicate it's clickable


            // Get image dimensions using an Image object
            const img = new Image();
            img.onload = function() {
              newResolutionSpan.textContent = `${this.naturalWidth}x${this.naturalHeight}`;
            }
            img.onerror = function() {
               newResolutionSpan.textContent = 'N/A'; // Could not read dimensions
            }
            img.src = dataUrl;

            // Get and format image size
            newSizeSpan.textContent = formatBytes(file.size);
          }

          reader.readAsDataURL(file); // Read the file as Data URL

        } else {
          // No file selected or selection cancelled
          newImgPreview.src = '#'; // Clear src
          newImgPreview.classList.add('d-none'); // Hide image
          newImgPreview.classList.remove('d-block');

          // Show the placeholder empty state
          newEmptyState.classList.remove('d-none');
          newEmptyState.classList.add('d-flex');

          newImgModalPreview.src = '#'; // Clear modal source too
          newResolutionSpan.textContent = 'N/A';
          newSizeSpan.textContent = 'N/A';

          // Hide the entire preview area if no file is selected
          newPreviewArea.classList.add('d-none');

          // Disable link to modal (optional, could just show empty modal)
          newPreviewLink.removeAttribute('data-bs-toggle');
          newPreviewLink.style.cursor = 'default';
        }
      }

      // Initial setup on page load
      document.addEventListener('DOMContentLoaded', function() {
         // Make sure the link isn't clickable initially if no preview is shown
         const newPreviewLink = document.getElementById("newImagePreviewLink");
         const newImgPreview = document.getElementById("newImagePreview");
         if (newPreviewLink && newImgPreview && newImgPreview.classList.contains('d-none')) {
             newPreviewLink.removeAttribute('data-bs-toggle');
             newPreviewLink.style.cursor = 'default';
         }

         // Optional: Trigger preview if browser remembers file input on page load/back
         const fileInput = document.getElementById('image');
         if (fileInput && fileInput.files && fileInput.files.length > 0) {
            showPreview({ target: fileInput });
         }
      });
    </script>

  </body>
</html>