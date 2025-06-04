          <div>
            <form action="myworks_delete.php?back=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" method="post">
              <!-- Modal -->
              <div class="modal fade" id="deleteImage_<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen modal-dialog-centered" role="document">
                  <div class="modal-content bg-transparent border-0">
                    <div class="modal-body d-flex justify-content-center align-items-center">
                      <div class="card container rounded-5 p-3 position-relative" style="max-width: 750px;">
                        <style>
                          .icon-stroke-1 { -webkit-text-stroke: 1px; }
                          .icon-stroke-2 { -webkit-text-stroke: 2px; }
                          .icon-stroke-3 { -webkit-text-stroke: 3px; }
                        </style>
                        <div class="position-absolute top-0 start-100 translate-middle">
                          <button type="button" class="btn btn-sm rounded-circle btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?> shadow" data-bs-dismiss="modal"><i class="bi bi-x icon-stroke-1"></i></button>
                        </div>
                        <div class="row d-flex justify-content-center">
                          <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="card border-0 rounded-4 overflow-auto scrollable-div" style="max-height: 250px;">
                              <a class="w-100 h-100" href="/private_image.php?artworkid=<?php echo $image['id']; ?>">
                                <div class="ratio ratio-1x1">
                                  <img class="rounded-4 object-fit-cover shadow lazy-load" data-src="/private_thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
                                </div>
                              </a>
                            </div>
                          </div>
                          <div class="col-sm-6">
                            <div class="h-100">
                              <div class="card-body">
                                <h5 class="text-center fw-bold"><?php echo $image['title']?></h5>
                              </div>
                            </div>
                          </div>
                        </div>
                        <p class="my-2 fw-semibold">This action can't be undone! Make sure you download the image before you delete it.</p>
                        <div class="btn-group w-100">
                          <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                          <button class="btn btn-outline-danger rounded-start-4 fw-bold" type="submit" value="Delete">delete</button>
                          <button type="button" class="btn btn-outline-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-end-4 fw-bold" data-bs-dismiss="modal">cancel</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>