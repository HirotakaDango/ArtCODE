<?php
require_once('../../auth.php');

$db = new PDO('sqlite:../../database.sqlite');

$email = $_SESSION['email'];
$id = $_GET['id'];

// Query to fetch a single row by ID with a JOIN on the "users" table
$query = "SELECT novel.*, users.id AS user_id, users.email, users.artist FROM novel JOIN users ON novel.email = users.email WHERE novel.id = :id";
$statement = $db->prepare($query);
$statement->bindParam(':id', $id);
$statement->execute();
$post = $statement->fetch();

$novel_id = $post['id'];

// Get the email of the selected user
$user_email = $post['email'];

// Process any favorite/unfavorite requests
if (isset($_POST['favorite'])) {
  $novel_id = $_POST['novel_id'];

  // Check if the novel has already been favorited by the current user
  $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_novel WHERE email = :email AND novel_id = :novel_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':novel_id', $novel_id);
  $stmt->execute();
  $existing_fav = $stmt->fetchColumn();

  if ($existing_fav == 0) {
    $stmt = $db->prepare("INSERT INTO favorites_novel (email, novel_id) VALUES (:email, :novel_id)");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->bindParam(':novel_id', $novel_id);
    $stmt->execute();
  }

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: view.php?id=' . $post['id']);
  exit();

} elseif (isset($_POST['unfavorite'])) {
  $novel_id = $_POST['novel_id'];
  $stmt = $db->prepare("DELETE FROM favorites_novel WHERE email = :email AND novel_id = :novel_id");
  $stmt->bindParam(':email', $_SESSION['email']);
  $stmt->bindParam(':novel_id', $novel_id);
  $stmt->execute();

  // Redirect to the same page with the appropriate sorting parameter
  header('Location: view.php?id=' . $post['id']);
  exit();
}

