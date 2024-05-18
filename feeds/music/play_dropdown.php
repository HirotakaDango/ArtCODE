        <div class="col-md-6 order-md-2 d-flex justify-content-center align-items-center">
          <div class="d-md-none d-lg-none mt-4 w-100" id="playList">
            <h3 class="text-start fw-bold pt-3 mb-3" style="overflow-x: auto; white-space: nowrap;"><i class="bi bi-music-note-list"></i> all songs from <?php echo $row['artist']; ?></h3>
            <div class="overflow-y-auto" id="autoHeightDivM" style="max-height: 100%;">
              <?php foreach ($allRows as $song): ?>
                <?php
                  // Use getID3 to analyze the music file
                  $getID3 = new getID3();
                  $fileInfo = $getID3->analyze($song['file']);
                  getid3_lib::CopyTagsToComments($fileInfo);

                  // Extract information
                  $duration = !empty($fileInfo['playtime_string']) ? $fileInfo['playtime_string'] : 'Unknown';
                ?>
                <div id="songM_<?php echo $song['id']; ?>" class="link-body-emphasis d-flex justify-content-between align-items-center rounded-4 bg-dark-subtle bg-opacity-10 my-2 text-shadow <?php echo ($song['id'] == $row['id']) ? 'rounded-4 bg-body-tertiary border border-opacity-25 border-light' : ''; ?>">
                  <a class="link-body-emphasis text-decoration-none music text-start w-100 text-white btn fw-bold border-0" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>" style="overflow-x: auto; white-space: nowrap;">
                    <?php echo $song['title']; ?><br>
                    <small class="text-white"><?php echo $song['artist']; ?> - <?php echo $song['album']; ?></small><br>
                    <small class="text-white">Playtime : <?php echo $duration; ?></small>
                  </a>
                  <div class="dropdown dropdown-menu-end">
                    <button class="text-decoration-none text-white btn fw-bold border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu rounded-4">
                      <li><button class="dropdown-item fw-medium" onclick="sharePageS('<?php echo $song['id']; ?>', '<?php echo $song['title']; ?>')"><i class="bi bi-share-fill"></i> share</button></li>
                      <li><a class="dropdown-item fw-medium" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $song['userid']; ?>"><i class="bi bi-person-fill"></i> show artist</a></li>
                      <li><a class="dropdown-item fw-medium" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $song['album']; ?>&userid=<?php echo $song['userid']; ?>"><i class="bi bi-disc-fill"></i> show album</a></li>
                      <li><a class="dropdown-item fw-medium" href="<?php echo $song['file']; ?>" download><i class="bi bi-cloud-arrow-down-fill"></i> download</a></li>
                    </ul>
                  </div>
                </div>
              <?php endforeach; ?>
              <br><br><br><br><br><br><br><br><br><br>
            </div>
          </div>
          <div class="d-none d-md-block d-lg-block w-100 overflow-y-auto vh-100 py-2">
            <h3 class="text-start fw-bold pt-3 mb-3 text-shadow text-white" style="overflow-x: auto; white-space: nowrap;"><i class="bi bi-music-note-list"></i> all songs from <?php echo $row['artist']; ?></h3>
            <div class="overflow-y-auto" id="autoHeightDiv" style="max-height: 100%;">
              <?php foreach ($allRows as $song): ?>
                <?php
                  // Use getID3 to analyze the music file
                  $getID3 = new getID3();
                  $fileInfo = $getID3->analyze($song['file']);
                  getid3_lib::CopyTagsToComments($fileInfo);

                  // Extract information
                  $duration = !empty($fileInfo['playtime_string']) ? $fileInfo['playtime_string'] : 'Unknown';
                ?>
                <div id="song_<?php echo $song['id']; ?>" class="link-body-emphasis d-flex justify-content-between align-items-center rounded-4 bg-dark bg-opacity-10 my-2 text-shadow <?php echo ($song['id'] == $row['id']) ? 'rounded-4 bg-body-tertiary border border-opacity-25 border-light' : ''; ?>">
                  <a class="link-body-emphasis text-decoration-none music text-start w-100 text-white btn fw-bold border-0" href="?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&album=<?php echo urlencode($song['album']); ?>&id=<?php echo $song['id']; ?>" style="overflow-x: auto; white-space: nowrap;">
                    <?php echo $song['title']; ?><br>
                    <small class="text-white"><?php echo $song['artist']; ?> - <?php echo $song['album']; ?></small><br>
                    <small class="text-white">Playtime : <?php echo $duration; ?></small>
                  </a>
                  <div class="dropdown dropdown-menu-end">
                    <button class="text-decoration-none text-white btn fw-bold border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu rounded-4">
                      <li><button class="dropdown-item fw-medium" onclick="sharePageS('<?php echo $song['id']; ?>', '<?php echo $song['title']; ?>')"><i class="bi bi-share-fill"></i> share</button></li>
                      <li><a class="dropdown-item fw-medium" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $song['userid']; ?>"><i class="bi bi-person-fill"></i> show artist</a></li>
                      <li><a class="dropdown-item fw-medium" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $song['album']; ?>&userid=<?php echo $song['userid']; ?>"><i class="bi bi-disc-fill"></i> show album</a></li>
                      <li><a class="dropdown-item fw-medium" href="<?php echo $song['file']; ?>" download><i class="bi bi-cloud-arrow-down-fill"></i> download</a></li>
                    </ul>
                  </div>
                </div>
              <?php endforeach; ?>
              <br><br><br><br><br><br><br><br><br><br>
            </div>
          </div>
        </div>