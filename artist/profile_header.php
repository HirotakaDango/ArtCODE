    <!-- Profile Header Modal -->
    <div class="modal fade" id="modalUserInfo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down" style="max-width: 660px;">
        <div class="modal-content rounded-min-5">
          <div class="modal-body fw-medium">
            <div class="container-sm" style="max-width: 500px;">
              <button type="button" class="btn border-0 position-absolute top-0 end-0 m-2" style="-webkit-text-stroke: 3px;" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
              <div class="d-flex justify-content-center">
                <img class="img-thumbnail border-0 shadow rounded-circle mt-5" src="<?php echo !empty($pic) ? $pic : "../icon/profile.svg"; ?>" alt="Profile Picture" style="width: 120px; height: 120px;">
              </div>
              <h1 class="fw-bold text-center mt-2"><?php echo $artist; ?></h1>
              <div class="d-flex justify-content-center my-4">
                <form class="w-100 container-fluid" method="post">
                  <?php if ($is_following): ?>
                    <button class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> btn-lg rounded-pill fw-medium w-100" type="submit" name="unfollow"><i class="bi bi-person-dash-fill"></i> <small>unfollow</small></button>
                  <?php else: ?>
                    <button class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> btn-lg rounded-pill fw-medium w-100" type="submit" name="follow"><i class="bi bi-person-fill-add"></i> <small>follow</small></button>
                  <?php endif; ?>
                </form>
              </div>
              <div class="d-flex justify-content-center align-item-center">
                <div class="btn-group gap-2 mt-2" role="group" aria-label="Social Media Links">
                  <span>
                    <?php if (!empty($twitter)): ?>
                      <?php $twitterUrl = (strpos($twitter, 'https') !== false) ? $twitter : 'https://' . $twitter; ?>
                      <a href="<?php echo $twitterUrl; ?>" class="btn btn-lg fw-medium" role="button">
                        <img class="" width="32" height="32" src="../icon/twitter.svg"> <small>Twitter</small>
                      </a>
                    <?php endif; ?>
                  </span>
                  <span>
                    <?php if (!empty($pixiv)): ?>
                      <?php $pixivUrl = (strpos($pixiv, 'https') !== false) ? $pixiv : 'https://' . $pixiv; ?>
                      <a href="<?php echo $pixivUrl; ?>" class="btn btn-lg fw-medium" role="button">
                        <img class="" width="32" height="32" src="../icon/pixiv.svg"> <small>Pixiv</small>
                      </a>
                    <?php endif; ?>
                  </span>
                  <span>
                    <?php if (!empty($other)): ?>
                      <?php $otherUrl = (strpos($other, 'https') !== false) ? $other : 'https://' . $other; ?>
                      <a href="<?php echo $otherUrl; ?>" class="btn btn-lg fw-medium" role="button">
                        <img class="" width="32" height="32" src="../icon/globe-asia-australia.svg"> <small>Other</small>
                      </a>
                    <?php endif; ?>
                  </span>
                </div>
              </div>
              <p class="small" style="white-space: break-spaces; overflow: hidden;">
                <?php
                  if (!function_exists('getYouTubeVideoId')) {
                    function getYouTubeVideoId($urlComment)
                    {
                      $videoId = '';
                      $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                      if (preg_match($pattern, $urlComment, $matches)) {
                        $videoId = $matches[1];
                      }
                      return $videoId;
                    }
                  }

                  $commentText = isset($desc) ? $desc : '';

                  if (!empty($commentText)) {
                    $paragraphs = explode("\n", $commentText);

                    foreach ($paragraphs as $index => $paragraph) {
                      $messageTextWithoutTags = strip_tags($paragraph);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $urlComment = htmlspecialchars($matches[0]);

                        if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlComment)) {
                          return '<a href="' . $urlComment . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlComment . '" alt="Image"></a>';
                        } elseif (strpos($urlComment, 'youtube.com') !== false) {
                          $videoId = getYouTubeVideoId($urlComment);
                          if ($videoId) {
                            $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/default.jpg';
                            return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                          } else {
                            return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                          }
                        } else {
                          return '<a href="' . $urlComment . '">' . $urlComment . '</a>';
                        }
                      }, $messageTextWithoutTags);
                  
                      echo "<p class='small' style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                    }
                  } else {
                    echo "Sorry, no text...";
                  }
                ?>
              </p>
              <hr class="border w-100 border-4 rounded-pill my-5 border-secondary-subtle">
              <div class="small mb-5">
                <div class="mb-3 row">
                  <label for="userID" class="col-sm-4 col-form-label text-nowrap">User ID</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control-plaintext fw-bold" id="userID" value="<?php echo !empty($id) ? $id : ''; ?>" readonly>
                  </div>
                </div>
                <div class="mb-3 row">
                  <label for="artistName" class="col-sm-4 col-form-label text-nowrap">Artist Name</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control-plaintext fw-bold" id="artistName" value="<?php echo !empty($artist) ? $artist : ''; ?>" readonly>
                  </div>
                </div>
                <div class="mb-3 row">
                  <label for="userRegion" class="col-sm-4 col-form-label text-nowrap">Region</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control-plaintext fw-bold" id="userRegion" value="<?php echo !empty($region) ? $region : ''; ?>" readonly>
                  </div>
                </div>
                <div class="mb-3 row">
                  <label for="joinDate" class="col-sm-4 col-form-label text-nowrap">Joined</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control-plaintext fw-bold" id="joinDate" value="<?php echo !empty($joined) ? date('Y/m/d', strtotime($joined)) : ''; ?>" readonly>
                  </div>
                </div>
                <div class="mb-3 row">
                  <label for="birthDate" class="col-sm-4 col-form-label text-nowrap">Birthdate</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control-plaintext fw-bold" id="birthDate" value="<?php echo !empty($born) ? date('Y/m/d', strtotime($born)) : ''; ?>" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End of Profile Header Modal -->