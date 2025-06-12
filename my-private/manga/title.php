<?php
// title.php
require_once('../../auth.php');
$db = new PDO('sqlite:../../database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$email = $_SESSION['email'];

// Update: Adjust private_favorites_manga table schema per new requirements (remove link, image_cover, add episode_name, uid)
$db->exec("CREATE TABLE IF NOT EXISTS private_favorites_manga (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL,
  episode_name TEXT,
  uid TEXT
)");

// Build the current URL for sharing links
$currentUrl = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Ensure required GET parameters are provided
if (!isset($_GET['title']) || !isset($_GET['uid'])) {
  echo "<p>Missing title or uid parameter.</p>";
  exit;
}

$episode_name = $_GET['title'];
$user_id = $_GET['uid'];

try {
  // Get the latest image for the specified episode and user
  $queryLatest = "
    SELECT
      private_images.*,
      users.id AS userid,
      users.artist
    FROM private_images
    JOIN users ON private_images.email = users.email
    WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND users.id = :user_id
    ORDER BY private_images.id DESC
    LIMIT 1
  ";
  $stmtLatest = $db->prepare($queryLatest);
  $stmtLatest->bindParam(':episode_name', $episode_name);
  $stmtLatest->bindParam(':user_id', $user_id);
  $stmtLatest->execute();
  $latest_cover = $stmtLatest->fetch(PDO::FETCH_ASSOC);

  // Get the first image for the specified episode and user
  $queryFirst = "
    SELECT
      private_images.*,
      users.id AS userid,
      users.artist
    FROM private_images
    JOIN users ON private_images.email = users.email
    WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND users.id = :user_id
    ORDER BY private_images.id ASC
    LIMIT 1
  ";
  $stmtFirst = $db->prepare($queryFirst);
  $stmtFirst->bindParam(':episode_name', $episode_name);
  $stmtFirst->bindParam(':user_id', $user_id);
  $stmtFirst->execute();
  $first_cover = $stmtFirst->fetch(PDO::FETCH_ASSOC);

  // Remove email field if present from cover private_images (security/privacy)
  if (isset($latest_cover['email'])) {
    unset($latest_cover['email']);
  }
  if (isset($first_cover['email'])) {
    unset($first_cover['email']);
  }

  // Get the total count of pages (from private_images and private_image_child) for this episode
  $queryCount = "
    SELECT COUNT(*) AS total_count
    FROM (
      SELECT id FROM private_images WHERE artwork_type = 'manga' AND episode_name = :episode_name
      UNION ALL
      SELECT private_image_child.id
      FROM private_image_child
      JOIN private_images ON private_image_child.image_id = private_images.id
      WHERE private_images.artwork_type = 'manga' AND private_images.episode_name = :episode_name
    ) AS all_pages
  ";
  $stmtCount = $db->prepare($queryCount);
  $stmtCount->bindParam(':episode_name', $episode_name);
  $stmtCount->execute();
  $total_count = $stmtCount->fetchColumn();

  // Get all private_images for the specified episode and user
  $queryImages = "
    SELECT
      private_images.*,
      users.id AS userid,
      users.artist
    FROM private_images
    JOIN users ON private_images.email = users.email
    WHERE artwork_type = 'manga'
      AND episode_name = :episode_name
      AND users.id = :user_id
    ORDER BY private_images.id DESC
  ";
  $stmtImages = $db->prepare($queryImages);
  $stmtImages->bindParam(':episode_name', $episode_name);
  $stmtImages->bindParam(':user_id', $user_id);
  $stmtImages->execute();
  $results = $stmtImages->fetchAll(PDO::FETCH_ASSOC);
  foreach ($results as &$result) {
    unset($result['email']);
  }

  $total_view_count = 0;
  foreach ($results as $image) {
    $total_view_count += $image['view_count'];
  }

  // --- TAGS LOGIC START ---
  $tags = [];
  foreach ($results as $image) {
    if (isset($image['tags']) && !is_null($image['tags'])) {
      $imageTags = explode(',', $image['tags']);
      foreach ($imageTags as $tag) {
        $tag = trim($tag);
        if (!empty($tag)) {
          $tags[$tag] = 0;
        }
      }
    }
  }
  if (!empty($tags)) {
    $queryTagCount = "
      SELECT COUNT(DISTINCT episode_name) AS count
      FROM private_images
      WHERE artwork_type = 'manga'
      AND (',' || tags || ',') LIKE :tag_pattern
    ";
    $stmtTagCount = $db->prepare($queryTagCount);
    foreach (array_keys($tags) as $tag) {
      $tagPattern = '%,' . $tag . ',%';
      $stmtTagCount->bindParam(':tag_pattern', $tagPattern);
      $stmtTagCount->execute();
      $count = $stmtTagCount->fetchColumn();
      $tags[$tag] = $count;
    }
  }
  // --- TAGS LOGIC END ---

  // Build parodies from the private_images
  $parodies = [];
  foreach ($results as $image) {
     if (isset($image['parodies']) && !is_null($image['parodies'])) {
      $imageParodies = explode(',', $image['parodies']);
      foreach ($imageParodies as $parody) {
        $parody = trim($parody);
        if (!empty($parody)) {
          $parodies[$parody] = 0;
        }
      }
    }
  }
  $queryParodies = "
    SELECT parodies, COUNT(*) AS count FROM (
      SELECT parodies, episode_name, MAX(id) AS latest_image_id
      FROM private_images
      WHERE artwork_type = 'manga'
      GROUP BY parodies, episode_name
    ) GROUP BY parodies
  ";
  $stmtParodies = $db->query($queryParodies);
  while ($row = $stmtParodies->fetch(PDO::FETCH_ASSOC)) {
    $parodyList = explode(',', $row['parodies']);
    foreach ($parodyList as $parody) {
      $parody = trim($parody);
      if (isset($parodies[$parody])) {
        $parodies[$parody] += $row['count'];
      }
    }
  }

  // Build characters from the private_images
  $characters = [];
  foreach ($results as $image) {
    if (isset($image['characters']) && !is_null($image['characters'])) {
      $imageCharacters = explode(',', $image['characters']);
      foreach ($imageCharacters as $character) {
        $character = trim($character);
        if (!empty($character)) {
          $characters[$character] = 0;
        }
      }
    }
  }
  $queryCharacters = "
    SELECT characters, COUNT(*) AS count FROM (
      SELECT characters, episode_name, MAX(id) AS latest_image_id
      FROM private_images
      WHERE artwork_type = 'manga'
      GROUP BY characters, episode_name
    ) GROUP BY characters
  ";
  $stmtCharacters = $db->query($queryCharacters);
  while ($row = $stmtCharacters->fetch(PDO::FETCH_ASSOC)) {
    $characterList = explode(',', $row['characters']);
    foreach ($characterList as $character) {
      $character = trim($character);
      if (isset($characters[$character])) {
        $characters[$character] += $row['count'];
      }
    }
  }

  // Get group counts based on current title for this user
  $queryGroupCounts = "
    SELECT private_images.`group`, COUNT(DISTINCT latest_images.episode_name) AS count
    FROM (
      SELECT DISTINCT episode_name, email
      FROM private_images
      WHERE artwork_type = 'manga'
        AND email = (SELECT email FROM users WHERE id = :user_id)
    ) AS latest_images
    JOIN private_images ON latest_images.episode_name = private_images.episode_name AND latest_images.email = private_images.email AND private_images.artwork_type = 'manga'
    JOIN users ON private_images.email = users.email
    WHERE users.id = :user_id
      AND private_images.`group` IS NOT NULL AND private_images.`group` <> ''
      AND private_images.`group` IN (
        SELECT DISTINCT private_images.`group`
        FROM private_images
        WHERE artwork_type = 'manga'
          AND episode_name = :episode_name
          AND email = (SELECT email FROM users WHERE id = :user_id)
      )
    GROUP BY private_images.`group`
  ";
  $stmtGroupCounts = $db->prepare($queryGroupCounts);
  $stmtGroupCounts->bindParam(':user_id', $user_id);
  $stmtGroupCounts->bindParam(':episode_name', $episode_name);
  $stmtGroupCounts->execute();
  $groupCounts = $stmtGroupCounts->fetchAll(PDO::FETCH_ASSOC);

  // Get categories count for current title
  $queryCategoriesCounts = "
    SELECT private_images.categories, COUNT(DISTINCT latest_images.episode_name) AS count
    FROM (
      SELECT DISTINCT episode_name, artwork_type
      FROM private_images
      WHERE artwork_type = 'manga'
    ) AS latest_images
    JOIN private_images ON latest_images.episode_name = private_images.episode_name AND latest_images.artwork_type = private_images.artwork_type
    WHERE private_images.artwork_type = 'manga'
      AND private_images.categories IS NOT NULL AND private_images.categories <> ''
      AND private_images.categories IN (
        SELECT DISTINCT private_images.categories
        FROM private_images
        WHERE artwork_type = 'manga'
          AND episode_name = :episode_name
      )
    GROUP BY private_images.categories
  ";
  $stmtCategoriesCounts = $db->prepare($queryCategoriesCounts);
  $stmtCategoriesCounts->bindParam(':episode_name', $episode_name);
  $stmtCategoriesCounts->execute();
  $categoriesCounts = $stmtCategoriesCounts->fetchAll(PDO::FETCH_ASSOC);

  // Get language counts for current title
  $queryLanguageCounts = "
    SELECT private_images.language, COUNT(DISTINCT latest_images.episode_name) AS count
    FROM (
      SELECT DISTINCT episode_name, artwork_type
      FROM private_images
      WHERE artwork_type = 'manga'
    ) AS latest_images
    JOIN private_images ON latest_images.episode_name = private_images.episode_name AND latest_images.artwork_type = private_images.artwork_type
    WHERE private_images.artwork_type = 'manga'
      AND private_images.language IS NOT NULL AND private_images.language <> ''
      AND private_images.language IN (
        SELECT DISTINCT private_images.language
        FROM private_images
        WHERE artwork_type = 'manga'
          AND episode_name = :episode_name
      )
    GROUP BY private_images.language
  ";
  $stmtLanguageCounts = $db->prepare($queryLanguageCounts);
  $stmtLanguageCounts->bindParam(':episode_name', $episode_name);
  $stmtLanguageCounts->execute();
  $languageCounts = $stmtLanguageCounts->fetchAll(PDO::FETCH_ASSOC);

  // Count how many latest private_images by the current artist (grouped per episode)
  $queryArtistCount = "
    SELECT COUNT(DISTINCT episode_name) AS count
    FROM private_images
    WHERE artwork_type = 'manga'
      AND email = (SELECT email FROM users WHERE id = :user_id)
  ";
  $stmtArtistCount = $db->prepare($queryArtistCount);
  $stmtArtistCount->bindParam(':user_id', $user_id);
  $stmtArtistCount->execute();
  $artistImageCount = $stmtArtistCount->fetchColumn();

} catch (PDOException $e) {
  echo "<p>Error: " . $e->getMessage() . "</p>";
  exit;
}

// Function to check if a title is already favorited by the user (by episode_name + uid)
function isFavorited($email, $episode_name, $uid, $db) {
  $stmt = $db->prepare("SELECT COUNT(*) as count FROM private_favorites_manga WHERE email = ? AND episode_name = ? AND uid = ?");
  $stmt->execute([$email, $episode_name, $uid]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result['count'] > 0;
}

// Favorite/unfavorite logic
if (isset($_POST['action']) && isset($_SESSION['email']) && isset($_GET['uid']) && isset($_GET['title'])) {
  $userEmail = $_SESSION['email'];
  $episodeName = $_GET['title'];
  $uid = $_GET['uid'];
  if ($_POST['action'] === 'favorite') {
    // Check if already favorited
    if (!isFavorited($userEmail, $episodeName, $uid, $db)) {
      $stmt = $db->prepare("INSERT INTO private_favorites_manga (email, episode_name, uid) VALUES (?, ?, ?)");
      $stmt->execute([$userEmail, $episodeName, $uid]);
    }
  } elseif ($_POST['action'] === 'unfavorite') {
    $stmt = $db->prepare("DELETE FROM private_favorites_manga WHERE email = ? AND episode_name = ? AND uid = ?");
    $stmt->execute([$userEmail, $episodeName, $uid]);
  }

  // Redirect to current URL after action to prevent resubmission on reload
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
  $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  header("Location: $currentUrl");
  exit();
}

// Fetch user's private_favorites_manga
if (isset($_SESSION['email'])) {
  $userEmail = $_SESSION['email'];
  $stmt = $db->prepare("SELECT * FROM private_favorites_manga WHERE email = ?");
  $stmt->execute([$userEmail]);
  $private_favorites_manga = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $private_favorites_manga = [];
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/mode.php'); ?>">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $episode_name; ?></title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
  </head>
  <body>
    <?php include('../../header.php'); ?>
    <?php include('./header_manga.php'); ?>
    <div class="container my-3">
      <div class="row">
        <div class="col-md-4">
          <div class="cover-image">
            <?php if (!empty($latest_cover)): ?>
            <a data-bs-toggle="modal" data-bs-target="#originalImage">
              <img class="rounded w-100 rounded-4" src="/private_thumbnails/<?php echo $latest_cover['filename']; ?>" alt="<?php echo $latest_cover['title']; ?>">
            </a>
            <?php else: ?>
            <div class="ratio ratio-1x1 bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-4 d-flex align-items-center justify-content-center">
              <span class="text-muted">No Cover Image</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-8">
          <h1 class="mb-4 fw-bold mt-4 mt-md-0"><?php echo $episode_name; ?></h1>
          <div class="mb-4">
            <p class="shadowed-text fw-medium" style="word-break: break-word;">
              <?php
                if (!empty($first_cover['imgdesc'])) {
                  $messageText = $first_cover['imgdesc'];
                  $messageTextWithoutTags = strip_tags($messageText);
                  $pattern = '/\bhttps?:\/\/\S+/i';

                  $formattedText = preg_replace_callback($pattern, function ($matches) {
                    $url = $matches[0];
                    return '<a href="' . $url . '">' . $url . '</a>';
                  }, $messageTextWithoutTags);

                  $charLimit = 400;

                  if (strlen($formattedText) > $charLimit) {
                    $limitedText = substr($formattedText, 0, $charLimit);
                    echo '<span id="limitedText">' . nl2br($limitedText) . '...</span>';
                    echo '<span id="more" style="display: none;">' . nl2br($formattedText) . '</span>';
                    echo '</br><button class="btn btn-sm mt-2 fw-medium p-0 border-0 text-white" onclick="myFunction()" id="myBtn"><small>read more</small></button>';
                  } else {
                    echo nl2br($formattedText);
                  }
                } else {
                  echo "User description is empty.";
                }
              ?>
              <script>
                function myFunction() {
                  var dots = document.getElementById("limitedText");
                  var moreText = document.getElementById("more");
                  var btnText = document.getElementById("myBtn");

                  if (moreText.style.display === "none") {
                    dots.style.display = "none";
                    moreText.style.display = "inline";
                    btnText.innerHTML = "read less";
                  } else {
                    dots.style.display = "inline";
                    moreText.style.display = "none";
                    btnText.innerHTML = "read more";
                  }
                }
              </script>
            </p>
          </div>
          <div class="mb-3">
            <div class="input-group">
              <input type="text" id="urlInput2" value="<?php echo $currentUrl; ?>" class="form-control border-2 fw-bold" readonly style="display: none;">
              <button class="btn btn-sm bg-transparent border-0 rounded fw-bold p-0 link-body-emphasis text-muted text-start" onclick="copyUrlToClipboard()">
                <small style="white-space: normal; word-break: break-word;"><?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?> <i class="bi bi-copy"></i></small>
              </button>
            </div>
            <script>
              function copyUrlToClipboard() {
                var urlInput2 = document.getElementById('urlInput2');
                urlInput2.select();
                urlInput2.setSelectionRange(0, 99999);
                document.execCommand('copy');
              }
            </script>
          </div>
          <?php if (!empty($latest_cover)): ?>
            <div class="mb-2 row align-items-center">
              <label class="col-3 col-form-label text-nowrap fw-medium">Artist</label>
              <div class="col-9">
                <div class="btn-group">
                  <a href="./?artist=<?php echo urlencode($latest_cover['artist']); ?>&uid=<?php echo $user_id; ?>" class="btn btn-sm bg-secondary-subtle fw-bold"><?php echo $latest_cover['artist']; ?></a>
                  <a href="#" class="btn btn-sm bg-body-tertiary fw-bold" disabled><?php echo $artistImageCount; ?></a>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <?php
          $groupName = '';
          $groupCount = 0;
          if (!empty($groupCounts)) {
            $firstGroup = reset($groupCounts);
            $groupName = $firstGroup['group'];
            $groupCount = $firstGroup['count'];
          }
          if (!empty($groupCounts)):
          ?>
            <div class="mb-2 row align-items-center">
              <label class="col-3 col-form-label text-nowrap fw-medium">Group</label>
              <div class="col-9">
                <div class="btn-group">
                  <a href="./?group=<?php echo urlencode($groupName); ?>" class="btn btn-sm bg-secondary-subtle fw-bold"><?php echo $groupName; ?></a>
                  <a href="#" class="btn btn-sm bg-body-tertiary fw-bold" disabled><?php echo $groupCount; ?></a>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($parodies)): ?>
            <div class="mb-2 row align-items-center">
              <label class="col-3 col-form-label text-nowrap fw-medium">Parodies</label>
              <div class="col-9 p-2">
                <?php foreach ($parodies as $parody => $count): ?>
                <div class="btn-group m-1">
                  <a href="./?parody=<?php echo urlencode($parody); ?>" class="btn btn-sm bg-secondary-subtle fw-bold"><?php echo $parody; ?></a>
                  <a href="#" class="btn btn-sm bg-body-tertiary fw-bold"><?php echo $count; ?></a>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($characters)): ?>
            <div class="mb-2 row align-items-center">
              <label class="col-3 col-form-label text-nowrap fw-medium">Characters</label>
              <div class="col-9 p-2">
                <?php foreach ($characters as $character => $count): ?>
                <div class="btn-group m-1">
                  <a href="./?character=<?php echo urlencode($character); ?>" class="btn btn-sm bg-secondary-subtle fw-bold"><?php echo $character; ?></a>
                  <a href="#" class="btn btn-sm bg-body-tertiary fw-bold"><?php echo $count; ?></a>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($tags)): ?>
            <div class="mb-2 row align-items-center">
              <label class="col-3 col-form-label text-nowrap fw-medium">Tags</label>
              <div class="col-9 p-2">
                <?php foreach ($tags as $tag => $count): ?>
                <div class="btn-group m-1">
                  <a href="./?tag=<?php echo urlencode($tag); ?>" class="btn btn-sm bg-secondary-subtle fw-bold"><?php echo $tag; ?></a>
                  <a href="#" class="btn btn-sm bg-body-tertiary fw-bold"><?php echo $count; ?></a>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <?php
          $categoriesName = '';
          $categoriesCount = 0;
          if (!empty($categoriesCounts)) {
            $firstCategory = reset($categoriesCounts);
            $categoriesName = $firstCategory['categories'];
            $categoriesCount = $firstCategory['count'];
          }
          ?>
          <?php if (!empty($categoriesCount)): ?>
            <div class="mb-2 row align-items-center">
              <label class="col-3 col-form-label text-nowrap fw-medium">Category</label>
              <div class="col-9">
                <div class="btn-group">
                  <a href="./?categories=<?php echo urlencode($categoriesName); ?>" class="btn btn-sm bg-secondary-subtle fw-bold"><?php echo $categoriesName; ?></a>
                  <a href="#" class="btn btn-sm bg-body-tertiary fw-bold" disabled><?php echo $categoriesCount; ?></a>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <?php
          $languageName = '';
          $languageCount = 0;
          if (!empty($languageCounts)) {
            $firstLanguage = reset($languageCounts);
            $languageName = $firstLanguage['language'];
            $languageCount = $firstLanguage['count'];
          }
          ?>
          <?php if (!empty($languageCount)): ?>
            <div class="mb-2 row align-items-center">
              <label class="col-3 col-form-label text-nowrap fw-medium">Language</label>
              <div class="col-9">
                <div class="btn-group">
                  <a href="./?language=<?php echo urlencode($languageName); ?>" class="btn btn-sm bg-secondary-subtle fw-bold"><?php echo $languageName; ?></a>
                  <a href="#" class="btn btn-sm bg-body-tertiary fw-bold" disabled><?php echo $languageCount; ?></a>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <div class="mb-2 row align-items-center">
            <label class="col-3 col-form-label text-nowrap fw-medium">Works</label>
            <div class="col-9">
              <h6 class="form-control-plaintext fw-bold"><?php echo count($results); ?></h6>
            </div>
          </div>
          <div class="mb-2 row align-items-center">
            <label class="col-3 col-form-label text-nowrap fw-medium">Pages</label>
            <div class="col-9">
              <h6 class="form-control-plaintext fw-bold"><?php echo $total_count; ?></h6>
            </div>
          </div>
          <div class="mb-2 row align-items-center">
            <label class="col-3 col-form-label text-nowrap fw-medium">Views</label>
            <div class="col-9">
              <h6 class="form-control-plaintext fw-bold"><?php echo $total_view_count; ?></h6>
            </div>
          </div>
          <?php if (!empty($latest_cover)): ?>
          <div class="mb-2 row align-items-center">
            <label class="col-3 col-form-label text-nowrap fw-medium">Date</label>
            <div class="col-9">
              <h6 class="form-control-plaintext fw-bold"><?php echo date("l, d F, Y", strtotime($latest_cover['date'])); ?></h6>
            </div>
          </div>
          <?php endif; ?>

          <!-- FAVORITE BUTTON FORM: episode_name and uid only, no htmlspecialchars! -->
          <form id="favoriteForm" method="post" action="">
            <input type="hidden" name="episode_name" value="<?php echo $episode_name; ?>">
            <input type="hidden" name="uid" value="<?php echo $user_id; ?>">
            <?php if (isset($_SESSION['email'])): ?>
              <?php if (isFavorited($_SESSION['email'], $episode_name, $user_id, $db)): ?>
                <button type="submit" name="action" value="unfavorite" class="btn btn-sm bg-body-tertiary link-body-emphasis rounded fw-bold">Remove from favorites</button>
              <?php else: ?>
                <button type="submit" name="action" value="favorite" class="btn btn-sm bg-body-tertiary link-body-emphasis rounded fw-bold">Add to favorites</button>
              <?php endif; ?>
            <?php endif; ?>
          </form>

        </div>
      </div>
    </div>
    <div class="container my-5">
      <div class="rounded-5 border border-2"></div>
    </div>
    <div class="container mb-5">
      <h5 class="my-3 fw-bold">All works in <?php echo $episode_name; ?> by <?php echo $latest_cover['artist'] ?? 'Unknown Artist'; ?></h5>
      <div class="btn-group mb-2 w-100 gap-2">
        <?php if (!empty($first_cover)): ?>
          <a class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50" href="view.php?title=<?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?>&id=<?php echo $first_cover['id']; ?>&page=1">read first</a>
        <?php else: ?>
          <button class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50" disabled>read first</button>
        <?php endif; ?>
          <button class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50 d-none d-md-block" data-bs-toggle="modal" data-bs-target="#shareLink">share</button>
        <?php if (!empty($first_cover)): ?>
          <a class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50" href="../episode/?title=<?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?>" target="_blank">original</a>
        <?php else: ?>
          <button class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-50" disabled>original</button>
        <?php endif; ?>
      </div>
      <button class="btn bg-body-tertiary link-body-emphasis rounded-5 fw-bold w-100 mb-2 d-md-none" data-bs-toggle="modal" data-bs-target="#shareLink">share</button>
      <div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xxl-4 g-1">
          <?php foreach ($results as $image): ?>
          <div class="col">
            <div class="card border-0 bg-body-tertiary shadow h-100 rounded-4">
              <a class="text-decoration-none link-body-emphasis" href="manga_preview.php?title=<?php echo urlencode($image['episode_name']); ?>&uid=<?php echo $image['userid']; ?>&id=<?php echo $image['id']; ?>&page=1">
                <div class="row g-0">
                  <div class="col-4">
                    <div class="ratio ratio-1x1 rounded-top-4">
                      <img class="object-fit-cover lazy-load h-100 w-100 rounded-top-4" data-src="/private_thumbnails/<?php echo $image['filename']; ?>" alt="<?php echo $image['title']; ?>">
                    </div>
                  </div>
                  <div class="col-8">
                    <div class="card-body d-flex align-items-center justify-content-start h-100">
                      <div class="text-truncate">
                        <h6 class="card-title fw-bold text-truncate"><?php echo $image['title']; ?></h6>
                        <h6 class="card-title fw-bold small"><?php echo $image['view_count']; ?> views</h6>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
              <a class="btn p-2 w-100 btn-dark rounded-bottom-4 fw-medium" href="view.php?title=<?php echo urlencode($image['episode_name']); ?>&uid=<?php echo $image['userid']; ?>&id=<?php echo $image['id']; ?>&page=1">read</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- Share Modal -->
    <div class="modal fade" id="shareLink" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group">
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-twitter"></i>
              </a>
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-line"></i>
              </a>
              <a class="btn" href="mailto:?body=<?php echo urlencode($currentUrl); ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-reddit"></i>
              </a>
              <a class="btn" href="https://www.instagram.com/?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-instagram"></i>
              </a>
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100 mb-2" role="group">
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-whatsapp"></i>
              </a>
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-pinterest"></i>
              </a>
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-linkedin"></i>
              </a>
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo urlencode($currentUrl); ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank">
                <i class="bi bi-messenger"></i>
              </a>
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-telegram"></i>
              </a>
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo urlencode($currentUrl); ?>" target="_blank">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
            <div class="input-group">
              <input type="text" id="urlInput1" value="<?php echo $currentUrl; ?>" class="form-control border-2 fw-bold" readonly>
              <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                <i class="bi bi-clipboard-fill"></i>
              </button>
            </div>
            <script>
              function copyToClipboard1() {
                var urlInput1 = document.getElementById('urlInput1');
                urlInput1.select();
                urlInput1.setSelectionRange(0, 99999);
                document.execCommand('copy');
              }
            </script>
          </div>
        </div>
      </div>
    </div>
    <!-- Original Image Modal -->
    <div class="modal fade" id="originalImage" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <?php if (!empty($latest_cover)): ?>
            <a href="view.php?title=<?php echo urlencode($episode_name); ?>&uid=<?php echo $user_id; ?>&id=<?php echo $first_cover['id'] ?? $latest_cover['id']; ?>&page=1">
              <img class="object-fit-contain w-100 rounded" src="/private_images/<?php echo $latest_cover['filename']; ?>">
            </a>
            <?php else: ?>
            <div class="ratio ratio-16x9 bg-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded d-flex align-items-center justify-content-center">
              <span class="text-muted">No Original Image Available</span>
            </div>
            <?php endif; ?>
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal">
              <i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i>
            </button>
            <?php if (!empty($latest_cover)): ?>
            <a class="btn btn-primary fw-bold w-100 mt-2" href="/private_images/<?php echo $latest_cover['filename']; ?>" download>Download Cover Image</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "/icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder;
          imageObserver.observe(image);
          image.style.filter = "blur(5px)";
          image.addEventListener("load", function() {
            image.style.filter = "none";
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
              document.removeEventListener("scroll", lazyload);
              window.removeEventListener("resize", lazyload);
              window.removeEventListener("orientationChange", lazyload);
            }
          }, 20);
        }

        document.addEventListener("scroll", lazyload);
        window.addEventListener("resize", lazyload);
        window.addEventListener("orientationChange", lazyload);
      }

      // Infinite scrolling
      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

        setTimeout(function() {
          for (let i = 0; i < 10; i++) {
            if (lazyloadImages.length === 0) {
              break;
            }
            let image = lazyloadImages[0];
            imageContainer.appendChild(image);
            lazyloadImages = Array.from(lazyloadImages).slice(1);
          }
          loading = false;
        }, 1000);
      }

      window.addEventListener("scroll", function() {
        if (window.innerHeight + window.scrollY >= imageContainer.clientHeight) {
          loadMoreImages();
        }
      });

      loadMoreImages();
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>