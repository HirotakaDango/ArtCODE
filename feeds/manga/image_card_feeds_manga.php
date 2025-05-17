    <div class="container-fluid">
      <?php if (basename($_SERVER['PHP_SELF']) !== 'favorites.php'): ?>
        <h6 class="fw-bold mb-3">
          <?php
            if (isset($_GET['search'])) {
              echo 'Search: "' . $_GET['search'] . '" (' . $totalImages . ')';
            } elseif (isset($_GET['artist'])) {
              echo 'Artist: "' . $_GET['artist'] . '" (' . $totalImages . ')';
            } elseif (isset($_GET['uid'])) {
              // Get artist name by uid
              $stmt = $db->prepare("SELECT artist FROM users WHERE id = :uid LIMIT 1");
              $stmt->execute([':uid' => $_GET['uid']]);
              $user = $stmt->fetch(PDO::FETCH_ASSOC);
              if ($user && !empty($user['artist'])) {
                echo 'Artist: "' . $user['artist'] . '" (' . $totalImages . ')';
              } else {
                echo 'Artist not found (' . $totalImages . ')';
              }
            } elseif (isset($_GET['tag'])) {
              echo 'Tag: "' . $_GET['tag'] . '" (' . $totalImages . ')';
            } elseif (isset($_GET['parody'])) {
              echo 'Parody: "' . $_GET['parody'] . '" (' . $totalImages . ')';
            } elseif (isset($_GET['character'])) {
              echo 'Character: "' . $_GET['character'] . '" (' . $totalImages . ')';
            } elseif (isset($_GET['group'])) {
              echo 'Group: "' . $_GET['group'] . '" (' . $totalImages . ')';
            } elseif (isset($_GET['categories'])) {
              echo 'Categories: "' . $_GET['categories'] . '" (' . $totalImages . ')';
            } elseif (isset($_GET['language'])) {
              echo 'Language: "' . $_GET['language'] . '" (' . $totalImages . ')';
            } else {
              echo 'All (' . $totalImages . ')';
            }
          ?>
        </h6>
      <?php endif; ?>
      <?php if (basename($_SERVER['PHP_SELF']) === 'favorites.php'): ?>
        <h6 class="fw-bold mb-3">All Favorites (<?php echo $totalCount; ?>)</h6>
      <?php endif; ?>
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 row-cols-lg-6 g-1" id="image-container">
        <?php
        if (is_array($displayImages) && !empty($displayImages)) :
          foreach ($displayImages as $image) :
        ?>
            <div class="col">
              <div class="card border-0 rounded-4">
                <a href="title.php?title=<?= urlencode($image['episode_name']); ?>&uid=<?= $image['userid']; ?>" class="text-decoration-none">
                  <div class="ratio ratio-cover">
                    <img class="rounded rounded-bottom-0 object-fit-cover lazy-load" data-src="/thumbnails/<?= $image['filename']; ?>" alt="<?= $image['title']; ?>">
                  </div>
                  <h6 class="text-center fw-bold text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> text-decoration-none bg-dark-subtle p-2 rounded rounded-top-0" id="episode-name_img<?= $image['id']; ?>_<?= $image['userid']; ?>">「<?= $image['artist']; ?>」<?= $image['episode_name']; ?>「<?= $image['language']; ?>」</h6>
                </a>
              </div>
            </div>
            <style>
              #episode-name_img<?= $image['id']; ?>_<?= $image['userid']; ?> {
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                max-width: auto;
                transition: max-width 0.3s ease;
              }

              #episode-name_img<?= $image['id']; ?>_<?= $image['userid']; ?>.expand {
                max-width: none;
                white-space: normal;
              }
            </style>
            <script>
              document.addEventListener("DOMContentLoaded", function() {
                const episodeName = document.getElementById('episode-name_img<?= $image['id']; ?>_<?= $image['userid']; ?>');
                const img = episodeName.closest('.card').querySelector('img');
                let timeout;
                const expandText = () => { clearTimeout(timeout); episodeName.classList.add('expand'); };
                const collapseText = () => { timeout = setTimeout(() => { episodeName.classList.remove('expand'); }, 200); };
                episodeName.addEventListener('mouseover', expandText);
                episodeName.addEventListener('mouseleave', collapseText);
                img.addEventListener('mouseover', expandText);
                img.addEventListener('mouseleave', collapseText);
                episodeName.addEventListener('touchstart', expandText);
                episodeName.addEventListener('touchend', collapseText);
                img.addEventListener('touchstart', expandText);
                img.addEventListener('touchend', collapseText);
              });
            </script>
        <?php
          endforeach;
        else :
        ?>
          <p class="fw-bold">No data found.</p>
        <?php endif; ?>
      </div>
    </div>