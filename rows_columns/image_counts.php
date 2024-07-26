<h6 class="position-absolute bottom-0 end-0 text-white small m-2 rounded-1" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"><i class="bi bi-images"></i> <?php echo $totalImagesCount; ?></h6>
<?php
// Initialize $image_email with a default value
$image_email = '';

// Check if the $image variable is set and has an 'email' key
if (isset($image['email'])) {
  $image_email = $image['email'];
}

// Update $image_email based on other potential email sources
if (isset($imageA['email'])) {
  $image_email = $imageA['email'];
} elseif (isset($imageD['email'])) {
  $image_email = $imageD['email'];
} elseif (isset($imageL['email'])) {
  $image_email = $imageL['email'];
} elseif (isset($imageP['email'])) {
  $image_email = $imageP['email'];
} elseif (isset($imageV['email'])) {
  $image_email = $imageV['email'];
}

// Ensure $image_email is not empty before proceeding with the database query
if (!empty($image_email)) {
  if ($db instanceof PDO) {
    // Query to get user profile picture and details using PDO
    $stmt = $db->prepare("SELECT pic, artist, id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $image_email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
  } elseif ($db instanceof SQLite3) {
    // Query to get user profile picture and details using SQLite3
    $stmt = $db->prepare("SELECT pic, artist, id FROM users WHERE email = :email");
    $stmt->bindValue(':email', $image_email, SQLITE3_TEXT);
    $userQuery = $stmt->execute();
    $user = $userQuery ? $userQuery->fetchArray(SQLITE3_ASSOC) : null;
  }

  if ($user) {
    $userPic = $user['pic'];
    $userArtist = $user['artist'];
    $userId = $user['id'];

    // Ensure the profile picture is not in the 'albums' folder
    $albumsPath = '/albums/';
    if (strpos($userPic, $albumsPath) === 0) {
      $userPic = 'icon/profile.svg'; // Default image if it's in the albums folder
    }

    // Limit artist name to 10 characters
    $userArtist = substr($userArtist, 0, 10);
  } else {
    echo "Error: User not found.";
  }
} else {
  // Handle the case where $image_email is empty
  echo "Error: No email provided.";
}
?>

<?php if ($userPic): ?>
  <div class="position-absolute bottom-0 start-0 m-2 d-flex align-items-center">
    <div class="dropdown d-flex align-items-center">
      <button class="fw-bold text-white btn border-0 link-body-emphasis p-0 d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;">
        <img src="/<?php echo !empty($userPic) ? $userPic : 'icon/profile.svg'; ?>" alt="User Profile Picture" class="rounded-circle object-fit-cover border border-2 border-light shadow" style="width: 32px; height: 32px;">
        <div class="ms-2">
          <?php echo $userArtist; ?>
        </div>
      </button>
      <ul class="dropdown-menu rounded-4 border-0 p-0 m-0 bg-transparent">
        <div class="position-relative">
          <a class="position-absolute top-0 end-0 m-2 text-white" href="/artist.php?id=<?php echo urlencode($userId); ?>" target="_blank"><i class="bi bi-box-arrow-up-right link-body-emphasis" style="-webkit-text-stroke: 1px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i></a>
          <iframe src="/rows_columns/user_preview.php?id=<?php echo urlencode($userId); ?>" class="rounded-4 p-0 shadow" width="300" height="250" style="border: none;"></iframe>
        </div>
      </ul>
    </div>
  </div>
<?php endif; ?>
