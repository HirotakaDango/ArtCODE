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

// Get the email of the selected user
$user_email = $post['email'];

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
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="height: 65px;">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                <i class="bi bi-house-fill"></i>
              </a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis fw-semibold text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis border-bottom border-3 py-2 fw-semibold text-decoration-none text-white fw-medium" href="view.php?id=<?php echo $id; ?>"><?php echo $post['title']; ?></a>
            </li>
            <?php if ($user_email === $email): ?>
              <li class="breadcrumb-item">
                <!-- Display the edit button only if the current user is the owner of the image -->
                <a class="link-body-emphasis py-2 fw-semibold text-decoration-none text-white fw-medium" href="edit.php?id=<?php echo $post['id']; ?>">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              </li>
            <?php endif; ?>
          </ol>
        </div>
        <div class="d-md-none d-lg-none">
          <ol class="breadcrumb breadcrumb-chevron p-3 bg-body-tertiary rounded-3" style="height: 65px;">
            <li class="breadcrumb-item">
              <a class="link-body-emphasis fw-semibold text-decoration-none fw-medium" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/feeds/novel/">Home</a>
            </li>
            <li class="breadcrumb-item">
              <a class="link-body-emphasis border-bottom border-3 py-2 fw-semibold text-decoration-none text-white fw-medium" href="view.php?id=<?php echo $id; ?>"><?php echo $post['title']; ?></a>
            </li>
            <?php if ($user_email === $email): ?>
              <li class="breadcrumb-item">
                <!-- Display the edit button only if the current user is the owner of the image -->
                <a class="link-body-emphasis py-2 fw-semibold text-decoration-none text-white fw-medium" href="edit.php?id=<?php echo $post['id']; ?>">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              </li>
            <?php endif; ?>
          </ol>
        </div>
      </nav>
      <div class="row featurette">
        <div class="col-md-3 order-md-1">
          <a href="images/<?php echo $post['filename']; ?>"><img class="d-block border border-2 object-fit-cover rounded mb-2" src="thumbnails/<?php echo $post['filename']; ?>" style="height: 500px; width: 100%;"></a>
        </div>
        <div class="col-md-7 order-md-2">
          <div class="fw-bold">
            <h1 class="text-center fw-bold"><?php echo isset($post['title']) ? $post['title'] : '' ?></h1>
            <p class="mt-5">Author: <?php echo isset($post['artist']) ? $post['artist'] : '' ?></p>
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
    </main>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
