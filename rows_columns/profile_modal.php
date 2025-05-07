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
} elseif (isset($imageW['email'])) {
  $image_email = $imageW['email'];
} elseif (isset($imageM['email'])) {
  $image_email = $imageM['email'];
} elseif (isset($imageY['email'])) {
  $image_email = $imageY['email'];
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

    // Limit artist name to 500 characters
    $userArtist = substr($userArtist, 0, 500);
  } else {
    echo "Error: User not found.";
  }
} else {
  // Handle the case where $image_email is empty
  echo "Error: No email provided.";
}

// Initialize $image_title with a default value
$image_title = '';

// Check if the $image variable is set and has a 'title' key
if (isset($image['title'])) {
  $image_title = $image['title'];
}

// Update $image_title based on other potential title sources
if (isset($imageA['title'])) {
  $image_title = $imageA['title'];
} elseif (isset($imageD['title'])) {
  $image_title = $imageD['title'];
} elseif (isset($imageL['title'])) {
  $image_title = $imageL['title'];
} elseif (isset($imageP['title'])) {
  $image_title = $imageP['title'];
} elseif (isset($imageV['title'])) {
  $image_title = $imageV['title'];
} elseif (isset($imageW['title'])) {
  $image_title = $imageW['title'];
} elseif (isset($imageM['title'])) {
  $image_title = $imageM['title'];
} elseif (isset($imageY['title'])) {
  $image_title = $imageY['title'];
}

// Ensure $image_title is not empty before proceeding with the database query
if (!empty($image_title)) {
  if ($db instanceof PDO) {
    // Query to get image title using PDO
    $stmt = $db->prepare("SELECT title FROM images WHERE title = :title");
    $stmt->bindParam(':title', $image_title, PDO::PARAM_STR);
    $stmt->execute();
    $imageRow = $stmt->fetch(PDO::FETCH_ASSOC);
  } elseif ($db instanceof SQLite3) {
    // Query to get image title using SQLite3
    $stmt = $db->prepare("SELECT title FROM images WHERE title = :title");
    $stmt->bindValue(':title', $image_title, SQLITE3_TEXT);
    $imageQuery = $stmt->execute();
    $imageRow = $imageQuery ? $imageQuery->fetchArray(SQLITE3_ASSOC) : null;
  }

  if ($imageRow) {
    $imageTitle = $imageRow['title'];
    // Limit image title to 500 characters
    $imageTitle = mb_substr($imageTitle, 0, 500);
  } else {
    echo "Error: Image not found.";
  }
} else {
  // Handle the case where $image_title is empty
  echo "Error: No title provided.";
}
?>
<div class="position-absolute bottom-0 start-0 mb-5 fw-bold text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">
  <div class="container">
    <h6 class="small text-truncate text-nowrap overflow-hidden" style="max-width: 200px;">
      <?php echo $imageTitle; ?>
    </h6>
  </div>
</div>
<div class="bg-body-secondary p-2 d-flex align-items-center justify-content-between rounded-bottom">
  <button class="fw-bold text-white btn border-0 link-body-emphasis p-0 d-flex align-items-center" type="button" data-bs-toggle="modal" data-bs-target="#userModal<?php echo urlencode($userId); ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;">
    <img src="/<?php echo !empty($userPic) ? $userPic : 'icon/profile.svg'; ?>" alt="User Profile Picture" class="rounded-circle object-fit-cover border border-2 border-light shadow" style="width: 30px; height: 30px;">
    <div class="ms-1">
      <h6 class="text-white link-body-emphasis text-truncate small my-auto ms-1" style="max-width: 100px;">
        <?php echo $userArtist; ?>
      </h6>
    </div>
  </button>
  <?php include('image_counts_prev.php'); ?>
</div>
<!-- Modal (note data-src instead of src) -->
<div class="modal fade" id="userModal<?php echo urlencode($userId); ?>"
     tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body position-relative">
        <a class="position-absolute top-0 end-0 m-4"
           href="/artist.php?id=<?php echo urlencode($userId); ?>"
           target="_blank">
          <i class="bi bi-box-arrow-up-right link-body-emphasis text-white"
             style="-webkit-text-stroke:1px; text-shadow:1px 1px 2px rgba(0,0,0,0.4),2px 2px 4px rgba(0,0,0,0.3),3px 3px 6px rgba(0,0,0,0.2);">
          </i>
        </a>
        <iframe
          data-src="/rows_columns/user_preview.php?id=<?php echo urlencode($userId); ?>"
          class="rounded-4 p-0 shadow"
          width="100%" height="300"
          style="border: none;"
        ></iframe>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation to catch any modal with id starting with "userModal"
    document.body.addEventListener('show.bs.modal', function(ev) {
      var modal = ev.target;
      if (!modal.id.startsWith('userModal')) return;

      var iframe = modal.querySelector('iframe[data-src]');
      if (iframe && !iframe.src) {
        iframe.src = iframe.getAttribute('data-src');
      }
    });

    // (Optional) unload iframe when closing, to free up resources
    document.body.addEventListener('hidden.bs.modal', function(ev) {
      var modal = ev.target;
      if (!modal.id.startsWith('userModal')) return;

      var iframe = modal.querySelector('iframe[data-src]');
      if (iframe) {
        iframe.removeAttribute('src');
      }
    });
  });
</script>