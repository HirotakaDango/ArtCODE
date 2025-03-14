<?php
session_start();

$db = new PDO('sqlite:forum/database.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, password TEXT NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, user_id INTEGER NOT NULL, date DATETIME, category TEXT, FOREIGN KEY (user_id) REFERENCES users(id))");
$db->exec("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, comment TEXT, date DATETIME, post_id TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS category (id INTEGER PRIMARY KEY AUTOINCREMENT, category_name TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, link TEXT, image_cover TEXT, episode_name TEXT)");
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_GET['title']; ?></title>
    <?php include('bootstrap.php'); ?>
    <?php include('connection.php'); ?>
    <link rel="icon" type="image/png" href="<?php echo $web; ?>/icon/favicon.png">
    <meta property="og:url" content="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $_GET['title']; ?>">
    <meta property="og:image" content="<?php echo $web; ?>/icon/favicon.png">
    <link rel="stylesheet" href="transitions.css" />
    <script type="module" src="swup.js"></script>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }

      /* For Webkit-based browsers */
      ::-webkit-scrollbar {
        width: 0;
        height: 0;
        border-radius: 10px;
      }

      ::-webkit-scrollbar-track {
        border-radius: 0;
      }

      ::-webkit-scrollbar-thumb {
        border-radius: 0;
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
    <div id="displayHeader">
      <?php include('header.php'); ?>
    </div>
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
      <div class="position-fixed bottom-0 end-0 z-2 d-none d-md-block">
        <a class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold m-2" data-bs-toggle="offcanvas" href="#offcanvasMenu" role="button" aria-controls="offcanvasMenu">
          <i class="bi bi-list text-stroke"></i> Menu
        </a>
      </div>
      <div class="position-absolute start-0 end-0 z-2 d-none d-md-block">
        <button class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold m-2" id="toggleHeaderBtn">Hide Header</button>
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

              // Determine the previous and next image sources
              $prevRender = ($page > 1) ? $web . '/images/' . ($page == 2 ? $image_details['filename'] : $image_child[$page - 3]['filename']) : '';
              $nextRender = ($page < count($image_child) + 1) ? $web . '/images/' . $image_child[$page - 1]['filename'] : '';

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
                      echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                    }
                  
                    echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
                  
                    // Next page link
                    if ($currentPage < $totalPages) {
                      $nextPage = $currentPage + 1;
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                    } else {
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                    }
                  
                    echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $totalPages . '"><i class="bi bi-chevron-double-right text-stroke"></i></a></main>';
                  ?>
                </div>
              </div>
              <main id="swup" class="transition-main">
                <div class="position-relative d-flex justify-content-center w-100">
                  <?php
                    $totalPages = count($image_child) + 1;
                    $currentPage = $page;
                  
                    // Previous page link
                    if ($currentPage > 1) {
                      $prevPage = $currentPage - 1;
                      echo '<a class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $prevPage . '"></a>';
                    } else {
                      echo '<a class="position-absolute top-0 start-0 w-25 h-100 text-decoration-none" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"></a>';
                    }
                  ?>
                  <img class="d-none" src="<?= $prevRender; ?>" alt="<?= $image_details['title']; ?>">
                  <img class="mangaImage" id="mainMangaImage" src="<?= $imageSource; ?>" alt="<?= $image_details['title']; ?>">
                  <img class="d-none" src="<?= $nextRender; ?>" alt="<?= $image_details['title']; ?>">
                  <?php
                    $totalPages = count($image_child) + 1;
                    $currentPage = $page;
                  
                    // Next page link
                    if ($currentPage < $totalPages) {
                      $nextPage = $currentPage + 1;
                      echo '<a class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"></a>';
                    } else {
                      echo '<a class="position-absolute top-0 end-0 w-25 h-100 text-decoration-none" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"></a>';
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
                      echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                    }
                  
                    echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
                  
                    // Next page link
                    if ($currentPage < $totalPages) {
                      $nextPage = $currentPage + 1;
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                    } else {
                      echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
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
      <div class="position-fixed bottom-0 start-0 z-2 d-none d-md-block">
        <a class="btn bg-body-tertiary border-0 link-body-emphasis fw-bold m-2" href="#">
          <main id="swup" class="transition-main"><h6 class="pt-1 small"><?php echo $currentPage . ' / ' . $totalPages; ?></h6></main>
        </a>
      </div>
      <div class="btn-group container gap-3 mb-3 d-md-none">
        <a class="btn w-50 rounded bg-body-tertiary link-body-emphasis fw-bold" href="preview.php?title=<?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?>&id=<?php echo $image_id; ?>">Back to Preview</a>
        <a class="btn w-50 rounded bg-body-tertiary link-body-emphasis fw-bold" href="title.php?title=<?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?>&id=<?php echo $image_id; ?>">Back to Title</a>
      </div>
      <h2 class="text-center mt-md-5 mb-5 d-md-none"><a class="btn bg-body-tertiary link-body-emphasis fw-bold" href="<?php echo $web; ?>/download_images.php?artworkid=<?php echo $_GET['id']; ?>">Download Batch</a></h2>
    </div>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center vh-100">
          <div class="w-100">
            <h5 class="fw-bold mb-5 px-3 pb-3"><?= $image_details['title']; ?></h5>
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
                  echo '<main id="swup" class="transition-main me-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
                }
              
                echo '<main id="swup" class="transition-main"><h6 class="pt-1">' . $currentPage . ' / ' . $totalPages . '</h6></main>';
              
                // Next page link
                if ($currentPage < $totalPages) {
                  $nextPage = $currentPage + 1;
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="?title=' . urlencode($episode_name) . '&uid=' . $user_id . '&id=' . $image_id . '&page=' . $nextPage . '"><i class="bi bi-chevron-right text-stroke"></i></a></main>';
                } else {
                  echo '<main id="swup" class="transition-main ms-auto"><a class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium" href="title.php?title=' . urlencode($episode_name) . '&uid=' . $user_id . '"><i class="bi bi-reply-fill"></i></a></main>';
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
            <div class="container mt-3">
              <button type="button" class="btn bg-body-tertiary link-body-emphasis border-0 fw-medium d-flex justify-content-between align-items-center w-100 rounded-bottom-0" data-bs-toggle="modal" data-bs-target="#allEpisodesModal">
                <div class="text-start d-flex justify-content-center gap-1">
                  Chapters
                </div>
                <div class="text-end">
                  <i class="bi bi-chevron-down text-stroke"></i>
                </div>
              </button>
            </div>
            <div class="container mb-3">
              <div class="bg-body-tertiary p-2 text-white border-0 fw-medium text-start w-100 rounded-3 rounded-top-0" data-bs-toggle="modal" data-bs-target="#allEpisodesModal">
                <h6 class="p-1"><?= $image_details['title']; ?></h6>
              </div>
            </div>
            <div class="container my-3">
              <main id="swup" class="transition-main "><a class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" href="<?= $imageSource; ?>" download>Download Current Image</a></main>
            </div>
            <div class="container my-3">
              <a class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" href="<?php echo $web; ?>/download_images.php?artworkid=<?php echo $_GET['id']; ?>">Download Batch</a>
            </div>
            <div class="container my-3">
              <button type="button" class="btn w-100 bg-body-tertiary link-body-emphasis fw-bold" data-bs-dismiss="offcanvas">Close Menu</button>
            </div>
            <div class="btn-group container gap-3">
              <a class="btn w-50 rounded bg-body-tertiary link-body-emphasis fw-bold" href="preview.php?title=<?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?>&id=<?php echo $image_id; ?>">Back to Preview</a>
              <a class="btn w-50 rounded bg-body-tertiary link-body-emphasis fw-bold" href="title.php?title=<?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?>&id=<?php echo $image_id; ?>">Back to Title</a>
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
                <a class="w-100 btn btn-outline-light border-0 fw-bold p-3 text-start my-1 <?= isActive($i, $currentPage) ?>" href="?title=<?= urlencode($episode_name) ?>&uid=<?= $user_id ?>&id=<?= $image_id ?>&page=<?= $i ?>">
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
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        // Function to get the current URL parameters
        function getQueryParams() {
          const params = new URLSearchParams(window.location.search);
          return {
            title: params.get('title'),
            uid: params.get('uid'),
            id: params.get('id'),
            page: parseInt(params.get('page'), 10) || 1
          };
        }
    
        // Function to navigate to a new page
        function navigateToPage(newPage) {
          const params = getQueryParams();
          params.page = newPage;
          const newUrl = `?title=${encodeURIComponent(params.title)}&uid=${params.uid}&id=${params.id}&page=${params.page}`;
          window.location.href = newUrl;
        }
    
        // Event listener for keydown events
        document.addEventListener('keydown', (event) => {
          const { page } = getQueryParams();
          const totalPages = <?php echo json_encode(count($image_child) + 1); ?>;
    
          if (event.key === 'ArrowLeft') {
            // Navigate to previous page
            if (page > 1) {
              navigateToPage(page - 1);
            } else {
              window.location.href = `title.php?title=${encodeURIComponent(getQueryParams().title)}&uid=${getQueryParams().uid}`;
            }
          } else if (event.key === 'ArrowRight') {
            // Navigate to next page
            if (page < totalPages) {
              navigateToPage(page + 1);
            } else {
              window.location.href = `title.php?title=${encodeURIComponent(getQueryParams().title)}&uid=${getQueryParams().uid}`;
            }
          }
        });
      });

      document.addEventListener('DOMContentLoaded', () => {
        const header = document.getElementById('displayHeader');
        const toggleBtn = document.getElementById('toggleHeaderBtn');
    
        // Function to check if the device is in desktop mode
        function isDesktop() {
          return window.innerWidth >= 768; // Adjust the width as needed
        }
    
        // Function to update header visibility based on local storage and viewport
        function updateHeaderVisibility() {
          if (isDesktop()) {
            // Check local storage to set the initial state of the header for desktop
            if (localStorage.getItem('headerVisible') === 'false') {
              header.style.display = 'none';
              toggleBtn.textContent = 'Show Header';
            } else {
              header.style.display = 'block';
              toggleBtn.textContent = 'Hide Header';
            }
          } else {
            // Always show header on mobile
            header.style.display = 'block';
            toggleBtn.style.display = 'none'; // Hide the button on mobile
          }
        }
    
        // Initial update
        updateHeaderVisibility();
    
        // Event listener for button click
        toggleBtn.addEventListener('click', () => {
          if (header.style.display === 'none') {
            header.style.display = 'block';
            toggleBtn.textContent = 'Hide Header';
            localStorage.setItem('headerVisible', 'true');
          } else {
            header.style.display = 'none';
            toggleBtn.textContent = 'Show Header';
            localStorage.setItem('headerVisible', 'false');
          }
        });
    
        // Adjust visibility on window resize
        window.addEventListener('resize', updateHeaderVisibility);
      });
    </script>
  </body>
</html>