<div class="modal fade" id="viewPost<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content border-0 rounded-0">
      <div class="fixed-top">
        <button type="button" class="btn border-0 text-start position-absolute start-0 top-0 m-md-2 mt-1" data-bs-dismiss="modal">
          <i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i>
        </button>
      </div>
      <?php
        $url = "../main_post.php?artworkid=" . $image['id'];
      ?>
      <iframe class="w-100 vh-100" src="<?php echo $url; ?>"></iframe>
    </div>
  </div>
</div>