<?php
require_once('auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

$id = $_GET['id'];

// Use prepared statements to prevent SQL injection
$query = "SELECT posts.*, users.artist FROM posts JOIN users ON posts.email = users.email WHERE posts.id = :id AND posts.email = :email";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

// Fetch the post
$post = $stmt->fetch();

$note_query = "
  SELECT title, id AS note_id, date
  FROM posts
  WHERE email = :email
  ORDER BY note_id DESC
";
$stmt = $db->prepare($note_query);
$stmt->bindParam(':email', $email);
$stmt->execute();
$notes = $stmt->fetchAll();

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $note_id = $_POST['note_id'];

  // Check if the novel has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM starred WHERE email = :email AND note_id = :note_id");
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':note_id', $note_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO starred (email, note_id) VALUES (:email, :note_id)");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':note_id', $note_id);
    $stmt->execute();
  }

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: view.php?id=' . $id);
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $note_id = $_POST['note_id'];
  $stmt = $db->prepare("DELETE FROM starred WHERE email = :email AND note_id = :note_id");
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':note_id', $note_id);
  $stmt->execute();

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: view.php?id=' . $id);
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title><?php echo $post['title'] ?> by <?php echo isset($post['email']) ? $post['artist'] : '' ?></title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row">
        <?php include('note_list.php'); ?>
        <div class="col-md-9 vh-100 overflow-y-auto">
          <div class="mt-3">
            <nav aria-label="breadcrumb">
              <div class="d-md-none d-lg-none">
                <a class="btn bg-body-tertiary p-3 fw-bold w-100 text-start mb-2" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
                </a>
                <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
                  <div class="btn-group-vertical w-100">
                    <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
                    <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/notes/">Home</a>
                    <a class="btn py-2 rounded text-start fw-bold" href="view.php?id=<?php echo $id; ?>"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> <?php echo $post['title']; ?></a>
                    <a class="btn py-2 rounded text-start fw-medium" href="edit.php?id=<?php echo $id; ?>">Edit <?php echo $post['title']; ?></a>
                  </div>
                </div>
              </div>
            </nav>
          </div>
          <div class="fw-bold">
            <h1 class="text-center fw-bold"><?php echo isset($post['title']) ? $post['title'] : '' ?></h1>
            <div class="mb-2 row">
              <label for="artist" class="col-1 col-form-label text-nowrap fw-medium">Author</label>
              <div class="col-11">
                <p class="form-control-plaintext fw-bold" id="artist"><a class="text-decoration-none text-white" href="#"><?php echo isset($post['artist']) ? $post['artist'] : '' ?></a></p>
              </div>
            </div>
            <div class="mb-2 row">
              <label for="artist" class="col-1 col-form-label text-nowrap fw-medium">Published</label>
              <div class="col-11">
                <p class="form-control-plaintext fw-bold" id="artist"><a class="text-decoration-none text-white" href="#"><?php echo isset($post['date']) ? $post['date'] : '' ?></a></p>
              </div>
            </div>
            <div class="mb-2 row">
              <label for="artist" class="col-1 col-form-label text-nowrap fw-medium">Category</label>
              <div class="col-11">
                <p class="form-control-plaintext fw-bold" id="artist"><a class="text-decoration-none text-white" href="category.php?q=<?php echo urlencode($post['category']); ?>"><?php echo str_replace('_', ' ', $post['category']); ?></a></p>
              </div>
            </div>
            <div class="mb-2 row">
              <label for="artist" class="col-1 col-form-label text-nowrap fw-medium">Genre</label>
              <div class="col-11">
                <p class="form-control-plaintext fw-bold" id="artist">
                  <?php
                    if (isset($tag)) {
                      echo '<a class="text-decoration-none link-light link-body-emphasis" href="genre.php">All</a> ';
                    }

                    if (isset($post['tags'])) {
                      $tags = explode(',', $post['tags']);
                      $totalTags = count($tags);
            
                      foreach ($tags as $index => $tag) {
                        $tag = trim($tag);
                        $url = 'genre.php?tag=' . urlencode($tag);

                        echo '<a class="text-decoration-none link-light link-body-emphasis" href="' . $url . '">' . $tag . '</a>';

                        // Add a comma if it's not the last tag
                        if ($index < $totalTags - 1) {
                          echo ', ';
                        }
                      }
                    }
                  ?>
                </p>
              </div>
            </div>
            <?php
              $stmt = $db->prepare("SELECT COUNT(*) FROM starred WHERE email = :email AND note_id = :note_id");
              $stmt->bindParam(':email', $email);
              $stmt->bindParam(':note_id', $id);
              $stmt->execute();
              $is_favorited = $stmt->fetchColumn();

              if ($is_favorited) {
            ?>
              <form method="POST">
                <input type="hidden" name="note_id" value="<?php echo $id; ?>">
                <button type="submit" class="btn p-0 link-body-emphasis fw-bold" name="unfavorite">
                  <i class="bi bi-star-fill"></i> unstar
                </button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="note_id" value="<?php echo $id; ?>">
                <button type="submit" class="btn p-0 link-body-emphasis fw-bold" name="favorite">
                  <i class="bi bi-star"></i> star
                </button>
              </form>
            <?php } ?>
          </div>
          <div class="text-white">
            <hr class="border-4 rounded-pill">
            <div style="white-space: break-spaces; overflow: hidden;">
              <?php
                $novelText = isset($post['content']) ? $post['content'] : '';

                if (!empty($novelText)) {
                  $paragraphs = explode("\n", $novelText);

                  foreach ($paragraphs as $index => $paragraph) {
                    $messageTextWithoutTags = strip_tags($paragraph);
                    $pattern = '/\bhttps?:\/\/\S+/i';

                    $formattedText = preg_replace_callback($pattern, function ($matches) {
                      $url = htmlspecialchars($matches[0]);

                      // Check if the URL ends with .png, .jpg, .jpeg, or .webp
                      if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $url)) {
                        return '<a href="' . $url . '" target="_blank"><img class="img-fluid rounded-4" loading="lazy" src="' . $url . '" alt="Image"></a>';
                      } elseif (strpos($url, 'youtube.com') !== false) {
                        // If the URL is from YouTube, embed it as an iframe with a very low-resolution thumbnail
                        $videoId = getYouTubeVideoId($url);
                        if ($videoId) {
                          $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/default.jpg';
                          return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe></div>';
                        } else {
                          return '<a href="' . $url . '">' . $url . '</a>';
                        }
                      } else {
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }
                    }, $messageTextWithoutTags);

                    echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }

                function getYouTubeVideoId($url)
                {
                  $videoId = '';
                  $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                  if (preg_match($pattern, $url, $matches)) {
                    $videoId = $matches[1];
                  }
                  return $videoId;
                }
              ?>
            </div>
          </div>
          <br>
        </div>
      </div>
    </div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
