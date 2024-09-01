      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 g-1">
        <?php while ($row = $results->fetchArray()): ?>
          <div class="col">
            <div class="card border-0 rounded-4 bg-body-tertiary h-100">
              <div class="card-body">
                <h5 class="fw-bold text-truncate mb-3" style="max-width: auto;"><?php echo $row['title']; ?></h5>
                <?php
                if (!empty($row['tags'])) {
                  $tags = explode(',', $row['tags']);
                  foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                      ?>
                      <a href="?tag=<?php echo urlencode($tag); ?>" class="btn btn-sm fw-medium btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill">
                        <?php echo $tag; ?>
                      </a>
                      <?php
                    }
                  }
                } else {
                  echo "<p class='text-muted'>No tags available.</p>";
                }
                ?>
                <div class="mt-3 mb-5">
                  <?php
                  $replyText = isset($row['content']) ? $row['content'] : '';

                  if (!empty($replyText)) {
                    // Truncate to 100 characters
                    $truncatedText = mb_strimwidth($replyText, 0, 100, '...');

                    $paragraphs = explode("\n", $truncatedText);

                    foreach ($paragraphs as $index => $paragraph) {
                      $textWithoutTags = strip_tags($paragraph);
                      $pattern = '/\bhttps?:\/\/\S+/i';

                      $formattedText = preg_replace_callback($pattern, function ($matches) {
                        $url = $matches[0];
                        return '<a href="' . $url . '">' . $url . '</a>';
                      }, $textWithoutTags);

                      echo "<p style=\"white-space: break-spaces; overflow: hidden;\">$formattedText</p>";
                    }
                  } else {
                    echo "Sorry, no text...";
                  }
                  ?>
                  <a class="link-body-emphasis text-decoration-none position-absolute start-0 bottom-0 m-3" href="view.php?id=<?php echo $row['id']; ?>">Read more</a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>