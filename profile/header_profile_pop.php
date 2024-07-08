    <div class="overflow-x-auto container-fluid p-1 mb-2 hide-scrollbar" style="white-space: nowrap; overflow: auto;">
      <a href="?by=popular&page=<?= isset($_GET['page']) ? htmlspecialchars($_GET['page']) : '1'; ?>" class="fw-medium btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill">all images</a>
      <?php
        try {
          // SQL query to get the most popular tags and their counts based on the user's email
          $queryTags = "SELECT SUBSTR(tags, 1, INSTR(tags, ',') - 1) as first_tag, COUNT(*) as tag_count 
                        FROM images 
                        WHERE tags LIKE :pattern AND email = :email 
                        GROUP BY first_tag 
                        ORDER BY tag_count ASC 
                        LIMIT 100";
    
          // Prepare the SQL statement
          $stmt = $db->prepare($queryTags);
    
          // Bind the parameter for the LIKE clause
          $pattern = "%,%";
          $stmt->bindParam(':pattern', $pattern, PDO::PARAM_STR);
    
          // Bind the email parameter with the correct value
          $email = $_SESSION['email']; // Use the user's email
          $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    
          // Execute the query
          $stmt->execute();
    
          // Fetch the results into an array
          $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        } catch (PDOException $e) {
          // Handle any database connection or query errors
          $errorMessage = "Error: " . $e->getMessage();
        }
      ?>
    
      <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($errorMessage) ?>
        </div>
      <?php endif; ?>
    
      <?php if (!empty($tags)): ?>
        <?php foreach ($tags as $row): ?>
          <?php
            $firstTag = htmlspecialchars($row['first_tag']);
            $tagCount = htmlspecialchars($row['tag_count']);
          ?>
          <a class="fw-medium btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill" style="margin-right: 2px;" href="?by=tagged_popular&tag=<?= $firstTag ?>">
            <i class="bi bi-tags-fill"></i> <?= $firstTag ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <style>
      .hide-scrollbar::-webkit-scrollbar {
        display: none;
      }

      .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
      }
    </style>