      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 g-1">
        <?php while ($row = $results->fetchArray()): ?>
          <div class="col">
            <div class="card border-0 rounded-4 bg-body-tertiary h-100">
              <div class="card-body">
                <h5 class="fw-bold text-truncate mb-3" style="max-width: auto;"><?php echo $row['title']; ?></h5>
                <?php
                // Get the email from the texts table
                $email = $row['email']; // Email from the texts table
                
                // Assuming you have a database connection $db
                $stmt = $db->prepare("SELECT id, artist FROM users WHERE email = :email");
                $stmt->bindValue(':email', $email, SQLITE3_TEXT); // Bind the email parameter
                $userResult = $stmt->execute();
                $user = $userResult->fetchArray(SQLITE3_ASSOC);
                
                // If the user is found, retrieve the user ID and artist name
                if ($user) {
                  $userId = $user['id'];
                  $artistName = $user['artist'];
                  echo "<h6 class='fw-medium text-truncate mb-3' style='max-width: auto;'>Author: <a class='btn border-0 p-0 pb-1 m-0 fw-medium' href='/text/?uid=$userId'>$artistName</a></h6>";
                } else {
                  echo "<h6 class='fw-medium text-truncate mb-3' style='max-width: auto;'>Author: Unknown</h6>";
                }
                ?>
                <?php
                if (!empty($row['tags'])) {
                  $tags = explode(',', $row['tags']);
                  $tagCount = 0; // Counter for the number of tags processed
                  $displayedTags = []; // Array to store tags that are displayed
                
                  // Include the file once and store its output in a variable
                  ob_start();
                  include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php');
                  $buttonClass = ob_get_clean(); // Capture the output of the include
                
                  foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                      $tagCount++; // Increment the tag counter
                      if ($tagCount <= 5) {
                        // Merge the current query parameters with the new tag parameter
                        $queryParams = array_merge($_GET, ['tag' => $tag]);
                        $tagUrl = '?' . http_build_query($queryParams);
                        $displayedTags[] = '<a href="' . $tagUrl . '" style="margin: 0.2em;" class="btn btn-sm fw-medium btn-' . $buttonClass . ' rounded-pill"><i class="bi bi-tag-fill"></i> ' . $tag . '</a>';
                      }
                    }
                  }
                
                  // Output the displayed tags
                  foreach ($displayedTags as $tagLink) {
                    echo $tagLink;
                  }
                
                  // If there are more than 5 tags, display the "View All Tags" button
                  if ($tagCount > 5) {
                    ?>
                    <!-- Button trigger modal -->
                    <button type="button" style="margin: 0.2em; margin-left: -2px;" class="btn btn-sm fw-medium btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> rounded-pill" data-bs-toggle="modal" data-bs-target="#tagsModal">
                      <i class="bi bi-tags-fill"></i> all tags
                    </button>
                
                    <!-- Modal -->
                    <div class="modal fade" id="tagsModal" tabindex="-1" aria-labelledby="tagsModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                          <div class="modal-header border-0">
                            <h1 class="modal-title fs-5" id="tagsModalLabel">All Tags from <?php echo $row['title']; ?></h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <?php
                            // Display all tags in the modal
                            foreach ($tags as $tag) {
                              $tag = trim($tag);
                              if (!empty($tag)) {
                                $queryParams = array_merge($_GET, ['tag' => $tag]);
                                $tagUrl = '?' . http_build_query($queryParams);
                                echo '<a href="' . $tagUrl . '" style="margin: 0.2em;" class="btn btn-sm fw-medium btn-' . $buttonClass . ' rounded-pill"><i class="bi bi-tag-fill"></i> ' . $tag . '</a>';
                              }
                            }
                            ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php
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
                  <a class="link-body-emphasis text-decoration-none position-absolute start-0 bottom-0 m-3 fw-bold" href="view.php?id=<?php echo $row['id']; ?>&uid=<?php echo $userId; ?>">Read more</a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>