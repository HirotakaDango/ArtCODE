              <!-- Preview Modal -->
              <div class="modal fade" id="previewLatestPopularModal" tabindex="-1" aria-labelledby="previewLatestPopularModal" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen m-0 p-0">
                  <div class="modal-content m-0 p-0 border-0 rounded-0">
                    <iframe id="modalLatestPopularIframe" class="w-100 h-100 border-0"></iframe>
                    <button type="button" class="btn btn-small btn-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?> fw-medium position-fixed bottom-0 end-0 m-2 rounded-pill" data-bs-dismiss="modal">close</button>
                  </div>
                </div>
              </div>
              <script>
                document.addEventListener('DOMContentLoaded', function () {
                  const modalElement = document.getElementById('previewLatestPopularModal');
                  const iframeElement = document.getElementById('modalLatestPopularIframe');
                  const iframeSrc = 'modal_latest_popular_load.php';

                  if (modalElement && iframeElement) {
                    modalElement.addEventListener('show.bs.modal', function () {
                      iframeElement.src = iframeSrc; // Load content when modal is opened
                    });

                    modalElement.addEventListener('hidden.bs.modal', function () {
                      iframeElement.src = ''; // Unload content when modal is closed
                    });
                  }
                });
              </script>
              <!-- End of Preview Modal -->