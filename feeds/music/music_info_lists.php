    <div class="d-flex justify-content-between align-items-center rounded-4 bg-dark-subtle bg-opacity-10 my-2">
      <a class="link-body-emphasis text-decoration-none music text-start w-100 text-white btn fw-bold border-0" href="play.php?album=<?php echo urlencode($row['album']); ?>&id=<?php echo $row['id']; ?>" style="overflow-x: auto; white-space: nowrap;">
        <?php echo $row['title']; ?><br>
        <small class="text-muted"><?php echo $row['artist']; ?> - <?php echo $row['album']; ?></small><br>
        <small class="text-muted">Playtime : <?php echo $duration; ?></small>
      </a>
      <div class="dropdown dropdown-menu-end">
        <button class="text-decoration-none text-white btn fw-bold border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
        <ul class="dropdown-menu rounded-4">
          <li><button class="dropdown-item fw-medium" onclick="sharePageS('<?php echo $row['id']; ?>', '<?php echo $row['title']; ?>')"><i class="bi bi-share-fill"></i> share</button></li>
          <li><a class="dropdown-item fw-medium" href="artist.php?id=<?php echo $row['userid']; ?>"><i class="bi bi-person-fill"></i> show artist</a></li>
          <li><a class="dropdown-item fw-medium" href="album.php?album=<?php echo $row['album']; ?>"><i class="bi bi-disc-fill"></i> show album</a></li>
          <li><a class="dropdown-item fw-medium" href="<?php echo $row['file']; ?>" download><i class="bi bi-cloud-arrow-down-fill"></i> download</a></li>
        </ul>
      </div>
    </div>