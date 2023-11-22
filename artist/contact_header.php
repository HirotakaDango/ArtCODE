    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-5 position-relative">
          <button type="button" class="btn border-0 position-absolute top-0 end-0 m-2 z-3" style="-webkit-text-stroke: 3px;" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
          <h5 class="text-center fw-medium pt-3">Contact <?php echo $artist; ?></h5>
          <div class="modal-body d-flex justify-content-center">
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
                  <a href="<?php echo $url; ?>" class="btn btn-lg fw-medium" role="button">
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
      </div>
    </div>
    <!-- End of Contact Modal -->