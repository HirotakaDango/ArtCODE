<?php
// Fetch music records with user information
$queryAlbumPop = "SELECT MIN(music.id) AS id, 
                    music.file, 
                    music.email, 
                    music.cover, 
                    music.album, 
                    music.title, 
                    users.id AS userid, 
                    users.artist,
                    COUNT(favorites_music.music_id) AS favorites_count
              FROM music 
              LEFT JOIN users ON music.email = users.email
              LEFT JOIN favorites_music ON music.id = favorites_music.music_id
              GROUP BY music.album
              ORDER BY favorites_count DESC
              LIMIT 30";
$stmtAlbumPop = $db->prepare($queryAlbumPop);
$resultAlbumPop = $stmtAlbumPop->execute();
?>

        <div class="d-md-none">
          <h5 class="mt-2 fw-bold">Top 30 Popular Albums</h5>
          <div id="carouselAlbumMobile" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <?php $count = 0; ?>
              <div class="carousel-item active">
                <div class="row row-cols-2 row-cols-sm-2 g-1">
                  <?php while ($row = $resultAlbumPop->fetchArray(SQLITE3_ASSOC)) : ?>
                    <?php if ($count % 2 == 0 && $count != 0) : ?>
                      </div>
                    </div>
                    <div class="carousel-item">
                      <div class="row row-cols-2 row-cols-sm-2 g-1">
                    <?php endif; ?>
                    <div class="col">
                      <div class="shadow-sm position-relative rounded-3 h-100">
                        <div class="card border-0 position-relative">
                          <a class="shadow position-relative btn p-0" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $row['album']; ?>&userid=<?php echo $row['userid']; ?>">
                            <img class="h-100 w-100 object-fit-cover rounded lazy-load" data-src="/feeds/music/covers/<?php echo $row['cover']; ?>">
                            <i class="bi bi-play-fill position-absolute start-50 top-50 translate-middle"></i>
                          </a>
                        </div>
                        <div class="p-2 position-absolute bottom-0 start-0">
                          <h5 class="card-text fw-bold text-shadow">
                            <?php echo (!is_null($row['album']) && strlen($row['album']) > 15) ? substr($row['album'], 0, 15) . '...' : $row['album']; ?>
                          </h5>
                          <p class="card-text small fw-bold text-shadow">
                            <small>by <a class="text-decoration-none text-white" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['userid']; ?>">
                              <?php echo (!is_null($row['artist']) && strlen($row['artist']) > 15) ? substr($row['artist'], 0, 15) . '...' : $row['artist']; ?>
                            </a></small>
                          </p>
                        </div>
                      </div>
                    </div>
                    <?php $count++; ?>
                  <?php endwhile; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-center align-items-center mt-2">
            <button class="btn border-0 link-body-emphasis me-auto" type="button" data-bs-target="#carouselAlbumMobile" data-bs-slide="prev">
              <i class="bi bi-arrow-left text-stroke fs-4"></i>
            </button>
            <button class="btn border-0 link-body-emphasis ms-auto" type="button" data-bs-target="#carouselAlbumMobile" data-bs-slide="next">
              <i class="bi bi-arrow-right text-stroke fs-4"></i>
            </button>
          </div>
        </div>