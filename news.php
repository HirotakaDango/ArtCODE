<?php
session_start();
if (!isset($_SESSION['email'])) {
  header("Location: session.php");
  exit;
}

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
    <title>My Table</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <?php include('header.php'); ?>
    <h5 class="text-secondary mt-2 fw-bold ms-2"><i class="bi bi-newspaper"></i> Update & News</h5>
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
