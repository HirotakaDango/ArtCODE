<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
    <div class="modal-content rounded-4 shadow border-0">
      <div class="modal-header border-0">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Comments</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe class="mt-2 rounded-3" style="width: 100%; height: 500px;" src="<?php echo $url_comment; ?>"></iframe>
      </div>
    </div>
  </div>
</div>