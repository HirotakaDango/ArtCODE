    <?php
    // Connect to the SQLite database
    $db = new SQLite3('../database.sqlite');

    // Prepare the query to get the user's numpage
    $queryNum = $db->prepare('SELECT numpage FROM users WHERE email = :email');
    $queryNum->bindValue(':email', $email, SQLITE3_TEXT); // Assuming $email is the email you want to search for
    $resultNum = $queryNum->execute();
    $user = $resultNum->fetchArray(SQLITE3_ASSOC);
    
    $numpage = $user['numpage'];
    
    // Set the limit of images per page
    $albumsPerPage = empty($numpage) ? 50 : $numpage;
    
    // Get the current page number from the query string, default to 1 if not set
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $albumsPerPage;
    
    // Calculate the total number of albums
    $email = $_SESSION['email'];
    $stmt = $db->prepare('SELECT COUNT(*) as total FROM album WHERE email = :email');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $totalAlbums = $stmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];
    
    // Display the album list with pagination
    $stmt = $db->prepare('SELECT DISTINCT id, album_name FROM album WHERE email = :email ORDER BY album_name ASC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':limit', $albumsPerPage, SQLITE3_INTEGER);
    $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
    $results = $stmt->execute();
    
    // Include the image card albums template
    include('image_card_albums.php');
    
    // Calculate total pages
    $totalPages = ceil($totalAlbums / $albumsPerPage);
    $prevPage = $page - 1;
    $nextPage = $page + 1;
    ?>
    
    <div class="pagination d-flex gap-1 justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=1"><i class="bi text-stroke bi-chevron-double-left"></i></a>
      <?php endif; ?>
    
      <?php if ($page > 1): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=<?php echo $prevPage; ?>"><i class="bi text-stroke bi-chevron-left"></i></a>
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
            echo '<a class="btn btn-sm btn-primary fw-bold" href="?by=' . (isset($_GET['by']) ? $_GET['by'] : 'newest') . '&page=' . $i . '">' . $i . '</a>';
          }
        }
      ?>
    
      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=<?php echo $nextPage; ?>"><i class="bi text-stroke bi-chevron-right"></i></a>
      <?php endif; ?>
    
      <?php if ($page < $totalPages): ?>
        <a class="btn btn-sm btn-primary fw-bold" href="?by=<?php echo isset($_GET['by']) ? $_GET['by'] : 'newest'; ?>&page=<?php echo $totalPages; ?>"><i class="bi text-stroke bi-chevron-double-right"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <?php
      // Close the database connection
      $db->close();
    ?>