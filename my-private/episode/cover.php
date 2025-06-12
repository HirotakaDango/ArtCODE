      <div class="bg-body-tertiary rounded-4 p-md-5 p-3 w-100 mb-4 shadow position-relative">
        <div class="row g-5">
          <div class="col-md-4 d-flex justify-content-center align-items-center">
            <div class="ratio-cover">
              <a data-bs-toggle="modal" data-bs-target="#originalImage"><img class="w-100 h-100 object-fit-cover rounded-3 shadow" src="/private_images/<?php echo $firstEpisode; ?>"></a>
            </div>
          </div>
          <div class="col-md-8 d-flex justify-content-center align-items-center">
            <div>
              <h4 class="fw-bold text-center my-2"><?php echo $episodeName; ?></h4>
              <div class="d-flex justify-content-center align-items-center w-100 gap-2 mt-4 mb-2">
                <div class="ratio ratio-1x1 cover-padding">
                  <div class="bg-secondary bg-opacity-10 rounded-4 d-flex justify-content-center align-items-center">
                    <div class="fw-bold">
                      <div>
                        <div class="text-center fs-custom-1"><?php echo $totalCount; ?></div>
                        <span class="fs-custom-2">artworks</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ratio ratio-1x1 cover-padding">
                  <div class="bg-secondary bg-opacity-10 rounded-4 d-flex justify-content-center align-items-center">
                    <div class="fw-bold">
                      <div>
                        <div class="text-center fs-custom-1"><?php echo $totalViews; ?></div>
                        <span class="fs-custom-2">views</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ratio ratio-1x1 cover-padding">
                  <div class="bg-secondary bg-opacity-10 rounded-4 d-flex justify-content-center align-items-center">
                    <div class="fw-bold">
                      <div>
                        <div class="text-center fs-custom-1"><?php echo $totalFavorites; ?></div>
                        <span class="fs-custom-2">favorites</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <a class="btn border-0 bg-secondary bg-opacity-10 w-100 rounded-4 fw-bold p-4 link-body-emphasis fs-custom-2 mb-2" target="_blank" href="../manga/title.php?title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>">
                Go to manga
              </a>
              <?php if ($firstEpisode !== null): ?>
                <a class="btn border-0 bg-secondary bg-opacity-10 w-100 rounded-4 fw-bold p-4 link-body-emphasis fs-custom-2 mb-md-0 mb-2" href="../manga/view.php?title=<?php echo $episodeName; ?>&uid=<?php echo $userId; ?>&id=<?php echo $firstEpisodeId; ?>&page=1">
                  Read first chapter
                </a>
              <?php endif; ?>
            </div>
            <button class="btn border-0 fw-bold position-absolute end-0 top-0 m-3 link-body-emphasis" data-bs-toggle="modal" data-bs-target="#shareLink"><small><i class="bi bi-share-fill text-stroke"></i> Share</small></button>
          </div>
        </div>
      </div>
      <style>
        @media (max-width: 767px) {
          .cover-padding {
            padding: 2em;
          }

          .fs-custom-1 {
            font-size: 1em;
          }
          
          .fs-custom-2 {
            font-size: 0.8em;
          }
        }
      
        @media (min-width: 768px) {
          .cover-padding {
            padding: 4em;
          }
          
          .fs-custom-1 {
            font-size: 1.5em;
          }
          
          .fs-custom-2 {
            font-size: 1.3em;
          }
        }
      </style>