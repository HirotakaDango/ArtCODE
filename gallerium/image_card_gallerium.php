    <div class="masonry-container px-2">
      <div id="masonry-grid" class="masonry-grid">
        <?php while ($image = $result->fetchArray()): ?>
          <div class="masonry-grid-item">
            <div class="position-relative">
              <a class="rounded" href="/image.php?artworkid=<?php echo $image['id']; ?>">
                <img class="rounded shadow w-100 lazy-load <?php echo ($image['type'] === 'nsfw') ? 'nsfw' : ''; ?>" src="../thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
              </a>
              <?php
              // Initialize $image_email with a default value
              $image_email = $image['email'];

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

              <div class="position-absolute bottom-0 start-0 m-2 d-flex align-items-center">
                <button class="fw-bold text-white btn border-0 link-body-emphasis p-0 d-flex align-items-center" type="button" data-bs-toggle="modal" data-bs-target="#userModal<?php echo urlencode($userId); ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;">
                  <img src="/<?php echo !empty($userPic) ? $userPic : 'icon/profile.svg'; ?>" alt="User Profile Picture" class="rounded-circle object-fit-cover border border-2 border-light shadow" style="width: 30px; height: 30px;">
                  <div class="ms-1">
                    <small class="text-white link-body-emphasis"><?php echo $userArtist; ?></small>
                  </div>
                </button>
              </div>
              <div class="modal fade" id="userModal<?php echo urlencode($userId); ?>" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content bg-transparent border-0">
                    <div class="modal-body position-relative">
                      <a class="position-absolute top-0 end-0 m-4" href="/artist.php?id=<?php echo urlencode($userId); ?>" target="_blank">
                        <i class="bi bi-box-arrow-up-right link-body-emphasis text-white" style="-webkit-text-stroke: 1px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                      </a>
                      <iframe src="/rows_columns/user_preview.php?id=<?php echo urlencode($userId); ?>" class="rounded-4 p-0 shadow" width="100%" height="300" style="border: none;"></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>