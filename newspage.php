<?php
// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve the total number of news items from the database
$count_results = $db->query('SELECT COUNT(*) as count FROM news');
$count_row = $count_results->fetchArray(SQLITE3_ASSOC);
$count = $count_row['count'];

// Calculate the total number of pages
$pages = ceil($count / 20);

// Get the current page number or default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting offset for the current page
$offset = ($page - 1) * 20;

// Retrieve the news items for the current page from the database
$results = $db->query("SELECT * FROM news ORDER BY id DESC LIMIT $offset, 20");
$data = [];

// Loop through each row in the result set
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
  $data[] = $row;
}

// Close the database connection
$db->close();

// Calculate previous and next page numbers
$prevPage = $page > 1 ? $page - 1 : 1;
$nextPage = $page < $pages ? $page + 1 : $pages;

?>

<!DOCTYPE html>
<html>
  <head>
    <title>ArtCODE</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('lp_header.php'); ?>
    <h2 class="text-secondary mt-2 fw-bold text-center"><i class="bi bi-newspaper"></i> Update & News</h2>
    <div class="container">
      <div class="row">
        <div class="container">
          <?php foreach ($data as $row): ?>  
            <div class="card mb-3">
              <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-newspaper"></i>
                News <p class="btn-sm text-white float-end"></p>
              </div>
              <div class="card-body text-secondary fw-bold">
                <img class="img-fluid" src="<?php echo !empty($row['preview']) ? $row['preview'] : "icon/Hot-beverage.svg"; ?>"
                <p class="text-start ms-3">Title: <?= $row['title'] ?></p>
                <p class="text-start ms-3">Description: <?= $row['description'] ?></p>
                <a class="text-start text-primary text-decoration-none ms-3" href="<?= $row['verlink'] ?>" target="_blank"><?= $row['ver'] ?></a>
                <p class="text-start ms-3">Created At: <?= $row['created_at'] ?></p>
              </div>
            </div>
          <?php endforeach; ?> 
        </div>
      </div>
    </div>
    <div class="mt-3 d-flex justify-content-center btn-toolbar container"> 
      <?php if ($page > 1): ?>
        <a class="btn btn-sm rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?page=<?php echo $prevPage; ?>"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>
      <?php endif; ?>
      <?php if ($page < $pages): ?>
        <a class="btn btn-sm rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?page=<?php echo $nextPage; ?>">next <i class="bi bi-arrow-right-circle-fill"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <?php include('session_user.php'); ?>
    <?php include('footer.php'); ?>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>
