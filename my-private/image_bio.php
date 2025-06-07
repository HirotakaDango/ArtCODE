      <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-bottom-0">
              <h5 class="modal-title fw-bold fs-5" id="exampleModalLabel"><?php echo $user['artist']; ?> <small class="badge rounded-pill text-bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>"><i class="bi bi-globe-asia-australia"></i> <?php echo $user['region']; ?></small></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row featurette">
                <div class="col-5 order-1">
                  <a class="text-decoration-none d-flex justify-content-center text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold rounded-pill" href="/artist.php?by=newest&id=<?= $user['id'] ?>">
                    <?php if (!empty($user['pic'])): ?>
                      <img class="object-fit-cover border border-3 rounded-circle" src="<?php echo $user['pic']; ?>" style="width: 103px; height: 103px;">
                    <?php else: ?>
                      <img class="object-fit-cover border border-3 rounded-circle" src="icon/profile.svg" style="width: 103px; height: 103px;">
                    <?php endif; ?>
                  </a>
                </div>
                <div class="col-7 order-2">
                  <div class="btn-group w-100 mb-1 gap-1" role="group" aria-label="Basic example">
                    <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> w-50 rounded fw-bold" href="/follower.php?id=<?php echo $user['id']; ?>"><small>followers</small></a>
                    <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> w-50 rounded fw-bold" href="/following.php?id=<?php echo $user['id']; ?>"><small>following</small></a>
                  </div>
                  <div class="btn-group w-100 mb-1 gap-1" role="group" aria-label="Basic example">
                    <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> w-50 rounded fw-bold" href="/artist.php?by=newest&id=<?php echo $user['id']; ?>"><small>images</small></a>
                    <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> w-50 rounded fw-bold" href="/list_favorite.php?id=<?php echo $user['id']; ?>"><small>favorites</small></a> 
                  </div>
                  <a class="btn btn-sm btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> w-100 rounded fw-bold" href="/artist.php?by=newest&id=<?php echo $user['id']; ?>"><small>view profile</small></a>
                </div>
              </div>
              <div class="input-group my-1">
                <?php
                  $domain = $_SERVER['HTTP_HOST'];
                  $user_id_url = $user['id'];
                  $url = "http://$domain/artist.php?by=newest&id=$user_id_url";
                ?>
                <input type="text" id="urlInput" value="<?php echo $url; ?>" class="form-control border-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> border-2 fw-bold" readonly>
                <button class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold" onclick="copyToClipboard()">
                  <i class="bi bi-clipboard-fill"></i>
                </button>
                <button class="btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-3 rounded-start-0 fw-bold" onclick="shareArtist(<?php echo $user_id_url; ?>)">
                  <i class="bi bi-share-fill"></i> <small>share</small>
                </button>
              </div>
              <a class="btn btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> w-100 fw-bold mt-1" data-bs-toggle="collapse" href="#collapseBio" role="button" aria-expanded="false" aria-controls="collapseExample">
                <small>view description</small>
              </a>
              <div class="collapse mt-2" id="collapseBio">
                <div class="card fw-bold border-0">
                  <small>
                    <?php
                      $messageText = $user['desc'];
                      $messageTextWithoutTags = strip_tags($messageText);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = htmlspecialchars($matches[0]);
                        return '<a target="_blank" href="' . $url . '">' . $url . '</a>';
                      }, $messageTextWithoutTags);

                      $formattedTextWithLineBreaks = nl2br($formattedText);
                      echo $formattedTextWithLineBreaks;
                    ?>
                  </small>
                </div>
              </div> 
            </div>
          </div>
        </div>
      </div>