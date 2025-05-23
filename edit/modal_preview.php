<?php
  $db = new SQLite3($_SERVER['DOCUMENT_ROOT'].'/database.sqlite');
  $stmt = $db->prepare('SELECT display FROM users WHERE email = :email');
  $stmt->bindValue(':email', $_SESSION['email'], SQLITE3_TEXT);
  $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
  $display = $result['display'] ?? 'simple_view';
  $artworkId = htmlspecialchars($_GET['id']);
  $iframeSrc = "/demo_full_{$display}.php?artworkid={$artworkId}";
?>
<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen m-0 p-0">
    <div class="modal-content m-0 p-0 border-0 rounded-0 position-relative">
      <iframe id="previewIframe" class="border-0 w-100 vh-100" data-src="<?= $iframeSrc ?>"></iframe>
      <button type="button" class="btn btn-small btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium position-fixed bottom-0 end-0 m-2 rounded-pill" data-bs-dismiss="modal">close</button>
    </div>
  </div>
</div>
<script>
  const previewModal = document.getElementById('previewModal');
  const iframe = document.getElementById('previewIframe');

  previewModal.addEventListener('show.bs.modal', () => {
    if (!iframe.src) {
      iframe.src = iframe.dataset.src;
    }
  });

  previewModal.addEventListener('hidden.bs.modal', () => {
    // Optionally unload iframe when modal closes
    iframe.src = '';
  });
</script>