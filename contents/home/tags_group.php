    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <li><a href="?by=newest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(!isset($_GET['by']) || $_GET['by'] == 'newest') echo 'active'; ?>">newest</a></li>
        <li><a href="?by=oldest&page=<?php echo isset($_GET['page']) ? $_GET['page'] : '1'; ?>" class="dropdown-item fw-bold <?php if(isset($_GET['by']) && $_GET['by'] == 'oldest') echo 'active'; ?>">oldest</a></li>
      </ul> 
    </div> 
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
    
            // Display each first tag as an <a> tag with the total count
            echo "<a class='fw-medium btn btn-sm btn-dark rounded-pill' style='margin-right: 2px;' href='../tagged_images.php?tag=". $firstTag ."'><i class='bi bi-tags-fill'></i> $firstTag</a>";
          }
        }
      ?>
    </div>
