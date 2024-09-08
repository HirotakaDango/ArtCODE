<?php
// admin/users_section/emails/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Retrieve the email from the session
$email = $_SESSION['admin']['email'];

// Connect to the SQLite database
try {
  $db = new PDO('sqlite:' . $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Could not connect to the database: " . $e->getMessage());
}

// Create 'inboxes' table if it doesn't exist
$createTableQuery = "
  CREATE TABLE IF NOT EXISTS inboxes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    title TEXT NOT NULL,
    post TEXT NOT NULL,
    to_email TEXT NOT NULL,
    read TEXT NOT NULL DEFAULT 'no',
    date TEXT DEFAULT CURRENT_TIMESTAMP
  );
";
try {
  $db->exec($createTableQuery);
} catch (PDOException $e) {
  die("Could not create table: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $post = filter_var($_POST['post'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    $to_email = filter_var($_POST['to_email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

    try {
      switch ($action) {
        case 'send':
          $stmt = $db->prepare("INSERT INTO inboxes (email, title, post, to_email) VALUES (?, ?, ?, ?)");
          $stmt->execute([$email, $title, $post, $to_email]);
          header("Location: {$_SERVER['REQUEST_URI']}");
          exit;

        case 'edit':
          $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
          $stmt = $db->prepare("UPDATE inboxes SET title = ?, post = ?, to_email = ? WHERE id = ?");
          $stmt->execute([$title, $post, $to_email, $id]);
          header("Location: {$_SERVER['REQUEST_URI']}");
          exit;

        case 'delete':
          $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
          $stmt = $db->prepare("DELETE FROM inboxes WHERE id = ?");
          $stmt->execute([$id]);
          header("Location: {$_SERVER['REQUEST_URI']}");
          exit;
      }
    } catch (PDOException $e) {
      die("Database error: " . $e->getMessage());
    }
  }
}

// Prepare the query to get the user's numpage
$queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
$queryNum->bindValue(':email', $email, PDO::PARAM_STR);

try {
  $queryNum->execute();
  $user = $queryNum->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}

$numpage = isset($user['numpage']) ? $user['numpage'] : null;

// Set the limit of images per page
$perPage = empty($numpage) ? 50 : $numpage;

// Search, sorting, and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Determine sorting and filtering
$sortBy = isset($_GET['by']) ? $_GET['by'] : 'date_desc';
$filterQuery = '';

switch ($sortBy) {
  case 'newest':
    $orderBy = 'date DESC';
    break;
  case 'oldest':
    $orderBy = 'date ASC';
    break;
  case 'date_newest':
    $orderBy = 'date DESC';
    break;
  case 'date_oldest':
    $orderBy = 'date ASC';
    break;
  case 'order_asc':
    $orderBy = 'title ASC';
    break;
  case 'order_desc':
    $orderBy = 'title DESC';
    break;
  case 'read':
    $orderBy = 'date DESC';
    $filterQuery = "AND read = 'yes'";
    break;
  case 'unread':
    $orderBy = 'date DESC';
    $filterQuery = "AND read = 'no'";
    break;
  default:
    $orderBy = 'date DESC';
}

