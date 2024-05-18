                <div class="d-md-none d-lg-none">
                  <h2 class="text-start text-white fw-bold" style="overflow-x: auto; white-space: nowrap;"><?php echo $row['title']; ?></h2>
                </div>
                <div class="d-none d-md-block d-lg-block">
                  <h3 class="text-start text-white fw-bold" style="overflow-x: auto; white-space: nowrap;"><?php echo $row['title']; ?></h3>
                </div>
                <div style="overflow-x: auto; white-space: nowrap;">
                  <a class="text-decoration-none text-white small fw-bold link-body-emphasis" href="artist.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&by=<?php echo isset($_GET['mode']) && $_GET['mode'] === 'grid' ? (isset($_GET['by']) && ($_GET['by'] === 'oldest' || $_GET['by'] === 'newest') ? $_GET['by'] : 'newest') : (isset($_GET['by']) && ($_GET['by'] === 'oldest_lists' || $_GET['by'] === 'newest_lists') ? $_GET['by'] : 'newest_lists'); ?>&id=<?php echo $row['userid']; ?>"><i class="bi bi-person-fill"></i> <?php echo $row['artist']; ?></a>
                </div>
                <div class="my-2" style="overflow-x: auto; white-space: nowrap;">
                  <a class="text-decoration-none text-white small fw-bold link-body-emphasis" href="album.php?mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : 'grid'; ?>&album=<?php echo $row['album']; ?>&userid=<?php echo $row['userid']; ?>"><i class="bi bi-disc-fill"></i> <?php echo $row['album']; ?></a>
                </div>