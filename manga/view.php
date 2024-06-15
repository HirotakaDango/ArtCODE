<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['title']; ?></title>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
    <link rel="stylesheet" href="transitions.css" />
    <script type="module" src="swup.js"></script>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      @media (max-width: 767px) {
        .vh-100-sm {
          height: 100vh;
        }

        .mangaImage {
          height: 100%;
          width: 100%;
          object-fit: contain;
        }
      }

      @media (min-width: 768px) {
        .mangaImage {
          height: 100vh;
        }
      }
    </style>
  </head>
  <body>
    <?php include('header.php'); ?>
    <div class="d-md-none btn-group w-100">
      <div class="btn-group w-100">
        <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-50 rounded-0" data-bs-toggle="modal" data-bs-target="#pageModal">
          <div class="text-start d-flex justify-content-center gap-1">
            Page <main id="swup" class="transition-main "><?php echo $_GET['page']; ?></main>
          </div>
          <div class="text-end">
            <i class="bi bi-chevron-down text-stroke"></i>
          </div>
        </button>
        <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-50 rounded-0" data-bs-toggle="modal" data-bs-target="#allEpisodesModal">
          <div class="text-start d-flex justify-content-center gap-1">
            Chapters
          </div>
          <div class="text-end">
            <i class="bi bi-chevron-down text-stroke"></i>
          </div>
        </button>
      </div>
    </div>
    <div class="">
      <div class="position-absolute top-0 end-0 z-2 pt-3 d-none d-md-block">
        <a class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold me-2 mt-5" data-bs-toggle="offcanvas" href="#offcanvasMenu" role="button" aria-controls="offcanvasMenu">
          <i class="bi bi-list text-stroke"></i> Menu
        </a>
      </div>
      <div class="d-flex justify-content-center align-items-center vh-100-sm">
        <div class="w-100">
          <?php
          // Check if title, uid, id, and page parameters are provided
          if (isset($_GET['title']) && isset($_GET['uid']) && isset($_GET['id']) && isset($_GET['page'])) {
            $episode_name = $_GET['title'];
            $user_id = $_GET['uid'];
            $image_id = $_GET['id'];
            $page = $_GET['page'];
            
            // Fetch JSON data from api_manga_view.php with title, uid, id, and page parameters
            $json = file_get_contents($web . '/api_manga_view.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $page);
            $data = json_decode($json, true);
    
            // Check if the data is an array and not empty
            if (is_array($data) && !empty($data)) {
              $image_details = $data['image_details'];
              $image_child = $data['image_child'];
              
              // Determine the image source based on the page number
              $imageSource = ($page == 1) ? $web . '/images/' . $image_details['filename'] : $web . '/images/' . $image_child[$page - 2]['filename'];
              ?>
              <div class="bg-body-tertiary py-1 d-md-none">
                <div class="d-flex justify-content-center align-items-center container">
                  <?php
                    $totalPages = count($image_child) + 1;
                    $currentPage = $page;
                  
                    echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=1"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
                  
                    // Previous page link
                    if ($currentPage > 1) {
                      $prevPage = $currentPage - 1;
                      echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $prevPage . '"><i class="bi bi-chevron-left text-stroke"></i></a></main>';
                    } else {
                      echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                    }
                  
                    echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
                  
                    // Next page link
                    if ($currentPage < $totalPages) {
                      $nextPage = $currentPage + 1;
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                    } else {
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                    }
                  
                    echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $totalPages . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
                  ?>
                </div>
              </div>
              <main id="swup" class="transition-main ">
                <div class="position-relative d-flex justify-content-center w-100">
                  <?php
                    $totalPages = count($image_child) + 1;
                    $currentPage = $page;
                  
                    // Previous page link
                    if ($currentPage > 1) {
                      $prevPage = $currentPage - 1;
                      echo '<a class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $prevPage . '"></a>';
                    } else {
                      echo '<a class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"></a>';
                    }
                  ?>
                  <img class="mangaImage" src="<?= $imageSource; ?>" alt="<?= $image_details['title']; ?>">
                  <?php
                    $totalPages = count($image_child) + 1;
                    $currentPage = $page;
                  
                    // Next page link
                    if ($currentPage < $totalPages) {
                      $nextPage = $currentPage + 1;
                      echo '<a class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"></a>';
                    } else {
                      echo '<a class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"></a>';
                    }
                  ?>
                </div>
              </main>
              <div class="bg-body-tertiary py-1 d-md-none">
                <div class="d-flex justify-content-center align-items-center container">
                  <?php
                    $totalPages = count($image_child) + 1;
                    $currentPage = $page;
                  
                    echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=1"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
                  
                    // Previous page link
                    if ($currentPage > 1) {
                      $prevPage = $currentPage - 1;
                      echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $prevPage . '"><i class="bi bi-chevron-left text-stroke"></i></a></main>';
                    } else {
                      echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                    }
                  
                    echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
                  
                    // Next page link
                    if ($currentPage < $totalPages) {
                      $nextPage = $currentPage + 1;
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                    } else {
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                    }
                  
                    echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $totalPages . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
                  ?>
                </div>
              </div>
            <?php } else { ?>
              <p class="position-absolute top-50 start-50">No data found.</p>
            <?php }
          } else { ?>
            <p>Missing title, uid, id, or page parameter.</p>
          <?php } ?>
        </div>
      </div>
      <h2 class="text-center mt-md-5 mb-5 d-md-none"><a class="btn bg-body-tertiary link-body-emphasis fw-bold" href="title.php?title=<?php echo $episode_name; ?>&uid=<?php echo $user_id; ?>">Back To Title</a></h2>
    </div>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center vh-100">
          <div class="w-100">
            <div class="d-flex justify-content-center align-items-center container my-3">
              <?php
                $totalPages = count($image_child) + 1;
                $currentPage = $page;
              
                echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=1"><i class="bi bi-chevron-double-left text-stroke"></i></a></main>';
              
                // Previous page link
                if ($currentPage > 1) {
                  $prevPage = $currentPage - 1;
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $prevPage . '"><i class="bi bi-chevron-left text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
              
                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
              
                // Next page link
                if ($currentPage < $totalPages) {
                  $nextPage = $currentPage + 1;
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . $episode_name . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
              
                echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $totalPages . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
              ?>
            </div>
            <div class="container my-3">
              <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-100" data-bs-toggle="modal" data-bs-target="#pageModal">
                <div class="text-start d-flex justify-content-center gap-1">
                  Page <main id="swup" class="transition-main "><?php echo $currentPage; ?></main>
                </div>
                <div class="text-end">
                  <i class="bi bi-chevron-down text-stroke"></i>
                </div>
              </button>
            </div>
            <div class="container my-3">
              <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-100" data-bs-toggle="modal" data-bs-target="#allEpisodesModal">
                <div class="text-start d-flex justify-content-center gap-1">
                  Chapters
                </div>
                <div class="text-end">
                  <i class="bi bi-chevron-down text-stroke"></i>
                </div>
              </button>
            </div>
            <div class="container my-3">
              <button type="button" class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" data-bs-dismiss="offcanvas">Close Menu</button>
            </div>
            <div class="container my-3">
              <a class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" href="title.php?title=<?php echo $episode_name; ?>&uid=<?php echo $user_id; ?>">Back To Title</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="pageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">All Pages</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php
              $totalPages = count($image_child) + 1;
              $currentPage = $page;
            
              // Function to determine if a button should be active
              function isActive($pageNumber, $currentPage) {
                return $pageNumber == $currentPage ? 'active' : '';
              }
            ?>
            <main id="swup" class="transition-main ">
              <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a class="w-100 btn btn-outline-light fw-bold p-3 text-start my-1 <?= isActive($i, $currentPage) ?>" href="?title=<?= urlencode($episode_name) ?>&uid=<?= $user_id ?>&id=<?= $image_id ?>&page=<?= $i ?>">
                  Page <?= $i ?>
                </a>
              <?php endfor; ?>
            </main>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="allEpisodesModal" tabindex="-1" aria-labelledby="allEpisodesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="allEpisodesModalLabel">All Episodes</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php
              // Check if the data is an array and not empty
              if (is_array($data) && !empty($data['all_episodes'])) {
                $all_episodes = $data['all_episodes'];
                ?>
                <main id="swup" class="transition-main">
                  <?php foreach ($all_episodes as $episode) : ?>
                    <a class="w-100 btn btn-outline-light fw-bold p-3 text-start my-1 <?php echo ($episode['id'] == $image_id) ? 'active' : ''; ?>" href="?title=<?= urlencode($episode_name) ?>&uid=<?= $user_id ?>&id=<?= $episode['id'] ?>&page=1">
                      <?= $episode['title'] ?>
                    </a>
                  <?php endforeach; ?>
                </main>
                <?php
              } else {
                echo '<p>No episodes found.</p>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>