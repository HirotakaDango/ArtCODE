    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-5 position-relative container">
          <button type="button" class="btn border-0 position-absolute top-0 end-0 m-2 z-3" style="-webkit-text-stroke: 3px;" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
          <h5 class="text-center fw-medium pt-3">Contact <?php echo $artist; ?></h5>
          <div class="modal-body">
            <div class="d-flex justify-content-center">
              <span>
                <?php
                $socialMediaIcons = [
                  'gmail.com' => ['icon' => 'bi-envelope-fill', 'name' => 'Gmail'], // Bootstrap icon for email
                  'line.com' => ['icon' => 'bi-line', 'name' => 'Line'],           // Bootstrap icon for Line
                  'wa.me' => ['icon' => 'bi-whatsapp', 'name' => 'WhatsApp'],      // Bootstrap icon for WhatsApp with wa.me domain
                  // Add more social media icons as needed
                ];

                // Loop through each message and display the corresponding icon
                for ($i = 1; $i <= 3; $i++) {
                  $messageVar = "message_$i";
                  $iconClass = 'bi-envelope-fill'; // Default icon for email
                  $displayName = ''; // Default display name

                  if (!empty($$messageVar)) {
                    $url = (strpos($$messageVar, 'https') !== false) ? $$messageVar : 'https://' . $$messageVar;
                    $domain = parse_url($url, PHP_URL_HOST);

                    // Check if it's a phone number
                    if (preg_match('/^\+?\d+$/', $domain)) {
                      $iconClass = 'bi-telephone'; // Bootstrap icon for phone
                      $displayName = 'Phone'; // Display name for phone
                    } else {
                      if (isset($socialMediaIcons[$domain])) {
                        $iconClass = $socialMediaIcons[$domain]['icon'];
                        $displayName = $socialMediaIcons[$domain]['name'];
                      }
                    }
                ?>
                    <a href="<?php echo $url; ?>" class="btn btn-lg fw-medium m-2" role="button">
                      <i class="bi <?php echo $iconClass; ?>"></i> <!-- Bootstrap icon here -->
                      <small><?php echo $displayName; ?></small>
                    </a>
                <?php
                  }
                }
                ?>
              </span>
            </div>
          </div>
          <div class="container">
            <hr class="border-4 rounded-pill my-3">
          </div>
          <div class="container mb-2">
            <div class="fw-medium">
              <p class="small" style="white-space: break-spaces; overflow: hidden;">
                <?php
                  if (!function_exists('getYouTubeVideoId')) {
                    function getYouTubeVideoId($urlCommentContact)
                    {
                      $videoIdContact = '';
                      $patternContact = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                      if (preg_match($patternContact, $urlCommentContact, $matchesContact)) {
                        $videoId = $matches[1];
                      }
                      return $videoIdContact;
                    }
                  }

                  $commentTextContact = isset($message_4) ? $message_4 : '';

                  if (!empty($commentTextContact)) {
                    $paragraphsContact = explode("\n", $commentTextContact);

                    foreach ($paragraphsContact as $indexContact => $paragraphContact) {
                      $messageTextWithoutTagsContact = strip_tags($paragraphContact);
                      $patternContact = '/\bhttps?:\/\/\S+/i';

                      $formattedTextContact = preg_replace_callback($patternContact, function ($matchesContact) {
                        $urlCommentContact = htmlspecialchars($matchesContact[0]);

                        if (preg_match('/\.(png|jpg|jpeg|webp)$/i', $urlCommentContact)) {
                          return '<a href="' . $urlCommentContact . '" target="_blank"><img class="w-100 h-100 rounded-4 lazy-load" loading="lazy" data-src="' . $urlCommentContact . '" alt="Image"></a>';
                        } elseif (strpos($urlCommentContact, 'youtube.com') !== false) {
                          $videoIdContact = getYouTubeVideoId($urlCommentContact);
                          if ($videoIdContact) {
                            $thumbnailUrlContact = 'https://img.youtube.com/vi/' . $videoIdContact . '/default.jpg';
                            return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' . $videoIdContact . '" frameborder="0" allowfullscreen></iframe></div>';
                          } else {
                            return '<a href="' . $urlCommentContact . '">' . $urlCommentContact . '</a>';
                          }
                        } else {
                          return '<a href="' . $urlCommentContact . '">' . $urlCommentContact . '</a>';
                        }
                      }, $messageTextWithoutTagsContact);
                  
                      echo "<p class='small' style=\"white-space: break-spaces; overflow: hidden;\">$formattedTextContact</p>";
                    }
                  } else {
                    echo "Sorry, no text...";
                  }
                ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End of Contact Modal -->