<div id="carouselWrapperManga" class="mb-3 position-relative" style="height: 115vh; width: 100%;">
  <!-- Carousel Div -->
  <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
    <div class="container-fluid px-5">
      <div id="imageCarouselManga" class="carousel slide" data-bs-ride="carousel">
        <h5 class="fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>">Popular Manga</h5>
        <h6 class="text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-bold small">
          These manga are displayed based on their view counts from <?php $timeManga = isset($_GET['time']) ? $_GET['time'] : 'day'; echo $timeManga === 'alltime' ? 'all time' : "this $timeManga"; ?>. The more views a manga has, the higher its ranking in this list.
        </h6>
        <div class="carousel-inner">
          <?php
          $totalImagesManga = count($imagesManga);
          $slidesCountManga = ceil($totalImagesManga / 6);
          
          for ($iManga = 0; $iManga < $slidesCountManga; $iManga++) :
            $startIndexManga = $iManga * 6;
            $endIndexManga = min($startIndexManga + 6, $totalImagesManga);
          ?>
            <div class="carousel-item <?php echo $iManga === 0 ? 'active' : ''; ?>" data-bs-interval="false">
              <div class="position-relative">
                <div class="row row-cols-6 g-1">
                  <?php for ($jManga = $startIndexManga; $jManga < $endIndexManga; $jManga++) :
                    $imageUManga = $imagesManga[$jManga];
                    $image_idManga = $imageUManga['id'];
                    $image_urlManga = $imageUManga['filename'];
                    $image_titleManga = $imageUManga['title'];
                    $image_episodeManga = $imageUManga['episode_name'];
                    $artist_nameManga = $imageUManga['artist'];
                    $user_idManga = $imageUManga['user_id'];
                    $userPicManga = $imageUManga['pic'];
                    $viewsManga = $imageUManga['views']; // Get the views count
                    $current_image_idManga = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
                    
                    $artist_nameManga = substr($artist_nameManga, 0, 10);
                    $image_episodeManga = mb_substr($image_episodeManga, 0, 15, 'UTF-8');
                    
                    // Optionally, you can add an ellipsis if the string was truncated
                    if (mb_strlen($image_episodeManga, 'UTF-8') > 15) {
                      $image_episodeManga .= '...';
                    }
    
                    // Determine badge color
                    $badgeClassManga = '';
                    switch ($jManga) {
                      case 0:
                        $badgeClassManga = 'gold text-white shadow'; // Gold
                        break;
                      case 1:
                        $badgeClassManga = 'silver text-white shadow'; // Silver
                        break;
                      case 2:
                        $badgeClassManga = 'bronze text-white shadow'; // Bronze (you may need to define .bg-brown in your CSS)
                        break;
                      default:
                        $badgeClassManga = 'nothing text-white shadow'; // Default color for others
                    }
                  ?>
                    <div class="col position-relative">
                      <div class="position-relative">
                        <a href="/manga/title.php?title=<?= urlencode($image_episodeManga); ?>&uid=<?= $user_idManga; ?>">
                          <div class="ratio-cover">
                            <img class="object-fit-cover rounded rounded-bottom-0" src="/thumbnails/<?php echo $image_urlManga; ?>" alt="<?php echo $image_episodeManga; ?>" style="object-fit: cover;">
                          </div>
                        </a>
                        <!-- Badge for rank -->
                        <span class="position-absolute top-0 start-0 m-2 badge <?php echo $badgeClassManga; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);">No. <?php echo $jManga + 1; ?></span>
                        <!-- Badge for views count -->
                      </div>
                      <div class="d-flex align-items-center p-2 bg-light rounded-bottom mx-0 text-dark">
                        <img class="rounded-circle object-fit-cover border border-1" width="40" height="40" src="/<?php echo !empty($userPicManga) ? $userPicManga : 'icon/profile.svg'; ?>" alt="Profile Picture" style="margin-top: -2px;">
                        <div class="ms-2">
                          <div class="fw-bold text-truncate" style="max-width: 140px;"><?php echo $image_episodeManga; ?></div>
                          <a class="fw-medium text-decoration-none text-dark link-body-emphasis small text-truncate" style="max-width: 140px;" href="#" type="button" data-bs-toggle="modal" data-bs-target="#userModalBestManga-<?php echo $user_idManga; ?>"><?php echo $artist_nameManga; ?></a>
                        </div>
                      </div>
                    </div>
                    <div class="modal fade" id="userModalBestManga-<?php echo $user_idManga; ?>" tabindex="-1" aria-labelledby="userModalLabelBestManga" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-transparent border-0">
                          <div class="modal-body position-relative">
                            <a class="position-absolute top-0 end-0 m-4" href="/artist.php?id=<?php echo urlencode($user_idManga); ?>" target="_blank">
                              <i class="bi bi-box-arrow-up-right link-body-emphasis text-white" style="-webkit-text-stroke: 1px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                            </a>
                            <iframe src="/rows_columns/user_preview.php?id=<?php echo urlencode($user_idManga); ?>" class="rounded-4 p-0 shadow" width="100%" height="300" style="border: none;"></iframe>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endfor; ?>
                </div>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
    <!-- Custom Navigation Buttons -->
    <button class="carousel-control-prev position-absolute top-50 start-0 translate-middle-y ms-4" type="button" data-bs-target="#imageCarouselManga" data-bs-slide="prev" style="background: rgba(0, 0, 0, 0.5); border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
      <i class="bi bi-chevron-left text-white" style="font-size: 24px;"></i>
    </button>
    <button class="carousel-control-next position-absolute top-50 end-0 translate-middle-y me-4" type="button" data-bs-target="#imageCarouselManga" data-bs-slide="next" style="background: rgba(0, 0, 0, 0.5); border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
      <i class="bi bi-chevron-right text-white" style="font-size: 24px;"></i>
    </button>
  </div>
</div>