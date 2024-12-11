<?php
// Connect to the SQLite database
$db = new SQLite3('../../database.sqlite');

// Create tables if not exist
$db->exec('CREATE TABLE IF NOT EXISTS texts (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, title TEXT NOT NULL, content TEXT NOT NULL, tags TEXT, date DATETIME, view_count INTEGER DEFAULT 0)');
$db->exec('CREATE TABLE IF NOT EXISTS text_favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, text_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (text_id) REFERENCES texts(id))');

// Get uid parameter from URL (it may be passed for a specific user, but it's optional now)
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : null;

// Build the SQL query to fetch posts without user-specific filtering
$query = "SELECT texts.*, users.email AS user_email, users.artist 
          FROM texts 
          LEFT JOIN users ON texts.email = users.email 
          WHERE 1=1";  // No user-specific filtering

if ($uid) {
  $query .= " AND users.id = " . $db->escapeString($uid);
}

$query .= " ORDER BY texts.id DESC";  // Order by text ID (most recent first)

// Fetch results
$results = $db->query($query);

// Check if there are no results
if ($results->fetchArray(SQLITE3_ASSOC) === false) {
  // No posts found, display a message
  $noPosts = true;
} else {
  // Reset the result pointer to the beginning for displaying posts
  $results->reset();
  $noPosts = false;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Texts</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../../bootstrapcss.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </head>
  <body>
    <div class="container-fluid mb-2">
      <?php if ($noPosts): ?>
        <p class="position-absolute start-50 top-50 translate-middle fw-bold fs-3">This user has not posted anything.</p>
      <?php else: ?>
        <div id="texts-container">
          <!-- Texts will be loaded here dynamically -->
        </div>
        <button id="load-more" class="btn btn-dark fw-medium rounded-4 w-100" data-offset="5">Load More</button>
      <?php endif; ?>
    </div>

    <script>
      $(document).ready(function() {
        function loadTexts(offset = 0) {
          $.ajax({
            url: 'side_view_get.php',
            method: 'GET',
            dataType: 'json',
            data: { offset: offset, uid: <?php echo $uid; ?> },
            success: function(response) {
              if (response.success) {
                response.texts.forEach(function(text) {
                  const cardHtml = `
                    <div class="card border-0 rounded-4 bg-body-tertiary my-2">
                      <div class="card-body">
                        <h5 class="fw-bold text-truncate mb-3" style="max-width: auto;">${text.title}</h5>
                        <h6 class="fw-medium text-truncate mb-3" style="max-width: auto;">Author: ${text.author}</h6>
                        <div class="d-flex">
                          <a class="ms-auto btn border-0 p-0 fw-bold" href="view.php?id=${text.id}&uid=<?php echo $uid; ?>" target="_blank">read <i class="bi bi-arrow-right"></i></a>
                        </div>
                      </div>
                    </div>
                  `;
                  $('#texts-container').append(cardHtml);
                });
                $('#load-more').data('offset', offset + 5);
              } else {
                $('#load-more').hide();  // Hide the button if no more data
              }
            }
          });
        }

        <?php if (!$noPosts): ?>
          // Initial load
          loadTexts(0);
        <?php endif; ?>

        $('#load-more').click(function() {
          const offset = $(this).data('offset');
          loadTexts(offset);
        });
      });
    </script>

    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>