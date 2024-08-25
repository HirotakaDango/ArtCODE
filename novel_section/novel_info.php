          <div class="col">
            <div class="card bg-body-tertiary shadow-sm h-100 border-0 rounded-4 position-relative">
              <a class="shadow rounded" href="/feeds/novel/view.php?id=<?php echo $image['id']; ?>">
                <img class="w-100 object-fit-cover rounded-top-4" src="/feeds/novel/thumbnails/<?php echo $image['filename']; ?>" style="aspect-ratio: 8/9;">
              </a>
              <a class="btn border-0 position-absolute top-0 end-0" href="/admin/novel_section/edit.php?id=<?php echo $image['id']; ?>&back=<?php echo urlencode('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"><i class="bi bi-pencil-fill text-white" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);"></i></a>
              <div class="card-body">
                <h5 class="card-text text-start fw-bold"><?php echo $image['title']; ?></h5>
                <p class="card-text text-start small fw-bold"><small>by <a class="text-decoration-none link-light" href="user.php?id=<?php echo $image['user_id']; ?>"><?php echo $image['artist']; ?></a></small></p>
                <p class="card-text text-start small fw-bold"><small><?php echo $image['view_count']; ?> views</small></p>
              </div>
            </div>
          </div>