// Query to check if there are more than 1 post by the same user
$user_posts_query = "SELECT id FROM novel WHERE email = :email";
$user_posts_statement = $db->prepare($user_posts_query);
$user_posts_statement->bindParam(':email', $email);
$user_posts_statement->execute();
$user_posts = $user_posts_statement->fetchAll();
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
    <main id="swup" class="transition-main">
    <div class="container mt-3 mb-5">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                ArtCODE
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis border-bottom border-3 py-2 text-decoration-none text-white fw-medium" href="view.php?id=<?php echo $id; ?>"><?php echo $post['title']; ?></a>
            </li>
            <?php if ($user_email === $email): ?>
              <li class="breadcrumb-item">
                <!-- Display the edit button only if the current user is the owner of the image -->
                <a class="link-body-emphasis py-2 text-decoration-none text-white fw-medium" href="edit.php?id=<?php echo $post['id']; ?>">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              </li>
            <?php endif; ?>
            <a class="btn border-0 btn-outline-light ms-auto" href="#" data-bs-toggle="modal" data-bs-target="#shareLink"><i class="bi bi-share-fill"></i></a>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis border-bottom border-3 py-2 text-decoration-none text-white fw-medium" href="view.php?id=<?php echo $id; ?>"><?php echo $post['title']; ?></a>
            </li>
            <?php if ($user_email === $email): ?>
              <li class="breadcrumb-item">
                <!-- Display the edit button only if the current user is the owner of the image -->
                <a class="link-body-emphasis py-2 text-decoration-none text-white fw-medium" href="edit.php?id=<?php echo $post['id']; ?>">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              </li>
            <?php endif; ?>
            <a class="btn border-0 btn-outline-light ms-auto" href="#" data-bs-toggle="modal" data-bs-target="#shareLink"><i class="bi bi-share-fill"></i></a>
          </ol>
        </div>
      </nav>
      <div class="row featurette">
        <div class="col-md-4 order-md-1 cover-size" style="height: 500px;">
          <div class="position-relative">
            <a data-bs-toggle="modal" data-bs-target="#originalImage">
              <img style="border-radius: 0.85em; height: 100%; width: 100%;" class="d-block object-fit-cover" src="thumbnails/<?php echo $post['filename']; ?>">
            </a>
            <?php
              $stmt = $db->prepare("SELECT COUNT(*) FROM favorites_novel WHERE email = :email AND novel_id = :novel_id");
              $stmt->bindParam(':email', $_SESSION['email']);
              $stmt->bindParam(':novel_id', $post['id']);
              $stmt->execute();
              $is_favorited = $stmt->fetchColumn();

              if ($is_favorited) {
            ?>
              <form method="POST">
                <input type="hidden" name="novel_id" value="<?php echo $post['id']; ?>">
                <button type="submit" class="btn btn-dark opacity-75 position-absolute bottom-0 end-0 m-2 fw-bold" name="unfavorite">
                  <i class="bi bi-heart-fill"></i> <small>unfavorite</small>
                </button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="novel_id" value="<?php echo $post['id']; ?>">
                <button type="submit" class="btn btn-dark opacity-75 position-absolute bottom-0 end-0 m-2 fw-bold" name="favorite">
                  <i class="bi bi-heart"></i> <small>favorite</small>
                </button>
              </form>
            <?php } ?>
          </div>
        </div>
        <div class="col-md-8 order-md-2">
          <div class="fw-bold">
            <h1 class="text-center fw-bold"><?php echo isset($post['title']) ? $post['title'] : '' ?></h1>
            <p class="mt-5">Author: <a class="text-decoration-none text-light" href="user.php?id=<?php echo $post['user_id']; ?>"><?php echo isset($post['artist']) ? $post['artist'] : '' ?><a/></p>
            <p class="mt-2">Published: <?php echo isset($post['date']) ? $post['date'] : '' ?></p>
            <p class="mt-2">Genre:
              <?php
                if (isset($tag)) {
                  echo '<a class="text-decoration-none text-white border-0 btn-sm rounded-pill fw-bold" href="genre.php">All</a> ';
                }

                if (isset($post['tags'])) {
                  $tags = explode(',', $post['tags']);
                  $totalTags = count($tags);

                  foreach ($tags as $index => $tag) {
                    $tag = trim($tag);
                    $url = 'genre.php?tag=' . urlencode($tag);

                    echo '<a class="text-decoration-none text-white border-0 btn-sm rounded-pill fw-bold" href="' . $url . '">' . $tag . '</a>';

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
      </div>
      <div class="text-white">
        <p style="white-space: break-spaces; overflow: hidden;">
          <?php
            $novelTextSynopsis = isset($post['description']) ? $post['description'] : ''; // Replace with the desired variable or value

            if (!empty($novelTextSynopsis)) {
              $messageTextSynopsis = $novelTextSynopsis;
              $messageTextWithoutTagsSynopsis = strip_tags($messageTextSynopsis);
              $patternSynopsis = '/\bhttps?:\/\/\S+/i';

              $formattedTextSynopsis = preg_replace_callback($patternSynopsis, function ($matchesSynopsis) {
                $urlSynopsis = htmlspecialchars($matchesSynopsis[0]);
                return '<a href="' . $urlSynopsis . '">' . $urlSynopsis . '</a>';
              }, $messageTextWithoutTagsSynopsis);

              $paragraphs = explode("\n", $formattedTextSynopsis);
    
              foreach ($paragraphs as $paragraph) {
                echo '<p style="white-space: break-spaces; overflow: hidden;">' . $paragraph . '</p>';
              }
            } else {
              echo "No text.";
            }
          ?>
        </p>
        <hr class="border-4 rounded-pill">
        <p style="white-space: break-spaces; overflow: hidden;">
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
        </p>
        </br>
        <a class="btn btn-primary w-100 mt-3" href="comments.php?id=<?php echo $id; ?>">view all comments</a>
        <div class="mb-5"></div>
      </div>
      <br>
    </div>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/view.php?id=' . $id; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="modal-body position-relative">
            <img class="object-fit-contain h-100 w-100 rounded" src="images/<?php echo $post['filename']; ?>">
            <button type="button" class="btn position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4 text-stroke"></i></button>
          </div>
        </div>
      </div>
    </div>
    <style>
      @media (min-width: 768px) {
        .cover-size {
          max-width: 375px;
        }
      }
    </style>
    <script>
      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
