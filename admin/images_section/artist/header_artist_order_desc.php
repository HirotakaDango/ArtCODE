    <div class="overflow-x-auto container-fluid p-1 mb-2 hide-scrollbar" style="white-space: nowrap;">
      <a href="?id=<?php echo $id; ?>&by=order_desc&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="fw-medium btn btn-sm btn-light rounded-pill">all images</a>
      <?php
        try {
          // SQL query to get the most popular tags and their counts based on the user's email using a JOIN
          $queryTags = "SELECT SUBSTR(images.tags, 1, INSTR(images.tags, ',') - 1) as first_tag, COUNT(*) as tag_count 
                        FROM images 
                        JOIN users ON images.email = users.email 
                        WHERE users.id = :id 
                        GROUP BY first_tag 
                        ORDER BY tag_count ASC 
                        LIMIT 100";
        
          // Prepare the SQL statement
          $stmt = $db->prepare($queryTags);
        
          // Bind the id parameter
          $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
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
          <a class="fw-medium btn btn-sm btn-light rounded-pill" style="margin-right: 2px;" href="?id=<?= $id ?>&by=tagged_order_desc&tag=<?= $firstTag ?>">
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