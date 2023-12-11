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

// Query to fetch comments
$comments_query = "SELECT comments_novel.*, users.artist, users.pic, users.id as iduser FROM comments_novel JOIN users ON comments_novel.email = users.email WHERE comments_novel.filename = :filename ORDER BY comments_novel.id DESC LIMIT 25";
$stmt = $db->prepare($comments_query);
$stmt->bindParam(':filename', $id, PDO::PARAM_STR);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Increment the view count for the image
$stmt = $db->prepare("UPDATE novel SET view_count = view_count + 1 WHERE id = :filename");
$stmt->bindParam(':filename', $id);
$stmt->execute();

// Get the updated image information from the database
$stmt = $db->prepare("SELECT * FROM novel WHERE id = :filename");
$stmt->bindParam(':filename', $id);
$stmt->execute();
$image = $stmt->fetch();

// Query to count the number of chapters for the given novel ID
$countChaptersQuery = "SELECT COUNT(*) FROM chapter WHERE novel_id = :novel_id";
$countChaptersStatement = $db->prepare($countChaptersQuery);
$countChaptersStatement->bindParam(':novel_id', $id);
$countChaptersStatement->execute();
$numChapters = $countChaptersStatement->fetchColumn();

// Query to fetch all chapters for the given novel ID
$chaptersQuery = "SELECT id, content, title as chap_title FROM chapter WHERE novel_id = :novel_id ORDER BY id DESC";
$chaptersStatement = $db->prepare($chaptersQuery);
$chaptersStatement->bindParam(':novel_id', $id);
$chaptersStatement->execute();
$chapters = $chaptersStatement->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="container-fluid mt-3">
      <nav aria-label="breadcrumb">
        <div class="d-none d-md-block d-lg-block">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                ArtCODE
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis text-decoration-none" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white fw-bold" href="view.php?id=<?php echo $id; ?>"><?php echo $post['title']; ?></a>
            </li>
            <?php if ($user_email === $email): ?>
              <li class="breadcrumb-item">
                <a class="link-body-emphasis py-2 text-decoration-none text-white" href="edit.php?id=<?php echo $post['id']; ?>">
                  Edit <?php echo $post['title']; ?>
                </a>
              </li>
            <?php endif; ?>
            <a class="btn btn-sm border-0 btn-outline-light ms-auto" href="#" data-bs-toggle="modal" data-bs-target="#shareLink"><i class="bi bi-share-fill"></i></a>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <div class="btn-group mb-2 w-100 p-3 bg-body-tertiary gap-2">
            <a class="btn fw-bold w-100 text-start rounded" data-bs-toggle="collapse" href="#collapseModal" role="button" aria-expanded="false" aria-controls="collapseExample">
              <i class="bi bi-list" style="-webkit-text-stroke: 1px;"></i> Menu
            </a>
            <a class="btn fw-bold w-100 rounded" href="#" data-bs-toggle="modal" style="max-width: 50px;" data-bs-target="#shareLink"><i class="bi bi-share-fill"></i></a>
          </div>
          <div class="collapse bg-body-tertiary mb-2 rounded" id="collapseModal">
            <div class="btn-group-vertical w-100">
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">ArtCODE</a>
              <a class="btn py-2 rounded text-start fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
              <a class="btn py-2 rounded text-start fw-bold" href="view.php?id=<?php echo $id; ?>"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> <?php echo $post['title']; ?></a>
              <?php if ($user_email === $email): ?>
                <a class="btn py-2 rounded text-start fw-medium" href="edit.php?id=<?php echo $post['id']; ?>">
                  Edit <?php echo $post['title']; ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </nav>
      <div class="row featurette">
        <div class="col-md-3 order-md-1">
          <div class="position-relative" style="height: 500px;">
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
                  <i class="bi bi-heart-fill"></i>
                </button>
              </form>
            <?php } else { ?>
              <form method="POST">
                <input type="hidden" name="novel_id" value="<?php echo $post['id']; ?>">
                <button type="submit" class="btn btn-dark opacity-75 position-absolute bottom-0 end-0 m-2 fw-bold" name="favorite">
                  <i class="bi bi-heart"></i>
                </button>
              </form>
            <?php } ?>
          </div>
        </div>
        <div class="col-md-9 order-md-2">
          <div class="fw-bold">
            <h1 class="text-center fw-bold my-5 mt-md-0"><?php echo isset($post['title']) ? $post['title'] : '' ?></h1>
            <div class="mb-3 row">
              <label for="Author" class="col-sm-4 col-form-label text-nowrap">Author:</label>
              <div class="col-sm-8">
                <a class="text-decoration-none text-light" href="user.php?id=<?php echo $post['user_id']; ?>"><?php echo isset($post['artist']) ? $post['artist'] : '' ?><a/>
              </div>
            </div>
            <div class="mb-3 row">
              <label for="Published" class="col-sm-4 col-form-label text-nowrap">Published:</label>
              <div class="col-sm-8">
                <input type="text" class="form-control-plaintext fw-bold" id="Published" value="<?php echo isset($post['date']) ? $post['date'] : '' ?>" readonly>
              </div>
            </div>
            <div class="mb-3 row">
              <label for="Views" class="col-sm-4 col-form-label text-nowrap">Views:</label>
              <div class="col-sm-8">
                <input type="text" class="form-control-plaintext fw-bold" id="Views" value="<?php echo isset($post['view_count']) ? $post['view_count'] : '' ?>" readonly>
              </div>
            </div>
            <div class="mb-3 row">
              <label for="Chapters" class="col-sm-4 col-form-label text-nowrap">Chapters:</label>
              <div class="col-sm-8">
                <input type="text" class="form-control-plaintext fw-bold" id="Views" value="<?php echo isset($numChapters) ? $numChapters : '' ?>" readonly>
              </div>
            </div>
            <div class="mb-5 row">
              <label for="Genre" class="col-sm-4 col-form-label text-nowrap mb-2">Genre:</label>
              <div class="col-sm-8">
                <?php
                  if (isset($tag)) {
                    echo '<a class="btn btn-sm btn-outline-light fw-bold border border-3 rounded-4" href="genre.php">All</a> ';
                }

                  if (isset($post['tags'])) {
                    $tags = explode(',', $post['tags']);
                    $totalTags = count($tags);

                    foreach ($tags as $index => $tag) {
                      $tag = trim($tag);
                      $url = 'genre.php?tag=' . urlencode($tag);

                      echo '<a class="btn btn-sm btn-outline-light fw-bold border border-3 rounded-4" href="' . $url . '">' . $tag . '</a>';

                      // Add a comma if it's not the last tag
                      if ($index < $totalTags - 1) {
                        echo ', ';
                      }
                    }
                  }
                ?>
              </div>
            </div>
            <?php if ($user_email === $email): ?>
              <a class="btn btn-sm border border-3 rounded-4 btn-outline-light fw-bold" href="upload_chapter.php?id=<?php echo $post['id']; ?>">upload new chapter</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="text-white mt-5 bg-body-tertiary rounded-4 p-3 p-md-5">
        <h5 class="fw-bold">Synopsis:</h5>
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
        <hr class="border-4 my-5 rounded-pill">
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

            if (!function_exists('getYouTubeVideoId')) {
              function getYouTubeVideoId($url)
              {
                $videoId = '';
                $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                if (preg_match($pattern, $url, $matches)) {
                  $videoId = $matches[1];
                }
                return $videoId;
              }
            }
          ?>
        </p>
      </div>
      <div class="mt-5">
        <h5 class="text-center fw-bold my-4">chapters</h5>
        <div class="btn-group-vertical w-100 gap-2">
          <?php foreach ($chapters as $chapter): ?>
            <a class="text-decoration-none btn p-0 fw-medium text-start rounded-4" href="read.php?novel_id=<?php echo $post['id']; ?>&chapter_id=<?= $chapter['id'] ?>">
              <div class="border-3 rounded-4 card p-3">
                <h6><?= $chapter['chap_title'] ?></h6>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        
        <hr class="border-4 my-5 rounded-pill">
        <h5 class="fw-bold text-center mb-5">comments section</h5>
        <?php foreach ($comments as $comment) : ?>
          <div class="card border-0 shadow mb-1 position-relative">
            <div class="d-flex align-items-center mb-2 position-relative">
              <div class="position-absolute top-0 start-0 m-1">
                <img class="rounded-circle" src="../../<?php echo !empty($comment['pic']) ? $comment['pic'] : "../../icon/profile.svg"; ?>" alt="Profile Picture" width="32" height="32">
                <a class="text-white text-decoration-none fw-semibold" href="../../artist.php?id=<?php echo $comment['iduser']; ?>" target="_blank">@<?php echo $comment['artist']; ?></a>・<small class="small fw-medium"><small><?php echo $comment['created_at']; ?></small></small>
              </div>
            </div>
            <div class="mt-5 container-fluid fw-medium">
              <p class="mt-3 small" style="white-space: break-spaces; overflow: hidden;">
                <?php
                // Function to get YouTube video ID
                if (!function_exists('getYouTubeVideoId')) {
                  function getYouTubeVideoId($urlComment1A)
                  {
                    $videoId1A = '';
                    $pattern1A = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                    if (preg_match($pattern1A, $urlComment1A, $matches1A)) {
                      $videoId1A = $matches1A[1];
                    }
                    return $videoId1A;
                  }
                }

                $commentText1A = isset($comment['comment']) ? $comment['comment'] : '';

                if (!empty($commentText1A)) {
                  $paragraphs1A = explode("\n", $commentText1A);

                  foreach ($paragraphs1A as $index1A => $paragraph1A) {
                    $messageTextWithoutTags1A = strip_tags($paragraph1A);
                    $pattern1A = '/\bhttps?:\/\/\S+/i';

                    $formattedText1A = preg_replace_callback($pattern1A, function ($matches1A) {
                      $urlComment1A = htmlspecialchars($matches1A[0]);

                      if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlComment1A)) {
                        return '<a href="' . $urlComment1A . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlComment1A . '" alt="Image"></a>';
                      } elseif (strpos($urlComment1A, 'youtube.com') !== false) {
                        $videoId1A = getYouTubeVideoId($urlComment1A);
                        if ($videoId1A) {
                          $thumbnailUrl1A = 'https://img.youtube.com/vi/' . $videoId1A . '/default.jpg';
                          return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoId1A . '" frameborder="0" allowfullscreen></iframe></div>';
                        } else {
                          return '<a href="' . $urlComment1A . '">' . $urlComment1A . '</a>';
                        }
                      } else {
                        return '<a href="' . $urlComment1A . '">' . $urlComment1A . '</a>';
                      }
                    }, $messageTextWithoutTags1A);
        
                    echo "<p class='small' style=\"white-space: break-spaces; overflow: hidden;\">$formattedText1A</p>";
                  }
                } else {
                  echo "Sorry, no text...";
                }
                ?>
              </p>
            </div>
            <div class="m-2 ms-auto">
              <a class="btn btn-sm fw-semibold" href="reply_comments_novel.php?novelid=<?php echo $id; ?>&comment_id=<?php echo $comment['id']; ?>"><i class="bi bi-reply-fill"></i> Reply</a>
            </div>
          </div>
        <?php endforeach; ?>
        <a class="btn btn-secondary w-100 mt-3 fw-bold border border-3 rounded-4" href="comments.php?novelid=<?php echo $id; ?>">view all comments</a>
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
            <button type="button" class="btn border-0 position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"><i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i></button>
          </div>
        </div>
      </div>
    </div>
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
