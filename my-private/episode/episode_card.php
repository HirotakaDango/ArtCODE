          <div class="col">
            <div class="card border-0 bg-body-tertiary shadow h-100 rounded-4">
              <a class="text-decoration-none link-body-emphasis" href="/private_image.php?artworkid=<?php echo $image['id']; ?>">
                <div class="row g-0">
                  <div class="col-4">
                    <div class="ratio ratio-1x1 rounded-4">
                      <img class="object-fit-cover lazy-load h-100 w-100 rounded-start-4" data-src="/private_thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
                    </div>
                  </div>
                  <div class="col-8">
                    <div class="card-body d-flex align-items-center justify-content-start h-100">
                      <div>
                        <h6 class="card-title fw-bold"><?php echo (!is_null($image['title']) && mb_strlen($image['title'], 'UTF-8') > 20) ? mb_substr($image['title'], 0, 20, 'UTF-8') . '...' : $image['title']; ?></h6>
                        <h6 class="card-title fw-bold small"><?php echo $image['view_count']; ?> views</h6>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>