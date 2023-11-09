    <div class="overflow-x-auto container-fluid p-1 mb-2 hide-scrollbar" style="white-space: nowrap; overflow: auto;">
      <a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="fw-medium btn btn-sm btn-dark rounded-pill">all images</a>
      <?php
        try {
          // SQL query to get the most popular tags and their counts based on the user's email
          $queryTags = "SELECT SUBSTR(tags, 1, INSTR(tags, ',') - 1) as first_tag, COUNT(*) as tag_count FROM images WHERE tags LIKE :pattern AND email = :email GROUP BY first_tag ORDER BY tag_count ASC LIMIT 100";

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

          // Fetch the results
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $firstTag = $row['first_tag'];
            $tagCount = $row['tag_count'];

            // Display each first tag as an <a> tag with the total count
            echo "<a class='fw-medium btn btn-sm btn-dark rounded-pill' style='margin-right: 2px;' href='?by=tagged_newest&tag=" . $firstTag . "'><i class='bi bi-tags-fill'></i> $firstTag</a>";
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