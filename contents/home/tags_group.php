    <h5 class="fw-bold ms-2">Popular Tags</h5>
    <div class="overflow-x-auto container-fluid p-1 scrollable-div" style="white-space: nowrap;">
      <?php
        // SQL query to get the most popular tags and their counts
        $queryTags = "SELECT SUBSTR(tags, 1, INSTR(tags, ',') - 1) as first_tag, COUNT(*) as tag_count FROM images WHERE tags LIKE '%,%' GROUP BY first_tag ORDER BY tag_count DESC LIMIT 100";
    
        $result = $db->query($queryTags);
    
        if ($result) {
          while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $firstTag = $row['first_tag'];
            $tagCount = $row['tag_count'];
      ?>
            <a class="fw-medium btn btn-sm btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill" style="margin-right: 2px;" href="../tagged_images.php?tag=<?php echo urlencode($firstTag); ?>">
              <i class="bi bi-tags-fill"></i> <?php echo htmlspecialchars($firstTag); ?>
            </a>
      <?php
          }
        }
      ?>
    </div>
