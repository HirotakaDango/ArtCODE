<?php
require_once('auth.php');

$db = new PDO('sqlite:../../database.sqlite');

$email = $_SESSION['email'];
$novel_id = $_GET['novel_id'];

// Check if the chapter ID is provided in the URL
if (!isset($_GET['chapter_id'])) {
  // Redirect to an error page or handle it as appropriate for your application
  header("Location: index.php");
  exit();
}

$chapter_id = $_GET['chapter_id'];

// Query to fetch the current chapter content
$chapterQuery = "
  SELECT chapter.*, novel.title as novel_title
  FROM chapter
  JOIN novel ON chapter.novel_id = novel.id
  WHERE chapter.id = :chapter_id
";

$chapterStatement = $db->prepare($chapterQuery);
$chapterStatement->bindParam(':chapter_id', $chapter_id);
$chapterStatement->execute();
$chapter = $chapterStatement->fetch(PDO::FETCH_ASSOC);

// Get the email of the selected user
$user_email = $chapter['email'];

// Query to fetch the next chapter ID
$nextChapterQuery = "SELECT id FROM chapter WHERE novel_id = :novel_id AND id > :chapter_id ORDER BY id ASC LIMIT 1";
$nextChapterStatement = $db->prepare($nextChapterQuery);
$nextChapterStatement->bindParam(':novel_id', $novel_id);
$nextChapterStatement->bindParam(':chapter_id', $chapter_id);
$nextChapterStatement->execute();
$nextChapter = $nextChapterStatement->fetch(PDO::FETCH_ASSOC);
$nextChapterId = ($nextChapter) ? $nextChapter['id'] : null;

// Query to fetch the previous chapter ID
$prevChapterQuery = "SELECT id FROM chapter WHERE novel_id = :novel_id AND id < :chapter_id ORDER BY id DESC LIMIT 1";
$prevChapterStatement = $db->prepare($prevChapterQuery);
$prevChapterStatement->bindParam(':novel_id', $novel_id);
$prevChapterStatement->bindParam(':chapter_id', $chapter_id);
$prevChapterStatement->execute();
$prevChapter = $prevChapterStatement->fetch(PDO::FETCH_ASSOC);
$prevChapterId = ($prevChapter) ? $prevChapter['id'] : null;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>Read <?php echo $chapter['title']; ?></title>
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
              <a class="link-body-emphasis py-2 text-decoration-none text-white" href="view.php?id=<?php echo $novel_id; ?>"><?php echo $chapter['novel_title']; ?></a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis py-2 text-decoration-none text-white fw-bold" href="read.php?id=<?php echo $chapter['id']; ?>"><?php echo $chapter['title']; ?></a>
            </li>
            <?php if ($user_email === $email): ?>
              <li class="breadcrumb-item">
                <a class="link-body-emphasis py-2 text-decoration-none text-white" href="edit_chapter.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $chapter['id']; ?>">
                  Edit <?php echo $chapter['title']; ?>
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
              <a class="btn py-2 rounded text-start fw-medium" href="view.php?id=<?php echo $novel_id; ?>"><?php echo $chapter['novel_title']; ?></a>
              <a class="btn py-2 rounded text-start fw-bold" href="read.php?id=<?php echo $chapter['id']; ?>"><i class="bi bi-chevron-right small" style="-webkit-text-stroke: 2px;"></i> <?php echo $chapter['title']; ?></a>
              <?php if ($user_email === $email): ?>
                <a class="btn py-2 rounded text-start fw-medium" href="edit_chapter.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $chapter['id']; ?>">
                  Edit <?php echo $chapter['title']; ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </nav>
    </div>
    <div class="container mt-3">
      <h2 class="text-center my-5"><?php echo $chapter['title']; ?></h2>
      <p style="white-space: break-spaces; overflow: hidden;">
        <?php
          $novelText = isset($chapter['content']) ? $chapter['content'] : '';

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
    <?php if (!is_null($prevChapterId)): ?>
      <a class="btn fw-medium btn-outline-light position-fixed top-50 end-0 pe-1 rounded-end-0 border-end-0 rounded-pill" href="read.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $prevChapterId; ?>"><i class="bi bi-chevron-right" style="-webkit-text-stroke: 2px;"></i></a>
    <?php endif; ?>
    <?php if (!is_null($nextChapterId)): ?>
      <a class="btn fw-medium btn-outline-light position-fixed top-50 start-0 ps-1 rounded-start-0 border-start-0 rounded-pill" href="read.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $nextChapterId; ?>"><i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i></a>
    <?php endif; ?>
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 rounded-0">
          <div class="card rounded-4 p-4">
            <p class="text-start fw-bold">share to:</p>
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i>
              </a>
                                
              <!-- Line -->
              <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                                
              <!-- Email -->
              <a class="btn" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                                
              <!-- Reddit -->
              <a class="btn" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                                
              <!-- Instagram -->
              <a class="btn" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                                
              <!-- Facebook -->
              <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <div class="btn-group w-100" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
    
              <!-- Pinterest -->
              <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
    
              <!-- LinkedIn -->
              <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
    
              <!-- Messenger -->
              <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
    
              <!-- Telegram -->
              <a class="btn" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
    
              <!-- Snapchat -->
              <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/feeds/novel/read.php?id=' . $chapter['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
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
    <div class="mt-5"></div>
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
