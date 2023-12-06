<?php
require_once('../../auth.php');

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
$chapterQuery = "SELECT * FROM chapter WHERE id = :chapter_id";
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
      <div class="container mt-3">
        <a class="btn fw-medium btn-outline-light position-absolute top-0 start-0 m-3" href="view.php?id=<?php echo $novel_id; ?>">back</a>
        <?php if ($user_email === $email): ?>
          <a class="btn fw-medium btn-outline-light position-absolute top-0 end-0 m-3" href="edit_chapter.php?novel_id=<?php echo $novel_id; ?>&chapter_id=<?php echo $chapter['id']; ?>">edit</a>
        <?php endif; ?>
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
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
