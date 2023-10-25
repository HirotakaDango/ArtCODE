            <div>
              <form action="../../profile/delete.php?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=<?php echo $page; ?>" method="post">
                <!-- Modal -->
                <div class="modal fade" id="deleteImage_<?php echo $imageD['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                            <button type="button" class="btn btn-sm rounded-circle btn-light shadow" data-bs-dismiss="modal"><i class="bi bi-x icon-stroke-1"></i></button>
                          </div>
                          <div class="row d-flex justify-content-center">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                              <div class="card border-0 rounded-4 overflow-auto scrollable-div" style="max-height: 250px;">
                                <a class="w-100 h-100" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/image.php?artworkid=<?php echo $imageD['id']; ?>">
                                  <img class="rounded-4 object-fit-cover shadow lazy-load" height="400" width="100%" data-src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/thumbnails/<?php echo $imageD['filename']; ?>" alt="<?php echo $imageD['title']; ?>">
                                </a>
                              </div>
                            </div>
                            <div class="col-sm-6">
                              <div class="h-100">
                                <div class="card-body">
                                  <h5 class="text-center fw-bold"><?php echo $imageD['title']?></h5>
                                  <p class="card-text fw-medium">
                                    <?php
                                      if (!empty($imageD['imgdesc'])) {
                                        $messageText = $imageD['imgdesc'];
                                        $messageTextWithoutTags = strip_tags($messageText);
                                        $pattern = '/\bhttps?:\/\/\S+/i';

                                        $formattedText = preg_replace_callback($pattern, function ($matches) {
                                          $url = htmlspecialchars($matches[0]);
                                          return '<a href="' . $url . '">' . $url . '</a>';
                                        }, $messageTextWithoutTags);

                                        $formattedTextWithLineBreaks = nl2br($formattedText);
                                        echo $formattedTextWithLineBreaks;
                                      } else {
                                        echo "Image description is empty.";
                                      }
                                    ?>
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                          <p class="my-2 fw-semibold">This action can't be undone! Make sure you download the image before you delete it.</p>
                          <div class="btn-group w-100">
                            <input type="hidden" name="id" value="<?php echo $imageD['id']; ?>">
                            <button class="btn btn-outline-danger rounded-start-4 fw-bold" type="submit" value="Delete">delete</button>
                            <button type="button" class="btn btn-outline-dark rounded-end-4 fw-bold" data-bs-dismiss="modal">cancel</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>