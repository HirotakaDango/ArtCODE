    <?php
      // Use getID3 to analyze the music file
      require_once($_SERVER['DOCUMENT_ROOT'] . '/feeds/music/getID3/getid3/getid3.php');
      $getID3 = new getID3();
      $fileInfo = $getID3->analyze($_SERVER['DOCUMENT_ROOT'] . '/feeds/minutes/' . $row['video']);
      getid3_lib::CopyTagsToComments($fileInfo);

      // Extract information
      $duration = !empty($fileInfo['playtime_string']) ? $fileInfo['playtime_string'] : 'Unknown';
    ?>
    <div class="col">
      <div class="card shadow-sm h-100 rounded-4 border-0">
        <div class="position-relative">
          <a class="shadow btn p-0 ratio ratio-16x9 border-0" href="/feeds/minutes/playing.php?id=<?php echo $row['id']; ?>">
            <img class="w-100 object-fit-cover rounded-4 rounded-bottom-0" height="200" src="/feeds/minutes/thumbnails/<?php echo $row['thumb']; ?>">
          </a>
          <p class="badge bg-dark opacity-75 position-absolute bottom-0 end-0 m-2"><?php echo $duration; ?></p>
          <a class="btn border-0 p-0 position-absolute top-0 end-0 m-2" href="edit.php?id=<?php echo $row['id']; ?>"><i class="bi bi-pencil-fill text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);"></i></a>
        </div>
        <div class="p-2 bg-body-tertiary rounded-bottom-4">
          <h5 class="card-text fw-bold text-shadow">
            <?php echo (!is_null($row['title']) && strlen($row['title']) > 15) ? substr($row['title'], 0, 15) . '...' : $row['title']; ?>
          </h5>
          <h6 class="card-text small fw-bold text-shadow">
            <small><a class="text-decoration-none text-white" href="/feeds/minutes/artist.php?id=<?php echo $row['userid']; ?>">
              <img height="20" width="20" class="rounded-circle object-fit-cover" src="../../<?php echo $row['pic']; ?>"> <?php echo (!is_null($row['artist']) && strlen($row['artist']) > 15) ? substr($row['artist'], 0, 15) . '...' : $row['artist']; ?>
            </a></small>
          </h6>
          <div class="d-flex">
            <small class="me-auto"><?php echo $row['view_count']; ?> views</small>
            <small class="ms-auto">
              <?php
                // Convert the date to the desired format
                $formattedDateVid = date('j F, Y', strtotime($row['date']));
                echo $formattedDateVid;
              ?>
            </small>
          </div>
        </div>
      </div>
    </div>