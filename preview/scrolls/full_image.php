    <div class="modal fade p-0" id="originalImage" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-transparent p-0">
          <div class="container px-0">
            <div class="d-flex justify-content-center align-items-center vh-100">
              <div class="position-relative p-0">
                <img id="modalImage" class="w-mobile-100" style="max-height: 100vh;" src="" alt="Loading...">
                <button type="button" class="btn border-0 position-absolute end-0 top-0" data-bs-dismiss="modal">
                  <i class="bi bi-x fs-4" style="-webkit-text-stroke: 2px;"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <style>
      @media (max-width: 767px) {
        .w-mobile-100 {
          width: 100%;
        }
      }
    </style>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var modalElement = document.getElementById('originalImage');
        var imageElement = document.getElementById('modalImage');
        var imagePath = '/images/<?php echo $image["filename"]; ?>';
    
        modalElement.addEventListener('show.bs.modal', function () {
          imageElement.src = imagePath;
        });
    
        modalElement.addEventListener('hide.bs.modal', function () {
          imageElement.src = '';
        });
      });
    </script>