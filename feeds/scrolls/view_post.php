<div class="modal fade" id="viewPost<?php echo $image['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content border-0 rounded-0">
      <div class="container-fluid px-0 py-2" style="max-width: 750px;">
        <button type="button" class="btn border-0 text-start position-absolute star-0 top-0 p-2" data-bs-dismiss="modal"><i class="bi bi-chevron-left" style="-webkit-text-stroke: 2px;"></i></button>
      </div>
      <?php
        $domainPost = $_SERVER['HTTP_HOST'];
        $iframePostId = $image['id'];
        $url = "http://$domainPost/feeds/scrolls/main_post.php?artworkid=$iframePostId";
      ?>
      <iframe class="w-100 vh-100" src="<?php echo $url; ?>"></iframe>
    </div>
  </div>
</div>