// Fetch total count of messages
$totalCountQuery = $db->prepare("
  SELECT COUNT(*) AS count
  FROM inboxes
  WHERE (title LIKE ? OR post LIKE ? OR to_email LIKE ?)
  $filterQuery
");
$totalCountQuery->execute(["%$search%", "%$search%", "%$search%"]);
$totalCount = $totalCountQuery->fetchColumn();

// Fetch messages for current page
$messagesQuery = $db->prepare("
  SELECT *
  FROM inboxes
  WHERE (title LIKE ? OR post LIKE ? OR to_email LIKE ?)
  $filterQuery
  ORDER BY $orderBy
  LIMIT ? OFFSET ?
");
$messagesQuery->bindValue(1, "%$search%", PDO::PARAM_STR);
$messagesQuery->bindValue(2, "%$search%", PDO::PARAM_STR);
$messagesQuery->bindValue(3, "%$search%", PDO::PARAM_STR);
$messagesQuery->bindValue(4, $perPage, PDO::PARAM_INT);
$messagesQuery->bindValue(5, $offset, PDO::PARAM_INT);

try {
  $messagesQuery->execute();
  $messages = $messagesQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Admin Emails Management</title>
    <?php include('../../../bootstrapcss.php'); ?>
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../../navbar.php'); ?>
          <div>
            <div class="container mt-4">
              <h1 class="mb-4">Manage Emails</h1>
        
              <!-- Search Form -->
              <form method="get" class="mb-4">
                <div class="input-group w-100">
                  <input type="text" class="form-control border-0 bg-body-tertiary rounded-start-4 focus-ring focus-ring-dark" name="search" value="<?php echo $search; ?>" placeholder="Search by title or content">
                  <button class="btn border-0 bg-body-tertiary rounded-end-4" type="submit"><i class="bi bi-search"></i></button>
                </div>
              </form>
        
              <!-- Button triggers for modals -->
              <button class="btn btn-primary mb-4 fw-medium" data-bs-toggle="modal" data-bs-target="#sendModal">Send New Email</button>
        
              <!-- Sort Dropdown -->
              <div class="d-flex justify-content-between">
                <div class="dropdown">
                  <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Sort by
                  </button>
                  <ul class="dropdown-menu">
                    <?php
                    // Get current query parameters, excluding 'by' and 'page'
                    $queryParams = array_diff_key($_GET, array('by' => '', 'page' => ''));
        
                    // Define sorting options and labels
                    $sortOptions = [
                      'newest' => 'newest',
                      'oldest' => 'oldest',
                      'date_newest' => 'newest by date',
                      'date_oldest' => 'oldest by date',
                      'order_asc' => 'from A to Z',
                      'order_desc' => 'from Z to A',
                      'read' => 'read',
                      'unread' => 'unread'
                    ];
        
                    // Loop through each sort option
                    foreach ($sortOptions as $key => $label) {
                      // Determine if the current option is active
                      $activeClass = ($key === $sortBy) ? 'active' : '';
        
                      // Generate the dropdown item with the appropriate active class
                      echo '<li><a href="?' . http_build_query(array_merge($queryParams, ['by' => $key, 'page' => isset($_GET['page']) ? $_GET['page'] : '1'])) . '" class="dropdown-item fw-bold ' . $activeClass . '">' . $label . '</a></li>';
                    }
                    ?>
                  </ul>
                </div>
                <button class="btn btn-sm fw-bold rounded-pill mb-2 btn-outline-light" onclick="window.location.reload();">Refresh Page</button>
              </div>
        
            <!-- Send Email Modal -->
              <div class="modal fade" id="sendModal" tabindex="-1" aria-labelledby="sendModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen">
                  <div class="modal-content">
                    <div class="modal-header border-0">
                      <h5 class="modal-title" id="sendModalLabel">Send New Email</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST">
                        <input type="hidden" name="action" value="send">
                        <input type="hidden" class="form-control" id="send-email" name="email" value="<?php echo $email; ?>" readonly>
                        <div class="form-floating mb-2">
                          <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-dark" type="text" id="send-title" name="title" placeholder="Title" maxlength="500" required>
                          <label for="send-title" class="fw-medium">Title</label>
                        </div>
                        <div class="form-floating mb-2">
                          <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-dark" type="email" id="send-to_email" name="to_email" placeholder="To Email" required>
                          <label for="send-to_email" class="fw-medium">To Email</label>
                        </div>
                        <div class="form-floating mb-2">
                          <textarea class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-dark vh-100" id="send-post" name="post" placeholder="Post" rows="3" required></textarea>
                          <label for="send-post" class="fw-medium">Post</label>
                        </div>
                        <button type="submit" class="btn btn-primary fw-medium w-100">send</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Edit Email Modal -->
              <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen">
                  <div class="modal-content">
                    <div class="modal-header border-0">
                      <h5 class="modal-title" id="editModalLabel">Edit Email</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id" required>
                        <div class="form-floating mb-2">
                          <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-dark" type="text" id="edit-title" name="title" placeholder="Title" maxlength="500" required>
                          <label for="edit-title" class="fw-medium">Title</label>
                        </div>
                        <div class="form-floating mb-2">
                          <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-dark" type="text" id="edit-to_email" name="to_email" placeholder="To Email" required>
                          <label for="edit-to_email" class="fw-medium">To Email</label>
                        </div>
                        <div class="form-floating mb-2">
                          <textarea class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-dark vh-100" id="edit-post" name="post" placeholder="Post" rows="3" required></textarea>
                          <label for="edit-post" class="fw-medium">Post</label>
                        </div>
                        <button type="submit" class="btn btn-primary fw-medium w-100">update</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
        
              <!-- Delete Email Modal -->
              <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                      <h5 class="modal-title" id="deleteModalLabel">Delete Email</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" id="delete-id" required>
                      <div class="modal-body">
                        <p>Are you sure you want to delete this email?</p>
                      </div>
                      <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger fw-medium">Delete</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
        
              <!-- Email List -->
              <div class="list-group">
                <?php foreach ($messages as $inbox): ?>
                  <?php
                    // Query to get the sender and recipient artist names, sender's avatar, and sender's ID
                    $query = "
                      SELECT
                      sender.artist AS sender_artist,
                      recipient.artist AS recipient_artist,
                      sender.pic AS sender_pic,
                      sender.id AS sender_id
                      FROM
                      users AS sender
                      JOIN
                      users AS recipient ON recipient.email = :to_email
                      WHERE
                      sender.email = :email
                    ";
                    $stmt = $db->prepare($query);
                    $stmt->bindValue(':email', $inbox['email'], PDO::PARAM_STR);
                    $stmt->bindValue(':to_email', $inbox['to_email'], PDO::PARAM_STR);
        
                    try {
                      $stmt->execute();
                      $artists = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                      die("Database error: " . $e->getMessage());
                    }
        
                    // Fetch the sender and recipient artist names, sender's avatar, and sender's ID
                    $sender_artist = $artists['sender_artist'];
                    $recipient_artist = $artists['recipient_artist'];
                    $sender_pic = $artists['sender_pic'] ?: '/icon/profile.svg'; // Default avatar if none provided
                    $sender_id = $artists['sender_id'];
        
                    // Format the date and time
                    $formatted_date = date("d M Y, H:i", strtotime($inbox['date']));
        
                    // Change sender's name to include "Owner" if sender_id is 1
                    if ($sender_id == 1) {
                      $sender_artist .= ' (Owner)';
                    }
                  ?>
                  <div class="list-group-item list-group-item-action my-1 rounded-4 p-3 border-0 <?php echo $inbox['read'] === 'yes' ? 'bg-body-secondary' : 'bg-body-tertiary'; ?> position-relative">
                    <!-- Badge for read/unread status -->
                    <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-<?php echo $inbox['read'] === 'yes' ? 'secondary' : 'primary'; ?>" style="margin: 10px;">
                      <?php echo $inbox['read'] === 'yes' ? 'Read' : 'Unread'; ?>
                    </span>
                    <!-- Dropdown menu for Edit and Delete actions -->
                    <div class="dropdown dropstart position-absolute top-0 end-0 m-2">
                      <button class="btn border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical text-white link-body-emphasis fs-5" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2); text-stroke: 2;"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item fw-medium" href="read.php?id=<?php echo urlencode($inbox['id']); ?>">read</a></li>
                        <li><button class="dropdown-item fw-medium" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $inbox['id']; ?>" data-title="<?php echo $inbox['title']; ?>" data-post="<?php echo $inbox['post']; ?>" data-to_email="<?php echo $inbox['to_email']; ?>">edit</button></li>
                        <li><button class="dropdown-item fw-medium" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $inbox['id']; ?>">delete</button></li>
                      </ul>
                    </div>
                    <div class="d-flex align-items-start">
                      <!-- Sender Avatar -->
                      <img src="/<?php echo $sender_pic; ?>" alt="<?php echo $sender_artist; ?> Avatar" class="rounded-circle me-3" width="60" height="60">
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <div>
                            <h5 class="mb-0 fw-bold"><?php echo $sender_artist; ?></h5>
                            <small class="text-muted fw-bold">&lt;<?php echo $inbox['email']; ?>&gt;</small><br>
                            <small class="text-muted"><?php echo $formatted_date; ?></small>
                          </div>
                        </div>
                        <h6 class="mb-1"><?php echo $inbox['title']; ?></h6>
                        <p class="text-muted mb-1"><?php echo substr($inbox['post'], 0, 150); ?><?php echo strlen($inbox['post']) > 150 ? '...' : ''; ?></p>
                        <small class="text-muted">Recipient: <?php echo $recipient_artist; ?> (<?php echo $inbox['to_email']; ?>)</small>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php
            // Calculate total pages
            $totalPages = ceil($totalCount / $perPage);
            $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
            $queryParams = array_diff_key($_GET, array('page' => ''));
            ?>
            <div class="pagination d-flex gap-1 justify-content-center mt-3">
              <?php if ($page > 1): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => 1])); ?>">
                  <i class="bi text-stroke bi-chevron-double-left"></i>
                </a>
              <?php endif; ?>
            
              <?php if ($page > 1): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $page - 1])); ?>">
                  <i class="bi text-stroke bi-chevron-left"></i>
                </a>
              <?php endif; ?>
            
              <?php
                // Calculate the range of page numbers to display
                $startPage = max($page - 2, 1);
                $endPage = min($page + 2, $totalPages);
            
                // Display page numbers within the range
                for ($i = $startPage; $i <= $endPage; $i++) {
                  if ($i === $page) {
                    echo '<span class="btn btn-sm btn-primary active fw-bold">' . $i . '</span>';
                  } else {
                    echo '<a class="btn btn-sm btn-primary fw-bold" href="' . $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $i])) . '">' . $i . '</a>';
                  }
                }
              ?>
            
              <?php if ($page < $totalPages): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $page + 1])); ?>">
                  <i class="bi text-stroke bi-chevron-right"></i>
                </a>
              <?php endif; ?>
            
              <?php if ($page < $totalPages): ?>
                <a class="btn btn-sm btn-primary fw-bold" href="<?php echo $currentUrl . '?' . http_build_query(array_merge($queryParams, ['page' => $totalPages])); ?>">
                  <i class="bi text-stroke bi-chevron-double-right"></i>
                </a>
              <?php endif; ?>
            </div>
            <div class="mt-5"></div>
          </div>
        </div>
      </div>
    </div>
    <script>
      // Populate the edit modal with data
      document.querySelectorAll('.dropdown-item[data-bs-target="#editModal"]').forEach(button => {
        button.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          const title = this.getAttribute('data-title');
          const post = this.getAttribute('data-post');
          const toEmail = this.getAttribute('data-to_email');

          document.getElementById('edit-id').value = id;
          document.getElementById('edit-title').value = title;
          document.getElementById('edit-post').value = post;
          document.getElementById('edit-to_email').value = toEmail;
        });
      });

      // Populate the delete modal with data
      document.querySelectorAll('.dropdown-item[data-bs-target="#deleteModal"]').forEach(button => {
        button.addEventListener('click', function() {
          const id = this.getAttribute('data-id');
          document.getElementById('delete-id').value = id;
        });
      });
    </script>
    <?php include('../../../bootstrapjs.php'); ?>
  </body>
</html>