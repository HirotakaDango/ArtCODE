          <div class="col">
            <div class="card bg-body-tertiary shadow-sm h-100 border-0 rounded-4">
              <a class="shadow rounded" href="view.php?id=<?php echo $image['id']; ?>">
                <img class="w-100 object-fit-cover rounded-top-4" src="thumbnails/<?php echo $image['filename']; ?>" style="aspect-ratio: 8/9;">
              </a>
              <div class="card-body">
                <h5 class="card-text text-start fw-bold"><?php echo $image['title']; ?></h5>
                <p class="card-text text-start small fw-bold"><small>by <a class="text-decoration-none link-light" href="user.php?id=<?php echo $image['user_id']; ?>"><?php echo $image['artist']; ?></a></small></p>
                <p class="card-text text-start small fw-bold"><small><?php echo $image['view_count']; ?> views</small></p>
              </div>
            </div>
          </div>