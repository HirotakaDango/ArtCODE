<?php
// Connect to the SQLite database
$db = new SQLite3('../../database.sqlite');

// Get the text ID from the query parameters
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the text content and associated user information from the database
$stmt = $db->prepare('
  SELECT texts.*, users.artist, users.id AS uid 
  FROM texts 
  LEFT JOIN users ON texts.email = users.email 
  WHERE texts.id = :id
');
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// Increment view count
if ($result) {
  $db->exec("UPDATE texts SET view_count = view_count + 1 WHERE id = $id");
} else {
  die('Text not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $result['title']; ?></title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../header_preview.php'); ?>
    <div class="container-fluid w-100">
      <div class="row g-0">
        <div class="col-md-3 d-none d-md-block overflow-auto border-0" style="height: calc(100svh - 52px); max-height: calc(100svh - 52px); min-height: calc(100svh - 52px); box-sizing: border-box;">
          <iframe src="/preview/text/side_view.php?uid=<?php echo $_GET['uid']; ?>" class="overflow-auto w-100" style="height: calc(100svh - 60px); max-height: calc(100svh - 60px); min-height: calc(100svh - 60px); box-sizing: border-box;"></iframe>
        </div>
        <div class="col-md-9 overflow-auto" style="height: calc(100svh - 52px); max-height: calc(100svh - 52px); min-height: calc(100svh - 52px); box-sizing: border-box;">
          <div class="container">
            <nav aria-label="breadcrumb">
              <div class="d-none d-md-block d-lg-block">
                <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
                  <li class="breadcrumb-item">
                    <a class="link-body-emphasis fw-medium text-decoration-none" href="/">
                      Home
                    </a>
                  </li>
                  <li class="breadcrumb-item">
                    <a class="link-body-emphasis fw-medium text-decoration-none" href="/text/">Text</a>
                  </li>
                  <li class="breadcrumb-item">
                    <a class="link-body-emphasis fw-medium text-decoration-none" href="/text/?uid=<?php echo $result['uid']; ?>"><?php echo $result['artist']; ?></a>
                  </li>
                  <li class="breadcrumb-item active fw-bold" aria-current="page">
                    <?php echo $result['title']; ?>
                  </li>
                </ol>
              </div>
              <div class="d-md-none d-lg-none">
                <a class="btn fw-bold w-100 text-start rounded p-3 bg-body-tertiary mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
                </a>
                <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
                  <div class="btn-group-vertical w-100">
                    <a class="btn py-2 rounded text-start fw-medium" href="/">Home</a>
                    <a class="btn py-2 rounded text-start fw-medium" href="/text/">Text</a>
                    <a class="btn py-2 rounded text-start fw-medium" href="/text/?uid=<?php echo $result['uid']; ?>"><?php echo $result['artist']; ?></a>
                    <a class="btn py-2 rounded text-start fw-bold" href="view.php?id=<?php echo $id; ?>"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> <?php echo $result['title']; ?></a>
                </div>
              </div>
            </nav>
          </div>
          <div class="container mt-2">
            <h1 class="fw-bold text-center display-5 mb-5" style="word-wrap: break-word; overflow-wrap: break-word;"><?php echo $result['title']; ?></h1>
            <div class="mb-2 row align-items-center">
              <label for="artist" class="col-4 col-form-label text-nowrap fw-medium">Artist</label>
              <div class="col-8">
                <a class="form-control-plaintext fw-bold text-decoration-none" id="artist" href="/preview/text/?uid=<?php echo $result['uid']; ?>"><?php echo $result['artist']; ?></a>
              </div>
            </div>
            <div class="mb-2 row align-items-center">
              <label for="uploaded" class="col-4 col-form-label text-nowrap fw-medium">Uploaded</label>
              <div class="col-8">
                <h6 class="form-control-plaintext fw-bold" id="uploaded"><?php echo $result['date']; ?></h6>
              </div>
            </div>
            <div class="mb-2 row align-items-center">
              <label for="views" class="col-4 col-form-label text-nowrap fw-medium">Views</label>
              <div class="col-8">
                <h6 class="form-control-plaintext fw-bold" id="views"><?php echo $result['view_count']; ?></h6>
              </div>
            </div>
            <?php
            if (!empty($result['tags'])) {
              $tags = explode(',', $result['tags']);
              foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                  ?>
                  <a href="/preview//text/?tag=<?php echo urlencode($tag); ?>" style="margin-bottom: 0.2em; margin-top: 0.2em;" class="btn btn-sm fw-medium btn-dark rounded-pill">
                    <?php echo $tag; ?>
                  </a>
                  <?php
                }
              }
            } else {
              echo "<p class='text-muted'>No tags available.</p>";
            }
            ?>
            <div class="d-flex justify-content-end mt-4">
              <button class="z-3 btn border-0" data-bs-toggle="modal" data-bs-target="#shareText"><i class="bi bi-share-fill"></i></button>
            </div>
            <hr class="mt-4 border-4 rounded-pill">
            <div class="mt-2">
              <?php
              $replyText = isset($result['content']) ? $result['content'] : '';

              if (!empty($replyText)) {
                $paragraphs = explode("\n", $replyText);

                foreach ($paragraphs as $index => $paragraph) {
                  $textWithoutTags = strip_tags($paragraph);
                  $pattern = '/\bhttps?:\/\/\S+/i';

                  $formattedText = preg_replace_callback($pattern, function ($matches) {
                    $url = $matches[0];
                    return '<a href="' . $url . '">' . $url . '</a>';
                  }, $textWithoutTags);

                  // Ensure normal paragraph spacing
                  echo "<p style=\"white-space: break-spaces; overflow: hidden; margin: 0; padding: 0;\">$formattedText</p>";
                }
              } else {
                echo "Sorry, no text...";
              }
              ?>
            </div>
          </div>
          <div class="mt-5"></div>
        </div>
      </div>
    </div>
    <?php include('share.php'); ?>
    <button class="z-3 btn border-0 rounded-pill fw-bold position-fixed bottom-0 end-0 m-2" id="scrollToTopBtn" onclick="scrollToTop()"><i class="bi bi-chevron-up" style="-webkit-text-stroke: 3px;"></i></button>
    <script>
      // Show or hide the button based on scroll position within col-md-9
      window.onscroll = function() {
        showScrollButton();
      };

      // Function to show or hide the button based on scroll position within col-md-9
      function showScrollButton() {
        var scrollButton = document.getElementById("scrollToTopBtn");
        var scrollableDiv = document.querySelector(".col-md-9"); // Get the target div
        // Show button if scroll position within .col-md-9 is greater than 20px
        if (scrollableDiv.scrollTop > 20) {
          scrollButton.style.display = "block";
        } else {
          scrollButton.style.display = "none";
        }
      }

      // Function to scroll to the top of .col-md-9
      function scrollToTop() {
        var scrollableDiv = document.querySelector(".col-md-9");
        scrollableDiv.scrollTo({
          top: 0,
          behavior: 'smooth' // Optional: to smoothly scroll to the top
        });
      }
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>