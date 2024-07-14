    <div class="modal fade" id="modalMessage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down" style="max-width: 660px;">
        <div class="modal-content rounded-min-5 position-relative">
          <button type="button" class="btn border-0 position-absolute top-0 start-0" data-bs-dismiss="modal"><i class="bi bi-x-circle fs-5" style="-webkit-text-stroke: 1px;"></i></button>
          <?php
            $url_message = "/messages/modal.php?userid=" . $id;
          ?>
          <iframe class="w-100 chat-container rounded-min-5" src="<?php echo $url_message; ?>"></iframe>
        </div>
      </div>
    </div>
    <style>
      @media (min-width: 768px) {
        .chat-container {
          height: 500px;
        }
      }

      @media (max-width: 767px) {
        .chat-container {
          height: 100vh;
        }
      }
    </style>