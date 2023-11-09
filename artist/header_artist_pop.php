    <div class="overflow-x-auto container-fluid p-1 mb-2 hide-scrollbar" style="white-space: nowrap;">
      <a href="?id=<?php echo $id; ?>&by=populart&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="fw-medium btn btn-sm btn-dark rounded-pill">all images</a>
        <?php
          try {
            // SQL query to get the most popular tags and their counts based on the user's email using a JOIN
            $queryTags = "SELECT SUBSTR(images.tags, 1, INSTR(images.tags, ',') - 1) as first_tag, COUNT(*) as tag_count FROM images JOIN users ON images.email = users.email WHERE users.id = :id GROUP BY first_tag ORDER BY tag_count ASC LIMIT 100
            ";

            // Prepare the SQL statement
            $stmt = $db->prepare($queryTags);

            // Bind the id parameter
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Execute the query
            $stmt->execute();

            // Fetch the results
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              $firstTag = $row['first_tag'];
              $tagCount = $row['tag_count'];

              // Display each first tag as an <a> tag with the total count
              echo "<a class='fw-medium btn btn-sm btn-dark rounded-pill' style='margin-right: 2px;' href='?id=$id&by=tagged_populart&tag=$firstTag'><i class='bi bi-tags-fill'></i> $firstTag</a>";
            }
          } catch (PDOException $e) {
            // Handle any database connection or query errors
            echo "Error: " . $e->getMessage();
          }
        ?>
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