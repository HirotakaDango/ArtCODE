<!-- Preview Modal -->
<div class="modal fade" id="previewMangaModal" tabindex="-1" aria-labelledby="previewMangaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen m-0 p-0">
    <div class="modal-content m-0 p-0 border-0 rounded-0">
      <iframe class="w-100 h-100 border-0" src="<?php echo $url_preview; ?>"></iframe>
      <button type="button" class="btn btn-small btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium position-fixed bottom-0 end-0 m-2 rounded-pill" data-bs-dismiss="modal">close</button>
    </div>
  </div>
</div>